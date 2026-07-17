<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormTemplateVersion extends Model
{
    protected $fillable = [
        'form_template_id',
        'published_by',
        'version',
        'schema_json',
        'is_active',
        'published_at',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class);
    }
}
