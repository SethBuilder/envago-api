<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::public()->with(['category', 'firstPhoto', 'firstVideo', 'provider', 'reviews']);

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $activities = $query->orderByRaw("CASE booking_method WHEN 'envago' THEN 1 WHEN 'lead' THEN 2 ELSE 3 END")
                           ->orderBy('created_at', 'desc')
                           ->paginate(12);

        return response()->json([
            'data' => $activities->map(fn($a) => [
                'id'               => $a->id,
                'name'             => $a->name,
                'slug'             => $a->slug,
                'price'            => $a->price,
                'pricing_type'     => $a->pricing_type,
                'pricing_unit'     => $a->pricing_unit,
                'unit_capacity'    => $a->unit_capacity,
                'duration_minutes' => $a->duration_minutes,
                'city'             => $a->city,
                'state'            => $a->state,
                'category'         => $a->category?->only(['id', 'name', 'slug']),
                'cover_url'        => $a->firstPhoto?->url,
                'first_video'      => !$a->firstPhoto && $a->firstVideo ? ['url' => $a->firstVideo->url, 'poster_url' => $a->firstVideo->poster_url] : null,
                'provider'         => $a->provider ? ['id' => $a->provider->id, 'business_name' => $a->provider->business_name, 'slug' => $a->provider->slug, 'logo_url' => $a->provider->logo_url] : null,
                'booking_method'   => $a->booking_method ?? 'envago',
                'link_url'         => $a->link_url,
                'avg_rating'       => $a->reviews->avg('rating') ? round($a->reviews->avg('rating'), 1) : null,
                'review_count'     => $a->reviews->count(),
                'recent_reviews'   => $a->reviews->take(4)->map(fn($r) => [
                    'author_name' => $r->author_name,
                    'body'        => $r->body,
                ]),
            ]),
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
                'total'        => $activities->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $activity = Activity::with(['category', 'media', 'provider', 'reviews', 'questions'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Only show schedules if provider is approved (Option A)
        $schedules = $activity->provider?->status === 'approved'
            ? $activity->schedules()
                ->where('date', '>=', Carbon::today())
                ->where('date', '<=', Carbon::today()->addDays(60))
                ->orderBy('date')
                ->orderBy('time')
                ->get()
                ->map(fn($s) => [
                    'id'           => $s->id,
                    'date'         => $s->date->toDateString(),
                    'time'         => substr($s->time, 0, 5),
                    'seats_total'  => $s->seats_total,
                    'seats_booked' => $s->seats_booked,
                    'seats_left'   => $s->seats_total - $s->seats_booked,
                ])
            : collect();

        return response()->json([
            'data' => [
                'id'               => $activity->id,
                'name'             => $activity->name,
                'slug'             => $activity->slug,
                'description'      => $activity->description,
                'highlights'       => $activity->highlights ?? [],
                'price'            => $activity->price,
                'pricing_type'     => $activity->pricing_type,
                'pricing_unit'     => $activity->pricing_unit,
                'unit_capacity'    => $activity->unit_capacity,
                'duration_minutes' => $activity->duration_minutes,
                'capacity'         => $activity->capacity,
                'city'             => $activity->city,
                'state'            => $activity->state,
                'category'         => $activity->category?->only(['id', 'name', 'slug']),
                'provider'         => $activity->provider ? [
                    'id'              => $activity->provider->id,
                    'business_name'   => $activity->provider->business_name,
                    'slug'            => $activity->provider->slug,
                    'logo_url'        => $activity->provider->logo_url,
                    'booking_enabled' => $activity->provider->status === 'approved',
                ] : null,
                'cover_url'        => $activity->media->whereIn('type', ['cover', 'gallery'])->first()?->url,
                'gallery'          => $activity->media->whereIn('type', ['cover', 'gallery'])->skip(1)->map(fn($m) => $m->url)->values(),
                'videos'           => $activity->media->where('type', 'video')->map(fn($m) => ['url' => $m->url, 'poster_url' => $m->poster_url, 'instagram_handle' => $m->instagram_handle])->values(),
                'media'            => $activity->media->map(fn($m) => [
                    'type'             => in_array($m->type, ['cover', 'gallery']) ? 'photo' : 'video',
                    'url'              => $m->url,
                    'poster_url'       => $m->poster_url,
                    'instagram_handle' => $m->instagram_handle,
                ])->values(),
                'booking_method'   => $activity->booking_method ?? 'envago',
                'link_url'         => $activity->link_url,
                'meeting_point'    => $activity->meeting_point,
                'meeting_point_lat'=> $activity->meeting_point_lat,
                'meeting_point_lng'=> $activity->meeting_point_lng,
                'avg_rating'       => $activity->reviews->avg('rating') ? round($activity->reviews->avg('rating'), 1) : null,
                'review_count'     => $activity->reviews->count(),
                'reviews'          => $activity->reviews->map(fn($r) => [
                    'author_name'       => $r->author_name,
                    'author_avatar_url' => $r->author_avatar_url,
                    'rating'            => $r->rating,
                    'body'              => $r->body,
                    'created_at'        => $r->created_at->toDateString(),
                ]),
                'schedules'        => $schedules,
                'questions'        => $activity->questions->map(fn($q) => [
                    'id'       => $q->id,
                    'label'    => $q->label,
                    'type'     => $q->type,
                    'options'  => $q->options ?? [],
                    'required' => $q->required,
                ]),
            ],
        ]);
    }
}