<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FailedImportRow extends Model
{
    protected $fillable = ['data', 'import_id', 'validation_error'];
    protected $casts = ['data' => 'array'];

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}