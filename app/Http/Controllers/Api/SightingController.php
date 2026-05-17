<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JeepSighting;
use Illuminate\Http\Request;

// Haversine distance in km between two lat/lng pairs
function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $R    = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a    = sin($dLat / 2) ** 2
          + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

class SightingController extends Controller
{
    // GET /sightings?route_id=X  — active sightings for a route (or all routes)
    public function index(Request $request)
    {
        $query = JeepSighting::active()->with('jeepneyRoute:id,name,origin,destination');

        if ($request->filled('route_id')) {
            $query->where('jeepney_route_id', $request->route_id);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    // POST /sightings  — report a new jeep sighting
    public function store(Request $request)
    {
        $data = $request->validate([
            'jeepney_route_id'   => 'required|exists:jeepney_routes,id',
            'latitude'           => 'required|numeric|between:-90,90',
            'longitude'          => 'required|numeric|between:-180,180',
            'reporter_latitude'  => 'required|numeric|between:-90,90',
            'reporter_longitude' => 'required|numeric|between:-180,180',
        ]);

        // Geofence: reporter must be within 200 m of the sighting
        $distKm = haversineKm(
            $data['reporter_latitude'], $data['reporter_longitude'],
            $data['latitude'],          $data['longitude'],
        );

        if ($distKm > 0.2) {
            return response()->json([
                'message' => 'You must be within 200 m of the reported location.',
            ], 422);
        }

        $sighting = JeepSighting::create([
            ...$data,
            'confirmations' => 1,
            'denials'       => 0,
            'expires_at'    => now()->addMinutes(8),
        ]);

        return response()->json(['sighting' => $sighting], 201);
    }

    // PATCH /sightings/{sighting}/confirm
    public function confirm(JeepSighting $sighting)
    {
        if ($sighting->expires_at->isPast()) {
            return response()->json(['message' => 'Sighting has expired.'], 410);
        }

        $sighting->increment('confirmations');
        // Renew expiry by 4 minutes on each confirmation (max 8 min from now)
        $newExpiry = now()->addMinutes(4);
        if ($newExpiry->gt($sighting->expires_at)) {
            $sighting->update(['expires_at' => $newExpiry]);
        }

        return response()->json(['sighting' => $sighting->fresh()]);
    }

    // PATCH /sightings/{sighting}/deny
    public function deny(JeepSighting $sighting)
    {
        if ($sighting->expires_at->isPast()) {
            return response()->json(['message' => 'Sighting has expired.'], 410);
        }

        $sighting->increment('denials');

        return response()->json(['sighting' => $sighting->fresh()]);
    }
}
