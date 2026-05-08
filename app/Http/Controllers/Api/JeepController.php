<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jeep;
use Illuminate\Http\Request;

class JeepController extends Controller
{
    public function index()
    {
        $jeeps = Jeep::with('latestLocation')
            ->where('user_id', auth()->id())
            ->get();

        return response()->json($jeeps);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'plate_number' => 'required|string|max:20|unique:jeeps',
            'route_name'   => 'nullable|string|max:255',
            'capacity'     => 'nullable|integer|min:1',
        ]);

        $jeep = Jeep::create([
            'user_id'      => auth()->id(),
            'name'         => $request->name,
            'plate_number' => $request->plate_number,
            'route_name'   => $request->route_name,
            'capacity'     => $request->capacity,
            'status'       => 'inactive',
        ]);

        return response()->json([
            'message' => 'Jeep created successfully.',
            'jeep'    => $jeep,
        ], 201);
    }

    public function show(Jeep $jeep)
    {
        $jeep->load('latestLocation', 'trips');

        return response()->json($jeep);
    }

    public function update(Request $request, Jeep $jeep)
    {
        $request->validate([
            'name'         => 'sometimes|string|max:255',
            'plate_number' => 'sometimes|string|max:20|unique:jeeps,plate_number,' . $jeep->id,
            'route_name'   => 'nullable|string|max:255',
            'capacity'     => 'nullable|integer|min:1',
            'status'       => 'sometimes|in:active,inactive,maintenance',
        ]);

        $jeep->update($request->all());

        return response()->json([
            'message' => 'Jeep updated successfully.',
            'jeep'    => $jeep,
        ]);
    }

    public function destroy(Jeep $jeep)
    {
        $jeep->delete();

        return response()->json(['message' => 'Jeep deleted successfully.']);
    }
}
