<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $table = 'school';

    protected $fillable = [
        'name', 
        'address', 
        'phone', 
        'email', 
        'domain_name', 
        'logo'
    ];
}