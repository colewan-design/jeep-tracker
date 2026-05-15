<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JeepneyRoute;
use App\Models\UserSavedRoute;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /** GET /user/saved-routes */
    public function index(Request $request)
    {
        $saved = UserSavedRoute::where('user_id', $request->user()->id)
            ->with('route')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($s) => array_merge(
                $s->route->toArray(),
                ['bookmark_id' => $s->id, 'nickname' => $s->nickname]
            ));

        return response()->json($saved);
    }

    /** POST /user/saved-routes  { jeepney_route_id, nickname? } */
    public function store(Request $request)
    {
        $request->validate([
            'jeepney_route_id' => 'required|exists:jeepney_routes,id',
            'nickname'         => 'nullable|string|max:60',
        ]);

        $existing = UserSavedRoute::where('user_id', $request->user()->id)
            ->where('jeepney_route_id', $request->jeepney_route_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already saved.'], 409);
        }

        $maxOrder = UserSavedRoute::where('user_id', $request->user()->id)->max('sort_order') ?? -1;

        $bookmark = UserSavedRoute::create([
            'user_id'          => $request->user()->id,
            'jeepney_route_id' => $request->jeepney_route_id,
            'nickname'         => $request->nickname,
            'sort_order'       => $maxOrder + 1,
        ]);

        return response()->json(['message' => 'Route saved.', 'bookmark' => $bookmark], 201);
    }

    /** DELETE /user/saved-routes/{jeepney_route_id} */
    public function destroy(Request $request, int $jeepneyRouteId)
    {
        $deleted = UserSavedRoute::where('user_id', $request->user()->id)
            ->where('jeepney_route_id', $jeepneyRouteId)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Bookmark not found.'], 404);
        }

        return response()->json(['message' => 'Bookmark removed.']);
    }
}
