<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormTemplateVersion extends Model
{
    protected $fillable = [
        'form_template_id',
        'user_id',
        'version',
        'schema_json',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function sections()
    {
        return $this->hasMany(FormSection::class, 'form_template_version_id')
                    ->orderBy('sort_order');
    }    
}
