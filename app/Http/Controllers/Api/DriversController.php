<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DriversController extends Controller
{
    public function index()
    {
        $drivers = User::where('role', 'driver')
            ->select('id', 'name', 'email', 'driver_code', 'created_at')
            ->withCount('jeeps')
            ->orderBy('name')
            ->get();

        return response()->json($drivers);
    }

    public function generateToken(Request $request, User $user)
    {
        abort_if($user->role !== 'driver', 403, 'User is not a driver.');

        // Generate a unique 6-digit code
        do {
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (User::where('driver_code', $code)->exists());

        $user->update(['driver_code' => $code]);

        return response()->json(['code' => $code]);
    }

    // Public: exchange a 6-digit code for a real Sanctum token
    public function access(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $user = User::where('driver_code', $request->code)
                    ->where('role', 'driver')
                    ->first();

        abort_if(!$user, 401, 'Invalid driver code.');

        $user->tokens()->delete();
        $token = $user->createToken('driver-access')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }
}
