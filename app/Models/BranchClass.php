<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchClass extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'starting_roll_no',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class, 'branch_class_id');
    }

    public function sectionSubjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class, 'branch_class_id');
    }    
}
