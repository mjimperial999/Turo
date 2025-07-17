<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use App\Models\{
    Users,
    Students,
    Teachers,
    StudentAchievements,
    StudentBadges,
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

class AdminController extends Controller
{
    private function checkAdminAccess()
    {
        if (!session()->has('user_id')) {
            return redirect('/admin-login')->with('error', 'You must be logged in');
        }

        if (session('role_id') == 1) {
            return redirect('/home-tutor');
        }

        if (session('role_id') == 2) {
            return redirect('/teachers-panel');
        }

        // Allow only role_id == 3 to proceed
        return null;
    }

    public function showLoginPage()
    {
        return view('admin.login-admin');
    }

    public function login(Request $request)
    {

        $admin = Users::where('email', $request->email)->first();

        if ($admin && Hash::check($request->password, $admin->password_hash)) {
            if ($admin->role_id == 3) {
                Session::put('user_id', $admin->user_id);
                Session::put('user_name', $admin->first_name . ' ' . $admin->last_name);
                Session::put('role_id', $admin->role_id);
                Session::save();

                return redirect()->intended('/admin-panel');
            }
        }

        return redirect('/admin-login')->with('error', 'Invalid credentials');
    }

    private function seq(string $title): int
    {
        preg_match('/\d+/', $title, $m);
        return empty($m) ? 0 : (int) $m[0];
    }

    public function showAnnouncement($announcementID)
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $announcement = CalendarEvent::findOrFail($announcementID);

        return view('admin.view-annoucement', compact('users', 'announcement'));
    }

    public function studentList(Request $req)
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $term        = trim($req->query('q', ''));                  // search box
        $section   = $req->query('section');          // dropdown filter

        $students = Students::with(['user.image', 'section'])
            /* text search: ID, first, last, "Last, First", AND section name */
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($sub) use ($term) {
                    // match fields on the related user
                    $sub->whereHas('user', function ($u) use ($term) {
                        $u->where('user_id', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name',  'like', "%{$term}%")
                            ->orWhereRaw("CONCAT(last_name, ', ', first_name) LIKE ?", ["%{$term}%"])
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"]);
                    })
                        // OR match section name
                        ->orWhereHas('section', function ($s) use ($term) {
                            $s->where('section_name', 'like', "%{$term}%");
                        });
                });
            })
            /* dropdown filter: strict section_id match */
            ->when($section, fn($q) => $q->where('section_id', $section))
            /* ordering */
            ->orderBy('section_id')
            ->orderByRaw("(SELECT last_name FROM user WHERE user.user_id = student.user_id)")
            ->paginate(20)
            ->withQueryString();  // keep filters on pagination links

        $sections = Sections::orderBy('section_name')->pluck('section_name', 'section_id');

        return view('admin.student-list', compact('students', 'sections', 'term', 'section'));
    }

    private function nextStudentId(): string
    {
        // e.g. 2025-00042   ⇒   2025 + 5-digit zero-padded counter
        $year       = date('Y');
        $max = Users::where('user_id', 'like', $year . '%')
            ->max('user_id');

        $seq = $max ? (int)substr($max, 4) + 1 : 1;

        return $year . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    private function slugName(string $name): string
    {
        // 1. convert to plain ASCII (drops accents like ñ → n)
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);

        // 2. remove anything that is not A–Z / a–z / 0–9
        return preg_replace('/[^A-Za-z0-9]/', '', $ascii);
    }

    /** Create both Users + Students rows inside one transaction. */
    private function createStudentRow(array $data): void
    {
        DB::transaction(function () use ($data) {

            $id  = $this->nextStudentId();
            $last  = $this->slugName($data['last_name']);
            $first = $this->slugName($data['first_name']);
            $pwd   = Hash::make($last . $first);

            Users::create([
                'user_id'                => $id,
                'first_name'             => $data['first_name'],
                'last_name'              => $data['last_name'],
                'email'                  => $data['email'],
                'password_hash'          => $pwd,
                'role_id'                => 1,    // student
                'agreed_to_terms'        => 0,
                'requires_password_change' => 1,
            ]);

            Students::create([
                'user_id'    => $id,
                'isCatchUp'  => 0,
                'total_points' => 0,
            ]);
        });
    }

    public function createStudentForm()
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        return view('admin.student-add');              // simple HTML form
    }

    public function storeStudent(Request $r)
    {
        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:user,email',
        ];
        Validator::make($r->all(), $rules)->validate();

        $this->createStudentRow($r->only('first_name', 'last_name', 'email'));

        return back()->with('success', 'Student account created.');
    }

    public function importForm()
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        return view('admin.student-import');
    }

    public function importCsv(Request $r)
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        $r->validate(['csv' => 'required|file|mimes:csv,txt']);

        $path   = $r->file('csv')->getPathname();
        $handle = fopen($path, 'r');
        $rows   = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {

            [$last, $first, $email] = array_map('trim', $row);

            $v = Validator::make(
                compact('first', 'last', 'email'),
                [
                    'first' => 'required|max:100',
                    'last'  => 'required|max:100',
                    'email' => 'required|email|unique:user,email',
                ]
            );

            if ($v->fails()) {
                $errors[] = "Invalid row #" . ($rows + 1) . ": " . implode('; ', $v->errors()->all());
                continue;
            }

            $this->createStudentRow([
                'first_name' => $first,
                'last_name' => $last,
                'email'     => $email
            ]);
            $rows++;
        }
        fclose($handle);

        return back()->with(
            $errors ? 'error' : 'success',
            $errors ? implode('<br>', $errors)
                : "$rows students imported successfully."
        );
    }

    /* ============================================================
 |   ADMIN: student profile + performance (all courses)
 |   GET /admin/student/{student}/performance
 * =========================================================== */
    public function viewStudentInfo(Students $student)
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        $student->load('user');                        // name + email + hash

        /* 1.  every course row with total_points + rank inside that course */
        $courses = collect();     // we'll fill this manually

        $sectionId = $student->section_id;

        $raw = StudentProgress::query()
            ->join('student   as st',  'st.user_id',      '=', 'studentprogress.student_id')
            ->join('course     as c',   'c.course_id',     '=', 'studentprogress.course_id')
            ->where('st.section_id', $sectionId)           // ← section filter lives here
            ->whereIn('studentprogress.course_id', function ($q) use ($student) {
                $q->select('course_id')
                    ->from('studentprogress')
                    ->where('student_id', $student->user_id);   // only courses the student actually takes
            })
            ->get([
                'studentprogress.course_id',
                'c.course_name',
                'studentprogress.student_id',
                'studentprogress.total_points',
            ]);

        /* group per-course, sort, and assign tied ranks */
        foreach ($raw->groupBy('course_id') as $cid => $rows) {

            // stable sort: points DESC   then user_id ASC
            $rows = $rows->sortByDesc('total_points')
                ->sortBy('student_id')   // deterministic tiebreaker
                ->values();              // reset keys 0…n

            $prevPts = null;
            $rank = 0;
            foreach ($rows as $idx => $row) {
                if ($prevPts === null || $row->total_points < $prevPts) {
                    $rank = $idx + 1;            // advance only when score drops
                }
                $row->rank = $rank;
                $prevPts   = $row->total_points;
            }

            /* keep only **this** student’s row */
            if ($mine = $rows->firstWhere('student_id', $student->user_id)) {
                $courses->put($cid, $mine);
            }
        }

        /* 2.  attach per-course aggregates (reuse one helper) */
        foreach ($courses as $c) {
            $cid = $c->course_id;
            $sid = $student->user_id;

            $c->practice  = $this->quizAverages($cid, $sid, 2);   // PRACTICE
            $c->short     = $this->quizAverages($cid, $sid, 1);   // SHORT
            $c->long      = $this->longQuizAvg($cid, $sid);
            $c->screening = $this->screeningBest($cid, $sid);
        }

        /* 3.  badges + achievements  (image blobs for inline <img>) */
        $badges = StudentBadges::query()
            ->join('badges', 'badges.badge_id', '=', 'student_badges.badge_id')
            ->where('student_id', $student->user_id)
            ->get(['badges.badge_image']);

        $achievements = StudentAchievements::query()
            ->join('achievements', 'achievements.achievement_id', '=', 'student_achievements.achievement_id')
            ->where('student_id', $student->user_id)
            ->get(['achievements.achievement_image']);

        return view('admin.student-info', compact(
            'student',
            'courses',
            'badges',
            'achievements'
        ));
    }

    /* ---------- helper queries (private) ------------------------- */

    private function quizAverages($courseId, $studentId, $quizType)
    {
        return AssessmentResult::query()
            ->join('activity as a', 'assessmentresult.activity_id', '=', 'a.activity_id')
            ->join('quiz as q', 'a.activity_id', '=', 'q.activity_id')
            ->join('module as m', 'a.module_id', '=', 'm.module_id')
            ->selectRaw('m.module_name, a.activity_name as quiz_name, AVG(score_percentage) as avg')
            ->where([
                ['assessmentresult.student_id', $studentId],
                ['assessmentresult.is_kept', 1],
                ['q.quiz_type_id', $quizType],
                ['m.course_id', $courseId],
            ])
            ->groupBy('m.module_name', 'a.activity_name')
            ->get();
    }

    private function longQuizAvg($courseId, $studentId)
    {
        return LongQuizAssessmentResult::query()
            ->join('longquiz as lq', 'long_assessmentresult.long_quiz_id', '=', 'lq.long_quiz_id')
            ->selectRaw('lq.long_quiz_name as quiz_name, AVG(score_percentage) as avg')
            ->where([
                ['student_id', $studentId],
                ['is_kept', 1],
                ['lq.course_id', $courseId],
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
                ['s.course_id', $courseId],
            ])
            ->groupBy('s.screening_name')
            ->get();
    }


    public function bulkSectionForm(Request $req)
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        /* ---------------------------------------------------------
     * Read filters from querystring (?q=...&section=...)
     * -------------------------------------------------------*/
        $term      = trim($req->query('q', ''));      // text box
        $section = $req->query('section');          // dropdown: section_id | 'none' | ''

        /* ---------------------------------------------------------
     * Base query
     * -------------------------------------------------------*/
        $students = Students::with(['user.image', 'section'])
            /* text search (ID, names, "Last, First", section name) */
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($sub) use ($term) {
                    $sub->whereHas('user', function ($u) use ($term) {
                        $u->where('user_id', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name',  'like', "%{$term}%")
                            ->orWhereRaw("CONCAT(last_name, ', ', first_name) LIKE ?", ["%{$term}%"])
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"]);
                    })
                        ->orWhereHas('section', function ($s) use ($term) {
                            $s->where('section_name', 'like', "%{$term}%");
                        });
                });
            })
            /* dropdown filter */
            ->when($section !== null && $section !== '', function ($q) use ($section) {
                if ($section === 'none') {
                    $q->whereNull('section_id');          // students not yet assigned
                } else {
                    $q->where('section_id', $section);   // specific section
                }
            })
            /* ordering: section then last name */
            ->orderBy('section_id')  // NULLs first (unassigned) in MySQL; adjust if needed
            ->orderByRaw("(SELECT last_name FROM user WHERE user.user_id = student.user_id)")
            ->get();

        /* for dropdown */
        $sections = Sections::orderBy('section_name')->get();

        return view('admin.student-bulk-section', compact('students', 'sections', 'term', 'section'));
    }


    public function bulkSectionUpdate(Request $r)
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        $r->validate([
            'students'   => 'required|array',
            'students.*' => 'exists:student,user_id',
            'section_id' => 'nullable|exists:section,section_id'  // allow “Clear section”
        ]);

        Students::whereIn('user_id', $r->students)
            ->update(['section_id' => $r->section_id]);

        return back()->with(
            'success',
            count($r->students) . ' student(s) updated successfully.'
        );
    }

    public function teacherList()
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $teachers = Teachers::whereHas('user')                 // ⬅️ NEW LINE
            ->with(['user.image', 'courseSections.course'])
            ->orderByRaw('(SELECT last_name 
                       FROM   user 
                       WHERE  user.user_id = teacher.user_id)')
            ->paginate(20);

        return view('admin.teacher-list', compact('teachers'));
    }

    public function teacherInfo($teacherId)
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        $teacher = Teachers::with([
            'user.image',
            'courseSections.course',
            'courseSections.section'
        ])->findOrFail($teacherId);

        $allCourses  = Courses::orderBy('course_name')->get();
        $allSections = Sections::orderBy('section_id')->get();

        return view(
            'admin.teacher-info',
            compact('teacher', 'allCourses', 'allSections')
        );
    }

    public function attachCourseSection(Request $r, Teachers $teacher)
    {
        $data = $r->validate([
            'course_id'  => 'required|exists:course,course_id',
            'section_id' => 'required|exists:section,section_id',
        ]);

        // prevent duplicates
        $exists = CourseSection::where($data + ['teacher_id' => $teacher->user_id])->exists();
        if ($exists) {
            return back()->with('error', 'Assignment already exists.');
        }

        CourseSection::create($data + ['teacher_id' => $teacher->user_id]);
        return back()->with('success', 'Assignment added.');
    }

    /* ── detach exactly one course-section ───────────────────────── */
    public function detachCourseSection(Request $r, Teachers $teacher)
    {
        $data = $r->validate([
            'course_id'  => 'required|exists:course,course_id',
            'section_id' => 'required|exists:section,section_id',
        ]);

        $deleted = CourseSection::where($data + ['teacher_id' => $teacher->user_id])->delete();
        return back()->with(
            $deleted ? 'success' : 'error',
            $deleted ? 'Assignment removed.' : 'Nothing deleted.'
        );
    }

    public function createTeacherForm()
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        return view('admin.teacher-add');              // simple HTML form
    }

    private function createTeacherRow(array $data): void
    {
        DB::transaction(function () use ($data) {

            $id  = $this->nextStudentId();
            $last  = $this->slugName($data['last_name']);
            $first = $this->slugName($data['first_name']);
            $pwd   = Hash::make($last . $first);

            Users::create([
                'user_id'                => $id,
                'first_name'             => $data['first_name'],
                'last_name'              => $data['last_name'],
                'email'                  => $data['email'],
                'password_hash'          => $pwd,
                'role_id'                => 2,    // student
                'agreed_to_terms'        => 0,
                'requires_password_change' => 1,
            ]);

            Teachers::create([
                'user_id'    => $id
            ]);
        });
    }

    private function nextTeacherId(): string
    {
        $year       = date('Y');
        $max = Users::where('user_id', 'like', $year . '%')
            ->max('user_id');

        $seq = $max ? (int)substr($max, 4) + 1 : 1;

        return $year . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    /* ───────────────────────────────────────────────
       ➊ Manual single-teacher create
       ───────────────────────────────────────────── */
    public function storeTeacher(Request $req)
    {
        $v = Validator::make($req->all(), [
            'last_name'  => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'email'      => 'required|email|unique:user,email',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $this->createTeacherRow($req->only('first_name', 'last_name', 'email'));

        return back()->with('success', "Teacher {$req->first_name} {$req->last_name} added.");
    }

    public function importCsvTeacher()
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        return view('admin.teacher-import');
    }

    /* ───────────────────────────────────────────────
       ➋ CSV importer  (last_name,first_name,email)
       ───────────────────────────────────────────── */
    public function importTeachersCsv(Request $req)
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        $req->validate(['csv' => 'required|file|mimes:csv,txt']);

        $path   = $req->file('csv')->getPathname();
        $handle = fopen($path, 'r');
        $rows   = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {

            [$last, $first, $email] = array_map('trim', $row);

            $v = Validator::make(
                compact('first', 'last', 'email'),
                [
                    'first' => 'required|max:100',
                    'last'  => 'required|max:100',
                    'email' => 'required|email|unique:user,email',
                ]
            );

            if ($v->fails()) {
                $errors[] = "Invalid row #" . ($rows + 1) . ": " . implode('; ', $v->errors()->all());
                continue;
            }

            $this->createTeacherRow([
                'first_name' => $first,
                'last_name' => $last,
                'email'     => $email
            ]);
            $rows++;
        }
        fclose($handle);

        return back()->with(
            $errors ? 'error' : 'success',
            $errors ? implode('<br>', $errors)
                : "$rows teachers imported successfully."
        );
    }

    public function createSectionForm()
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        return view('admin.teacher-section-add');              // simple HTML form
    }

    /* ───────────────────────────────────────────────
       ➌ create new section
       ───────────────────────────────────────────── */
    public function addSection(Request $req)
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        $req->validate([
            'section_id'   => 'required|string|unique:section,section_id',
            'section_name' => 'required|string|max:255',
        ]);
        Sections::create($req->only('section_id', 'section_name'));
        return back()->with('success', "Section {$req->section_id} created.");
    }

    public function logout()
    {
        Session::flush();
        return redirect('/admin-login')->with('success', 'Successfully Logged Out');
    }

    public function adminPanel()
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        return view('admin.admin-panel');
    }

    // ADMIN CRUD
    public function editContentPage()
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $courses = Courses::with('image')->get();

        return view('admin.edit-content', compact('courses', 'users'));
    }

    public function createCourse()
    {
        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $courses = Courses::with('image')->get();

        return view('admin_crud.course-create', compact('courses', 'users'));
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

        return redirect("/admin-panel/edit-content")
            ->with('success', 'A new course has been created.');
    }

    public function editCourse(Courses $course)
    {
        return view('admin_crud.course-edit', compact('course'));
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
        return redirect('/admin-panel/edit-content')->with('success', 'Course has been deleted.');
    }


    public function viewCourse(Courses $course)
    {

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $course->modules     = $course->modules
            ->sortBy(fn($m)  => $this->seq($m->module_name))
            ->values();

        $course->longquizzes = $course->longquizzes
            ->sortBy(fn($lq) => $this->seq($lq->long_quiz_name))
            ->values();

        $course->screenings  = $course->screenings
            ->sortBy(fn($s)  => $this->seq($s->screening_name))
            ->values();

        return view('admin_crud.view-course', compact('course', 'users'));
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Module CRUD
    public function createModule(Courses $course)
    {


        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);
        $courses = Courses::with([
            'modules.moduleimage',
        ])->get();

        return view('admin_crud.module-create', compact('course', 'users'));
    }

    public function storeModule(Request $req, Courses $course)
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

        Modules $module
    ) {






        return view('admin_crud.module-edit', compact('course', 'module'));
    }

    public function updateModule(Request $req, $courseID, Modules $module)
    {
        $req->validate([
            'module_name'        => 'required|string|max:255',
            'image'              => 'nullable|image|max:2048'
        ]);

        $module->update($req->only('module_name', 'module_description'));

        $blob = file_get_contents($req->file('image')->getRealPath());
        $mime = $req->file('image')->getMimeType();

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

    public function deleteModule($courseID, Modules $module)
    {
        $module->delete();
        return back()->with('success', 'Module deleted.');
    }

    public function viewModule(
        Courses $course,
        Modules $module
    ) {

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $module->load(['activities.quiz']);

        /* ── sort activities by numeric order in title ──────────── */
        $module->activities = $module->activities
            ->sortBy(fn($a) => $this->seq($a->activity_name))
            ->values();


        return view('admin_crud.view-module', compact('course', 'module', 'users'));
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Long Quiz CRUD
    public function createLongQuiz(
        Courses $course
    ) {


        $users = Users::with('image')->findOrFail(session('user_id'));
        return view('admin_crud.longquiz-create', compact('course', 'users'));
    }

    /* 3-c  Store new quiz  */
    public function storeLongQuiz(Request $req, Courses $course)
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

        return redirect("/admin-panel/edit-content/course/{$course->course_id}")
            ->with('success', 'Long-quiz created.');
    }

    /* 3-d  Edit form */
    public function editLongQuiz(
        Courses $course,
        LongQuizzes $longquiz
    ) {

        $users = Users::with('image')->findOrFail(session('user_id'));
        $longquiz->load('longquizquestions.longquizoptions');
        return view('admin_crud.longquiz-edit', compact('course', 'longquiz', 'users'));
    }

    /* 3-e  Update */
    public function updateLongQuiz(Request $req, Courses $course, LongQuizzes $longquiz)
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
    public function deleteLongQuiz(Courses $course, LongQuizzes $longquiz)
    {
        $longquiz->delete();   // FK ON DELETE CASCADE wipes related Q/Opt/Img
        return back()->with('success', 'Long quiz deleted.');
    }

    public function viewLongQuiz(
        Courses $course,
        $longQuizID
    ) {

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $longquiz = LongQuizzes::with([
            'longquizquestions.longquizoptions',
            'longquizquestions.longquizimage'
        ])
            ->where('course_id', $course->course_id)
            ->findOrFail($longQuizID);

        $questions = $longquiz->longquizquestions;

        return view('admin_crud.view-longquiz', compact('course', 'longquiz', 'users', 'questions'));
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Lecture CRUD
    public function createLecture(
        Courses $course,
        Modules $module
    ) {

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        return view('admin_crud.lecture-create', compact('course', 'module', 'users'));
    }

    public function storeLecture(Request $req, Courses $course, Modules $module)
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

        return redirect()->back()->with('success', 'A new lecture has been created.');
    }

    public function editLecture(
        Courses $course,

        Modules $module,
        Activities $activity
    ) {

        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('lecture');
        return view('admin_crud.lecture-edit', compact('course', 'module', 'activity', 'users'));
    }

    public function updateLecture(Request $req, Courses $course, Modules $module, Activities $activity)
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

    public function deleteLecture(Courses $course, Modules $module, Activities $activity)
    {
        $activity->delete();
        return back()->with('success', 'Lecture deleted');
    }

    public function viewLecture(
        Courses $course,
        Modules $module,
        Activities $activity
    ) {

        $userID = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $activity->load('lecture');

        return view('admin_crud.view-lecture', compact('course', 'module', 'activity', 'users'));
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Tutorial Video CRUD
    public function createTutorial(
        Courses $course,
        Modules $module
    ) {

        $users = Users::with('image')->findOrFail(session('user_id'));
        return view('admin_crud.tutorial-create', compact('course', 'module', 'users'));
    }

    /* ────────────────────────────────────────────────────────────────
   3-b  Store new record
   ───────────────────────────────────────────────────────────────*/
    public function storeTutorial(
        Request $req,
        Courses $course,

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
        Modules $module,
        Activities $activity
    ) {


        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('tutorial');
        return view('admin_crud.tutorial-edit', compact('course', 'module', 'activity', 'users'));
    }

    /* ────────────────────────────────────────────────────────────────
   3-d  Update
   ───────────────────────────────────────────────────────────────*/
    public function updateTutorial(Request $req, Courses $course, Modules $module, Activities $activity)
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
    public function deleteTutorial(Courses $course, Modules $module, Activities $activity)
    {
        $activity->delete();
        return back()->with('success', 'Tutorial video deleted.');
    }

    /* ────────────────────────────────────────────────────────────────
   3-f  View (teacher or student)
   ───────────────────────────────────────────────────────────────*/
    public function viewTutorial(
        Courses $course,

        Modules $module,
        Activities $activity
    ) {






        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('tutorial');
        return view('admin_crud.view-tutorial', compact('course', 'module', 'activity', 'users'));
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Short Quiz CRUD
    public function createShortQuiz(
        Courses $course,

        Modules $module
    ) {






        $users = Users::with('image')->findOrFail(session('user_id'));
        return view('admin_crud.shortquiz-create', compact('course', 'module', 'users'));
    }

    /* 2 ─────────────── Store */
    public function storeShortQuiz(Request $req, Courses $course, Modules $module)
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

        return redirect()
            ->to("/admin-panel/edit-content/course/{$course->course_id}/module/{$module->module_id}")
            ->with('success', 'Short-quiz created.');
    }

    /* 3 ─────────────── Edit form */
    public function editShortQuiz(
        Courses $course,

        Modules $module,
        Activities $activity
    ) {






        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('quiz', 'quiz.questions.options', 'quiz.questions.questionimage');
        return view('admin_crud.shortquiz-edit', compact('course', 'module', 'activity', 'users'));
    }

    /* 4 ─────────────── Update */
    public function updateShortQuiz(Request $req, Courses $course, Modules $module, Activities $activity)
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
    public function deleteShortQuiz(Courses $course, Modules $module, Activities $activity)
    {
        $activity->delete();   // cascades to quiz / questions / options via FK
        return back()->with('success', 'Short-quiz deleted.');
    }

    /* 6 ─────────────── View (read-only) */
    public function viewShortQuiz(Courses $course, Modules $module, Activities $activity)
    {


        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('quiz.questions.options', 'quiz.questions.questionimage');
        $questions = Questions::where('activity_id', $activity->activity_id)
            ->with(['options' => fn($q) => $q->orderBy('option_id')])   // keep original order
            ->orderBy('question_id')
            ->get();

        return view('admin_crud.view-shortquiz', compact('course', 'module', 'activity', 'questions', 'users'));
    }


    // ---------------------------------------------
    // ---------------------------------------------
    // Practice Quiz CRUD
    public function createPracticeQuiz(
        Courses $course,

        Modules $module
    ) {






        $users = Users::with('image')->findOrFail(session('user_id'));
        return view('admin_crud.practicequiz-create', compact('course', 'module', 'users'));
    }

    /* 2 ─────────────── Store */
    public function storePracticeQuiz(Request $req, Courses $course, Modules $module)
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

        return redirect()
            ->to("/admin-panel/edit-content/course/{$course->course_id}/module/{$module->module_id}")
            ->with('success', 'Practice-quiz created.');
    }

    /* 3 ─────────────── Edit form */
    public function editPracticeQuiz(
        Courses $course,

        Modules $module,
        Activities $activity
    ) {






        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('quiz', 'quiz.questions.options', 'quiz.questions.questionimage');
        return view('admin_crud.practicequiz-edit', compact('course', 'module', 'activity', 'users'));
    }

    /* 4 ─────────────── Update */
    public function updatePracticeQuiz(Request $req, Courses $course, Modules $module, Activities $activity)
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
    public function deletePracticeQuiz(Courses $course, Modules $module, Activities $activity)
    {
        $activity->delete();  // cascades to quiz / questions / options via FK
        return back()->with('success', 'Practice-quiz deleted.');
    }

    /* 6 ─────────────── View (read-only) */
    public function viewPracticeQuiz(
        Courses $course,
        Modules $module,
        Activities $activity
    ) {


        $users = Users::with('image')->findOrFail(session('user_id'));
        $activity->load('quiz.questions.options', 'quiz.questions.questionimage');
        $questions = Questions::where('activity_id', $activity->activity_id)
            ->with(['options' => fn($q) => $q->orderBy('option_id')])   // keep original order
            ->orderBy('question_id')
            ->get();

        return view('admin_crud.view-practicequiz', compact('course', 'module', 'activity', 'questions', 'users'));
    }

    // ---------------------------------------------
    // ---------------------------------------------
    // Screening Exam CRUD
    public function createScreening(
        Courses $course
    ) {

        $users = Users::with('image')->findOrFail(session('user_id'));
        return view('admin_crud.screening-create', compact('course', 'users'));
    }

    /* 2 ── Store new screening */
    public function storeScreening(Request $req, Courses $course)
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

        return redirect("/admin-panel/edit-content/course/{$course->course_id}")
            ->with('success', 'Screening exam created.');
    }

    /* 3 ── Edit form  */
    public function editScreening(Courses $course, Screening $screening)
    {
        $users = Users::with('image')->findOrFail(session('user_id'));
        $screening->load('concepts.topics.questions.options', 'concepts.topics.questions.image');
        return view('admin_crud.screening-edit', compact('course', 'screening', 'users'));
    }

    /* 4 ── Update  */
    public function updateScreening(
        Request $req,
        Courses  $course,
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
    public function deleteScreening(Courses $course, Screening $screening)
    {
        $screening->delete();   // FK cascade removes all children
        return back()->with('success', 'Screening exam deleted.');
    }


    public function viewScreening(Courses $course, Screening $screening)
    {

        $users = Users::with('image')->findOrFail(session('user_id'));

        $screening->load([
            'concepts.topics.questions.options',
            'concepts.topics.questions.image'
        ]);

        $questions = $screening->concepts
            ->pluck('topics')
            ->flatten()
            ->pluck('questions')
            ->flatten();

        return view(
            'admin_crud.view-screening',
            compact('course', 'screening', 'users', 'questions')
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

        Screening $screening
    ) {
        // pull concepts + topics + any existing resources
        $screening->load([
            'concepts.topics',
            'concepts.resources',            // ← relation defined below
            'concepts.topics.resources'
        ]);

        return view('admin_crud.screening-resource', compact('course', 'screening'));
    }

    /** POST same URL  (no validation rules = optional upload/URL) */
    public function updateScreeningResource(Request $req, $courseID, Screening $screening)
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
