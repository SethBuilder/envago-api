<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'provider_id', 'category_id', 'name', 'slug', 'description',
        'price', 'duration_minutes', 'capacity', 'city', 'state',
        'meeting_point', 'meeting_point_lat', 'meeting_point_lng',
        'lat', 'lng', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'price' => 'decimal:2'];

    public function scopePublic($query)
    {
        return $query->where('is_active', true)
                     ->whereHas('provider', fn($q) => $q->where('status', 'approved'));
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class)->orderBy('order');
    }

    public function cover()
    {
        return $this->hasOne(Media::class)->where('type', 'cover')->orderBy('order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }
}
