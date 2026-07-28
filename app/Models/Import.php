<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Import extends Model
{
    protected $fillable = [
        'completed_at', 'file_name', 'file_path',
        'importer', 'processed_rows', 'total_rows',
        'successful_rows', 'user_id',
    ];

    protected $casts = ['completed_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function failedRows(): HasMany
    {
        return $this->hasMany(FailedImportRow::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->completed_at) return 'Completed';
        if ($this->processed_rows > 0) return 'In Progress';
        return 'Pending';
    }

    public function getDurationAttribute(): ?string
    {
        if (! $this->completed_at) return null;
        $s = $this->created_at->diffInSeconds($this->completed_at);
        return $s < 60 ? "{$s}s" : floor($s/60)."m ".($s%60)."s";
    }

    public function getFailedRowsCountAttribute(): int
    {
        return $this->total_rows - $this->successful_rows;
    }
}