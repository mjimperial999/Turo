<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Students;
use App\Models\Courses;
use App\Models\Modules;
use App\Models\Activities;
use App\Models\LongQuizzes;
use App\Models\AssessmentResult;
use App\Models\LongQuizAssessmentResult;

class MainController extends Controller
{
    private function checkStudentAccess()
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'You must be logged in');
        }

        if (session('role_id') == 2) {
            return redirect('/teachers-panel');
        }

        // Allow only role_id == 1 to proceed
        return null;
    }

    public function landingRedirect()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        return session('role_id') == 1
            ? redirect('/home-tutor')
            : redirect('/teachers-panel');
    }


    public function profilePage()
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        return view('student-profile', compact('users'));
    }

    public function performancePage()
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        return view('student-performance', compact('users'));
    }

    public function courseList()
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $courses = Courses::all();

        return view('home-tutor', compact('courses', 'users'));
    }


    public function moduleList($courseID)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $course = Courses::with('modules.moduleimage')->findOrFail($courseID);
        $longquiz = Courses::with('longquizzes')->findOrFail($courseID);

        return view('module-menu', compact('course', 'longquiz', 'users'));
    }

    public function activityList($moduleID)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $module = Modules::with('activities.quiz')->findOrFail($moduleID);
        return view('explore-module', compact('module', 'users'));
    }

    public function lecturePage($activityID)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $activity = Activities::with('lecture')->findOrFail($activityID);
        return view('activity-lecture', compact('activity', 'users'));
    }

    public function tutorialPage($activityID)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $activity = Activities::with('tutorial')->findOrFail($activityID);
        return view('activity-tutorial', compact('activity', 'users'));
    }

    public function quizPage($activityID)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $activity = Activities::with('quiz')->findOrFail($activityID);
        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $assessment = AssessmentResult::where('student_id', $userID)
            ->where('activity_id', $activityID)
            ->orderByDesc('is_kept')
            ->first();

        $attempts = AssessmentResult::where('student_id', $userID)
            ->where('activity_id', $activityID)
            ->count();

        $assessDisplay = AssessmentResult::where('student_id', $userID)
            ->where('activity_id', $activityID)
            ->orderBy('date_taken', 'asc')
            ->get();

        return view('activity-quiz', compact('activity', 'assessment', 'attempts', 'assessDisplay', 'users'));
    }

    public function summary($activityID)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $activity = Activities::with('quiz')->findOrFail($activityID);
        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $assessment = AssessmentResult::where('student_id', $userID)
            ->where('activity_id', $activityID)
            ->orderBy('date_taken', 'desc')
            ->first();

        $attempts = AssessmentResult::where('student_id', $userID)
            ->where('activity_id', $activityID)
            ->count();

        $assessDisplay = AssessmentResult::where('student_id', $userID)
            ->where('activity_id', $activityID)
            ->orderBy('date_taken', 'asc')
            ->get();

        return view('activity-quiz-summary', compact('activity', 'assessment', 'attempts', 'assessDisplay', 'users'));
    }

    public function longquizPage($courseID, $longQuizID)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $course = Courses::findOrFail($courseID);
        $longquiz = LongQuizzes::findOrFail($longQuizID);
        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $assessment = LongQuizAssessmentResult::where('student_id', $userID)
            ->where('long_quiz_id', $longQuizID)
            ->orderByDesc('is_kept')
            ->first();

        $attempts = LongQuizAssessmentResult::where('student_id', $userID)
            ->where('long_quiz_id', $longQuizID)
            ->count();

        $assessDisplay = LongQuizAssessmentResult::where('student_id', $userID)
            ->where('long_quiz_id', $longQuizID)
            ->orderBy('date_taken', 'asc')
            ->get();


        return view('long-quiz', compact('course', 'longquiz', 'assessment', 'attempts', 'assessDisplay', 'users'));
    }

    public function longquizSummary($courseID, $longQuizID)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $course = Courses::findOrFail($courseID);
        $longquiz = LongQuizzes::findOrFail($longQuizID);
        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $assessment = LongQuizAssessmentResult::where('student_id', $userID)
            ->where('long_quiz_id', $longQuizID)
            ->orderBy('date_taken', 'desc')
            ->first();

        $attempts = LongQuizAssessmentResult::where('student_id', $userID)
            ->where('long_quiz_id', $longQuizID)
            ->count();

        $assessDisplay = LongQuizAssessmentResult::where('student_id', $userID)
            ->where('long_quiz_id', $longQuizID)
            ->orderBy('date_taken', 'asc')
            ->get();

        return view('long-quiz-summary', compact('course', 'longquiz', 'assessment', 'attempts', 'assessDisplay', 'users'));
    }

    public function studentPerformance($userID)
    {
        return view('student-performance', compact('activity', 'assessment'));
    }
}
