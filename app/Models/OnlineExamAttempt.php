<?php

namespace App\Models;

use App\Enums\AttemptStatus;
use App\Models\Concerns\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineExamAttempt extends Model
{
    use HasFactory, HasBranchScope;

    protected $fillable = [
        'exam_id',
        'exam_student_entry_id',
        'exam_subject_id',
        'started_at',
        'submitted_at',
        'ended_at',
        'status',
        'answers',
        'auto_score',
        'total_obtained',
        'total_maximum',
        'percentage',
        'time_spent_per_question',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'started_at'                => 'datetime',
        'submitted_at'              => 'datetime',
        'ended_at'                  => 'datetime',
        'status'                    => AttemptStatus::class,
        'answers'                   => 'array',
        'auto_score'                => 'array',
        'time_spent_per_question'   => 'array',
        'percentage'                => 'decimal:2',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ExamStudentEntry::class, 'exam_student_entry_id');
    }

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function student(): BelongsTo
    {
        return $this->entry->student();
    }

    protected static function booted(): void
    {
        static::creating(function (OnlineExamAttempt $attempt) {
            $attempt->status ??= AttemptStatus::NOT_STARTED;
            $attempt->total_maximum = $attempt->examSubject->examSubject->max_marks;
        });

        static::updating(function (OnlineExamAttempt $attempt) {
            if ($attempt->isDirty('status') && $attempt->status === AttemptStatus::SUBMITTED) {
                $attempt->submitted_at ??= now();
            }
            if ($attempt->isDirty('status') && $attempt->status === AttemptStatus::AUTO_SUBMITTED) {
                $attempt->ended_at ??= now();
            }
        });
    }

    public function calculateAutoScore(): void
    {
        if (!$this->examSubject->questionPaper) return;

        $paper = $this->examSubject->questionPaper;
        $answers = $this->answers ?? [];
        $score = [];
        $total = 0;

        foreach ($paper->questions as $question) {
            $answer = $answers[$question->id] ?? null;
            $correct = $question->correct_answer;
            $marks = $question->assigned_marks ?? $question->marks;

            $obtained = 0;
            if ($question->isAutoGradable()) {
                $obtained = $this->gradeAnswer($answer, $correct, $marks, $question->negative_marks);
            }

            $score[$question->id] = $obtained;
            $total += $obtained;
        }

        $this->update([
            'auto_score'     => $score,
            'total_obtained' => $total,
            'percentage'     => $this->total_maximum > 0 ? round(($total / $this->total_maximum) * 100, 2) : 0,
        ]);
    }

    private function gradeAnswer($answer, $correct, $marks, $negative): int
    {
        if ($answer === null || $answer === '') return 0;
        if ($answer === $correct) return $marks;
        return max(0, -$negative);
    }

    public function getTimeSpentAttribute(): int
    {
        if ($this->started_at && $this->ended_at) {
            return $this->started_at->diffInSeconds($this->ended_at);
        }
        if ($this->started_at) {
            return $this->started_at->diffInSeconds(now());
        }
        return 0;
    }
}