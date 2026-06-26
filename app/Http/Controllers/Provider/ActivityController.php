<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Media;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ActivityController extends Controller
{
    public function create()
    {
        $provider   = auth()->user()->provider;
        $categories = Category::orderBy('name')->get();
        return view('provider.activity-create', compact('provider', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:120',
            'category_id'       => 'nullable|exists:categories,id',
            'description'       => 'nullable|string|max:2000',
            'price'             => 'required|numeric|min:1|max:99999',
            'duration_minutes'  => 'required|integer|min:15|max:1440',
            'capacity'          => 'required|integer|min:1|max:999',
            'city'              => 'required|string|max:80',
            'state'             => 'required|string|size:2',
            'groups'            => 'required|array|min:1',
            'groups.*.days'     => 'required|array|min:1',
            'groups.*.days.*'   => 'integer|between:0,6',
            'groups.*.times'    => 'required|array|min:1',
            'groups.*.times.*'  => 'date_format:H:i',
            'photos'            => 'required|array|min:1',
            'photos.*'          => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $provider = auth()->user()->provider;

        $slug = Str::slug($request->name);
        $base = $slug;
        $i    = 2;
        while (Activity::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $activity = Activity::create([
            'provider_id'      => $provider->id,
            'category_id'      => $request->category_id,
            'name'             => $request->name,
            'slug'             => $slug,
            'description'      => $request->description,
            'price'            => $request->price,
            'duration_minutes' => $request->duration_minutes,
            'capacity'         => $request->capacity,
            'city'             => $request->city,
            'state'            => strtoupper($request->state),
            'is_active'        => true,
        ]);

        // Schedules from groups
        $today     = Carbon::today();
        $schedules = [];

        foreach ($request->groups as $group) {
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

        // Photos
        if ($request->hasFile('photos')) {
            $disk  = config('filesystems.default') === 's3' ? 's3' : 'public';
            $order = 0;
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('activities/' . $activity->id, ['disk' => $disk, 'visibility' => 'public']);
                $url  = Storage::disk($disk)->url($path);
                Media::create([
                    'activity_id' => $activity->id,
                    'url'         => $url,
                    'type'        => $order === 0 ? 'cover' : 'gallery',
                    'order'       => $order++,
                ]);
            }
        }

        return response()->json([
            'redirect' => route('provider.activities'),
            'message'  => 'Passeio "' . $activity->name . '" criado com ' . count($schedules) . ' horarios.',
        ]);
    }
}
