<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = ['user_id', 'business_name', 'slug', 'pix_key', 'bank_details', 'logo_url', 'verified_at', 'status', 'whatsapp_number'];

    protected static function booted(): void
    {
        static::creating(function ($provider) {
            if (!$provider->slug && $provider->business_name) {
                $base = \Illuminate\Support\Str::slug($provider->business_name);
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $provider->slug = $slug;
            }
        });
    }

    protected $casts = ['verified_at' => 'datetime', 'bank_details' => 'array'];

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

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
}
