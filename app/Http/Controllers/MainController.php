<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Users;
use App\Models\Students;
use App\Models\StudentProgress;
use App\Models\Courses;
use App\Models\Modules;
use App\Models\Screening;
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
        return view('student.student-profile', compact('users'));
    }


    public function courseList()
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $courses = Courses::with('image')->get();

        return view('student.home-page', compact('courses', 'users'));
    }


    public function moduleList(Courses $course)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $courses = Courses::with([
            'modules.moduleimage',
            'longquizzes.keptResult' => fn($q) => $q->where('student_id', $userID),
            'screenings.keptResult'  => fn($q) => $q->where('student_id', $userID),
        ])->get();

        return view('student.view-course', compact('course', 'courses', 'users'));
    }

    public function activityList(Courses $course, Modules $module)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $module->load('activities.quiz');

        return view('student.view-module', compact('course', 'module', 'users'));
    }

    public function lecturePage(Courses $course, Modules $module, Activities $activity)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $activity->load('lecture');


        return view('student.activity-lecture', compact('course', 'module', 'activity', 'users'));
    }

    public function tutorialPage(Courses $course, Modules $module, Activities $activity)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $activity->load('tutorial');
        return view('student.activity-tutorial', compact('course', 'module', 'activity', 'users'));
    }

    public function quizPage(Courses $course, Modules $module, Activities $activity)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $activityID = $activity->activity_id;
        $activity->load('quiz');

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

        return view('student.activity-quiz', compact('course', 'module', 'activity', 'assessment', 'attempts', 'assessDisplay', 'users'));
    }

    public function summary(Courses $course, Modules $module, Activities $activity)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $activityID = $activity->activity_id;
        $activity->load('quiz');

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

        return view('student.activity-quiz-summary', compact('course', 'module', 'activity', 'assessment', 'attempts', 'assessDisplay', 'users'));
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


        return view('student.long-quiz', compact('course', 'longquiz', 'assessment', 'attempts', 'assessDisplay', 'users'));
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

        return view('student.long-quiz-summary', compact('course', 'longquiz', 'assessment', 'attempts', 'assessDisplay', 'users'));
    }

    public function performancePage()
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $studentID = $userID;

        /* -------------------------------------------------------------
     | 1.  overall progress row (may be null)
     |------------------------------------------------------------ */
        $progress = StudentProgress::where('student_id', $studentID)->first();

        /* -------------------------------------------------------------
     | 2.  courses in which the student is enrolled
     |------------------------------------------------------------ */
        $courses = Courses::query()
            ->join('enrollment', 'course.course_id', '=', 'enrollment.course_id')
            ->where('enrollment.student_id', $studentID)
            ->select('course.course_id', 'course.course_name')
            ->get();

        /* -------------------------------------------------------------
     | 3.  module-level averages  (short quizzes only)
     |------------------------------------------------------------ */
        $moduleAverages = AssessmentResult::query()
            ->join('module', 'assessmentresult.module_id', '=', 'module.module_id')
            ->join('course',  'module.course_id', '=', 'course.course_id')
            ->where([
                ['assessmentresult.student_id', $studentID],
                ['assessmentresult.is_kept',    1],
            ])
            ->groupBy('assessmentresult.module_id', 'module.module_name', 'module.course_id')
            ->get([
                'assessmentresult.module_id',
                'module.module_name',
                'module.course_id',
                DB::raw('AVG(score_percentage) as average_score'),
            ]);

        /* -------------------------------------------------------------
     | 4.  short-quiz average per course
     |------------------------------------------------------------ */
        $shortAverages = AssessmentResult::query()
            ->join('module', 'assessmentresult.module_id', '=', 'module.module_id')
            ->where([
                ['assessmentresult.student_id', $studentID],
                ['assessmentresult.is_kept',    1],
            ])
            ->groupBy('module.course_id')
            ->get([
                'module.course_id',
                DB::raw('AVG(score_percentage) as short_avg'),
            ])
            ->keyBy('course_id');

        /* -------------------------------------------------------------
     | 5.  long-quiz average per course
     |------------------------------------------------------------ */
        $longAverages = LongQuizAssessmentResult::query()
            ->join('longquiz', 'long_assessmentresult.long_quiz_id', '=', 'longquiz.long_quiz_id')
            ->where([
                ['long_assessmentresult.student_id', $studentID],
                ['long_assessmentresult.is_kept',    1],
            ])
            ->groupBy('longquiz.course_id')
            ->get([
                'longquiz.course_id',
                DB::raw('AVG(score_percentage) as long_avg'),
            ])
            ->keyBy('course_id');

        /* -------------------------------------------------------------
     | 6.  per-quiz breakdown for long quizzes                       
     |------------------------------------------------------------ */
        $longQuizzes = LongQuizAssessmentResult::query()
            ->join('longquiz', 'long_assessmentresult.long_quiz_id', '=', 'longquiz.long_quiz_id')
            ->where([
                ['long_assessmentresult.student_id', $studentID],
                ['long_assessmentresult.is_kept',    1],
            ])
            ->groupBy('longquiz.course_id', 'longquiz.long_quiz_name')
            ->get([
                'longquiz.course_id',
                'longquiz.long_quiz_name',
                DB::raw('AVG(long_assessmentresult.score_percentage) as average_score'),
            ]);

        /* -------------------------------------------------------------
     | 7.  convenience numbers for a dashboard card / gauge
     |------------------------------------------------------------ */
        $percentage = $progress
            ? round($progress->average_score ?? 0, 2)
            : null;

        /* -------------------------------------------------------------
     | 8.  pass everything to a view
     |     (adjust view name & compact vars as you need)
     |------------------------------------------------------------ */
        return view(
            'student.student-performance',   // <- create / reuse this blade or php view
            compact(
                'users',
                'courses',
                'moduleAverages',
                'shortAverages',
                'longAverages',
                'longQuizzes',
                'progress',
                'percentage'
            )
        );
    }
}
