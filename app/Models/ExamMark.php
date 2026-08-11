<?php

namespace App\Models;

use App\Enums\MarksSource;
use App\Models\Concerns\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ExamMark extends Model
{
    use HasFactory, HasBranchScope;

    protected $fillable = [
        'exam_id',
        'exam_student_entry_id',
        'exam_subject_id',
        'exam_schedule_id',
        'theory_obtained',
        'theory_maximum',
        'practical_obtained',
        'practical_maximum',
        'internal_obtained',
        'internal_maximum',
        'total_obtained',
        'total_maximum',
        'percentage',
        'grade',
        'grade_remarks',
        'graded_by',
        'graded_at',
        'source',
        'sub_question_marks',
        'is_locked',
    ];

    protected $casts = [
        'percentage'           => 'decimal:2',
        'graded_at'            => 'datetime',
        'sub_question_marks'   => 'array',
        'is_locked'            => 'boolean',
        'source'               => MarksSource::class,
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

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function student(): BelongsTo
    {
        return $this->entry->student();
    }

    protected static function booted(): void
    {
        static::saving(function (ExamMark $mark) {
            // Auto-calculate totals
            $mark->total_obtained = $mark->theory_obtained + $mark->practical_obtained + $mark->internal_obtained;
            $mark->total_maximum  = $mark->theory_maximum + $mark->practical_maximum + $mark->internal_maximum;
            $mark->percentage     = $mark->total_maximum > 0
                ? round(($mark->total_obtained / $mark->total_maximum) * 100, 2)
                : 0;

            // Auto-set pass/fail based on subject pass marks
            $passMarks = $mark->examSubject->pass_marks;
            $mark->is_passed = $mark->total_obtained >= $passMarks;

            // Set grader if not set
            if ($mark->isDirty(['theory_obtained', 'practical_obtained', 'internal_obtained']) && !$mark->graded_by) {
                $mark->graded_by = Auth::id();
                $mark->graded_at = now();
            }
        });

        static::saved(function (ExamMark $mark) {
            // Recalculate aggregate result
            $mark->entry->recalculateResult();
        });
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($this->is_locked) return false;
        return $user->hasAnyRole(['super_admin', 'admin', 'principal', 'teacher'])
            && $user->branch_id === $this->exam->branch_id;
    }
}