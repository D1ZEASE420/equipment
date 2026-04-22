<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['name', 'email', 'group', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];
}
