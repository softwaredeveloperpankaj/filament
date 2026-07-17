<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionSubject extends Model
{
    protected $fillable = [
        'branch_id',
        'class_section_id',
        'branch_class_id',
        'section_id',
        'subject_id',
        'teacher_profile_id'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function branchClass(): BelongsTo
    {
        return $this->belongsTo(BranchClass::class, 'branch_class_id');
    }
    
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_profile_id');
    }

}
