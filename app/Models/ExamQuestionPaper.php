<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestionPaper extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'exam_subject_id',
        'question_bank_id',
        'selected_questions',
        'total_marks',
        'sections',
        'instructions',
        'is_shuffled',
        'generated_by',
    ];

    protected $casts = [
        'selected_questions' => 'array',
        'sections'           => 'array',
        'is_shuffled'        => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getQuestionsAttribute(): \Illuminate\Support\Collection
    {
        if (empty($this->selected_questions)) {
            return collect();
        }

        return QuestionBankItem::whereIn('id', array_keys($this->selected_questions))
            ->get()
            ->mapWithKeys(fn($q) => [$q->id => $q])
            ->map(function ($question, $id) {
                $assignedMarks = $this->selected_questions[$id] ?? $question->marks;
                $question->assigned_marks = $assignedMarks;
                return $question;
            });
    }

    protected static function booted(): void
    {
        static::saving(function (ExamQuestionPaper $paper) {
            $paper->total_marks = collect($paper->selected_questions ?? [])->sum();
        });
    }
}