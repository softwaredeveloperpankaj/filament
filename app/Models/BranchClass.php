<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchClass extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'starting_roll_no',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
