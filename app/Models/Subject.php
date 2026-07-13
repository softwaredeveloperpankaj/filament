<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['branch_id', 'name', 'code'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
