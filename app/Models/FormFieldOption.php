<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormFieldOption extends Model
{
    protected $fillable = [
        'form_field_id', 'label', 'value', 'sort_order', 'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function field()
    {
        return $this->belongsTo(FormField::class, 'form_field_id');
    }
}
