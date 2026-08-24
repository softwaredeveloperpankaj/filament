<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSection extends Model
{
    protected $fillable = ['branch_id', 'branch_class_id', 'section_id'];

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

    public function sectionSubjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class, 'class_section_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'section_subjects',
            'class_section_id',
            'subject_id',
        )->using(SectionSubject::class)
         ->withPivot(['teacher_profile_id', 'weekly_periods', 'is_elective', 'is_active'])
         ->withTimestamps();
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(SectionSubject::class);
    }    
}
