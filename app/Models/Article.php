<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'slug', 'title', 'meta_title', 'meta_description',
        'destination', 'template_type', 'content', 'cover_image',
        'status', 'published_at',
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function story()
    {
        return $this->hasOne(WebStory::class);
    }
}
