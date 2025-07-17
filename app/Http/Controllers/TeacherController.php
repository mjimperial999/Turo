<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use App\Models\{
    Users,
    Students,
    Courses,
    Sections,
    CourseSection,
    CourseImage,
    Modules,
    ModuleImage,
    StudentProgress,
    Screening,
    ScreeningConcept,
    ScreeningTopic,
    ScreeningQuestion,
    ScreeningQuestionImage,
    ScreeningOption,
    Activities,
    Quizzes,
    Questions,
    QuestionImages,
    Options,
    LongQuizzes,
    LongQuizQuestions,
    LongQuizOptions,
    LongQuizQuestionImages,
    AssessmentResult,
    LongQuizAssessmentResult,
    ScreeningResult,
    LearningResource,
    UserImages,
    CalendarEvent
};

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

        if (session('role_id') == 3) {
            return redirect('/admin-panel');
        }

        return null;
    }

    public function showAnnouncement($announcementID)
    {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $announcement = CalendarEvent::findOrFail($announcementID);

        return view('teacher.view-annoucement', compact('users', 'announcement'));
    }

    public function teacherPanel()
    {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $courseLinks = CourseSection::with(['course.image', 'section'])
            ->where('teacher_id', $userID)
            ->orderBy('course_id')
            ->orderBy('section_id')
            ->get();

        return view('teacher.teachers-panel', compact('courseLinks', 'users',));
    }

    private function seq(string $title): int
    {
        preg_match('/\d+/', $title, $m);
        return empty($m) ? 0 : (int) $m[0];
    }

    public function profilePage(Request $request)
    {
        /* ----------  auth guard (already there) ---------- */
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

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

            return back();
        }

        /* ----------  user + blob image for GET ---------- */
        $users = Users::with('image')->findOrFail($userID);



        return view('teacher.teacher-profile', compact('users'));
    }

    private function assertOwnsCourseSection($courseId, $sectionId): void
    {
        $teacherId = session('user_id');

        $owns = DB::table('course_section')
            ->where([
                'course_id'  => $courseId,
                'section_id' => $sectionId,
                'teacher_id' => $teacherId
            ])->exists();

        abort_if(!$owns, 403, 'Not assigned to this section.');
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Course CRUD
    public function createCourse()
    {
        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $courses = Courses::with('image')->get();

        return view('teacher.course-create', compact('courses', 'users'));
    }

    public function storeCourse(Request $req)
    {
        $req->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:255',
            'course_description' => 'nullable|string',
            'image'        => 'nullable|image|max:2048'
        ]);

        $course = Courses::create([
            'course_id'          => Str::uuid()->toString(),
            'course_code'        => $req->course_code,
            'course_name'        => $req->course_name,
            'course_description' => $req->course_description ?? '',
            'start_date'         => null,
            'end_date'           => null,
            'teacher_id'         => session('user_id')
        ]);

        if ($req->hasFile('image')) {
            $blob = file_get_contents($req->file('image')->getRealPath());
            $mime = $req->file('image')->getMimeType();

            CourseImage::updateOrCreate(
                ['course_id' => $course->course_id],
                [
                    'image'     => $blob,
                    'mime_type' => $mime ?? 'image/jpeg'
                ]
            );
        }

        return redirect("/teachers-panel")
            ->with('success', 'A new course has been created.');
    }

    public function editCourse(Courses $course)
    {
        return view('teacher.course-edit', compact('course'));
    }

    public function updateCourse(Request $req, Courses $course)
    {
        $req->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:255',
            'image'       => 'nullable|image|max:2048'
        ]);

        $course->course_name = $req->course_name;
        $course->course_code = $req->course_code;
        $course->course_description = $req->course_description;
        $course->save();

        if ($req->hasFile('image')) {
            $blob = file_get_contents($req->file('image')->getRealPath());
            $mime = $req->file('image')->getMimeType();

            $course->image()->updateOrCreate(
                ['course_id' => $course->course_id],
                [
                    'image' => $blob,
                    'mime_type' => $mime ?? 'image/jpeg'
                ]
            );
        }

        return redirect()->back()->with('success', 'Course has been updated.');
    }

    public function deleteCourse(Courses $course)
    {
        $course->delete();
        $course->image()->delete();

        return redirect('/teachers-panel')->with('success', 'Course has been deleted.');
    }


    public function viewCourse(Courses $course, Sections $section)
    {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $sectionID);

        $students = Students::query()
            ->with([
                'user.image',
            ])
            ->leftJoin('studentprogress as sp', function ($q) use ($course) {
                $q->on('sp.student_id', '=', 'student.user_id')
                    ->where('sp.course_id', '=', $course->course_id);
            })
            ->where('section_id', $sectionID)
            ->join('user', 'user.user_id', '=', 'student.user_id')
            ->orderBy('user.last_name')
            ->orderBy('user.first_name')
            ->select('student.*')
            ->get();

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $course->load([
            'modules.moduleimage',
            'longquizzes',
            'screenings',
        ]);

        /* ── PHP sorts ──────────────────────────────────────────── */
        $course->modules     = $course->modules
            ->sortBy(fn($m)  => $this->seq($m->module_name))
            ->values();

        $course->longquizzes = $course->longquizzes
            ->sortBy(fn($lq) => $this->seq($lq->long_quiz_name))
            ->values();

        $course->screenings  = $course->screenings
            ->sortBy(fn($s)  => $this->seq($s->screening_name))
            ->values();

        return view('teacher.view-course', compact('course', 'users', 'students', 'section'));
    }

    public function viewStudentCoursePerformance(
        Courses $course,
        Sections $section,
        Students $student      // route-model
    ) {
        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $sectionID);
        abort_if($student->section_id !== $sectionID, 403);

        /* ----- roll-ups (helpers below) ----- */
        $overall   = $this->overallRow($course->course_id, $student->user_id, $sectionID);
        $practice  = $this->quizAverages($course->course_id, $student->user_id, 'practice');
        $short     = $this->quizAverages($course->course_id, $student->user_id, 'short');
        $long      = $this->longQuizAverages($course->course_id, $student->user_id);
        $screening = $this->screeningBest($course->course_id, $student->user_id);

        return view('teacher.student-performance', compact(
            'course',
            'section',
            'student',
            'overall',
            'practice',
            'short',
            'long',
            'screening',
            'users'
        ));
    }

    /* ========= helpers ========= */

    private function overallRow(string $courseId, string $studentId, string $sectionId)
    {
        /* alias the tables so every column is unambiguous ------------------- */
        $rows = StudentProgress::query()
            ->from('studentprogress as sp')
            ->join('student as st', 'st.user_id', '=', 'sp.student_id')
            ->where('sp.course_id',  $courseId)   // course filter
            ->where('st.section_id', $sectionId)  // section filter
            ->orderByDesc('sp.total_points')      // fully-qualified
            ->orderBy('sp.student_id')            // deterministic tie-break
            ->get([
                'sp.student_id',
                'sp.total_points',                // fully-qualified ‼
            ]);

        /* tie-aware ranks (duplicates allowed) ------------------------------ */
        $prevPts = null;
        $rank    = 0;

        foreach ($rows as $idx => $r) {
            if ($prevPts === null || $r->total_points < $prevPts) {
                $rank = $idx + 1;                 // increase only when score drops
            }
            $r->rank  = $rank;                    // attach for later use
            $prevPts  = $r->total_points;
        }

        /* return *my* row – or a harmless stub if I don’t have points yet --- */
        return $rows->firstWhere('student_id', $studentId)
            ?? (object) ['total_points' => 0, 'rank' => null];
    }


    private function quizAverages($courseId, $studentId, $type)
    {
        $quizType = ['practice' => 2, 'short' => 1][$type];
        return AssessmentResult::query()
            ->join('activity as a', 'assessmentresult.activity_id', '=', 'a.activity_id')
            ->join('quiz as q', 'a.activity_id', '=', 'q.activity_id')
            ->join('module as m', 'a.module_id', '=', 'm.module_id')
            ->selectRaw('m.module_name, a.activity_name as quiz_name,
                     AVG(score_percentage) as avg')
            ->where([
                ['assessmentresult.student_id', $studentId],
                ['assessmentresult.is_kept', 1],
                ['q.quiz_type_id', $quizType],
                ['m.course_id', $courseId]
            ])
            ->groupBy('m.module_name', 'a.activity_name')
            ->get();
    }

    private function longQuizAverages($courseId, $studentId)
    {
        return LongQuizAssessmentResult::query()
            ->join('longquiz as lq', 'long_assessmentresult.long_quiz_id', '=', 'lq.long_quiz_id')
            ->selectRaw('lq.long_quiz_name as quiz_name, AVG(score_percentage) as avg')
            ->where([
                ['student_id', $studentId],
                ['is_kept', 1],
                ['lq.course_id', $courseId]
            ])
            ->groupBy('lq.long_quiz_name')
            ->get();
    }

    private function screeningBest($courseId, $studentId)
    {
        return ScreeningResult::query()
            ->join('screening as s', 's.screening_id', '=', 'screeningresult.screening_id')
            ->selectRaw('s.screening_name, MAX(score_percentage) as best_score')
            ->where([
                ['screeningresult.student_id', $studentId],
                ['s.course_id', $courseId]
            ])
            ->groupBy('s.screening_name')
            ->get();
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Module CRUD
    public function createModule(Courses $course, Sections $section)
    {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $courses = Courses::with([
            'modules.moduleimage',
        ])->get();

        return view('teacher.module-create', compact('course', 'users', 'section'));
    }

    public function storeModule(Request $req, Courses $course, $sectionId)
    {
        $req->validate([
            'module_name'        => 'required|string|max:255',
            'image'              => 'nullable|image|max:2048'
        ]);

        $module = Modules::create([
            'module_id'         => Str::uuid()->toString(),
            'course_id'         => $course->course_id,
            'module_name'       => $req->module_name,
            'module_description' => $req->module_description
        ]);

        if ($req->hasFile('image')) {
            $blob = file_get_contents($req->file('image')->getRealPath());
            $mime = $req->file('image')->getMimeType();

            $module->moduleimage()->updateOrCreate(
                ['module_id' => $module->module_id],
                [
                    'image'     => $blob,
                    'mime_type' => $mime ?? 'image/jpeg'
                ]

            );
        }

        return redirect()->back()->with('success', 'A new module has been created.');
    }

    public function editModule(
        Courses $course,
        Sections $section,
        Modules $module
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        return view('teacher.module-edit', compact('course', 'section', 'module'));
    }

    public function updateModule(Request $req, $courseID, $sectionId, Modules $module)
    {
        $req->validate([
            'module_name'        => 'required|string|max:255',
            'image'              => 'nullable|image|max:2048'
        ]);

        $module->update($req->only('module_name', 'module_description'));

        if ($req->hasFile('image')) {
            $blob = file_get_contents($req->file('image')->getRealPath());
            $mime = $req->file('image')->getMimeType();

            $module->moduleimage()->updateOrCreate(
                ['module_id' => $module->module_id],
                [
                    'image'     => $blob,
                    'mime_type' => $mime ?? 'image/jpeg'
                ]

            );
        }

        return redirect()->back()->with('success', 'Module has been updated.');
    }

    public function deleteModule($courseID, $sectionId, Modules $module)
    {
        $module->delete();
        $module->moduleimage()->delete();

        return back()->with('success', 'Module deleted.');
    }

    public function viewModule(
        Courses $course,
        Sections $section,
        Modules $module
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $module->load(['activities.quiz']);

        /* ── sort activities by numeric order in title ──────────── */
        $module->activities = $module->activities
            ->sortBy(fn($a) => $this->seq($a->activity_name))
            ->values();


        return view('teacher.view-module', compact('course', 'section', 'module', 'users'));
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Long Quiz CRUD
    public function createLongQuiz(
        Courses $course,
        Sections $section
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        return view('teacher.longquiz-create', compact('course', 'section', 'users'));
    }

    /* 3-c  Store new quiz  */
    public function storeLongQuiz(Request $req, Courses $course, $sectionId)
    {
        /* ---------- validation ---------- */
        $rules = [
            'long_quiz_name'         => 'required|string|max:255',
            'long_quiz_instructions' => 'required|string',
            'number_of_attempts'     => 'required|integer|min:1',
            'number_of_questions'    => 'required|integer|min:1',
            'time_limit_minutes'     => 'required|integer|min:1',
            'unlock_date'            => 'required|date',
            'deadline_date'          => 'required|date|after:unlock_date',
            'has_answers_shown'      => 'nullable|boolean',

            /* question / option structure */
            'questions'                      => 'required|array|min:1',
            'questions.*.text'               => 'required|string',
            'questions.*.correct'            => 'required|integer|min:0',
            'questions.*.options'            => 'required|array|min:1|max:4',
            'questions.*.options.*'          => 'required|string',
            'questions.*.image'              => 'nullable|image|max:2048',
        ];

        $validator = Validator::make($req->all(), $rules);

        /* make sure the question bank ≥ draw size */
        $validator->after(function ($v) use ($req) {
            if (count($req->questions) < $req->number_of_questions) {
                $v->errors()->add(
                    'number_of_questions',
                    '“Number of Questions” can’t exceed the number of questions you entered.'
                );
            }
        });

        $validator->validate();

        /* ---------- DB tx ---------- */
        DB::transaction(function () use ($req, $course) {

            /* 1) quiz shell */
            $longquiz = LongQuizzes::create([
                'long_quiz_id'          => Str::uuid()->toString(),
                'course_id'             => $course->course_id,
                'long_quiz_name'        => $req->long_quiz_name,
                'long_quiz_instructions' => $req->long_quiz_instructions,
                'number_of_attempts'    => $req->number_of_attempts,
                'number_of_questions'   => $req->number_of_questions,
                'overall_points'        => $req->number_of_questions,
                'time_limit'            => $req->time_limit_minutes * 60,
                'has_answers_shown'     => $req->boolean('has_answers_shown'),
                'unlock_date'           => Carbon::parse($req->unlock_date),
                'deadline_date'         => Carbon::parse($req->deadline_date),
            ]);

            /* 2) questions + options */
            foreach ($req->questions as $qData) {

                $question = $longquiz->longquizquestions()->create([
                    'long_quiz_question_id' => Str::uuid()->toString(),
                    'question_text'         => $qData['text'],
                    'question_type_id'      => 1,
                    'score'                 => 1,
                ]);

                /* optional image */
                if (isset($qData['image'])) {
                    $img = $qData['image'];
                    $question->longquizimage()->updateOrCreate(
                        [],
                        [
                            'image'     => file_get_contents($img->getRealPath()),
                            'mime_type' => $img->getMimeType() ?? 'image/jpeg',
                        ]
                    );
                }

                /* options */
                foreach ($qData['options'] as $oIdx => $optText) {
                    $question->longquizoptions()->create([
                        'long_quiz_option_id' => Str::uuid()->toString(),
                        'option_text'         => $optText,
                        'is_correct'          => ($oIdx == $qData['correct']) ? 1 : 0,
                    ]);
                }
            }
        });

        return back()->with('success', 'Long-quiz created.');
    }

    /* 3-d  Edit form */
    public function editLongQuiz(
        Courses $course,
        Sections $section,
        LongQuizzes $longquiz
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        $longquiz->load('longquizquestions.longquizoptions');
        return view('teacher.longquiz-edit', compact('course', 'section', 'longquiz', 'users'));
    }

    /* 3-e  Update */
    public function updateLongQuiz(Request $req, Courses $course, $sectionId, LongQuizzes $longquiz)
    {
        /* same validation rules as above */
        $validator = Validator::make($req->all(), [
            'long_quiz_name'         => 'required|string|max:255',
            'long_quiz_instructions' => 'required|string',
            'number_of_attempts'     => 'required|integer|min:1',
            'number_of_questions'    => 'required|integer|min:1',
            'time_limit_minutes'     => 'required|integer|min:1',
            'unlock_date'            => 'required|date',
            'deadline_date'          => 'required|date|after:unlock_date',
            'has_answers_shown'      => 'nullable|boolean',

            'questions'                     => 'required|array|min:1',
            'questions.*.text'              => 'required|string',
            'questions.*.correct'           => 'required|integer|min:0',
            'questions.*.options'           => 'required|array|min:1|max:4',
            'questions.*.options.*.text'    => 'required|string',
            'questions.*.image'             => 'nullable|image|max:2048',
        ]);

        $validator->after(function ($v) use ($req) {
            if (count($req->questions) < $req->number_of_questions) {
                $v->errors()->add(
                    'number_of_questions',
                    '“Number of Questions” can’t exceed the number of questions you entered.'
                );
            }
        });

        $validator->validate();

        /* --------------- TX --------------- */
        DB::transaction(function () use ($req, $longquiz) {

            /* A) quiz meta */
            $longquiz->update([
                'long_quiz_name'         => $req->long_quiz_name,
                'long_quiz_instructions' => $req->long_quiz_instructions,
                'number_of_attempts'     => $req->number_of_attempts,
                'number_of_questions'    => $req->number_of_questions,
                'overall_points'         => $req->number_of_questions,
                'time_limit'             => $req->time_limit_minutes * 60,
                'has_answers_shown'      => $req->boolean('has_answers_shown'),
                'unlock_date'            => Carbon::parse($req->unlock_date),
                'deadline_date'          => Carbon::parse($req->deadline_date),
            ]);

            /* B) questions + options */
            $keptQ = [];

            foreach ($req->questions as $qData) {

                $qid = trim($qData['qid'] ?? '') ?: Str::uuid()->toString();

                $question = LongQuizQuestions::updateOrCreate(
                    ['long_quiz_question_id' => $qid],
                    [
                        'long_quiz_id'    => $longquiz->long_quiz_id,
                        'question_text'   => $qData['text'],
                        'question_type_id' => 1,
                        'score'           => 1,
                    ]
                );
                $keptQ[] = $question->long_quiz_question_id;

                /* image (replace only if new file chosen) */
                if (isset($qData['image'])) {
                    $img = $qData['image'];
                    $question->longquizimage()->updateOrCreate(
                        [],
                        [
                            'image'     => file_get_contents($img->getRealPath()),
                            'mime_type' => $img->getMimeType(),
                        ]
                    );
                }

                /* options */
                $keptO = [];
                foreach ($qData['options'] as $oIdx => $opt) {
                    $oid  = trim($opt['oid'] ?? '') ?: Str::uuid()->toString();
                    $row = LongQuizOptions::updateOrCreate(
                        ['long_quiz_option_id' => $oid],
                        [
                            'long_quiz_question_id' => $question->long_quiz_question_id,
                            'option_text'           => $opt['text'],
                            'is_correct'            => ($oIdx == $qData['correct']) ? 1 : 0,
                        ]
                    );
                    $keptO[] = $row->long_quiz_option_id;
                }

                /* delete dropped options */
                $question->longquizoptions()
                    ->whereNotIn('long_quiz_option_id', $keptO)
                    ->delete();
            }

            /* delete dropped questions */
            $longquiz->longquizquestions()
                ->whereNotIn('long_quiz_question_id', $keptQ)
                ->delete();
        });

        return back()->with('success', 'Long-quiz updated.');
    }

    /* 3-f  Destroy  –  cascades via FK or manual */
    public function deleteLongQuiz(Courses $course, $sectionId, LongQuizzes $longquiz)
    {
        $longquiz->delete();   // FK ON DELETE CASCADE wipes related Q/Opt/Img
        return back()->with('success', 'Long quiz deleted.');
    }

    public function viewLongQuiz(
        Courses $course,
        Sections $section,
        $longQuizID
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $longquiz = LongQuizzes::with([
            'longquizquestions.longquizoptions',
            'longquizquestions.longquizimage'
        ])
            ->where('course_id', $course->course_id)
            ->findOrFail($longQuizID);

        $questions = $longquiz->longquizquestions;

        $bestResults = LongQuizAssessmentResult::with(['student.user'])
            ->where([
                ['long_quiz_id', $longquiz->long_quiz_id],
                ['is_kept', 1],
            ])
            ->whereHas('student', fn($q) => $q->where('section_id', $sectionID))
            ->orderByDesc('score_percentage')
            ->get();

        return view('teacher.view-longquiz', compact('course', 'section', 'longquiz', 'users', 'bestResults', 'questions'));
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Lecture CRUD
    public function createLecture(
        Courses $course,
        Sections $section,
        Modules $module
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        return view('teacher.lecture-create', compact('course', 'section', 'module', 'users'));
    }

    public function storeLecture(Request $req, Courses $course, $sectionId, Modules $module)
    {
        $req->validate([
            'activity_name'           => 'required|string|max:255',
            'activity_description'    => 'required|string|max:255',
            'pdf'                     => 'required|file|mimes:pdf|max:2048'
        ]);

        $activity = Activities::create([
            'activity_id'         => Str::uuid()->toString(),
            'module_id'         => $module->module_id,
            'activity_type'       => 'LECTURE',
            'activity_name'       => $req->activity_name,
            'activity_description' => $req->activity_description,
            'unlock_date'          => $req->unlock_date,
            'deadline_date'        => null

        ]);

        if ($req->hasFile('pdf')) {
            $blob = file_get_contents($req->file('pdf')->getRealPath());
            $mime = $req->file('pdf')->getMimeType();

            $activity->lecture()->updateOrCreate(
                ['activity_id' => $activity->activity_id],           // match column
                [
                    'content_type_id' => 2,                           // “PDF/DOCS”
                    'file_url'        => $blob,                     // store blob
                    'file_mime_type'  => $mime,
                    'file_name'       => $req->file('pdf')->getClientOriginalName(),
                ]
            );
        }

        return back()->with('success', 'A new lecture has been created.');
    }

    public function editLecture(
        Courses $course,
        Sections $section,
        Modules $module,
        Activities $activity
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('lecture');
        return view('teacher.lecture-edit', compact('course', 'section', 'module', 'activity', 'users'));
    }

    public function updateLecture(Request $req, Courses $course, $sectionId, Modules $module, Activities $activity)
    {
        $req->validate([
            'activity_name'           => 'required|string|max:255',
            'activity_description'    => 'required|string|max:255',
            'pdf'                     => 'nullable|file|mimes:pdf|max:2048'
        ]);

        $activity->update([
            'activity_name'        => $req->activity_name,
            'activity_description' => $req->activity_description,
            'unlock_date'          => $req->unlock_date,
        ]);

        if ($req->hasFile('pdf')) {
            $blob = file_get_contents($req->file('pdf')->getRealPath());
            $mime = $req->file('pdf')->getMimeType();

            $activity->lecture()->updateOrCreate(
                ['activity_id' => $activity->activity_id],           // match column
                [
                    'content_type_id' => 2,                           // “PDF/DOCS”
                    'file_url'        => $blob,                     // store blob
                    'file_mime_type'  => $mime,
                    'file_name'       => $req->file('pdf')->getClientOriginalName(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Lecture material has been updated.');
    }

    public function deleteLecture(Courses $course, $sectionId, Modules $module, Activities $activity)
    {
        $activity->delete();
        return back()->with('success', 'Lecture deleted');
    }

    public function viewLecture(
        Courses $course,
        Sections $section,
        Modules $module,
        Activities $activity
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $activity->load('lecture');

        return view('teacher.view-lecture', compact('course', 'section', 'module', 'activity', 'users'));
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Tutorial Video CRUD
    public function createTutorial(
        Courses $course,
        Sections $section,
        Modules $module
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        return view('teacher.tutorial-create', compact('course', 'section', 'module', 'users'));
    }

    /* ────────────────────────────────────────────────────────────────
   3-b  Store new record
   ───────────────────────────────────────────────────────────────*/
    public function storeTutorial(
        Request $req,
        Courses $course,
        $sectionId,
        Modules $module
    ) {
        $req->validate([
            'activity_name'        => 'required|string|max:255',
            'activity_description' => 'required|string|max:255',
            'video_url'            => 'required|url',
            'unlock_date'          => 'required|date',
        ]);

        /* 1) parent activity */
        $activity = Activities::create([
            'activity_id'         => Str::uuid()->toString(),
            'module_id'           => $module->module_id,
            'activity_type'       => 'TUTORIAL',
            'activity_name'       => $req->activity_name,
            'activity_description' => $req->activity_description,
            'unlock_date'         => Carbon::parse($req->unlock_date),
            'deadline_date'       => null,
        ]);

        /* 2) tutorial row */
        $activity->tutorial()->create([
            'content_type_id' => 3,                // VIDEO
            'video_url'       => $req->video_url,
        ]);

        return back()->with('success', 'Tutorial video has been posted.');
    }

    /* ────────────────────────────────────────────────────────────────
   3-c  Edit form
   ───────────────────────────────────────────────────────────────*/
    public function editTutorial(
        Courses $course,
        Sections $section,
        Modules $module,
        Activities $activity
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('tutorial');
        return view('teacher.tutorial-edit', compact('course', 'section', 'module', 'activity', 'users'));
    }

    /* ────────────────────────────────────────────────────────────────
   3-d  Update
   ───────────────────────────────────────────────────────────────*/
    public function updateTutorial(Request $req, Courses $course, $sectionId, Modules $module, Activities $activity)
    {
        $req->validate([
            'activity_name'        => 'required|string|max:255',
            'activity_description' => 'required|string|max:255',
            'video_url'            => 'required|url',
            'unlock_date'          => 'required|date',
        ]);

        $activity->update([
            'activity_name'        => $req->activity_name,
            'activity_description' => $req->activity_description,
            'unlock_date'          => Carbon::parse($req->unlock_date),
        ]);

        $activity->tutorial()->updateOrCreate(
            ['activity_id' => $activity->activity_id],
            [
                'content_type_id' => 3,
                'video_url'       => $req->video_url,
            ]
        );

        return back()->with('success', 'Tutorial updated.');
    }

    /* ────────────────────────────────────────────────────────────────
   3-e  Delete
   ───────────────────────────────────────────────────────────────*/
    public function deleteTutorial(Courses $course, $sectionId, Modules $module, Activities $activity)
    {
        $activity->delete();
        return back()->with('success', 'Tutorial video deleted.');
    }

    /* ────────────────────────────────────────────────────────────────
   3-f  View (teacher or student)
   ───────────────────────────────────────────────────────────────*/
    public function viewTutorial(
        Courses $course,
        Sections $section,
        Modules $module,
        Activities $activity
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('tutorial');
        return view('teacher.view-tutorial', compact('course', 'section', 'module', 'activity', 'users'));
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Short Quiz CRUD
    public function createShortQuiz(
        Courses $course,
        Sections $section,
        Modules $module
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        return view('teacher.shortquiz-create', compact('course', 'section', 'module', 'users'));
    }

    /* 2 ─────────────── Store */
    public function storeShortQuiz(Request $req, Courses $course, $sectionId, Modules $module)
    {
        /* ▸ a) validate ------------------------------------------------------- */
        $rules = [
            'quiz_name'            => 'required|string|max:255',
            'quiz_instructions'    => 'required|string',
            'number_of_attempts'   => 'required|integer|min:1',
            'number_of_questions'  => 'required|integer|min:1',
            'time_limit_minutes'   => 'required|integer|min:1',
            'unlock_date'          => 'required|date',
            'deadline_date'        => 'required|date|after:unlock_date',
            'has_answers_shown'    => 'nullable|boolean',

            'questions'                     => 'required|array|min:1',
            'questions.*.text'              => 'required|string',
            'questions.*.correct'           => 'required|integer|min:0',
            'questions.*.options'           => 'required|array|min:1|max:4',
            'questions.*.options.*'         => 'required|string',
            'questions.*.image'             => 'nullable|image|max:2048',
        ];

        $validator = Validator::make($req->all(), $rules);
        $validator->after(function ($v) use ($req) {
            if (count($req->questions) < $req->number_of_questions) {
                $v->errors()->add(
                    'number_of_questions',
                    '“Number of Questions” can’t exceed the number of questions you entered.'
                );
            }
        });
        $validator->validate();

        /* ▸ b) TX ------------------------------------------------------------- */
        DB::transaction(function () use ($req, $module) {

            /* i) activity row */
            $activityID = Str::uuid()->toString();
            $activity   = Activities::create([
                'activity_id'         => $activityID,
                'module_id'           => $module->module_id,
                'activity_type'       => 'QUIZ',
                'activity_name'       => $req->quiz_name,
                'activity_description' => $req->quiz_instructions,
                'unlock_date'         => Carbon::parse($req->unlock_date),
                'deadline_date'       => Carbon::parse($req->deadline_date),
            ]);

            /* ii) quiz row (short = id 1) */
            Quizzes::create([
                'activity_id'        => $activityID,
                'number_of_attempts' => $req->number_of_attempts,
                'quiz_type_id'       => 1,                       // short-quiz
                'time_limit'         => $req->time_limit_minutes * 60,
                'number_of_questions' => $req->number_of_questions,
                'overall_points'     => $req->number_of_questions,
                'has_answers_shown'  => $req->boolean('has_answers_shown'),
            ]);

            /* iii) question bank */
            foreach ($req->questions as $qIdx => $qData) {

                $questionID = Str::uuid()->toString();
                $question   = Questions::create([
                    'question_id'     => $questionID,
                    'question_text'   => $qData['text'],
                    'question_type_id' => 1,
                    'score'           => 1,
                    'activity_id'     => $activityID,
                ]);

                /* optional image */
                if (isset($qData['image'])) {
                    $img = $qData['image'];
                    QuestionImages::updateOrCreate(
                        ['question_id' => $questionID],
                        [
                            'image' => file_get_contents($img->getRealPath())
                        ]
                    );
                }

                /* options */
                foreach ($qData['options'] as $oIdx => $optText) {
                    Options::create([
                        'option_id'  => Str::uuid()->toString(),
                        'question_id' => $questionID,
                        'option_text' => $optText,
                        'is_correct' => ($oIdx == $qData['correct']) ? 1 : 0,
                    ]);
                }
            }
        });

        return back()->with('success', 'Short-quiz created.');
    }

    /* 3 ─────────────── Edit form */
    public function editShortQuiz(
        Courses $course,
        Sections $section,
        Modules $module,
        Activities $activity
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('quiz', 'quiz.questions.options', 'quiz.questions.questionimage');
        return view('teacher.shortquiz-edit', compact('course', 'section', 'module', 'activity', 'users'));
    }

    /* 4 ─────────────── Update */
    public function updateShortQuiz(Request $req, Courses $course, $sectionId, Modules $module, Activities $activity)
    {
        /* same validation as store … */
        $rules = [
            'quiz_name'            => 'required|string|max:255',
            'quiz_instructions'    => 'required|string',
            'number_of_attempts'   => 'required|integer|min:1',
            'number_of_questions'  => 'required|integer|min:1',
            'time_limit_minutes'   => 'required|integer|min:1',
            'unlock_date'          => 'required|date',
            'deadline_date'        => 'required|date|after:unlock_date',
            'has_answers_shown'    => 'nullable|boolean',

            'questions'                     => 'required|array|min:1',
            'questions.*.text'              => 'required|string',
            'questions.*.correct'           => 'required|integer|min:0',
            'questions.*.options'           => 'required|array|min:1|max:4',
            'questions.*.options.*.text'    => 'required|string',
            'questions.*.image'             => 'nullable|image|max:2048',

        ];

        $validator = Validator::make($req->all(), $rules);
        $validator->after(function ($v) use ($req) {
            if (count($req->questions) < $req->number_of_questions) {
                $v->errors()->add(
                    'number_of_questions',
                    '“Number of Questions” can’t exceed the number of questions you entered.'
                );
            }
        });
        $validator->validate();

        DB::transaction(function () use ($req, $activity) {

            /* A) activity + quiz meta */
            $activity->update([
                'activity_name'        => $req->quiz_name,
                'activity_description' => $req->quiz_instructions,
                'unlock_date'          => Carbon::parse($req->unlock_date),
                'deadline_date'        => Carbon::parse($req->deadline_date),
            ]);

            $activity->quiz()->update([
                'number_of_attempts'  => $req->number_of_attempts,
                'number_of_questions' => $req->number_of_questions,
                'overall_points'      => $req->number_of_questions,
                'time_limit'          => $req->time_limit_minutes * 60,
                'has_answers_shown'   => $req->boolean('has_answers_shown'),
            ]);

            /* B) questions */
            $keptQ = [];

            foreach ($req->questions as $qIdx => $qData) {

                $qid = trim($qData['qid'] ?? '') ?: Str::uuid()->toString();

                $question = Questions::updateOrCreate(
                    ['question_id' => $qid],
                    [
                        'activity_id'      => $activity->activity_id,
                        'question_text'    => $qData['text'],
                        'question_type_id' => 1,
                        'score'            => 1,
                    ]
                );
                $keptQ[] = $qid;

                /* image replacement */
                if (isset($qData['image'])) {
                    $img = $qData['image'];
                    QuestionImages::updateOrCreate(
                        ['question_id' => $qid],
                        ['image' => file_get_contents($img->getRealPath())]
                    );
                }

                /* options */
                $keptO = [];
                foreach ($qData['options'] as $oIdx => $opt) {
                    $oid = trim($opt['oid'] ?? '') ?: Str::uuid()->toString();
                    $row = Options::updateOrCreate(
                        ['option_id' => $oid],
                        [
                            'question_id' => $qid,
                            'option_text' => $opt['text'],
                            'is_correct'  => ($oIdx == $qData['correct']) ? 1 : 0,
                        ]
                    );
                    $keptO[] = $oid;
                }
                Options::where('question_id', $qid)
                    ->whereNotIn('option_id', $keptO)
                    ->delete();
            }

            /* delete removed questions */
            Questions::where('activity_id', $activity->activity_id)
                ->whereNotIn('question_id', $keptQ)
                ->delete();
        });

        return back()->with('success', 'Short-quiz updated.');
    }

    /* 5 ─────────────── Destroy */
    public function deleteShortQuiz(Courses $course, Sections $section, Modules $module, Activities $activity)
    {
        $activity->delete();   // cascades to quiz / questions / options via FK
        return back()->with('success', 'Short-quiz deleted.');
    }

    /* 6 ─────────────── View (read-only) */
    public function viewShortQuiz(Courses $course, Sections $section, Modules $module, Activities $activity)
    {

        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('quiz.questions.options', 'quiz.questions.questionimage');
        $questions = Questions::where('activity_id', $activity->activity_id)
            ->with(['options' => fn($q) => $q->orderBy('option_id')])   // keep original order
            ->orderBy('question_id')
            ->get();

        $bestResults = AssessmentResult::with(['student.user'])
            ->where([
                ['activity_id',  $activity->quiz->activity_id],
                ['is_kept', 1],
            ])
            ->whereHas('student', fn($q) => $q->where('section_id', $sectionID))
            ->orderByDesc('score_percentage')
            ->get();

        return view('teacher.view-shortquiz', compact('course', 'section', 'module', 'activity', 'bestResults', 'questions', 'users'));
    }


    // ---------------------------------------------
    // ---------------------------------------------
    // Practice Quiz CRUD
    public function createPracticeQuiz(
        Courses $course,
        Sections $section,
        Modules $module
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        return view('teacher.practicequiz-create', compact('course', 'section', 'module', 'users'));
    }

    /* 2 ─────────────── Store */
    public function storePracticeQuiz(Request $req, $sectionId, Courses $course, Modules $module)
    {
        /* a) validation — identical to short-quiz but without attempts field */
        $rules = [
            'quiz_name'           => 'required|string|max:255',
            'quiz_instructions'   => 'required|string',
            'number_of_questions' => 'required|integer|min:1',
            'time_limit_minutes'  => 'required|integer|min:1',
            'unlock_date'         => 'required|date',
            'deadline_date'       => 'required|date|after:unlock_date',
            'has_answers_shown'   => 'nullable|boolean',

            'questions'                    => 'required|array|min:1',
            'questions.*.text'             => 'required|string',
            'questions.*.correct'          => 'required|integer|min:0',
            'questions.*.options'          => 'required|array|min:1|max:4',
            'questions.*.options.*'        => 'required|string',
            'questions.*.image'            => 'nullable|image|max:2048',
        ];

        $validator = Validator::make($req->all(), $rules);
        $validator->after(function ($v) use ($req) {
            if (count($req->questions) < $req->number_of_questions) {
                $v->errors()->add(
                    'number_of_questions',
                    '“Number of Questions” can’t exceed the questions you entered.'
                );
            }
        });
        $validator->validate();

        /* b) DB-TX ----------------------------------------------------------- */
        DB::transaction(function () use ($req, $module) {

            /* i) parent activity row */
            $activityID = Str::uuid()->toString();
            Activities::create([
                'activity_id'         => $activityID,
                'module_id'           => $module->module_id,
                'activity_type'       => 'QUIZ',
                'activity_name'       => $req->quiz_name,
                'activity_description' => $req->quiz_instructions,
                'unlock_date'         => Carbon::parse($req->unlock_date),
                'deadline_date'       => Carbon::parse($req->deadline_date),
            ]);

            /* ii) quiz row – quiz_type_id = 2, attempts = INT(11) max */
            Quizzes::create([
                'activity_id'         => $activityID,
                'number_of_attempts'  => 2147483647,   // infinite
                'quiz_type_id'        => 2,            // practice-quiz
                'time_limit'          => $req->time_limit_minutes * 60,
                'number_of_questions' => $req->number_of_questions,
                'overall_points'      => $req->number_of_questions,
                'has_answers_shown'   => $req->boolean('has_answers_shown'),
            ]);

            /* iii) question bank (unchanged) */
            foreach ($req->questions as $qIdx => $qData) {
                $questionID = Str::uuid()->toString();
                Questions::create([
                    'question_id'      => $questionID,
                    'question_text'    => $qData['text'],
                    'question_type_id' => 1,
                    'score'            => 1,
                    'activity_id'      => $activityID,
                ]);

                if (isset($qData['image'])) {
                    $img = $qData['image'];
                    QuestionImages::updateOrCreate(
                        ['question_id' => $questionID],
                        ['image' => file_get_contents($img->getRealPath())]
                    );
                }

                foreach ($qData['options'] as $oIdx => $optText) {
                    Options::create([
                        'option_id'   => Str::uuid()->toString(),
                        'question_id' => $questionID,
                        'option_text' => $optText,
                        'is_correct'  => ($oIdx == $qData['correct']) ? 1 : 0,
                    ]);
                }
            }
        });

        return  back()->with('success', 'Practice-quiz created.');
    }

    /* 3 ─────────────── Edit form */
    public function editPracticeQuiz(
        Courses $course,
        Sections $section,
        Modules $module,
        Activities $activity
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('quiz', 'quiz.questions.options', 'quiz.questions.questionimage');
        return view('teacher.practicequiz-edit', compact('course', 'section', 'module', 'activity', 'users'));
    }

    /* 4 ─────────────── Update */
    public function updatePracticeQuiz(Request $req, Courses $course, $sectionId, Modules $module, Activities $activity)
    {
        /* identical rules as store, still no attempts field */
        $rules = [
            'quiz_name'           => 'required|string|max:255',
            'quiz_instructions'   => 'required|string',
            'number_of_questions' => 'required|integer|min:1',
            'time_limit_minutes'  => 'required|integer|min:1',
            'unlock_date'         => 'required|date',
            'deadline_date'       => 'required|date|after:unlock_date',
            'has_answers_shown'   => 'nullable|boolean',

            'questions'                    => 'required|array|min:1',
            'questions.*.text'             => 'required|string',
            'questions.*.correct'          => 'required|integer|min:0',
            'questions.*.options'          => 'required|array|min:1|max:4',
            'questions.*.options.*.text'   => 'required|string',
            'questions.*.image'            => 'nullable|image|max:2048',
        ];
        $validator = Validator::make($req->all(), $rules);
        $validator->after(function ($v) use ($req) {
            if (count($req->questions) < $req->number_of_questions) {
                $v->errors()->add(
                    'number_of_questions',
                    '“Number of Questions” can’t exceed the questions you entered.'
                );
            }
        });
        $validator->validate();

        DB::transaction(function () use ($req, $activity) {

            /* A) activity + quiz meta */
            $activity->update([
                'activity_name'        => $req->quiz_name,
                'activity_description' => $req->quiz_instructions,
                'unlock_date'          => Carbon::parse($req->unlock_date),
                'deadline_date'        => Carbon::parse($req->deadline_date),
            ]);

            $activity->quiz()->update([
                'number_of_attempts'  => 2147483647,
                'number_of_questions' => $req->number_of_questions,
                'overall_points'      => $req->number_of_questions,
                'time_limit'          => $req->time_limit_minutes * 60,
                'has_answers_shown'   => $req->boolean('has_answers_shown'),
            ]);

            /* B) questions + options (same logic as short-quiz update) */
            $keptQ = [];

            foreach ($req->questions as $qData) {
                $qid = trim($qData['qid'] ?? '') ?: Str::uuid()->toString();

                $question = Questions::updateOrCreate(
                    ['question_id' => $qid],
                    [
                        'activity_id'      => $activity->activity_id,
                        'question_text'    => $qData['text'],
                        'question_type_id' => 1,
                        'score'            => 1,
                    ]
                );
                $keptQ[] = $qid;

                if (isset($qData['image'])) {
                    $img = $qData['image'];
                    QuestionImages::updateOrCreate(
                        ['question_id' => $qid],
                        ['image' => file_get_contents($img->getRealPath())]
                    );
                }

                $keptO = [];
                foreach ($qData['options'] as $oIdx => $opt) {
                    $oid = trim($opt['oid'] ?? '') ?: Str::uuid()->toString();
                    Options::updateOrCreate(
                        ['option_id' => $oid],
                        [
                            'question_id' => $qid,
                            'option_text' => $opt['text'],
                            'is_correct'  => ($oIdx == $qData['correct']) ? 1 : 0,
                        ]
                    );
                    $keptO[] = $oid;
                }
                Options::where('question_id', $qid)
                    ->whereNotIn('option_id', $keptO)
                    ->delete();
            }

            Questions::where('activity_id', $activity->activity_id)
                ->whereNotIn('question_id', $keptQ)
                ->delete();
        });

        return back()->with('success', 'Practice-quiz updated.');
    }

    /* 5 ─────────────── Destroy */
    public function deletePracticeQuiz(Courses $course, $sectionId, Modules $module, Activities $activity)
    {
        $activity->delete();  // cascades to quiz / questions / options via FK
        return back()->with('success', 'Practice-quiz deleted.');
    }

    /* 6 ─────────────── View (read-only) */
    public function viewPracticeQuiz(
        Courses $course,
        Sections $section,
        Modules $module,
        Activities $activity
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('quiz.questions.options', 'quiz.questions.questionimage');
        $questions = Questions::where('activity_id', $activity->activity_id)
            ->with(['options' => fn($q) => $q->orderBy('option_id')])   // keep original order
            ->orderBy('question_id')
            ->get();

        $bestResults = AssessmentResult::with(['student.user'])
            ->where([
                ['activity_id',  $activity->quiz->activity_id],
                ['is_kept', 1],
            ])
            ->whereHas('student', fn($q) => $q->where('section_id', $sectionID))
            ->orderByDesc('score_percentage')
            ->get();

        return view('teacher.view-practicequiz', compact('course', 'section', 'module', 'activity', 'bestResults', 'questions', 'users'));
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Screening Exam CRUD
    public function createScreening(
        Courses $course,
        Sections $section
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        return view('teacher.screening-create', compact('course', 'section', 'users'));
    }

    /* 2 ── Store new screening */
    public function storeScreening(Request $req, Courses $course, $sectionId)
    {
        /* a)  validation ---------------------------------------------------- */
        $rules = [
            'screening_name'         => 'required|string|max:255',
            'screening_instructions' => 'required|string',
            'number_of_questions'    => 'required|integer|min:1',
            'time_limit_minutes'     => 'required|integer|min:1',
            'has_answers_shown'      => 'nullable|boolean',

            /* concept / topic / question tree */
            'concepts'                                 => 'required|array|min:1',
            'concepts.*.concept_name'                  => 'required|string',
            'concepts.*.topics'                        => 'required|array|min:1',
            'concepts.*.topics.*.topic_name'           => 'required|string',
            'concepts.*.topics.*.questions'            => 'required|array|min:1',
            'concepts.*.topics.*.questions.*.text'     => 'required|string',
            'concepts.*.topics.*.questions.*.correct'  => 'required|integer|min:0',
            'concepts.*.topics.*.questions.*.options'  => 'required|array|min:1|max:4',
            'concepts.*.topics.*.questions.*.options.*' => 'required|string',
            'concepts.*.topics.*.questions.*.image'    => 'nullable|image|max:2048',
        ];

        Validator::make($req->all(), $rules)->validate();

        /* b)  transaction --------------------------------------------------- */
        DB::transaction(function () use ($req, $course) {

            /*  i) screening header  */
            $screeningID = Str::uuid()->toString();
            $screening = Screening::create([
                'screening_id'         => $screeningID,
                'course_id'            => $course->course_id,
                'screening_name'       => $req->screening_name,
                'screening_instructions' => $req->screening_instructions,
                'number_of_questions'  => $req->number_of_questions,
                'overall_points'       => $req->number_of_questions,
                'time_limit'           => $req->time_limit_minutes * 60,
                'number_of_attempts'   => PHP_INT_MAX,          // ∞ attempts
                'has_answers_shown'    => $req->boolean('has_answers_shown'),
                'unlock_date'          => Carbon::parse($req->unlock_date),
                'deadline_date'        => Carbon::parse($req->deadline_date),
            ]);

            /* ii) loop concepts → topics → questions ----------------------- */
            foreach ($req->concepts as $cData) {

                $conceptID = Str::of($cData['concept_name'])
                    ->trim()
                    ->replace(' ', '_')
                    ->lower();

                if (ScreeningConcept::where([
                    ['screening_concept_id', $conceptID],
                    ['screening_id', $screeningID],
                ])->exists()) {
                    $conceptID = $conceptID . '_' . Str::uuid()->toString();
                }

                $concept = ScreeningConcept::updateOrCreate(
                    ['screening_concept_id' => $conceptID],
                    [
                        'screening_id'  => $screeningID,
                        'concept_name'  => $cData['concept_name'],
                        'passing_score' => 60,
                    ]
                );

                foreach ($cData['topics'] as $tData) {

                    $topicID = Str::of($tData['topic_name'])
                        ->trim()->replace(' ', '_')->lower();

                    if (ScreeningTopic::where([
                        ['screening_topic_id', $topicID],
                        ['screening_concept_id', $conceptID],
                    ])->exists()) {
                        $topicID = $topicID . '_' . Str::uuid()->toString();
                    }

                    $topic = ScreeningTopic::updateOrCreate(
                        ['screening_topic_id' => $topicID],
                        [
                            'screening_concept_id' => $conceptID,
                            'topic_name'           => $tData['topic_name'],
                        ]
                    );

                    foreach ($tData['questions'] as $qIdx => $qData) {

                        $qid = Str::uuid()->toString();
                        $question = $topic->questions()->create([
                            'screening_question_id'      => $topicID,
                            'screening_topic_id'      => $qid,
                            'question_text'    => $qData['text'],
                            'question_type_id' => 1,    // MCQ
                            'score'            => 1,
                        ]);

                        /* optional image */
                        if (isset($qData['image'])) {
                            $img = $qData['image'];
                            $question->image()->updateOrCreate(
                                [],
                                [
                                    'image'     => file_get_contents($img->getRealPath()),
                                    'mime_type' => $img->getMimeType() ?? 'image/jpeg',
                                ]
                            );
                        }

                        /* options */
                        foreach ($qData['options'] as $oIdx => $optTxt) {
                            $question->options()->create([
                                'screening_option_id'   => Str::uuid()->toString(),
                                'screening_question_id' => $qid,
                                'option_text'           => $optTxt,
                                'is_correct'            => ($oIdx == $qData['correct']) ? 1 : 0,
                            ]);
                        }
                    }
                }
            }
        });

        return  back()->with('success', 'Screening exam created.');
    }

    /* 3 ── Edit form  */
    public function editScreening(
        Courses $course,
        Sections $section,
        Screening $screening
    ) {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        $users = Users::with('image')->findOrFail(session('user_id'));
        $screening->load('concepts.topics.questions.options', 'concepts.topics.questions.image');
        return view('teacher.screening-edit', compact('course', 'section', 'screening', 'users'));
    }

    /* 4 ── Update  */
    public function updateScreening(
        Request $req,
        Courses  $course,
        Sections $section,
        Screening $screening
    ) {
        /* a) validate – identical rules ------------------------------------ */
        $rules = [
            'screening_name'         => 'required|string|max:255',
            'screening_instructions' => 'required|string',
            'number_of_questions'    => 'required|integer|min:1',
            'time_limit_minutes'     => 'required|integer|min:1',
            'has_answers_shown'      => 'nullable|boolean',

            'concepts'                                 => 'required|array|min:1',
            'concepts.*.concept_name'                  => 'required|string',
            'concepts.*.topics'                        => 'required|array|min:1',
            'concepts.*.topics.*.topic_name'           => 'required|string',
            'concepts.*.topics.*.questions'            => 'required|array|min:1',
            'concepts.*.topics.*.questions.*.text'     => 'required|string',
            'concepts.*.topics.*.questions.*.correct'  => 'required|integer|min:0',
            'concepts.*.topics.*.questions.*.options'  => 'required|array|min:1|max:4',
            'concepts.*.topics.*.questions.*.options.*' => 'required|string',
            'concepts.*.topics.*.questions.*.image'    => 'nullable|image|max:2048',
        ];
        Validator::make($req->all(), $rules)->validate();

        /* b) transaction --------------------------------------------------- */
        DB::transaction(function () use ($req, $screening) {

            /* ── A) update header row ─────────────────────────────────── */
            $screening->update([
                'screening_name'        => $req->screening_name,
                'screening_instructions' => $req->screening_instructions,
                'number_of_questions'   => $req->number_of_questions,
                'overall_points'        => $req->number_of_questions,
                'time_limit'            => $req->time_limit_minutes * 60,
                'has_answers_shown'     => $req->boolean('has_answers_shown'),
                'unlock_date'           => Carbon::parse($req->unlock_date),
                'deadline_date'         => Carbon::parse($req->deadline_date),
            ]);

            /* ── B) sync concepts / topics / questions ─────────────────── */
            $keepConcept = [];

            foreach ($req->concepts as $cData) {

                // ------------ concept ------------
                $conceptID = Str::of($cData['concept_name'])
                    ->trim()->replace(' ', '_')->lower();
                // keep existing id if form sent one
                $conceptID = $cData['concept_id'] ?? $conceptID;

                $concept = ScreeningConcept::updateOrCreate(
                    ['screening_concept_id' => $conceptID],
                    [
                        'screening_id'  => $screening->screening_id,
                        'concept_name'  => $cData['concept_name'],
                        'passing_score' => 60,
                    ]
                );
                $keepConcept[] = $conceptID;

                $keepTopic = [];

                foreach ($cData['topics'] as $tData) {

                    // ---------- topic ----------
                    $topicID = Str::of($tData['topic_name'])
                        ->trim()->replace(' ', '_')->lower();
                    $topicID = $tData['topic_id'] ?? $topicID;

                    $topic = ScreeningTopic::updateOrCreate(
                        ['screening_topic_id' => $topicID],
                        [
                            'screening_concept_id' => $conceptID,
                            'topic_name'           => $tData['topic_name'],
                        ]
                    );
                    $keepTopic[] = $topicID;

                    $keepQ = [];

                    foreach ($tData['questions'] as $qData) {

                        // ------ question -------
                        $qID = $qData['question_id'] ?? Str::uuid()->toString();

                        $question = ScreeningQuestion::updateOrCreate(
                            ['screening_question_id' => $qID],
                            [
                                'screening_topic_id' => $topicID,
                                'question_text'      => $qData['text'],
                                'question_type_id'   => 1,
                                'score'              => 1,
                            ]
                        );
                        $keepQ[] = $qID;

                        // image (replace iff file present)
                        if (isset($qData['image'])) {
                            $img = $qData['image'];
                            $question->image()->updateOrCreate(
                                [],
                                [
                                    'image'     => file_get_contents($img->getRealPath()),
                                    'mime_type' => $img->getMimeType() ?? 'image/jpeg',
                                ]
                            );
                        }

                        // options
                        $keepOpt = [];
                        foreach ($qData['options'] as $oIdx => $optTxt) {
                            $oID = $qData['option_ids'][$oIdx] ?? Str::uuid()->toString();
                            $row = ScreeningOption::updateOrCreate(
                                ['screening_option_id' => $oID],
                                [
                                    'screening_question_id' => $qID,
                                    'option_text'           => $optTxt,
                                    'is_correct'            => ($oIdx == $qData['correct']) ? 1 : 0,
                                ]
                            );
                            $keepOpt[] = $row->screening_option_id;
                        }
                        // prune dropped options
                        $question->options()
                            ->whereNotIn('screening_option_id', $keepOpt)
                            ->delete();
                    }
                    // prune dropped questions
                    $topic->questions()
                        ->whereNotIn('screening_question_id', $keepQ)
                        ->delete();
                }
                // prune dropped topics
                $concept->topics()
                    ->whereNotIn('screening_topic_id', $keepTopic)
                    ->delete();
            }
            // prune dropped concepts
            $screening->concepts()
                ->whereNotIn('screening_concept_id', $keepConcept)
                ->delete();
        });

        return back()->with('success', 'Screening exam updated.');
    }

    /* 5 ── Delete */
    public function deleteScreening(Courses $course, Sections $section, Screening $screening)
    {
        $screening->delete();
        $screening->image()->delete();

        return back()->with('success', 'Screening exam deleted.');
    }


    public function viewScreening(Courses $course, Sections $section, Screening $screening)
    {
        if ($redirect = $this->checkTeacherAccess()) return $redirect;
        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        // teacher profile pic, etc.
        $users = Users::with('image')
            ->findOrFail(session('user_id'));

        /* pull everything in **one** eager-load so we never touch
       the DB inside the view                                         */
        $screening->load([
            'concepts.topics.questions.options',   // MCQ options
            'concepts.topics.questions.image'      // optional blob per Q
        ]);

        /* ── flatten all questions to one collection
          (useful if you still need $questions somewhere else)       */
        $questions = $screening->concepts
            ->pluck('topics')          // collection of topics per concept
            ->flatten()
            ->pluck('questions')       // collection of questions per topic
            ->flatten();

        $bestResults = ScreeningResult::with(['student.user'])
            ->where([
                ['screening_id',  $screening->screening_id],
                ['is_kept', 1],
            ])
            ->whereHas('student', fn($q) => $q->where('section_id', $sectionID))
            ->orderByDesc('score_percentage')
            ->get();

        return view(
            'teacher.view-screening',
            compact('course', 'section', 'screening', 'users', 'bestResults', 'questions')
        );
    }

    /* Helper – reuse the validation array in update() */
    private function screeningRules(): array
    {
        return [
            'screening_name'         => 'required|string|max:255',
            'screening_instructions' => 'required|string',
            'number_of_questions'    => 'required|integer|min:1',
            'time_limit_minutes'     => 'required|integer|min:1',
            'unlock_date'            => 'required|date',
            'deadline_date'          => 'required|date|after:unlock_date',
            'has_answers_shown'      => 'nullable|boolean',

            'concepts'                                 => 'required|array|min:1',
            'concepts.*.concept_name'                  => 'required|string',
            'concepts.*.topics'                        => 'required|array|min:1',
            'concepts.*.topics.*.topic_name'           => 'required|string',
            'concepts.*.topics.*.questions'            => 'required|array|min:1',
            'concepts.*.topics.*.questions.*.text'     => 'required|string',
            'concepts.*.topics.*.questions.*.correct'  => 'required|integer|min:0',
            'concepts.*.topics.*.questions.*.options'  => 'required|array|min:1|max:4',
            'concepts.*.topics.*.questions.*.options.*' => 'required|string',
            'concepts.*.topics.*.questions.*.image'    => 'nullable|image|max:2048',
        ];
    }

    public function editScreeningResource(
        Request $req,
        Courses $course,
        Sections $section,
        Screening $screening
    ) {

        if ($redirect = $this->checkTeacherAccess()) return $redirect;

        $sectionID = $section->section_id;

        $this->assertOwnsCourseSection($course->course_id, $section->section_id);

        // pull concepts + topics + any existing resources
        $screening->load([
            'concepts.topics',
            'concepts.resources',            // ← relation defined below
            'concepts.topics.resources'
        ]);

        return view('teacher.screening-resource', compact('course', 'section', 'screening'));
    }

    /** POST same URL  (no validation rules = optional upload/URL) */
    public function updateScreeningResource(Request $req, $courseID, $sectionId, Screening $screening)
    {
        DB::transaction(function () use ($req) {

            /* ----------  loop over all concept rows ---------- */
            foreach ($req->input('concepts', []) as $cID => $cData) {

                $video  = trim($cData['video_url'] ?? '');
                $pdf    = $req->file("concepts.$cID.pdf_file");

                if ($video || $pdf) {
                    LearningResource::updateOrCreate(
                        ['screening_concept_id' => $cID, 'screening_topic_id' => null],
                        [
                            'learning_resource_id' => Str::uuid(),
                            'title'       => $cData['title'] ?? 'Concept Resource',
                            'video_url'   => $video ?: null,
                            'pdf_blob'    => $pdf ? file_get_contents($pdf->path()) : DB::raw('pdf_blob'), // keep old blob if none uploaded
                        ]
                    );
                }

                /* ----------  topic rows under this concept ---------- */
                foreach ($cData['topics'] ?? [] as $tID => $tData) {

                    $videoT = trim($tData['video_url'] ?? '');
                    $pdfT   = $req->file("concepts.$cID.topics.$tID.pdf_file");

                    if ($videoT || $pdfT) {
                        LearningResource::updateOrCreate(
                            ['screening_topic_id' => $tID],
                            [
                                'learning_resource_id' => Str::uuid(),
                                'screening_concept_id' => $cID,
                                'title'       => $tData['title'] ?? 'Topic Resource',
                                'video_url'   => $videoT ?: null,
                                'pdf_blob'    => $pdfT ? file_get_contents($pdfT->path()) : DB::raw('pdf_blob'),
                            ]
                        );
                    }
                }
            }
        });

        return back()->with('success', 'Resources saved.');
    }
}
