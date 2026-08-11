<?php

namespace App\Models;

use App\Enums\QuestionType;
use App\Enums\DifficultyLevel;
use App\Models\Concerns\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionBankItem extends Model
{
    use HasFactory, HasBranchScope;

    protected $fillable = [
        'question_bank_id',
        'subject_id',
        'topic_id',
        'question_type',
        'question_text',
        'options',
        'correct_answer',
        'marks',
        'negative_marks',
        'difficulty',
        'tags',
        'explanation',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'options'          => 'array',
        'correct_answer'   => 'array',
        'tags'             => 'array',
        'is_active'        => 'boolean',
        'question_type'    => QuestionType::class,
        'difficulty'       => DifficultyLevel::class,
    ];

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('question_type', $type);
    }

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public function isAutoGradable(): bool
    {
        return in_array($this->question_type, [
            QuestionType::MCQ,
            QuestionType::TRUE_FALSE,
            QuestionType::FILL_BLANK,
            QuestionType::MATCHING,
        ]);
    }
}