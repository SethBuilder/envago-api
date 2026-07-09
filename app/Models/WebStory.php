<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebStory extends Model
{
    protected $fillable = ['article_id', 'slides', 'status', 'poster_image', 'published_at'];

    protected $casts = ['slides' => 'array', 'published_at' => 'datetime'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
