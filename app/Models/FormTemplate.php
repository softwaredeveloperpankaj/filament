<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function sections(): HasMany
    {
        return $this->hasMany(FormSection::class)->orderBy('sort_order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FormTemplateVersion::class, 'form_template_id');
    }

    // The currently active version
    public function activeVersion(): HasOne
    {
        return $this->hasOne(FormTemplateVersion::class)->where('is_active', true)->orderByDesc('version');
    }    

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
