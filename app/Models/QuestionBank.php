<?php

namespace App\Models;

use App\Models\Concerns\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class QuestionBank extends Model
{
    use HasFactory, SoftDeletes, HasBranchScope;

    protected $fillable = [
        'branch_id',
        'subject_id',
        'name',
        'description',
        'type',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuestionBankItem::class)->orderBy('id');
    }

    public function examPapers(): HasMany
    {
        return $this->hasMany(ExamQuestionPaper::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBranchAndSubject($query, int $branchId, int $subjectId)
    {
        return $query->where('branch_id', $branchId)->where('subject_id', $subjectId);
    }

    // ─── Boot Logic (auto-generate slug) ───
    protected static function booted(): void
    {
        static::creating(function (QuestionBank $bank) {
            $bank->slug ??= Str::slug($bank->name);
            $bank->created_by ??= Auth::id();
        });

        static::updating(function (QuestionBank $bank) {
            if ($bank->isDirty('name') && !$bank->isDirty('slug')) {
                $bank->slug = Str::slug($bank->name);
            }
        });
    }

    // ─── Accessors ───
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getItemsActiveCountAttribute(): int
    {
        return $this->items()->where('is_active', true)->count();
    }    
}