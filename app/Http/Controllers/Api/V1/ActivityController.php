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
        $query = Activity::with(['category', 'cover', 'provider', 'reviews'])
            ->where('is_active', true)
            ->whereHas('provider', fn($q) => $q->where('status', 'approved'));

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(12);

        return response()->json([
            'data' => $activities->map(fn($a) => [
                'id'               => $a->id,
                'name'             => $a->name,
                'slug'             => $a->slug,
                'price'            => $a->price,
                'duration_minutes' => $a->duration_minutes,
                'city'             => $a->city,
                'state'            => $a->state,
                'category'         => $a->category?->only(['id', 'name', 'slug']),
                'cover_url'        => $a->cover?->url,
                'provider'         => $a->provider ? ['id' => $a->provider->id, 'business_name' => $a->provider->business_name, 'slug' => $a->provider->slug, 'logo_url' => $a->provider->logo_url] : null,
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
        $activity = Activity::with(['category', 'media', 'provider', 'reviews'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Only show schedules if provider is approved (Option A)
        $schedules = $activity->provider?->status === 'approved'
            ? $activity->schedules()
                ->where('date', '>=', Carbon::today())
                ->where('date', '<=', Carbon::today()->addDays(14))
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
                'price'            => $activity->price,
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
                'cover_url'        => $activity->media->where('type', 'cover')->first()?->url,
                'gallery'          => $activity->media->where('type', 'gallery')->map(fn($m) => $m->url)->values(),
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
            ],
        ]);
    }
}