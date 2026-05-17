<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
            'role'     => 'sometimes|in:driver,passenger',
        ]);

        // Verify token with Google's tokeninfo endpoint (no SDK dependency)
        $response = file_get_contents(
            'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($request->id_token)
        );

        if ($response === false) {
            return response()->json(['error' => 'Could not reach Google servers.'], 503);
        }

        $payload = json_decode($response, true);

        // Validate audience matches our client ID
        if (empty($payload['sub']) || ($payload['aud'] ?? '') !== env('GOOGLE_CLIENT_ID')) {
            return response()->json(['error' => 'Invalid Google token.'], 401);
        }

        $googleId = $payload['sub'];
        $email    = $payload['email'] ?? null;
        $name     = $payload['name']  ?? ($payload['given_name'] ?? 'Google User');
        $role     = $request->input('role', 'passenger');

        if (!$email) {
            return response()->json(['error' => 'Google account has no email address.'], 422);
        }

        // Find by google_id first, then by email, then create
        $user = User::where('google_id', $googleId)->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            // Attach google_id if this is an existing email-only account
            if (!$user->google_id) {
                $user->update(['google_id' => $googleId]);
            }
        } else {
            $user = User::create([
                'name'      => $name,
                'email'     => $email,
                'google_id' => $googleId,
                'role'      => $role,
                'password'  => Hash::make(Str::random(32)),
            ]);
        }

        $token = $user->createToken('google-mobile')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }
}
