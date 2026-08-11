<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSchedule extends Model
{
    use HasFactory;

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
                $schedule->end_time = $schedule->start_time->addMinutes($schedule->examSubject->duration_minutes);
            }
        });
    }

    public function getDurationAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }
}