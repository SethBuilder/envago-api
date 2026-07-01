<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'schedule_id', 'seats', 'total_price',
        'status', 'cancellation_reason', 'payment_ref', 'confirmed_at',
        'guest_name', 'guest_email', 'guest_cpf', 'guest_whatsapp', 'reference', 'payout_id',
    ];

    protected $casts = ['confirmed_at' => 'datetime', 'total_price' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }
}
