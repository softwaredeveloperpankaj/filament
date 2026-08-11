<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'subject_id',
        'max_marks',
        'pass_marks',
        'theory_marks',
        'practical_marks',
        'duration_minutes',
        'paper_structure',
        'is_graded',
        'display_order',
    ];

    protected $casts = [
        'paper_structure' => 'array',
        'is_graded'       => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class)->orderBy('exam_date');
    }

    public function questionPaper(): HasOne
    {
        return $this->hasOne(ExamQuestionPaper::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(ExamMark::class);
    }

    protected static function booted(): void
    {
        static::creating(function (ExamSubject $es) {
            if ($es->display_order === null) {
                $es->display_order = $es->exam->subjects()->max('display_order') ?? 0 + 1;
            }
        });
    }
}