<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $fillable = [
        'form_template_id',
        'form_section_id',
        'field_key',
        'label',
        'type',
        'placeholder',
        'help_text',
        'sort_order',
        'is_required',
        'option_layout',
        'is_active',
        'validation_rules',
        'settings',
        'visibility_conditions',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'settings' => 'array',
        'visibility_conditions' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'option_layout' => 'string',
    ];

    public function section()
    {
        return $this->belongsTo(FormSection::class, 'form_section_id');
    }

    public function options()
    {
        return $this->hasMany(FormFieldOption::class)->orderBy('sort_order');
    }

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }
}
