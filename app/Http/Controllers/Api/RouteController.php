<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jeep;
use App\Models\Trip;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index(Jeep $jeep)
    {
        $trips = $jeep->trips()->orderByDesc('started_at')->get();

        return response()->json($trips);
    }

    public function store(Request $request, Jeep $jeep)
    {
        $request->validate([
            'origin'           => 'required|string|max:255',
            'destination'      => 'required|string|max:255',
            'jeepney_route_id' => 'nullable|exists:jeepney_routes,id',
            'started_at'       => 'nullable|date',
        ]);

        $trip = $jeep->trips()->create([
            'origin'           => $request->origin,
            'destination'      => $request->destination,
            'jeepney_route_id' => $request->jeepney_route_id,
            'started_at'       => $request->started_at ?? now(),
            'status'           => 'waiting',
        ]);

        return response()->json([
            'message' => 'Trip started.',
            'trip'    => $trip,
        ], 201);
    }

    public function show(Trip $trip)
    {
        $trip->load('jeep');

        return response()->json($trip);
    }

    public function update(Request $request, Trip $trip)
    {
        $request->validate([
            'origin'          => 'sometimes|string|max:255',
            'destination'     => 'sometimes|string|max:255',
            'status'          => 'sometimes|in:waiting,boarding,in_transit,ongoing,completed,cancelled',
            'ended_at'        => 'nullable|date',
            'route_points'    => 'nullable|array',
            'avg_speed'       => 'nullable|numeric',
            'max_speed'       => 'nullable|numeric',
            'passenger_count' => 'nullable|integer|min:0',
        ]);

        if ($request->status === 'completed' && !$trip->ended_at) {
            $trip->ended_at = now();
        }

        $trip->update($request->only([
            'origin', 'destination', 'status', 'ended_at',
            'route_points', 'avg_speed', 'max_speed', 'passenger_count',
        ]));

        return response()->json([
            'message' => 'Trip updated.',
            'trip'    => $trip,
        ]);
    }

    public function destroy(Trip $trip)
    {
        $trip->delete();

        return response()->json(['message' => 'Trip deleted.']);
    }
}
