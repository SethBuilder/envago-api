<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = ['activity_id', 'url', 'type', 'order'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
