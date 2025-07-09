<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;

class LoginController extends Controller
{
    public function showLoginPage()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        Session::flush();

        $user = Users::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password_hash)) {
            if ($user->role_id == 1) {
                Session::put('user_id', $user->user_id);
                Session::put('user_name', $user->first_name . ' ' . $user->last_name);
                Session::put('role_id', $user->role_id);
                session([
                    'user_id' => $user->user_id,
                    'email'   => $user->email,
                ]);
                Session::save();

                if ($user->role_id == 1 && $user->requires_password_change == 1) {
                    return redirect('/pin');
                }

                if ($user->role_id == 1 && $user->agreed_to_terms == 0) {
                    return redirect('/terms');
                }

                return redirect()->intended('/home-tutor');
            } elseif ($user->role_id == 2) {
                Session::put('user_id', $user->user_id);
                Session::put('user_name', $user->first_name . ' ' . $user->last_name);
                Session::put('role_id', $user->role_id);
                Session::save();

                return redirect()->intended('/teachers-panel');
            }
        }
        if ($user->role_id != 3) {
            return redirect('/login')->with('error', 'Invalid credentials');
        } else {
            return redirect('/admin-login')->with('error', 'Invalid credentials');
        }
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login')->with('success', 'Successfully Logged Out');
    }
}
