<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchData extends Model
{
    protected $table = 'research_data';

    protected $fillable = ['destination', 'source', 'raw_data', 'synthesized'];

    protected $casts = [
        'raw_data' => 'array',
        'synthesized' => 'boolean',
    ];
}
