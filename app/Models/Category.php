<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon_path'];

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
