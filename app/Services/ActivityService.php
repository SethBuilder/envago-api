<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Media;
use App\Models\Provider;
use App\Models\Schedule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ActivityService
{
    /**
     * Create an activity with schedules and photos.
     *
     * @param  Provider        $provider
     * @param  array           $data      Validated activity fields + 'groups' for schedules + 'category_slug'/'category_name' for Lara flow
     * @param  UploadedFile[]|string[]  $photos  UploadedFile objects (dashboard) or absolute temp file paths (Lara)
     */
    public function createActivity(Provider $provider, array $data, array $photos = [], array $videos = []): Activity
    {
        $slug = $this->generateSlug($data['name']);

        $categoryId = $data['category_id'] ?? null;
        if (!$categoryId && !empty($data['category_slug'])) {
            $category   = Category::firstOrCreate(
                ['slug' => $data['category_slug']],
                ['name' => $data['category_name'] ?? ucwords(str_replace('-', ' ', $data['category_slug']))]
            );
            $categoryId = $category->id;
        }

        $pricingType  = $data['pricing_type'] ?? 'per_person';
        $pricingUnit  = $pricingType === 'per_unit' ? ($data['pricing_unit'] ?? null) : null;
        $unitCapacity = $pricingType === 'per_unit' ? ($data['unit_capacity'] ?? null) : null;

        $activity = Activity::create([
            'provider_id'       => $provider->id,
            'category_id'       => $categoryId,
            'name'              => $data['name'],
            'slug'              => $slug,
            'description'       => $data['description'] ?? null,
            'price'             => $data['price'],
            'pricing_type'      => $pricingType,
            'pricing_unit'      => $pricingUnit,
            'unit_capacity'     => $unitCapacity,
            'duration_minutes'  => $data['duration_minutes'],
            'capacity'          => $data['capacity'],
            'city'              => $data['city'],
            'state'             => strtoupper($data['state']),
            'meeting_point'     => $data['meeting_point'] ?? null,
            'meeting_point_lat' => $data['meeting_point_lat'] ?? null,
            'meeting_point_lng' => $data['meeting_point_lng'] ?? null,
            'is_active'         => true,
        ]);

        $this->generateSchedules($activity, $data['groups'] ?? []);
        $this->storePhotos($activity, $photos);
        $this->storeVideos($activity, $videos);

        return $activity;
    }

    /**
     * Update an activity, regenerate schedules (preserving booked slots), manage photos.
     *
     * @param  UploadedFile[]|string[]  $newPhotos
     * @param  int[]                    $deleteMediaIds
     */
    public function updateActivity(Activity $activity, array $data, array $newPhotos = [], array $deleteMediaIds = [], array $newVideos = []): Activity
    {
        $pricingType  = $data['pricing_type'] ?? $activity->pricing_type;
        $pricingUnit  = $pricingType === 'per_unit' ? ($data['pricing_unit'] ?? $activity->pricing_unit) : null;
        $unitCapacity = $pricingType === 'per_unit' ? ($data['unit_capacity'] ?? $activity->unit_capacity) : null;

        $activity->update([
            'category_id'       => $data['category_id'] ?? $activity->category_id,
            'name'              => $data['name'],
            'description'       => $data['description'] ?? null,
            'price'             => $data['price'],
            'pricing_type'      => $pricingType,
            'pricing_unit'      => $pricingUnit,
            'unit_capacity'     => $unitCapacity,
            'duration_minutes'  => $data['duration_minutes'],
            'capacity'          => $data['capacity'],
            'city'              => $data['city'],
            'state'             => strtoupper($data['state']),
            'meeting_point'     => $data['meeting_point'] ?? null,
            'meeting_point_lat' => $data['meeting_point_lat'] ?? null,
            'meeting_point_lng' => $data['meeting_point_lng'] ?? null,
        ]);

        if (!empty($deleteMediaIds)) {
            $toDelete = Media::where('activity_id', $activity->id)
                ->whereIn('id', $deleteMediaIds)
                ->get();
            foreach ($toDelete as $m) {
                Storage::disk('public')->delete($m->url);
                $m->delete();
            }
        }

        $this->storePhotos($activity, $newPhotos);
        $this->storeVideos($activity, $newVideos);

        // Regenerate future schedules — preserve slots that already have bookings
        $today = Carbon::today();
        $activity->schedules()
            ->where('date', '>=', $today)
            ->where('seats_booked', 0)
            ->delete();

        foreach ($data['groups'] ?? [] as $group) {
            $days  = array_map('intval', $group['days']);
            $times = $group['times'];
            for ($d = 0; $d < 60; $d++) {
                $date = $today->copy()->addDays($d);
                if (!in_array($date->dayOfWeek, $days)) continue;
                foreach ($times as $time) {
                    Schedule::firstOrCreate(
                        ['activity_id' => $activity->id, 'date' => $date->toDateString(), 'time' => $time . ':00'],
                        ['seats_total' => $activity->capacity, 'seats_booked' => 0]
                    );
                }
            }
        }

        return $activity;
    }

    private function generateSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 2;
        while (Activity::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function generateSchedules(Activity $activity, array $groups): void
    {
        if (empty($groups)) return;

        $today     = Carbon::today();
        $schedules = [];

        foreach ($groups as $group) {
            $days  = array_map('intval', $group['days']);
            $times = $group['times'];
            for ($d = 0; $d < 60; $d++) {
                $date = $today->copy()->addDays($d);
                if (!in_array($date->dayOfWeek, $days)) continue;
                foreach ($times as $time) {
                    $schedules[] = [
                        'activity_id'  => $activity->id,
                        'date'         => $date->toDateString(),
                        'time'         => $time . ':00',
                        'seats_total'  => $activity->capacity,
                        'seats_booked' => 0,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
            }
        }

        if ($schedules) {
            Schedule::insert($schedules);
        }
    }

    /**
     * Store videos and create Media records.
     * Accepts UploadedFile objects (dashboard) or ['path' => ..., 'poster_url' => ...] arrays (Lara onboarding).
     */
    private function storeVideos(Activity $activity, array $videos): void
    {
        if (empty($videos)) return;

        $order = ($activity->media()->max('order') ?? -1) + 1;

        $providerSlug = Str::slug($activity->provider->business_name ?? '');
        $nameSlug     = Str::slug($activity->name);
        $citySlug     = Str::slug($activity->city);
        $stateSlug    = strtolower($activity->state);

        foreach ($videos as $video) {
            $seoName         = "{$providerSlug}-{$nameSlug}-{$citySlug}-{$stateSlug}-video-{$order}";
            $posterUrl       = null;
            $instagramHandle = null;

            if ($video instanceof UploadedFile) {
                $ext  = strtolower($video->getClientOriginalExtension()) ?: 'mp4';
                $path = $video->storeAs('activities/' . $activity->id, "{$seoName}.{$ext}", ['disk' => 'public']);
            } elseif (is_array($video)) {
                $ext             = strtolower(pathinfo($video['path'], PATHINFO_EXTENSION)) ?: 'mp4';
                $dest            = 'activities/' . $activity->id . '/' . "{$seoName}.{$ext}";
                Storage::disk('public')->put($dest, file_get_contents($video['path']));
                $path            = $dest;
                $instagramHandle = $video['instagram_handle'] ?? null;

                // Download poster from Apify/CDN and store locally so the URL never expires
                $posterUrl = null;
                $rawPoster = $video['poster_url'] ?? null;
                if ($rawPoster && filter_var($rawPoster, FILTER_VALIDATE_URL)) {
                    try {
                        $response = Http::timeout(15)->get($rawPoster);
                        if ($response->successful()) {
                            $posterDest = 'activities/' . $activity->id . '/' . "{$seoName}-poster.jpg";
                            Storage::disk('public')->put($posterDest, $response->body());
                            $posterUrl = $posterDest;
                        }
                    } catch (\Throwable $e) {
                        Log::warning('poster download failed', ['url' => $rawPoster, 'err' => $e->getMessage()]);
                    }
                }
            } else {
                continue;
            }

            Media::create([
                'activity_id'      => $activity->id,
                'url'              => $path,
                'poster_url'       => $posterUrl,
                'instagram_handle' => $instagramHandle,
                'type'             => 'video',
                'order'            => $order++,
            ]);
        }
    }

    /**
     * Store photos and create Media records.
     * Accepts UploadedFile objects (dashboard) or absolute temp file paths (Lara onboarding).
     */
    private function storePhotos(Activity $activity, array $photos): void
    {
        if (empty($photos)) return;

        $order    = ($activity->media()->max('order') ?? -1) + 1;
        $hasCover = $activity->media()->where('type', 'cover')->exists();

        $providerSlug = Str::slug($activity->provider->business_name ?? '');
        $nameSlug     = Str::slug($activity->name);
        $citySlug     = Str::slug($activity->city);
        $stateSlug    = strtolower($activity->state);

        foreach ($photos as $photo) {
            $seoName = "{$providerSlug}-{$nameSlug}-{$citySlug}-{$stateSlug}-{$order}";

            if ($photo instanceof UploadedFile) {
                $ext  = strtolower($photo->getClientOriginalExtension()) ?: 'jpg';
                $path = $photo->storeAs('activities/' . $activity->id, "{$seoName}.{$ext}", ['disk' => 'public']);
            } else {
                // Absolute path to temp file — move to permanent storage with SEO name
                $ext  = strtolower(pathinfo($photo, PATHINFO_EXTENSION)) ?: 'jpg';
                $dest = 'activities/' . $activity->id . '/' . "{$seoName}.{$ext}";
                Storage::disk('public')->put($dest, file_get_contents($photo));
                $path = $dest;
            }

            Media::create([
                'activity_id' => $activity->id,
                'url'         => $path,
                'type'        => $hasCover ? 'gallery' : 'cover',
                'order'       => $order++,
            ]);
            $hasCover = true;
        }
    }
}
