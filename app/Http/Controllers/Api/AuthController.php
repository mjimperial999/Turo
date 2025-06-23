<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1️⃣  Validate input
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // 2️⃣  Look up user
        $user = User::where('email', $request->email)->first();

        // 3️⃣  Verify credentials
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // 4️⃣  Mint a Sanctum token labelled “mobile”
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token'                     => $token,
            'user_id'                   => $user->user_id,                    // note: your column is user_id
            'first_name'                => $user->first_name,
            'last_name'                 => $user->last_name,
            'role_id'                   => $user->role_id,
            'requires_password_change'  => $user->requires_password_change,
            'agreed_to_terms'           => $user->agreed_to_terms,
            'email'                     => $user->email,
            'image'                     => $user->profile_pic,                // raw base64 or URL
        ], 200);
    }
}
