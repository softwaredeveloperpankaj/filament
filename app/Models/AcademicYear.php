<?php

namespace App\Models;

use App\Enums\AcademicYearStatus;
use App\Models\Concerns\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use HasFactory, HasBranchScope, SoftDeletes;

    protected $fillable = [
        'name',              // e.g. "2025-2026"
        'start_date',
        'end_date',
        'is_current',
        'status',            // active, archived, upcoming
        'settings',          // JSON: term structure, grading scale, etc.
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_current' => 'boolean',
        'settings'   => 'array',
        'status'     => AcademicYearStatus::class,   // enum below
    ];

    /* ---------- Relationships ---------- */

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    // public function feeStructures(): HasMany
    // {
    //     return $this->hasMany(FeeStructure::class);
    // }

    /* ---------- Scopes ---------- */

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true)->first();
    }

    public function scopeActive($query)
    {
        return $query->where('status', AcademicYearStatus::ACTIVE);
    }

    public function scopeCurrentOrLatest($query)
    {
        return $query->where('is_current', true)
            ->orWhere('end_date', '>=', now()->toDateString())
            ->latest('start_date');
    }

    /* ---------- Boot Logic ---------- */

    protected static function booted(): void
    {
        static::saving(function (AcademicYear $year) {
            // Ensure only one current year
            if ($year->is_current && $year->isDirty('is_current')) {
                static::where('is_current', true)
                    ->where('id', '!=', $year->id)
                    ->update(['is_current' => false]);
            }
        });

        static::deleting(function (AcademicYear $year) {
            if ($year->exams()->exists() || $year->students()->exists()) {
                return false; // prevent deletion if referenced
            }
        });
    }

    /* ---------- Accessors ---------- */

    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->start_date->year}-{$this->end_date->year}"
        );
    }

    public function getRouteKeyName(): string
    {
        return 'name'; // e.g. "2025-2026"
    }
}