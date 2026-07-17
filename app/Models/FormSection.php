<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSection extends Model
{
    protected $fillable = [
        'form_template_id', 'title', 'section_key', 'sort_order', 'is_active'
    ];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function fields()
    {
        return $this->hasMany(FormField::class, 'form_section_id')->orderBy('sort_order');
    }
}
