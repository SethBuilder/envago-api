<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = ['activity_id', 'url', 'type', 'order'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    // `url` column stores a storage path (e.g. activities/49/file.jpg).
    // Legacy records may have a full URL — those are returned as-is.
    public function getUrlAttribute(string $value): string
    {
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }
}
