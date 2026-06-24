<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'activity_id', 'user_id', 'author_name', 'author_avatar_url',
        'rating', 'body', 'source', 'source_url', 'is_approved', 'scraped_at',
    ];

    protected $casts = ['is_approved' => 'boolean', 'scraped_at' => 'datetime'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
