<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Services\StudentAnalytics;
use App\Services\AchievementService;

use App\Models\{
    Achievements,
    Activities,
    Courses,
    Modules,
    ModuleProgress,
    StudentProgress,
    LongQuizzes,
    Badges,
    StudentAchievements,
    StudentBadges,
    Screening,
    ScreeningResult,
    Students,
    Users,
    UserImages,
    CalendarEvent,
    AssessmentResult,
    LongQuizAssessmentResult
};


class MainController extends Controller
{
    private function checkStudentAccess()
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'You must be logged in');
        }

        if (session('role_id') == 3) {
            return redirect('/admin-panel');
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

    protected function mustHaveCatchUp(string $courseId = null)
    {
        $isCatchUp = Students::where('user_id', session('user_id'))
            ->value('isCatchUp');

        if ($isCatchUp == 0) {
            return redirect("/home-tutor")
                ->with(
                    'error',
                    'Invalid Access.'
                );
        }

        return null;
    }

    public function showAnnouncement($announcementID)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $announcement = CalendarEvent::findOrFail($announcementID);

        return view('student.view-annoucement', compact('users', 'announcement'));
    }


    public function profilePage(Request $request)
    {
        /* ----------  auth guard (already there) ---------- */
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $userID = session('user_id');

        /* ----------  handle image upload  ---------- */
        if ($request->isMethod('post')) {
            $request->validate([
                'profile_pic' => 'required|file|mimes:jpg,jpeg,png|max:2048',   // 2 MB
            ]);

            $blob = file_get_contents($request->file('profile_pic')->path());

            UserImages::updateOrCreate(
                ['user_id' => $userID],
                ['image'   => $blob]
            );

            return back();                            // flash success if you like
        }

        /* ----------  user + blob image for GET ---------- */
        $users = Users::with('image')->findOrFail($userID);

        /* ----------  section / rank / points ---------- */
        $student = Students::with(['section'])        // assumes section() relation
            ->findOrFail($userID);

        /* simple rank inside the student’s section */
        $rank = Students::where('section_id', $student->section_id)
            ->orderByDesc('total_points')
            ->pluck('user_id')
            ->search($userID) + 1;   // 1-based

        return view('student.student-profile', [
            'users'     => $users,
            'section'   => $student->section->section_name ?? '—',
            'rank'      => $rank,
            'points'    => $student->total_points,
        ]);
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

        if ($redirect = $this->mustHaveCatchUp($course->course_id)) {
            return $redirect;
        }

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $module->load('activities.quiz');

        return view('student.view-module', compact('course', 'module', 'users'));
    }

    public function lecturePage(Courses $course, Modules $module, Activities $activity)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        if ($redirect = $this->mustHaveCatchUp($course->course_id)) {
            return $redirect;
        }

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $activity->load('lecture');


        return view('student.activity-lecture', compact('course', 'module', 'activity', 'users'));
    }

    public function tutorialPage(Courses $course, Modules $module, Activities $activity)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        if ($redirect = $this->mustHaveCatchUp($course->course_id)) {
            return $redirect;
        }

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $activity->load('tutorial');
        return view('student.activity-tutorial', compact('course', 'module', 'activity', 'users'));
    }

    public function quizPage(Courses $course, Modules $module, Activities $activity)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        if ($redirect = $this->mustHaveCatchUp($course->course_id)) {
            return $redirect;
        }

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

        if ($redirect = $this->mustHaveCatchUp($course->course_id)) {
            return $redirect;
        }

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $activityID = $activity->activity_id;
        $activity->load('quiz');

        $assessment = AssessmentResult::where('student_id', $userID)
            ->where('activity_id', $activityID)
            ->orderByDesc('date_taken')
            ->with([
                'answers.option',                     // chosen option
                'answers.question.options'            // ALL options for the Q
            ])
            ->first();           // null-safe in case no attempt yet

        /* -------------------------------------------
   transform to a flat array the view can loop
   -------------------------------------------*/
        $answeredQuestions = collect();

        if ($assessment) {
            foreach ($assessment->answers as $ans) {
                $q = $ans->question;                 // Question model
                $q->selected_option_id = $ans->option_id;
                $answeredQuestions->push($q);        // keep full question object
            }
        }

        $attempts = AssessmentResult::where('student_id', $userID)
            ->where('activity_id', $activityID)
            ->count();

        $assessDisplay = AssessmentResult::where('student_id', $userID)
            ->where('activity_id', $activityID)
            ->orderBy('date_taken', 'asc')
            ->get();

        return view('student.activity-quiz-summary', compact('course', 'module', 'activity', 'assessment', 'attempts', 'answeredQuestions',  'assessDisplay', 'users'));
    }

    public function longquizPage($courseID, $longQuizID)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        if ($redirect = $this->mustHaveCatchUp($courseID)) {
            return $redirect;
        }

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

        if ($redirect = $this->mustHaveCatchUp($courseID)) {
            return $redirect;
        }

        $course = Courses::findOrFail($courseID);
        $longquiz = LongQuizzes::findOrFail($longQuizID);
        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $assessment = LongQuizAssessmentResult::where('student_id', $userID)
            ->where('course_id', $courseID)
            ->orderByDesc('date_taken')
            ->with([
                'answers.longquizoption',                     // chosen option
                'answers.longquizquestion.longquizoptions'            // ALL options for the Q
            ])
            ->first();           // null-safe in case no attempt yet

        /* -------------------------------------------
   transform to a flat array the view can loop
   -------------------------------------------*/
        $answeredQuestions = collect();

        if ($assessment) {
            foreach ($assessment->answers as $ans) {
                $q = $ans->longquizquestion;                 // Question model
                $q->selected_option_id = $ans->option_id;
                $answeredQuestions->push($q);        // keep full question object
            }
        }

        $attempts = LongQuizAssessmentResult::where('student_id', $userID)
            ->where('long_quiz_id', $longQuizID)
            ->count();

        $assessDisplay = LongQuizAssessmentResult::where('student_id', $userID)
            ->where('long_quiz_id', $longQuizID)
            ->orderBy('date_taken', 'asc')
            ->get();

        return view('student.long-quiz-summary', compact('course', 'longquiz', 'assessment', 'attempts', 'answeredQuestions', 'assessDisplay', 'users'));
    }

    public function performancePage()
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $studentId = session('user_id');

        /* ── keep all roll-ups fresh ───────────── */
        StudentAnalytics::refreshStudentSummary($studentId);
        AchievementService::evaluate($studentId);

        /* ── basic user + grand-total row ───────── */
        $users   = Users::with('image')->findOrFail($studentId);
        $overall = Students::findOrFail($studentId);       // <- has total_points

        /* ── course-level aggregates (already built by StudentAnalytics) */
        $courses = StudentProgress::with('course')         // eager-loads course name
            ->where('student_id', $studentId)
            ->get();

        /* ── module averages (short+practice) for each course */
        $modules = ModuleProgress::with('module')          // pull module name
            ->where('student_id', $studentId)
            ->get()
            ->groupBy('course_id');                 // ⇒ $modules[<course>][]

        /* ── practice-quiz breakdown inside every module */
        $practice = AssessmentResult::query()
            ->from('assessmentresult as ar')                 // alias main table
            ->join('activity as a',   'ar.activity_id', '=', 'a.activity_id')
            ->join('quiz as q',       'a.activity_id',  '=', 'q.activity_id')
            ->selectRaw('
        a.module_id                              as module_id,
        ar.activity_id,
        a.activity_name                          as quiz_name,
        AVG(ar.score_percentage)                 as avg
    ')
            ->where([
                ['ar.student_id',  $studentId],
                ['ar.is_kept',     1],
                ['q.quiz_type_id', 2],                   // PRACTICE
            ])
            ->groupBy('a.module_id', 'ar.activity_id', 'a.activity_name')
            ->get()
            ->groupBy('module_id');

        /* ── short-quiz breakdown (same idea, quiz_type_id = 1) */
        $short = AssessmentResult::query()
            ->from('assessmentresult as ar')
            ->join('activity as a', 'ar.activity_id', '=', 'a.activity_id')
            ->join('quiz as q',     'a.activity_id',  '=', 'q.activity_id')
            ->selectRaw('
        a.module_id,
        ar.activity_id,
        a.activity_name      as quiz_name,
        AVG(ar.score_percentage) as avg
    ')
            ->where([
                ['ar.student_id',  $studentId],
                ['ar.is_kept',     1],
                ['q.quiz_type_id', 1],          // SHORT
            ])
            ->groupBy('a.module_id', 'ar.activity_id', 'a.activity_name')
            ->get()
            ->groupBy('module_id');

        /* ── long-quiz averages & per-quiz rows (course-scoped) */
        $long = LongQuizAssessmentResult::query()
            ->selectRaw(
                'longquiz.course_id,
             longquiz.long_quiz_id,
             longquiz.long_quiz_name as quiz_name,
             AVG(score_percentage)   as avg'
            )
            ->join('longquiz', 'long_assessmentresult.long_quiz_id', '=', 'longquiz.long_quiz_id')
            ->where([
                ['student_id', $studentId],
                ['is_kept',    1]
            ])
            ->groupBy('longquiz.course_id', 'longquiz.long_quiz_id', 'longquiz.long_quiz_name')
            ->get()
            ->groupBy('course_id');        // $long[<course>][]

        /* ── best screening-exam score per exam (optional) */
        $screening = ScreeningResult::query()
            ->join(
                'screening',      // ← grab title / course id
                'screening.screening_id',
                '=',
                'screeningresult.screening_id'
            )
            ->where('screeningresult.student_id', $studentId)
            ->groupBy(
                'screening.screening_id',
                'screening.course_id',
                'screening.screening_name'
            )
            ->select(
                'screening.screening_id',
                'screening.course_id',
                'screening.screening_name',
                DB::raw('MAX(score_percentage) as best_score')
            )
            ->get()
            ->groupBy('course_id');           // $screening[<course>][]

        $ownedAchIds = StudentAchievements::where('student_id', $studentId)
            ->pluck('achievement_id')
            ->toArray();                       // quick lookup array

        $ownedBadgeIds = StudentBadges::where('student_id', $studentId)
            ->pluck('badge_id')
            ->toArray();

        /* ----------------------------------------------------------
     | 3.  Load *all* achievements with their condition details
     |     and add an `owned` attribute for easy checks in Blade
     * -------------------------------------------------------- */
        $achievements = Achievements::with('conditionType')        // ->conditionType relation
            ->orderBy('achievement_id')
            ->get()
            ->map(function ($a) use ($ownedAchIds) {
                $a->owned = in_array($a->achievement_id, $ownedAchIds);
                return $a;
            });

        /* badges -------------------------------------------------- */
        $badges = Badges::orderBy('points_required')
            ->get()
            ->map(function ($b) use ($ownedBadgeIds) {
                $b->owned = in_array($b->badge_id, $ownedBadgeIds);
                return $b;
            });


        return view(
            'student.student-performance',
            compact(
                'users',
                'overall',
                'courses',
                'modules',
                'practice',
                'short',
                'long',
                'screening',
                'achievements',
                'badges'
            )
        );
    }

    public function leaderboardPage()
    {
        $studentId = session('user_id');

        /* 1. Fetch the caller’s section  */
        $me = Students::with('user')->findOrFail($studentId);
        $sectionId = $me->section_id;

        $users = Users::with('image')->findOrFail($studentId);

        /* 2. Rank everyone in that section by total_points DESC  */
        $ranked = Students::with('user')                // eager-load name + pic
            ->where('section_id', $sectionId)
            ->orderByDesc('total_points')
            ->orderBy('user_id')                        // deterministic tie-break
            ->get()
            ->values();                                 // fresh 0-based index

        /* 3. Determine my rank (1-based)                    */
        $myRank = optional(
            $ranked->firstWhere('user_id', $studentId)
        )->index ?? null;
        $myRank = $myRank !== null ? $myRank + 1 : null;

        /* 4. Slice the Top-15 for display                  */
        $top15 = $ranked->take(15);

        return view('student.student-leaderboards', compact(
            'top15',
            'me',
            'myRank',
            'ranked',
            'users'
        ));
    }
}
