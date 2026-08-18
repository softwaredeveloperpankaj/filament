<?php

namespace App\Models;

use App\Enums\AttemptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OnlineExamAttempt extends Model
{
    use HasFactory;
    // ❌ Removed HasBranchScope — no branch_id column on this table

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

    /* ────────────────────────────────────────
     * Relationships
     *──────────────────────────────────────── */

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

    // ✅ Fixed: proper Eloquent relationship instead of manually loading entry first
    public function student(): HasOneThrough
    {
        return $this->hasOneThrough(
            Student::class,
            ExamStudentEntry::class,
            'id',              // FK on exam_student_entries referencing this attempt
            'id',              // PK on students
            'exam_student_entry_id', // FK on this table
            'student_id'       // FK on exam_student_entries
        );
    }

    /* ────────────────────────────────────────
     * Scopes (custom — branch is indirect via exam)
     *──────────────────────────────────────── */

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->whereHas('exam', fn($q) => $q->where('branch_id', $branchId));
    }

    public function scopeForCurrentUserBranch(Builder $query): Builder
    {
        $branchId = Auth::user()?->branch_id;

        return $branchId
            ? $query->whereHas('exam', fn($q) => $q->where('branch_id', $branchId))
            : $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', AttemptStatus::IN_PROGRESS);
    }

    /* ────────────────────────────────────────
     * Boot Logic
     *──────────────────────────────────────── */

    protected static function booted(): void
    {
        static::creating(function (OnlineExamAttempt $attempt) {
            $attempt->status ??= AttemptStatus::NOT_STARTED;
            // ✅ Fixed: was $attempt->examSubject->examSubject->max_marks (double relation)
            $attempt->total_maximum ??= $attempt->examSubject?->max_marks ?? 0;
        });

        static::updating(function (OnlineExamAttempt $attempt) {
            if ($attempt->isDirty('status') && $attempt->status === AttemptStatus::SUBMITTED) {
                $attempt->submitted_at ??= now();
            }
            if ($attempt->isDirty('status') && $attempt->status === AttemptStatus::AUTO_SUBMITTED) {
                $attempt->ended_at ??= now();
            }
            if ($attempt->isDirty('status') && $attempt->status === AttemptStatus::TERMINATED) {
                $attempt->ended_at ??= now();
            }
        });
    }

    /* ────────────────────────────────────────
     * Auto-Grading
     *──────────────────────────────────────── */

    public function calculateAutoScore(): void
    {
        if (!$this->examSubject?->questionPaper) {
            return;
        }

        $paper = $this->examSubject->questionPaper;
        $answers = $this->answers ?? [];
        $score = [];
        $total = 0;

        foreach ($paper->questions as $question) {
            $answer = $answers[$question->id] ?? null;
            $marks = $question->assigned_marks ?? $question->marks;

            $obtained = 0;
            if ($question->isAutoGradable()) {
                $obtained = $this->gradeAnswer($answer, $question->correct_answer, $marks, $question->negative_marks);
            }

            $score[$question->id] = $obtained;
            $total += $obtained;
        }

        $this->update([
            'auto_score'     => $score,
            'total_obtained' => max(0, $total), // prevent negative totals from negative marking
            'percentage'     => $this->total_maximum > 0
                ? round((max(0, $total) / $this->total_maximum) * 100, 2)
                : 0,
        ]);
    }

    /**
     * Grade a single answer against the correct answer.
     * Handles: scalar answers, array answers (matching), case-insensitive text.
     */
    private function gradeAnswer($answer, $correct, $marks, $negative): float
    {
        if ($answer === null || $answer === '' || $answer === []) {
            return 0;
        }

        $isCorrect = match (true) {
            is_array($correct) && is_array($answer) => $this->arraysMatch($answer, $correct),
            is_array($correct) => in_array($this->normalize($answer), array_map(fn($c) => $this->normalize($c), $correct)),
            default => $this->normalize($answer) === $this->normalize($correct),
        };

        if ($isCorrect) {
            return (float) $marks;
        }

        return $negative > 0 ? -1 * (float) $negative : 0;
    }

    private function normalize($value): string
    {
        return strtolower(trim((string) $value));
    }

    private function arraysMatch(array $a, array $b): bool
    {
        $normalizedA = array_map(fn($v) => $this->normalize($v), $a);
        $normalizedB = array_map(fn($v) => $this->normalize($v), $b);

        sort($normalizedA);
        sort($normalizedB);

        return $normalizedA === $normalizedB;
    }

    /* ────────────────────────────────────────
     * Accessors
     *──────────────────────────────────────── */

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

    public function isInProgress(): bool
    {
        return $this->status === AttemptStatus::IN_PROGRESS;
    }

    public function canBeTerminated(): bool
    {
        return $this->status === AttemptStatus::IN_PROGRESS;
    }
}