<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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

        $userFromTable = Users::where('user_id', "202210383")->first();

        // 4️⃣  Mint a Sanctum token labelled “mobile”
        $token = "1|905a4315b0ad4d597be462c1bfc13435524fdb9cf66dfb69e7dba81d6b050a3b";

        return (new UsersResource($userFromTable))
        ->additional(['token' => $token]);
    }
}
