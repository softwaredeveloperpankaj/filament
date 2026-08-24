<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TeacherProfileSubject extends Pivot
{
    use HasFactory;

    protected $table = 'teacher_profile_subjects';

    public $incrementing = true;

    protected $fillable = [
        'teacher_profile_id',
        'subject_id',
    ];

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}