<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = ['activity_id', 'date', 'time', 'seats_total', 'seats_booked'];

    protected $casts = ['date' => 'date'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getSeatsLeftAttribute(): int
    {
        return $this->seats_total - $this->seats_booked;
    }
}
