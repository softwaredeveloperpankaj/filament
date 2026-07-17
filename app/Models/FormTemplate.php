<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormTemplate extends Model
{
    protected $fillable = [
        'branch_id',
        'active_version_id',
        'user_id',
        'registration_serial',
        'name',
        'slug',
        'type',
        'status',
        'is_active',
        'form_layout',
        'rollno_generation_scope',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sections()
    {
        return $this->hasMany(FormSection::class)->orderBy('sort_order');
    }
    public function versions()
    {
        return $this->hasMany(FormTemplateVersion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
