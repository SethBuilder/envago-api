<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Media;
use App\Models\Provider;
use App\Models\Schedule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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
    public function createActivity(Provider $provider, array $data, array $photos = []): Activity
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

        $activity = Activity::create([
            'provider_id'       => $provider->id,
            'category_id'       => $categoryId,
            'name'              => $data['name'],
            'slug'              => $slug,
            'description'       => $data['description'] ?? null,
            'price'             => $data['price'],
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

        return $activity;
    }

    /**
     * Update an activity, regenerate schedules (preserving booked slots), manage photos.
     *
     * @param  UploadedFile[]|string[]  $newPhotos
     * @param  int[]                    $deleteMediaIds
     */
    public function updateActivity(Activity $activity, array $data, array $newPhotos = [], array $deleteMediaIds = []): Activity
    {
        $activity->update([
            'category_id'       => $data['category_id'] ?? $activity->category_id,
            'name'              => $data['name'],
            'description'       => $data['description'] ?? null,
            'price'             => $data['price'],
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
     * Store photos and create Media records.
     * Accepts UploadedFile objects (dashboard) or absolute temp file paths (Lara onboarding).
     */
    private function storePhotos(Activity $activity, array $photos): void
    {
        if (empty($photos)) return;

        $disk     = config('filesystems.default') === 's3' ? 's3' : 'public';
        $order    = ($activity->media()->max('order') ?? -1) + 1;
        $hasCover = $activity->media()->where('type', 'cover')->exists();

        $nameSlug  = Str::slug($activity->name);
        $citySlug  = Str::slug($activity->city);
        $stateSlug = strtolower($activity->state);

        foreach ($photos as $photo) {
            $seoName = "{$nameSlug}-{$citySlug}-{$stateSlug}-{$order}";

            if ($photo instanceof UploadedFile) {
                $ext  = strtolower($photo->getClientOriginalExtension()) ?: 'jpg';
                $path = $photo->storeAs('activities/' . $activity->id, "{$seoName}.{$ext}", ['disk' => $disk, 'visibility' => 'public']);
            } else {
                // Absolute path to temp file — move to permanent storage with SEO name
                $ext  = strtolower(pathinfo($photo, PATHINFO_EXTENSION)) ?: 'jpg';
                $dest = 'activities/' . $activity->id . '/' . "{$seoName}.{$ext}";
                Storage::disk($disk)->put($dest, file_get_contents($photo), 'public');
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
