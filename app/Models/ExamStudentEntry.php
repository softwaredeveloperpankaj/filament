<?php

namespace App\Models;

use App\Enums\ExamEntryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamStudentEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'exam_id',
        'student_id',
        'section_id',
        'roll_no',
        'status',
        'admit_card_printed',
        'admit_card_printed_at',
        'admit_card_data',
    ];

    protected $casts = [
        'status'                  => ExamEntryStatus::class,
        'admit_card_printed'      => 'boolean',
        'admit_card_printed_at'   => 'datetime',
        'admit_card_data'         => 'array',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(ExamMark::class);
    }

    public function onlineAttempts(): HasMany
    {
        return $this->hasMany(OnlineExamAttempt::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(ExamResult::class);
    }

    public function schedules(): HasMany
    {
        return $this->exam->schedules()->where('exam_subject_id', $this->marks->pluck('exam_subject_id'));
    }

    protected static function booted(): void
    {
        static::creating(function (ExamStudentEntry $entry) {
            $entry->roll_no ??= $entry->student->roll_no;
            $entry->status ??= ExamEntryStatus::ENROLLED;
        });

        static::updated(function (ExamStudentEntry $entry) {
            if ($entry->wasChanged('status') && $entry->status === ExamEntryStatus::APPEARED) {
                $entry->student->increment('exams_appeared_count');
            }
        });
    }

    public function generateAdmitCardData(): array
    {
        return [
            'student_name'    => $this->student->name,
            'roll_no'         => $this->roll_no,
            'exam_name'       => $this->exam->name,
            'branch'          => $this->exam->branch->name,
            'class'           => $this->exam->branchClass->name,
            'section'         => $this->section->name,
            'schedule'        => $this->exam->schedules->map(fn($s) => [
                'subject'     => $s->examSubject->subject->name,
                'date'        => $s->exam_date->format('d M Y'),
                'time'        => $s->start_time->format('H:i') . ' - ' . $s->end_time->format('H:i'),
                'room'        => $s->room_number,
                'instructions'=> $s->instructions,
            ]),
            'instructions'    => $this->exam->instructions,
            'generated_at'    => now()->toDateTimeString(),
        ];
    }

    public function markAdmitCardPrinted(): void
    {
        $this->update([
            'admit_card_printed'     => true,
            'admit_card_printed_at'  => now(),
            'admit_card_data'        => $this->generateAdmitCardData(),
            'status'                 => ExamEntryStatus::ADMIT_CARD_GENERATED,
        ]);
    }
}