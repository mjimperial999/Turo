<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use Illuminate\Support\Facades\Session;

use App\Models\Users;

class TermsController extends Controller
{
    public function show()
    {
        abort_if(!session()->has('user_id'), 403);
        $user = Users::findOrFail(session('user_id'));

        if ($user->agreed_to_terms) {

            Session::put('user_id', $user->user_id);
            Session::put('user_name', $user->first_name . ' ' . $user->last_name);
            Session::put('role_id', $user->role_id);
            Session::save();

            return match ($user->role_id) {


                1 => redirect('/home-tutor'),
                2 => redirect('/teachers-panel'),
                3 => redirect('/admin-panel'),
                default => abort(403)
            };
        }

        return view('terms');
    }

    public function accept(Request $r)
    {
        $r->validate(['agree' => 'required|in:1']);

        $user = Users::findOrFail(session('user_id'));
        $user->update(['agreed_to_terms' => 1]);

        Session::put('user_id', $user->user_id);
        Session::put('user_name', $user->first_name . ' ' . $user->last_name);
        Session::put('role_id', $user->role_id);
        Session::save();

        return redirect(
            $user->role_id === 1 ? '/home-tutor' : ($user->role_id === 2 ? '/teachers-panel' : '/login')
        );
    }
}
