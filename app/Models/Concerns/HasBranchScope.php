<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasBranchScope
{
    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForCurrentUserBranch(Builder $query): Builder
    {
        return $query->where('branch_id', auth()->user()?->branch_id);
    }
}