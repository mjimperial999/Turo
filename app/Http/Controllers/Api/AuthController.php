<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\Users;
use App\Http\Resources\UsersResource;

class AuthController extends Controller
{
    public function login(Request $request)
    {   
        // 1️⃣  Validate input
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = Users::where('email', $request->email)->first();

        // 3️⃣  Verify credentials
        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        else {
            $token = $user->createToken('mobile')->plainTextToken;
            
            return (new UsersResource($user))
            ->additional(['token' => $token]);
        }

        return response()->json(['message' => 'Code block did not run :/'], 401);
    }
}
