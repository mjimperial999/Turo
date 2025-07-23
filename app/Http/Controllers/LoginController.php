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

    public function redirectAuth(){
        Session::flush();
        return redirect('/login')->with('error', 'Invalid access');
    }

    public function landingRedirect()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $user = Users::findOrFail(session('user_id'));

        if ($user->requires_password_change == 1){
            return redirect('/pin');
        }

        if ($user->agreed_to_terms == 0){
            return redirect('/terms');
        }

        return session('role_id') == 1
            ? redirect('/home-tutor')
            : redirect('/teachers-panel');
    }


    public function login(Request $request)
    {
        Session::flush();

        $user = Users::where('email', $request->email)->first();

        if (!$user){
            return redirect('/login')->with('error', 'Invalid email. Email is not registered.');
        }

        $user = Users::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password_hash)) {
            
            if ($user->requires_password_change == 1){
                session([
                    'user_id' => $user->user_id,
                    'email'   => $user->email,
                ]);
                Session::save();
                return redirect('/pin');
            }

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
            return redirect('/login')->with('error', 'Invalid Password');
        } else {
            return redirect('/admin-login')->with('error', 'Invalid Password'); // To hide that they are trying to access admin from this page
        }
    }


    public function logout()
    {
        Session::flush();
        return redirect('/login')->with('success', 'Successfully Logged Out');
    }
}
