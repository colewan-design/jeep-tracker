<?php

namespace App\Http\Controllers\Api;

use App\Events\JeepLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Jeep;
use App\Models\JeepLocation;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function update(Request $request, Jeep $jeep)
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed'     => 'nullable|numeric|min:0',
            'heading'   => 'nullable|numeric|between:0,360',
            'accuracy'  => 'nullable|numeric|min:0',
        ]);

        $location = JeepLocation::create([
            'jeep_id'   => $jeep->id,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'speed'     => $request->speed,
            'heading'   => $request->heading,
            'accuracy'  => $request->accuracy,
            'recorded_at' => now(),
        ]);

        $jeep->update(['status' => 'active']);

        broadcast(new JeepLocationUpdated($jeep, $location));

        return response()->json([
            'message'  => 'Location updated.',
            'location' => $location,
        ]);
    }

    public function current(Jeep $jeep)
    {
        $location = $jeep->latestLocation;

        if (!$location) {
            return response()->json(['message' => 'No location data available.'], 404);
        }

        return response()->json([
            'jeep'     => $jeep->only(['id', 'name', 'plate_number', 'route_name', 'status']),
            'location' => $location,
        ]);
    }

    public function history(Request $request, Jeep $jeep)
    {
        $request->validate([
            'from'  => 'nullable|date',
            'to'    => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        $query = $jeep->locations()->orderByDesc('recorded_at');

        if ($request->from) {
            $query->where('recorded_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->where('recorded_at', '<=', $request->to);
        }

        $locations = $query->limit($request->limit ?? 100)->get();

        return response()->json([
            'jeep'      => $jeep->only(['id', 'name', 'plate_number', 'route_name']),
            'locations' => $locations,
        ]);
    }
}
