<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityQuestion extends Model
{
    protected $fillable = ['activity_id', 'label', 'type', 'options', 'required', 'sort_order'];

    protected $casts = ['options' => 'array', 'required' => 'boolean'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
