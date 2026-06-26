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
    // This means switching to S3 only requires changing the disk config.
    public function getUrlAttribute(string $value): string
    {
        // Normalise: strip any stored domain prefix, keep just the storage path
        if (str_starts_with($value, 'http')) {
            if (preg_match('#/storage/(.+)$#', $value, $m)) {
                $value = $m[1];
            } else {
                return $value;
            }
        }

        if (config('filesystems.default') === 's3') {
            return Storage::disk('s3')->url($value);
        }

        return rtrim(config('app.api_url'), '/') . '/storage/' . $value;
    }
}
