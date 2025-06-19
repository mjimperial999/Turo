<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminController extends Controller
{
    public function showLoginPage()
    {
        return view('admin.login-admin');
    }

    public function login(Request $request)
    {

        $admin = Admin::where('user_name', $request->user_name)->first();

        if ($admin && Hash::check($request->password, $admin->password_hash)) {
                Session::put('user_id', $admin->user_id);
                Session::put('user_name', $admin->user_name);
                Session::save();
                
                return redirect()->intended('/admin-panel');
            }

        return redirect('/admin-login')->with('error', 'Invalid credentials');
    }

    public function logout()
    {
        Session::flush();
        return redirect('/admin-login')->with('success', 'Successfully Logged Out');
    }

    public function adminPanel(){
        return view('admin-panel');
    }
}
