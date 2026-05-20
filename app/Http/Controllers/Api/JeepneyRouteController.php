<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JeepneyRoute;
use App\Models\UserSavedRoute;
use Illuminate\Http\Request;

class JeepneyRouteController extends Controller
{
    /**
     * GET /jeepney-routes
     * Returns all 43 routes. Attaches:
     *  - saved: whether the authed user has bookmarked this route
     *  - active_jeeps_count: how many jeeps are live on it right now
     */
    public function index(Request $request)
    {
        $userId = $request->user()?->id;

        $routes = JeepneyRoute::withCount([
            'trips as active_jeeps_count' => fn ($q) => $q
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->whereHas('jeep', fn ($j) => $j->where('status', 'active')),
        ])->get();

        if ($userId) {
            $savedIds = UserSavedRoute::where('user_id', $userId)
                ->pluck('jeepney_route_id')
                ->flip();

            $routes->each(function ($route) use ($savedIds) {
                $route->saved = $savedIds->has($route->id);
            });
        } else {
            $routes->each(fn ($r) => $r->saved = false);
        }

        // Mark route as active if it has at least one live jeep
        $routes->each(fn ($r) => $r->is_active = $r->active_jeeps_count > 0);

        return response()->json($routes);
    }

    /**
     * GET /active-jeeps
     * All currently active jeeps across every route, with latest location.
     * Public — no auth required.
     */
    public function activeJeeps()
    {
        $jeeps = \App\Models\Trip::whereNotIn('status', ['completed', 'cancelled'])
            ->with(['jeep.latestLocation', 'jeepneyRoute:id,name,origin,destination,fare_regular,vehicle_type'])
            ->whereHas('jeep', fn ($q) => $q->where('status', 'active'))
            ->get()
            ->unique(fn ($trip) => $trip->jeep->id) // one entry per jeep even if multiple open trips
            ->map(function ($trip) {
                $loc = $trip->jeep->latestLocation;

                return [
                    'id'              => $trip->jeep->id,
                    'name'            => $trip->jeep->name,
                    'plate_number'    => $trip->jeep->plate_number,
                    'seats_available' => $trip->jeep->seats_available ?? 'available',
                    'trip_id'         => $trip->id,
                    'trip_status'     => $trip->status,
                    'route'           => $trip->jeepneyRoute,
                    'location'        => $loc ? [
                        'latitude'    => (float) $loc->latitude,
                        'longitude'   => (float) $loc->longitude,
                        'speed'       => (float) ($loc->speed ?? 0),
                        'heading'     => (float) ($loc->heading ?? 0),
                        'recorded_at' => $loc->recorded_at?->toIso8601String(),
                    ] : null,
                ];
            })
            ->values();

        return response()->json($jeeps);
    }

    /**
     * GET /jeepney-routes/{route}
     * Route detail with stops and any currently active jeeps.
     */
    public function show(Request $request, JeepneyRoute $jeepneyRoute)
    {
        $userId = $request->user()?->id;

        $jeepneyRoute->load(['stops']);

        // Jeeps currently running this route
        $activeJeeps = $jeepneyRoute->trips()
            ->where('status', 'in_transit')
            ->with(['jeep.latestLocation'])
            ->whereHas('jeep', fn ($q) => $q->where('status', 'active'))
            ->get()
            ->map(fn ($trip) => [
                'id'           => $trip->jeep->id,
                'name'         => $trip->jeep->name,
                'plate_number' => $trip->jeep->plate_number,
                'trip_id'      => $trip->id,
                'trip_status'  => $trip->status,
                'location'     => $trip->jeep->latestLocation,
            ]);

        $saved = $userId
            ? UserSavedRoute::where('user_id', $userId)
                            ->where('jeepney_route_id', $jeepneyRoute->id)
                            ->exists()
            : false;

        return response()->json([
            'route'        => $jeepneyRoute,
            'active_jeeps' => $activeJeeps,
            'saved'        => $saved,
        ]);
    }
}
