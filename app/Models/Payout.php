<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = ['provider_id', 'booking_id', 'amount', 'commission', 'status', 'paid_at'];

    protected $casts = ['paid_at' => 'datetime', 'amount' => 'decimal:2', 'commission' => 'decimal:2'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
