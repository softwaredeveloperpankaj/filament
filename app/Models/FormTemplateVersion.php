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

    public function template()
    {
        return $this->belongsTo(FormTemplate::class);
    }
}
