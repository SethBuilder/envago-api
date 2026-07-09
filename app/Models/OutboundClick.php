<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboundClick extends Model
{
    protected $fillable = ['activity_id', 'type', 'destination', 'category'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
