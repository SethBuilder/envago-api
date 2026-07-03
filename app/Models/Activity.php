<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'provider_id', 'category_id', 'name', 'slug', 'description',
        'price', 'pricing_type', 'pricing_unit', 'unit_capacity', 'duration_minutes', 'capacity', 'city', 'state',
        'meeting_point', 'meeting_point_lat', 'meeting_point_lng',
        'lat', 'lng', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'price' => 'decimal:2'];

    public function isPricedPerUnit(): bool
    {
        return $this->pricing_type === 'per_unit';
    }

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

    public function firstVideo()
    {
        return $this->hasOne(Media::class)->where('type', 'video')->orderBy('order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }
}
