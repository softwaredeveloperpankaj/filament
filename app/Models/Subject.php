<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['branch_id', 'name', 'code'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(TeacherProfile::class, 'teacher_profile_subjects', 'subject_id', 'teacher_profile_id')->withTimestamps();
    }
    
    public function sectionSubjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class, 'subject_id');
    }    
}
