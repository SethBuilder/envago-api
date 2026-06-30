<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaraInstruction extends Model
{
    protected $fillable = ['context', 'instruction', 'active'];

    protected $casts = ['active' => 'boolean'];
}
