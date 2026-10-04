<?php

namespace App\Models;

use App\Models\Concerns\HasBranchScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class ExamSchedule extends Model
{
    use HasFactory, HasBranchScope;

    protected $fillable = [
        'exam_id',
        'exam_subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'room_number',
        'invigilator_id',
        'instructions',
    ];

    protected $casts = [
        'exam_date'  => 'date',
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function invigilator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invigilator_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(ExamMark::class);
    }

    protected static function booted(): void
    {
        static::creating(function (ExamSchedule $schedule) {
            if (empty($schedule->end_time) && $schedule->examSubject) {
                $schedule->end_time = $schedule->start_time->copy()->addMinutes($schedule->examSubject->duration_minutes);
            }

            $schedule->validateSchedule();
        });

        static::saving(function (ExamSchedule $schedule) {
            if ($schedule->isDirty(['exam_date', 'start_time', 'end_time', 'exam_subject_id', 'exam_id'])) {
                $schedule->validateSchedule();
            }
        });
    }

    public function getDurationAttribute(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function validateSchedule(): void
    {
        $exam = $this->exam;
        if (!$exam) {
            return;
        }

        $start = $this->normalizeTime($this->start_time);
        $end = $this->normalizeTime($this->end_time);

        if (!$start || !$end || $start->greaterThanOrEqualTo($end)) {
            throw ValidationException::withMessages([
                'start_time' => 'Start time must be before end time.',
                'end_time' => 'End time must be after the start time.',
            ]);
        }

        if (!$this->exam_date) {
            throw ValidationException::withMessages([
                'exam_date' => 'Exam date is required.',
            ]);
        }

        $examStart = Carbon::parse($exam->start_date);
        $examEnd = Carbon::parse($exam->end_date);
        $scheduleDate = Carbon::parse($this->exam_date);

        if ($scheduleDate->lt($examStart) || $scheduleDate->gt($examEnd)) {
            throw ValidationException::withMessages([
                'exam_date' => 'Exam date must be within the exam date range.',
            ]);
        }

        $duplicateQuery = static::query()
            ->where('exam_id', $this->exam_id)
            ->where('exam_subject_id', $this->exam_subject_id);

        if (!empty($this->id)) {
            $duplicateQuery->whereKeyNot($this->id);
        }

        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'exam_subject_id' => 'This subject is already scheduled for this exam.',
            ]);
        }

        $conflicts = static::query()
            ->where('exam_id', $this->exam_id)
            ->whereDate('exam_date', $this->exam_date)
            ->when($this->id, fn ($query) => $query->whereKeyNot($this->id))
            ->get();

        foreach ($conflicts as $conflict) {
            if ($conflict->id === $this->id) {
                continue;
            }

            if ($this->timesOverlap(
                $this->start_time,
                $this->end_time,
                $conflict->start_time,
                $conflict->end_time,
            )) {
                throw ValidationException::withMessages([
                    'start_time' => 'This time overlaps with another scheduled subject on the same date.',
                    'end_time' => 'This time overlaps with another scheduled subject on the same date.',
                ]);
            }
        }
    }

    protected function normalizeTime($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value);
    }

    protected function timesOverlap($startA, $endA, $startB, $endB): bool
    {
        $aStart = $this->normalizeTime($startA);
        $aEnd = $this->normalizeTime($endA);
        $bStart = $this->normalizeTime($startB);
        $bEnd = $this->normalizeTime($endB);

        if (!$aStart || !$aEnd || !$bStart || !$bEnd) {
            return false;
        }

        return $aStart->lt($bEnd) && $bStart->lt($aEnd);
    }
}