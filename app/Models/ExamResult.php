<?php

namespace App\Models;

use App\Enums\ExamEntryStatus;
use App\Enums\ResultStatus;
use App\Models\Concerns\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'exam_student_entry_id',
        'grand_total_obtained',
        'grand_total_maximum',
        'overall_percentage',
        'overall_grade',
        'rank_in_class',
        'rank_in_section',
        'rank_in_branch',
        'is_passed',
        'failed_subjects',
        'status',
        'published_by',
        'published_at',
        'subject_wise_breakdown',
    ];

    protected $casts = [
        'overall_percentage'       => 'decimal:2',
        'published_at'             => 'datetime',
        'failed_subjects'          => 'array',
        'subject_wise_breakdown'   => 'array',
        'status'                   => ResultStatus::class,
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ExamStudentEntry::class, 'exam_student_entry_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    protected static function booted(): void
    {
        static::creating(function (ExamResult $result) {
            $result->status ??= ResultStatus::DRAFT;
        });

        static::updated(function (ExamResult $result) {
            if ($result->wasChanged('status') && $result->status === ResultStatus::PUBLISHED) {
                $result->published_by = Auth::id();
                $result->published_at = now();
                $result->saveQuietly();

                // Update entry status
                $result->entry->update(['status' => ExamEntryStatus::APPEARED]);
            }
        });
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($this->status === ResultStatus::PUBLISHED) {
            return $user->hasRole('super_admin');
        }

        return $user->hasAnyRole(['super_admin', 'admin', 'principal'])
            && $user->branch_id === $this->exam->branch_id;
    }

    public function canTransitionTo(ResultStatus $status): bool
    {
        $current = $this->status instanceof ResultStatus ? $this->status : ResultStatus::tryFrom((string) $this->status ?? 'draft');

        if ($current === null) {
            return $status === ResultStatus::DRAFT;
        }

        return match ($current) {
            ResultStatus::DRAFT => in_array($status, [ResultStatus::PUBLISHED, ResultStatus::WITHHELD, ResultStatus::CANCELLED], true),
            ResultStatus::PUBLISHED => in_array($status, [ResultStatus::WITHHELD, ResultStatus::CANCELLED], true),
            ResultStatus::WITHHELD => in_array($status, [ResultStatus::PUBLISHED, ResultStatus::CANCELLED], true),
            ResultStatus::CANCELLED => false,
        };
    }

    public function publish(User $user): bool
    {
        if (!$this->canTransitionTo(ResultStatus::PUBLISHED)) {
            return false;
        }

        $this->update([
            'status'        => ResultStatus::PUBLISHED,
            'published_by'  => $user->id,
            'published_at'  => now(),
        ]);

        event(new \App\Events\ResultPublished($this->exam));

        return true;
    }

    public function withhold(User $user): bool
    {
        if (!$this->canTransitionTo(ResultStatus::WITHHELD)) {
            return false;
        }

        $this->update([
            'status'       => ResultStatus::WITHHELD,
            'published_by' => $user->id,
            'published_at' => now(),
        ]);

        return true;
    }

    public function cancel(User $user): bool
    {
        if (!$this->canTransitionTo(ResultStatus::CANCELLED)) {
            return false;
        }

        $this->update([
            'status'       => ResultStatus::CANCELLED,
            'published_by' => $user->id,
            'published_at' => now(),
        ]);

        return true;
    }

    public function getResultUrlAttribute(): string
    {
        return route('student.exam.result', ['exam' => $this->exam->slug, 'student' => $this->student->id]);
    }
}