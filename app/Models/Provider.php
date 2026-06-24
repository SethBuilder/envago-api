<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = ['user_id', 'business_name', 'pix_key', 'logo_url', 'verified_at'];

    protected $casts = ['verified_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }
}
