<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DriversController extends Controller
{
    // List all driver accounts (admin only)
    public function index()
    {
        $drivers = User::where('role', 'driver')
            ->select('id', 'name', 'email', 'created_at')
            ->withCount('jeeps')
            ->orderBy('name')
            ->get();

        return response()->json($drivers);
    }

    // Revoke all existing tokens for a driver and issue a fresh one
    public function generateToken(Request $request, User $user)
    {
        abort_if($user->role !== 'driver', 403, 'User is not a driver.');

        $user->tokens()->delete();
        $token = $user->createToken('driver-access')->plainTextToken;

        return response()->json(['token' => $token]);
    }
}
