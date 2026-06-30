<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScopeLead extends Model
{
    protected $fillable = [
        'session_id', 'business_name', 'location', 'offering',
        'price_range', 'whatsapp', 'summary', 'photos',
    ];

    protected $casts = ['photos' => 'array'];
}
