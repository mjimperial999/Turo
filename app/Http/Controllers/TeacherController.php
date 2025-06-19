<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;

class TeacherController extends Controller
{
    private function checkTeacherAccess()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        if (session('role_id') == 1) {
            return redirect('/home-tutor');
        }

        return null;
    }

    public function teacherPanel()
    {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $userID = session('user_id');
        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        return view('student-profile', compact('users'));
    }
}
