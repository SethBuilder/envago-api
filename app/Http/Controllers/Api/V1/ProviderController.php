<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function show(string $slug, Request $request)
    {
        $preview = $request->boolean('preview');

        $provider = Provider::with(['activities' => function ($q) {
                $q->with(['cover', 'category', 'reviews'])
                  ->where('is_active', true)
                  ->orderBy('name');
            }])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id'              => $provider->id,
                'business_name'   => $provider->business_name,
                'slug'            => $provider->slug,
                'logo_url'        => $provider->logo_url,
                'is_approved'     => $provider->status === 'approved',
                'booking_enabled' => $provider->status === 'approved',
                'activities'      => $provider->activities->map(fn($a) => [
                    'id'               => $a->id,
                    'name'             => $a->name,
                    'slug'             => $a->slug,
                    'price'            => $a->price,
                    'duration_minutes' => $a->duration_minutes,
                    'city'             => $a->city,
                    'state'            => $a->state,
                    'cover_url'        => $a->cover?->url,
                    'category'         => $a->category?->only(['id', 'name', 'slug']),
                    'avg_rating'       => $a->reviews->avg('rating') ? round($a->reviews->avg('rating'), 1) : null,
                    'review_count'     => $a->reviews->count(),
                ]),
            ],
        ]);
    }
}
