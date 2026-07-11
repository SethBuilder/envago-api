<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\OutboundClick;
use Illuminate\Http\Request;

class ClickController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'activity_id' => 'required|integer|exists:activities,id',
            'name'        => 'nullable|string|max:120',
            'email'       => 'nullable|email|max:120',
            'whatsapp'    => 'nullable|string|max:30',
        ]);

        $activity = Activity::find($request->activity_id);

        if (!in_array($activity->booking_method, ['affiliate', 'lead'])) {
            return response()->json(['ok' => false], 422);
        }

        OutboundClick::create([
            'activity_id' => $activity->id,
            'type'        => $activity->booking_method,
            'destination' => $activity->city,
            'category'    => $activity->category?->slug,
            'name'        => $request->name,
            'email'       => $request->email,
            'whatsapp'    => $request->whatsapp,
        ]);

        return response()->json(['ok' => true, 'url' => $activity->link_url]);
    }
}
