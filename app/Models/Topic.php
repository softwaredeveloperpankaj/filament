<?php

namespace App\Models;

use App\Models\Concerns\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Topic extends Model
{
    use HasFactory, SoftDeletes, HasBranchScope;

    protected $fillable = [
        'subject_id',
        'branch_id',
        'name',
        'slug',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'order'      => 'integer',
        'is_active'  => 'boolean',
    ];

    /* ────────────────────────────────────────
     * Relationships
     *──────────────────────────────────────── */

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function questionBankItems(): HasMany
    {
        return $this->hasMany(QuestionBankItem::class);
    }

    /* ────────────────────────────────────────
     * Scopes
     *──────────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /* ────────────────────────────────────────
     * Boot Logic
     *──────────────────────────────────────── */

    protected static function booted(): void
    {
        static::creating(function (Topic $topic) {
            $topic->slug ??= Str::slug($topic->name);
            $topic->order ??= static::query()->where('subject_id', $topic->subject_id)->max('order') + 1;
        });

        static::updating(function (Topic $topic) {
            if ($topic->isDirty('name') && !$topic->isDirty('slug')) {
                $topic->slug = Str::slug($topic->name);
            }
        });
    }

    /* ────────────────────────────────────────
     * Accessors / Helpers
     *──────────────────────────────────────── */

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->subject->name} › {$this->name}";
    }

    public function getQuestionsCountAttribute(): int
    {
        return $this->questionBankItems()->where('is_active', true)->count();
    }
}