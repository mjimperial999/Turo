<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;

class TermsController extends Controller
{
    public function show()
    {
        abort_if(!session()->has('user_id'), 403);
        $user = Users::findOrFail(session('user_id'));
        if ($user->agreed_to_terms) return redirect('/home-tutor');

        return view('terms');   // /resources/views/terms.php
    }

    public function accept(Request $r)
    {
        $r->validate(['agree' => 'required|in:1']);

        $user = Users::findOrFail(session('user_id'));
        $user->update(['agreed_to_terms' => 1]);

        return redirect('/home-tutor');
    }
}
