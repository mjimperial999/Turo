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

        $user = Users::findOrFail(session('user_id'));

        if ($user->requires_password_change == 1){
            return redirect('/login');
        }

        if ($user->agreed_to_terms == 0){
            return redirect('/terms');
        }

        if (session('role_id') == 3) {
            return redirect('/admin-panel');
        }

        if (session('role_id') == 2) {
            return redirect('/teachers-panel');
        }

        return null;
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

    private function seq(string $title): int
    {
        preg_match('/\d+/', $title, $m);
        return empty($m) ? 0 : (int) $m[0];
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

        $userID = session('user_id');
        $users  = Users::with('image')->findOrFail($userID);

        $course->load([
            'modules' => fn($q) => $q->with([
                'moduleimage',
                'studentprogress' => fn($p) => $p->where('student_id', $userID),
            ]),
            'longquizzes.keptResult' => fn($q) => $q->where('student_id', $userID),
            'screenings.keptResult'  => fn($q) => $q->where('student_id', $userID),
        ]);

        $course->modules    = $course->modules
            ->sortBy(fn($m) => $this->seq($m->module_name))
            ->values();

        $course->longquizzes = $course->longquizzes
            ->sortBy(fn($lq) => $this->seq($lq->long_quiz_name))
            ->values();

        $course->screenings  = $course->screenings
            ->sortBy(fn($s) => $this->seq($s->screening_name))
            ->values();

        /* ---------- pass to the view ---------- */
        return view('student.view-course', compact('course', 'users'));
    }

    public function activityList(Courses $course, Modules $module)
    {
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        if ($redirect = $this->mustHaveCatchUp($course->course_id)) {
            return $redirect;
        }

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $module->load(['activities.quiz']);

        /* ── sort activities by numeric order in title ──────────── */
        $module->activities = $module->activities
            ->sortBy(fn($a) => $this->seq($a->activity_name))
            ->values();

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
            ->where('long_quiz_id', $longQuizID)
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
                $q->selected_option_id = $ans->long_quiz_option_id;
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

        StudentAnalytics::refreshStudentSummary($studentId);
        AchievementService::evaluate($studentId);

        $users   = Users::with('image')->findOrFail($studentId);
        $overall = Students::findOrFail($studentId);

        $courses = StudentProgress::with('course')
            ->where('student_id', $studentId)
            ->get();

        $modules = ModuleProgress::with('module')
            ->where('student_id', $studentId)
            ->get()
            ->sortBy(function ($row) {

                return (int) preg_replace('/^.*?(\d+).*$/', '$1', $row->module->module_name);
            })
            ->values()
            ->groupBy('course_id');

        $practice = AssessmentResult::query()
            ->from('assessmentresult as ar')
            ->join('activity as a',  'ar.activity_id', '=', 'a.activity_id')
            ->join('quiz as q',      'a.activity_id',  '=', 'q.activity_id')
            ->selectRaw('
        a.module_id,
        ar.activity_id,
        a.activity_name as quiz_name,
        AVG(ar.score_percentage) as avg')
            ->where([
                ['ar.student_id',  $studentId],
                ['ar.is_kept',     1],
                ['q.quiz_type_id', 2],
            ])
            ->groupBy('a.module_id', 'ar.activity_id', 'a.activity_name')
            ->get()
            ->sortBy([
                fn($row) => (int) preg_replace('/^.*?(\d+).*$/', '$1', optional($row->module)->module_name ?? ''),
                fn($row) => $row->quiz_name,
            ])
            ->groupBy('module_id');


        $short = AssessmentResult::query()
            ->from('assessmentresult as ar')
            ->join('activity as a', 'ar.activity_id', '=', 'a.activity_id')
            ->join('quiz as q',     'a.activity_id',  '=', 'q.activity_id')
            ->selectRaw('
        a.module_id,
        ar.activity_id,
        a.activity_name as quiz_name,
        AVG(ar.score_percentage) as avg')
            ->where([
                ['ar.student_id',  $studentId],
                ['ar.is_kept',     1],
                ['q.quiz_type_id', 1],
            ])
            ->groupBy('a.module_id', 'ar.activity_id', 'a.activity_name')
            ->get()
            ->sortBy([
                fn($row) => (int) preg_replace('/^.*?(\d+).*$/', '$1', optional($row->module)->module_name ?? ''),
                fn($row) => $row->quiz_name,
            ])
            ->groupBy('module_id');


        $long = LongQuizAssessmentResult::query()
            ->join('longquiz as lq', 'long_assessmentresult.long_quiz_id', '=', 'lq.long_quiz_id')
            ->selectRaw('
        lq.course_id,
        lq.long_quiz_id,
        lq.long_quiz_name as quiz_name,
        AVG(score_percentage) as avg')
            ->where([
                ['student_id', $studentId],
                ['is_kept',    1],
            ])
            ->groupBy('lq.course_id', 'lq.long_quiz_id', 'lq.long_quiz_name')
            ->get()
            ->sortBy(fn($row) => (int) preg_replace('/^.*?(\d+).*$/', '$1', $row->quiz_name))
            ->groupBy('course_id');

        $screening = ScreeningResult::query()
            ->join('screening as s', 's.screening_id', '=', 'screeningresult.screening_id')
            ->where('screeningresult.student_id', $studentId)
            ->groupBy('s.screening_id', 's.course_id', 's.screening_name')
            ->selectRaw('
        s.screening_id,
        s.course_id,
        s.screening_name,
        MAX(score_percentage) as best_score')
            ->get()
            ->sortBy(fn($row) => (int) preg_replace('/^.*?(\d+).*$/', '$1', $row->screening_name))
            ->groupBy('course_id');

        $ownedAch = StudentAchievements::where('student_id', $studentId)
            ->get()
            ->keyBy('achievement_id');

        $achievements = Achievements::with('conditionType')
            ->orderBy('achievement_id')
            ->get()
            ->map(function ($a) use ($ownedAch) {
                $row = $ownedAch->get($a->achievement_id);
                $a->owned       = (bool) $row;
                $a->unlocked_at = $row?->unlocked_at;
                return $a;
            });

        $ownedBadge = StudentBadges::where('student_id', $studentId)
            ->get()                                 // student_id | badge_id | unlocked_at
            ->keyBy('badge_id');

        $badges = Badges::orderBy('points_required')
            ->get()
            ->map(function ($b) use ($ownedBadge) {
                $row = $ownedBadge->get($b->badge_id);
                $b->owned       = (bool) $row;
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
        if ($redirect = $this->checkStudentAccess()) return $redirect;

        $studentId = session('user_id');

        /* 1. caller’s section */
        $me        = Students::with('user')->findOrFail($studentId);
        $sectionId = $me->section_id;

        $users     = Users::with('image')->findOrFail($studentId);

        /* 2. everyone in that section, HIGH-to-LOW points               */
        $ranked = Students::with('user')
            ->where('section_id', $sectionId)
            ->orderByDesc('total_points')
            ->orderBy('user_id')          // deterministic tie-break
            ->get()
            ->values();                   // fresh 0-based index

        /* 3. assign “tied” ranks (1-based, duplicates allowed)           */
        $prevPts = null;
        $rank    = 0;

        foreach ($ranked as $idx => $row) {
            if ($prevPts === null || $row->total_points < $prevPts) {
                // points dropped → next rank is current position +1
                $rank = $idx + 1;
            }
            $row->rank = $rank;           // attach for the view
            $prevPts   = $row->total_points;
        }

        /* 4. my own rank                                                  */
        $myRank = optional(
            $ranked->firstWhere('user_id', $studentId)
        )->rank;

        /* 5. Top-15 slice (ties can produce duplicate ranks)              */
        $top15 = $ranked->take(15);

        return view(
            'student.student-leaderboards',
            compact('top15', 'me', 'myRank', 'ranked', 'users')
        );
    }
}
