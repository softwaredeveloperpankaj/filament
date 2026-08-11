<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionBank extends Model
{
    use HasFactory, SoftDeletes;

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
}