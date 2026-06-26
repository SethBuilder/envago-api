<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'schedule_id', 'provider_id', 'amount', 'booking_count',
        'currency', 'status', 'paid_at', 'confirmation_path', 'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
