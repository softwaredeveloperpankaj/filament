<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    // what subjects are taught in this class-section combo
    public function sectionSubjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class, 'section_id', 'section_id')
                    ->where('branch_class_id', $this->branch_class_id);
    }
}
