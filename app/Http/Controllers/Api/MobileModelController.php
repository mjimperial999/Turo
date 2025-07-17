<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use App\Http\Resources\{
    ModuleResource,
    CoursesResource,
    AssessmentScoreResource,
    AssessmentResultResource,
    ModuleCollectionResource,
    ModuleTeacherCollectionResource,
    ActivityCollectionResource,
    LectureResource,
    ResultResource,
    QuizResource,
    QuizContentResource,
    LongQuizCollectionResource,
    LongQuizResource,
    LongQuizContentResource,
    ScreeningCollectionResource,
    ScreeningResource,
    ScreeningConceptResource,
    TutorialResource
};

use App\Http\Requests\{
    ModuleStoreRequest,
    ModuleUpdateRequest,
    AssessmentResultStoreRequest,
    QuizStoreRequest,
    QuizUpdateRequest,
    LongQuizStoreRequest,
    LongQuizUpdateRequest,
    LongQuizAssessmentResultStoreRequest,
    LectureStoreRequest,
    LectureUpdateRequest,
    TutorialStoreRequest,
    TutorialUpdateRequest,
    ScreeningStoreRequest,
    ScreeningUpdateRequest,
    ScreeningResultStoreRequest,
};

use App\Models\{
    Courses,
    CourseSection,
    Sections,
    Modules,
    Activities,
    Quizzes,
    Questions,
    Options,
    AssessmentResult,
    AssessmentResultAnswer,
    LongQuizzes,
    LongQuizQuestions,
    LongQuizOptions,
    LongQuizAssessmentResult,
    LongQuizAssessmentResultAnswer,
    Screening,
    ScreeningConcept,
    ScreeningTopic,
    ScreeningQuestion,
    ScreeningOption,
    ScreeningResult,
    ScreeningResultAnswer,
    LearningResource,
    Students,
    StudentProgress,
    Achievements,
    Badges,
    StudentAchievements,
    StudentBadges,
    Users,
    ModuleProgress,
    CalendarEvent,
    Inbox,
    InboxParticipant,
    Message,
    MessageUserState,
    ModuleImage,
    Teachers
};

use App\Services\{
    StudentAnalytics,
    AchievementService
};

class MobileModelController extends Controller
{

    private function seq(string $title): int
    {
        preg_match('/\d+/', $title, $m);
        return empty($m) ? 0 : (int) $m[0];
    }

    public function getCourses()
    {
        return CoursesResource::collection(
            Courses::with('image')->get()
        );
    }

    // Fetch Courses
    public function course()
    {
        return CoursesResource::collection(Users::all());
    }

    public function getCatchUpStatus(Request $r)
    {

        $r->validate([
            'student_id' => 'required|exists:student,user_id',
        ]);

        $isCatchUp = (int) Students::where('user_id', $r->student_id)
            ->value('isCatchUp') ?? 0;

        return response()->json(
            ['is_catch_up' => $isCatchUp]
        );
    }

    // Fetch Modules
    /* ---------- GET get-course_modules-for-student ---------- */
    public function indexStudent(Request $r)
    {
        $student = Students::findOrFail($r->student_id);

        $modules = Modules::query()
            ->where('module.course_id', $r->course_id)

            ->orderByRaw("
            CAST( REGEXP_REPLACE(module.module_name, '[^0-9]', '') AS UNSIGNED ),
            module.module_name
        ")

            ->leftJoin('moduleprogress as mp', function ($q) use ($r) {
                $q->on('mp.module_id', '=', 'module.module_id')
                    ->where('mp.student_id', '=', $r->student_id);
            })
            ->leftJoin('module_image as mi', 'mi.module_id', '=', 'module.module_id')
            ->selectRaw('
            module.*,
            mp.progress as progress_value,
            mi.image    as picture_blob
        ')
            ->get();

        return response()->json([
            'data' => ModuleCollectionResource::collection($modules)
        ]);
    }

    // Fetch Activities
    /* ---------- GET get-activities-in-module.php ---------- */
    public function activities(Request $r)
    {
        $activities = Activities::query()
            ->where('module_id', $r->module_id)
            ->leftJoin('quiz as q', 'q.activity_id', '=', 'activity.activity_id')
            ->selectRaw('
            activity.*,
            q.quiz_type_id    as quiz_type_id          -- 1 = SHORT, 2 = PRACTICE
        ')
            ->get();

        return response()->json([
            'data' => ActivityCollectionResource::collection($activities)
        ]);
    }

    public function scoresForStudentAndQuiz(Request $r)
    {
        /* 1. basic validation */
        $r->validate([
            'student_id'  => 'required|exists:student,user_id',
            'activity_id' => 'required|exists:activity,activity_id',
        ]);

        /* 2. query attempts for that (student, activity) combo */
        $scores = AssessmentResult::where([
            ['student_id',  $r->student_id],
            ['activity_id', $r->activity_id],
        ])
            ->orderBy('date_taken')                      // earliest → latest
            ->get();

        /* 3. return JSON in the shape the app expects */
        return response()->json([
            'scores' => AssessmentScoreResource::collection($scores)
        ]);
    }

    // Lecture PDF
    public function showLecture(Request $r)
    {
        $r->validate([
            'activity_id' => 'required|exists:activity,activity_id',
        ]);

        $lecture = Activities::query()
            ->where('activity.activity_id', $r->activity_id)
            ->leftJoin('lecture as l', 'l.activity_id', '=', 'activity.activity_id')
            ->selectRaw('
            activity.activity_id,
            activity.activity_name,
            activity.activity_description,
            l.file_url    as file_blob       -- BLOB column
        ')
            ->firstOrFail();

        return response()->json(
            new LectureResource($lecture)
        );
    }

    // Tutorial Video
    public function showTutorial(Request $r)
    {
        $r->validate([
            'activity_id' => 'required|exists:activity,activity_id',
        ]);

        $lecture = Activities::query()
            ->where('activity.activity_id', $r->activity_id)
            ->leftJoin('tutorial as t', 't.activity_id', '=', 'activity.activity_id')
            ->selectRaw('
            activity.activity_id,
            activity.activity_name,
            activity.activity_description,
            t.video_url    as video_url
        ')
            ->firstOrFail();

        return response()->json(
            new TutorialResource($lecture)
        );
    }

    // Practice and Short Quiz
    public function showQuiz(Request $r)
    {
        $r->validate([
            'activity_id' => 'required|exists:activity,activity_id',
        ]);

        $quiz = Activities::query()
            ->join('module      as m', 'm.module_id',  '=', 'activity.module_id')
            ->join('quiz        as q', 'q.activity_id', '=', 'activity.activity_id')
            ->selectRaw('
            activity.activity_id,
            m.module_name,
            activity.activity_type,
            activity.activity_name,
            activity.activity_description,
            activity.unlock_date,
            activity.deadline_date,
            q.quiz_type_id,
            q.time_limit,
            q.number_of_attempts,
            q.number_of_questions,
            q.overall_points,
            q.has_answers_shown
        ')
            ->where('activity.activity_id', $r->activity_id)
            ->firstOrFail();

        return response()->json(
            new QuizResource($quiz)
        );
    }

    // Interface - Practice and Short Quiz
    public function showQuizContent(Request $r)
    {
        $r->validate([
            'activity_id' => 'required|exists:activity,activity_id',
        ]);

        $questions = Questions::query()
            ->where('activity_id', $r->activity_id)
            ->leftJoin('quiz_question_image as qi', 'qi.question_id', '=', 'question.question_id')
            ->selectRaw('
            question.*,
            qi.image as question_blob
        ')
            ->get()
            ->each(function ($q) {
                $q->options = Options::where('question_id', $q->question_id)->get();
            });

        return response()->json(
            [
                'questions' => QuizContentResource::collection($questions)
            ]
        );
    }

    // Store New Record - Practice and Short Quiz
    public function saveAssessmentResult(AssessmentResultStoreRequest $r)
    {
        $now = Carbon::now()->timestamp;

        DB::transaction(function () use ($r, $now) {

            // Count All Attempts and Set New Attempt Number
            $attemptNumber = AssessmentResult::where([
                'student_id'  => $r->student_id,
                'activity_id' => $r->activity_id,
            ])->count() + 1;

            // Create New Attempt Entry
            $result = AssessmentResult::create([
                'result_id' => (string) Str::uuid(),
                'student_id'          => $r->student_id,
                'module_id'           => $r->module_id,
                'activity_id'         => $r->activity_id,
                'attempt_number'      => $attemptNumber,
                'tier_level_id'       => 1,
                'score_percentage'    => $r->score_percentage,
                'earned_points'       => $r->earned_points,
                'date_taken'          => Carbon::now('Asia/Manila'),
                'is_kept'             => 0,          // change later if you keep best
            ]);

            AssessmentResult::where([
                ['student_id',  $r->student_id],
                ['activity_id', $r->activity_id],
            ])->update(['is_kept' => 0]);

            // After storing, find and set the Best Record
            $best = AssessmentResult::where([
                ['student_id',  $r->student_id],
                ['activity_id', $r->activity_id],
            ])
                ->orderByDesc('score_percentage')
                ->orderBy('date_taken')
                ->first();

            $best?->update(['is_kept' => 1]);

            // Store Answers
            foreach ($r->input('answers') as $ans) {
                AssessmentResultAnswer::create([
                    'result_id'           => $result->result_id,
                    'question_id'         => $ans['question_id'],
                    'option_id'           => $ans['option_id'],
                    'is_correct'          => $ans['is_correct'],
                ]);
            }
        });

        StudentAnalytics::refreshStudentSummary($r->student_id);
        AchievementService::evaluate($r->student_id);
        return response()->json(['message' => 'Result saved'], 201);
    }

    public function assessmentResults(Request $r)
    {
        $r->validate([
            'student_id'      => 'required|exists:student,user_id',
            'activity_id'     => 'required|exists:activity,activity_id',
        ]);

        // Get the Best Record
        $best = AssessmentResult::where([
            'student_id'  => $r->student_id,
            'activity_id' => $r->activity_id,
            'is_kept'     => 1,
        ])
            ->with('answers')
            ->first();

        return response()->json([
            'data' => is_array($best) && array_is_list($best) ? $best : [$best]
        ]);
    }

    public function showLongQuizList(Request $r)
    {
        $longquiz = LongQuizzes::where('course_id', $r->course_id)
            ->orderBy('long_quiz_name')          // optional: sort any way you like
            ->get();

        $longquiz = $longquiz
            ->sortBy(fn($lq) => $this->seq($lq->long_quiz_name))
            ->values();

        return response()->json([
            'data' => LongQuizCollectionResource::collection($longquiz)
        ]);
    }

    // Long Quiz
    public function showLongQuiz(Request $r)
    {
        $r->validate([
            'long_quiz_id' => 'required|exists:longquiz,long_quiz_id',
        ]);

        $longquiz = LongQuizzes::findOrFail($r->long_quiz_id);

        return response()->json(
            new LongQuizResource($longquiz)
        );
    }

    public function showLongQuizContent(Request $r)
    {
        $r->validate([
            'long_quiz_id' => 'required|exists:longquiz,long_quiz_id',
        ]);

        $questions = LongQuizQuestions::query()
            ->with('longquizoptions')                      // eager-load options
            ->leftJoin(
                'longquiz_question_image as lqi',
                'lqi.long_quiz_question_id',
                '=',
                'longquiz_question.long_quiz_question_id'  // fully-qualified
            )
            ->where('longquiz_question.long_quiz_id', $r->long_quiz_id)
            ->select(
                'longquiz_question.*',
                'lqi.image as question_blob'
            )
            ->get();

        return response()->json(
            [
                'questions' => LongQuizContentResource::collection($questions)
            ]
        );
    }

    // Store New Long Quiz Record
    public function saveLongAssessmentResult(LongQuizAssessmentResultStoreRequest $r)
    {
        $now = Carbon::now()->timestamp;

        DB::transaction(function () use ($r, $now) {

            /* attempt # = existing rows +1 */
            $attemptNumber = LongQuizAssessmentResult::where([
                'student_id'  => $r->student_id,
                'long_quiz_id' => $r->long_quiz_id,
            ])->count() + 1;

            /* create parent row */
            $result = LongQuizAssessmentResult::create([
                'result_id'           => (string) Str::uuid(),
                'student_id'          => $r->student_id,
                'long_quiz_id'        => $r->long_quiz_id,
                'attempt_number'      => $attemptNumber,
                'tier_level_id'       => 1,
                'score_percentage'    => $r->score_percentage,
                'earned_points'       => $r->earned_points,
                'date_taken'          => Carbon::now('Asia/Manila'),
                'is_kept'             => 0,          // change later if you keep best
            ]);

            LongQuizAssessmentResult::where([
                ['student_id',   $r->student_id],
                ['long_quiz_id', $r->long_quiz_id],
            ])->update(['is_kept' => 0]);

            $best = LongQuizAssessmentResult::where([
                ['student_id',  $r->student_id],
                ['long_quiz_id', $r->long_quiz_id],
            ])
                ->orderByDesc('score_percentage')
                ->orderBy('date_taken')          // earliest wins when scores tie
                ->first();

            /* 3️⃣  flag that one as kept */
            $best?->update(['is_kept' => 1]);

            /* store every answer */
            foreach ($r->input('answers') as $ans) {
                LongQuizAssessmentResultAnswer::create([
                    'result_id'                 => $result->result_id,
                    'long_quiz_question_id'     => $ans['question_id'],
                    'long_quiz_option_id'       => $ans['option_id'],
                    'is_correct'                => $ans['is_correct'],
                ]);
            }
        });

        StudentAnalytics::refreshStudentSummary($r->student_id);
        AchievementService::evaluate($r->student_id);
        return response()->json(['message' => 'Result saved'], 201);
    }

    public function longAssessmentResults(Request $r)
    {
        $r->validate([
            'student_id'      => 'required|exists:student,user_id',
            'long_quiz_id'     => 'required|exists:longquiz,long_quiz_id',
        ]);

        $best = LongQuizAssessmentResult::where([
            'student_id'   => $r->student_id,
            'long_quiz_id' => $r->long_quiz_id,
            'is_kept'      => 1,
        ])
            ->with('answers')
            ->first();

        return response()->json([
            'data' => is_array($best) && array_is_list($best) ? $best : [$best]
        ]);
    }

    // Screening Exams
    public function showScreeningExamList(Request $r)
    {
        $r->validate([
            'course_id' => 'required|exists:course,course_id',
        ]);

        $screening = Screening::with('image')
            ->where('course_id', $r->course_id)->get();

        return response()->json([
            'data' => ScreeningCollectionResource::collection($screening),
        ]);
    }


    public function showScreeningExam(Request $r)
    {
        $r->validate([
            'screening_id' => 'required|exists:screening,screening_id',
        ]);

        /* single model → single resource */
        $screening = Screening::findOrFail($r->screening_id);

        return response()->json(new ScreeningResource($screening));
    }

    // Store New Long Quiz Record
    public function showScreeningExamContent(Request $r)
    {
        $r->validate([
            'screening_id' => 'required|exists:screening,screening_id',
        ]);

        /* eager-load ↓↓↓ everything in one go */
        $concepts = ScreeningConcept::with([
            'topics.questions.options',
            'topics.questions.image',
        ])
            ->where('screening_id', $r->screening_id)
            ->get();

        return response()->json([
            'concepts' => ScreeningConceptResource::collection($concepts),
        ]);
    }

    public function saveScreeningResults(ScreeningResultStoreRequest  $r)
    {
        $now = Carbon::now()->timestamp;

        DB::transaction(function () use ($r, $now) {

            /* attempt # = existing rows +1 */
            $attemptNumber = ScreeningResult::where([
                'student_id'  => $r->student_id,
                'screening_id' => $r->screening_id,
            ])->count() + 1;

            $result = ScreeningResult::create([
                'result_id'        => (string) Str::uuid(),
                'screening_id'     => $r->screening_id,
                'student_id'       => $r->student_id,
                'tier_id'          => $attemptNumber,
                'score_percentage' => $r->score_percentage,
                'earned_points'    => $r->earned_points,
                'attempt_number'   => $attemptNumber,
                'date_taken'       => Carbon::now('Asia/Manila'),
                'is_kept'          => 0,
            ]);

            ScreeningResult::where([
                ['student_id',   $r->student_id],
                ['screening_id', $r->screening_id],
            ])->update(['is_kept' => 0]);

            $latest = ScreeningResult::where([
                ['student_id',  $r->student_id],
                ['screening_id', $r->screening_id],
            ])
                ->orderByDesc('date_taken')
                ->first();

            /* 3️⃣  flag that one as kept */
            $latest?->update(['is_kept' => 1]);

            /* store every answer */
            foreach ($r->answers as $a) {
                ScreeningResultAnswer::create([
                    'result_id'             => $result->result_id,
                    'screening_question_id' => $a['question_id'],
                    'screening_option_id'   => $a['option_id'],
                    'is_correct'            => $a['is_correct'],
                ]);
            }
        });

        StudentAnalytics::refreshStudentSummary($r->student_id);
        AchievementService::evaluate($r->student_id);
        return response()->json(['message' => 'Result saved'], 201);
    }

    public function screeningExamResults(Request $r)
    {
        /* ---------- 1. validate input ---------- */
        $r->validate([
            'student_id'   => 'required|exists:student,user_id',
            'screening_id' => 'required|exists:screening,screening_id',
        ]);

        /* ---------- 2. newest (latest-attempt) result ---------- */
        $result = ScreeningResult::where([
            'student_id'   => $r->student_id,
            'screening_id' => $r->screening_id,
        ])
            ->orderByDesc('attempt_number')                 // NEWEST first
            ->with('answers.question.topic.concept')        // eager-load graph
            ->first();

        if (!$result) {
            return response()->json([], 404);               // no attempt yet
        }

        /* ---------- 3. tally correct / totals ---------- */
        $conceptRaw = $topicRaw = [];

        foreach ($result->answers as $ans) {
            $q         = $ans->question;                       // already loaded
            $concept   = $q->topic->concept;
            $topic     = $q->topic;

            $cId = $concept->screening_concept_id;
            $tId = $topic->screening_topic_id;

            // ensure array keys exist
            $conceptRaw[$cId] ??= [
                'name'    => $concept->concept_name,
                'total'   => 0,
                'correct' => 0,
                'topics'  => [],
            ];
            $conceptRaw[$cId]['total']++;

            if ($result->tier_id == 2) {                     // topic stats only for tier-2
                $topicRaw[$tId] ??= [
                    'name'       => $topic->topic_name,
                    'concept_id' => $cId,
                    'total'      => 0,
                    'correct'    => 0,
                ];
                $topicRaw[$tId]['total']++;
            }

            if ($ans->is_correct) {
                $conceptRaw[$cId]['correct']++;
                if (isset($topicRaw[$tId])) {
                    $topicRaw[$tId]['correct']++;
                }
            }
        }

        /* ---------- 4. fold topics into concepts (tier-2) ---------- */
        if ($result->tier_id == 2) {
            foreach ($topicRaw as $tId => $row) {
                $pct = $row['total']
                    ? round($row['correct'] / $row['total'] * 100, 2)
                    : 0;
                $conceptRaw[$row['concept_id']]['topics'][] = [
                    'topic_id'               => $tId,
                    'topic_name'             => $row['name'],
                    'topic_score_percentage' => $pct,
                    'passed'                 => $pct >= 60,
                ];
            }
        }

        /* ---------- 5. final normalisation pass (handles stray rows) ---------- */
        foreach ($conceptRaw as $cId => &$row) {
            // if already normalised (tier-2 path) skip recalculation
            if (isset($row['concept_score_percentage'])) continue;

            $pct = $row['total']
                ? round($row['correct'] / $row['total'] * 100, 2)
                : 0;

            $row = [
                'concept_id'               => $cId,
                'concept_name'             => $row['name'],
                'concept_score_percentage' => $pct,
                'passed'                   => $pct >= 60,
                'topics'                   => $row['topics'] ?? [],   // tier-1 ⇒ []
            ];
        }
        unset($row);

        /* ---------- 6. ship it ---------- */
        return response()->json([
            'earned_points'       => $result->earned_points,
            'number_of_questions' => $result->answers->count(),
            'tier_id'             => $result->tier_id,
            'attempt_number'      => $result->attempt_number,
            'data'                => array_values($conceptRaw),       // re-index
        ]);
    }

    public function fetchLearningResources(Request $r)
    {
        $r->validate([
            'concept_id' => 'required|exists:screeningconcept,screening_concept_id',
            'topic_id'   => 'nullable|exists:screeningtopic,screening_topic_id',
        ]);

        $resources = LearningResource::query()            // model + columns :contentReference[oaicite:8]{index=8}
            ->where('screening_concept_id', $r->concept_id)
            ->when(
                $r->filled('topic_id'),
                fn($q) => $q->where('screening_topic_id', $r->topic_id),
                fn($q) => $q->whereNull('screening_topic_id')  // tier-1 materials
            )
            ->get()
            ->map(fn($res) => [
                'title'       => $res->title,
                'description' => $res->description,
                'video_url'   => $res->video_url ?: null,
                'pdf_blob'    => $res->pdf_blob ? base64_encode($res->pdf_blob) : null,
            ]);

        return response()->json(['resources' => $resources]);
    }

    public function setCatchUp(Request $request)
    {
        /* 1️⃣  Validate ------------------------------------------------------- */
        $data = $request->validate([
            'student_id' => 'required|exists:student,user_id',
        ]);

        /* 2️⃣  Update --------------------------------------------------------- */
        $updated = Students::where('user_id', $data['student_id'])
            ->update(['isCatchUp' => 1]);

        /* 3️⃣  Return --------------------------------------------------------- */
        // $updated will be 0 or 1 (number of rows changed)
        return response()->json(['message' => 'Student Catch-Up Status updated'], 201);
    }

    /**
     * GET /api/v1/get-student-analysis
     *
     * Query params ─ student_id, course_id   (both required)
     * Response ─ structured analytics for a single student inside one course.
     */
    public function showStudentAnalysis(Request $r)
    {
        /* 1 ───── validate --------------------------------------------------- */
        $r->validate([
            'student_id' => 'required|exists:student,user_id',
            'course_id'  => 'required|exists:course,course_id',
        ]);

        $studentId = $r->student_id;
        $courseId  = $r->course_id;

        /* 2 ───── section name & course points ------------------------------- */
        $student      = Students::with('section')->findOrFail($studentId);
        $sectionName  = $student->section->section_name ?? '';

        $courseName = Courses::where('course_id', $r->course_id)
            ->value('course_name');

        $points = StudentProgress::where([
            ['student_id', $studentId],
            ['course_id',  $courseId],
        ])->value('total_points') ?? 0;

        /* helper to shape each quiz row ------------------------------------- */
        $fmt = fn($row) => [
            'quiz_name'  => $row->quiz_name,
            'percentage' => round($row->avg, 2),
        ];

        /* 3 ───── practice- & short-quiz averages --------------------------- */
        $collectQuizRows = function (int $quizType) use ($studentId, $courseId) {
            return AssessmentResult::query()
                ->join('activity   as a', 'a.activity_id', '=', 'assessmentresult.activity_id')
                ->join('quiz       as q', 'q.activity_id',   '=', 'a.activity_id')
                ->join('module     as m', 'm.module_id',     '=', 'a.module_id')
                ->where([
                    ['assessmentresult.student_id', $studentId],
                    ['assessmentresult.is_kept',    1],
                    ['q.quiz_type_id',              $quizType],   // 2 = practice, 1 = short
                    ['m.course_id',                 $courseId],
                ])
                ->groupBy('m.module_name', 'a.activity_name')
                ->selectRaw('m.module_name, a.activity_name as quiz_name, AVG(score_percentage) as avg')
                ->get();
        };

        $practiceRows = $collectQuizRows(2);
        $shortRows    = $collectQuizRows(1);

        /* group by module for JSON ------------------------------------------ */
        $groupByModule = function ($rows) use ($fmt) {
            return $rows->groupBy('module_name')
                ->map(fn($grp) => [
                    'module_name' => $grp->first()->module_name,
                    'quiz'        => $grp->map($fmt)->values(),
                ])->values();
        };

        $practice = [
            'average' => round($practiceRows->avg('avg') ?? 0, 2),
            'module'  => $groupByModule($practiceRows),
        ];

        $short = [
            'average' => round($shortRows->avg('avg') ?? 0, 2),
            'module'  => $groupByModule($shortRows),
        ];

        /* 4 ───── long-quiz averages ---------------------------------------- */
        $longRows = LongQuizAssessmentResult::query()
            ->join('longquiz as lq', 'lq.long_quiz_id', '=', 'long_assessmentresult.long_quiz_id')
            ->where([
                ['long_assessmentresult.student_id', $studentId],
                ['long_assessmentresult.is_kept',    1],
                ['lq.course_id',                     $courseId],
            ])
            ->groupBy('lq.long_quiz_name')
            ->selectRaw('lq.long_quiz_name as quiz_name, AVG(long_assessmentresult.score_percentage) as avg')
            ->get();

        $long = [
            'average' => round($longRows->avg('avg') ?? 0, 2),
            'quiz'    => $longRows->map($fmt)->values(),
        ];

        /* 5 ───── screening (best score, if any) ----------------------------- */
        $screenRow = ScreeningResult::query()
            ->join('screening as s', 's.screening_id', '=', 'screeningresult.screening_id')
            ->where([
                ['screeningresult.student_id', $studentId],
                ['s.course_id',                $courseId],
            ])
            ->orderByDesc('screeningresult.score_percentage')
            ->orderBy('screeningresult.date_taken')
            ->select('s.screening_name', 'screeningresult.score_percentage')
            ->first();

        $screening = $screenRow
            ? [
                'screening_name' => $screenRow->screening_name,
                'percentage'     => round($screenRow->score_percentage, 2),
            ]
            : null;

        /* 6 ───── overall grade (exclude screening) ------------------------- */
        $components = array_filter([
            $practice['average'],
            $short['average'],
            $long['average'],
        ], fn($v) => $v > 0);

        $overall = $components
            ? round(array_sum($components) / count($components), 2)
            : 0;

        /* 7 ───── respond --------------------------------------------------- */
        return response()->json([
            'course'         => $courseName,
            'section'        => $sectionName,
            'overall_grade'  => $overall,
            'points'         => (int) $points,

            'practice_quiz'  => $practice,
            'short_quiz'     => $short,
            'long_quiz'      => $long,
            'screening'      => $screening,
        ]);
    }

    /* -----------------------------------------------------------
 *  GET  /api/v1/get-gamified-elements?student_id=…
 * ----------------------------------------------------------*/
    public function showGamifiedElements(Request $r)
    {
        /* ---------- 1. validate input ----------------------------------- */
        $data = $r->validate([
            'student_id' => 'required|exists:student,user_id',
        ]);
        $studentId = $data['student_id'];

        $student = Users::where('user_id', $r->student_id)->first();
        $studentName = $student
            ? trim("{$student->first_name} {$student->last_name}")
            : null;

        $overallPoints = Students::where('user_id', $r->student_id)
            ->value('total_points');

        /* ---------- 2. caller, section + leaderboard -------------------- */
        $me       = Students::with('user', 'section')->findOrFail($studentId);
        $section  = $me->section;                       // Eager-loaded above
        $sectionId = $section->section_id;

        /* Pull every student in this section, ordered by points ↓ */
        $ranked = Students::with('user')
            ->where('section_id', $sectionId)
            ->whereHas('user')                          // skip orphans safely
            ->orderByDesc('total_points')
            ->orderBy('user_id')                        // deterministic tie-break
            ->get()
            ->values();                                 // 0-based indexing

        /* Tie-aware ranks (duplicates allowed) */
        $prev = null;
        $rank = 0;
        foreach ($ranked as $idx => $row) {
            if ($prev === null || $row->total_points < $prev) {
                $rank = $idx + 1;
            }
            $row->calc_rank = $rank;                   // attach for later
            $prev           = $row->total_points;
        }

        /* Top-15 slice for the payload */
        $top15 = $ranked->take(15)->map(fn($s) => [
            'student_name'          => trim($s->user->first_name . ' ' . $s->user->last_name),
            'student_ranking'       => $s->calc_rank,
            'student_points'        => (int) $s->total_points,
            'student_image'         => $s->user->image?->image
                ? base64_encode($s->user->image?->image)
                : null,
        ]);

        $myRank = optional(
            $ranked->firstWhere('user_id', $studentId)
        )->calc_rank;

        /* ---------- 3. achievements + which ones are unlocked ---------- */
        $achievements = Achievements::orderBy('achievement_id')
            ->get(['achievement_id', 'achievement_name', 'achievement_description', 'achievement_image']);

        $ownedAch = StudentAchievements::where('student_id', $studentId)
            ->pluck('unlocked_at', 'achievement_id');        // [ id => datetime ]

        $achievementRows = $achievements->map(fn($a) => [
            'achievement_id'          => $a->achievement_id,
            'achievement_name'        => $a->achievement_name,
            'achievement_description' => $a->achievement_description,
            'image_name'              => $a->achievement_image,
        ]);

        $achievementsRetrieved = $ownedAch->map(fn($dt, $id) => [
            'achievement_id' => $id,
            'unlocked_at'   => $dt,
        ])->values();

        /* ---------- 4. badges + which ones are unlocked ---------------- */
        $badges = Badges::orderBy('badge_id')
            ->get(['badge_id', 'badge_name', 'badge_description', 'badge_image']);

        $ownedBadges = StudentBadges::where('student_id', $studentId)
            ->pluck('unlocked_at', 'badge_id');              // [ id => datetime ]

        $badgeRows = $badges->map(fn($b) => [
            'badge_id'          => $b->badge_id,
            'badge_name'        => $b->badge_name,
            'badge_description' => $b->badge_description,
            'image_name'        => $b->badge_image,
        ]);

        $badgesRetrieved = $ownedBadges->map(fn($dt, $id) => [
            'badge_id'    => $id,
            'unlocked_at' => $dt,
        ])->values();

        /* ---------- 5. final JSON payload ------------------------------ */
        return response()->json([
            'student_name'        => $studentName,
            'overall_points'      => $overallPoints,
            'leaderboard_ranking' => $myRank,
            'section'             => $section->section_name,
            'leaderboards'        => $top15,

            'achievements'            => $achievementRows,
            'achievements_retrieved'  => $achievementsRetrieved,

            'badges'           => $badgeRows,
            'badges_retrieved' => $badgesRetrieved,
        ]);
    }

    /* ===========================================================
|  GET /api/v1/get-calendar-events
|  Returns *all* items that should be plotted in the calendar
|  — raw dates, no formatting; is_urgent converted to boolean
|===========================================================*/
    public function showCalendarEvents()
    {
        /* ─── 1. ANNOUNCEMENTS ───────────────────────────────── */
        $announcements = CalendarEvent::where('event_type_id', 1)
            ->orderByDesc('is_urgent')
            ->orderBy('date')
            ->get(['title', 'description', 'date', 'is_urgent'])
            ->map(fn($e) => [
                'title'       => $e->title,
                'description' => $e->description,
                'date'        => $e->date,                // raw datetime string
                'is_urgent'   => (bool) $e->is_urgent,    // int → bool
            ]);

        /* ─── 2. PRACTICE-QUIZZES (quiz_type_id = 2) ─────────── */
        $practiceQuiz = Activities::with('quiz')                // eager-load quiz tiny
            ->whereHas('quiz', fn($q) => $q->where('quiz_type_id', 2))
            ->get(['activity_name', 'unlock_date', 'deadline_date'])
            ->map(fn($a) => [
                'name'          => $a->activity_name,
                'unlock_date'   => $a->unlock_date,
                'deadline_date' => $a->deadline_date,
            ]);

        /* ─── 3. SHORT-QUIZZES (quiz_type_id = 1) ────────────── */
        $shortQuiz = Activities::with('quiz')
            ->whereHas('quiz', fn($q) => $q->where('quiz_type_id', 1))
            ->get(['activity_name', 'unlock_date', 'deadline_date'])
            ->map(fn($a) => [
                'name'          => $a->activity_name,
                'unlock_date'   => $a->unlock_date,
                'deadline_date' => $a->deadline_date,
            ]);

        /* ─── 4. LONG-QUIZZES  ───────────────────────────────── */
        $longQuiz = LongQuizzes::get(['long_quiz_name', 'unlock_date', 'deadline_date'])
            ->map(fn($l) => [
                'name'          => $l->long_quiz_name,
                'unlock_date'   => $l->unlock_date,
                'deadline_date' => $l->deadline_date,
            ]);

        /* ─── 5. FINAL PAYLOAD ───────────────────────────────── */
        return response()->json([
            'announcements'  => $announcements,
            'practice_quiz'  => $practiceQuiz,
            'short_quiz'     => $shortQuiz,
            'long_quiz'      => $longQuiz,
        ]);
    }

    public function showInbox(Request $r)
    {
        $r->validate([
            'user_id' => 'required|exists:user,user_id',
        ]);

        $me = $r->user_id;                        // shorthand

        /* -------------------------------------------------------------
     * Helpers
     * ----------------------------------------------------------- */
        $img = function (?Users $u) {
            if (!$u || empty($u->image?->image)) {
                return null;                         // let mobile show a stock icon
            }
            return base64_encode($u->image->image);  // blob → base64
        };

        /* -------------------------------------------------------------
     * INCOMING  (threads that have at least ONE msg not from me)
     * ----------------------------------------------------------- */
        $incoming = Inbox::whereHas(
            'participants',
            fn($q) => $q->where('participant_id', $me)
        )
            ->whereHas(
                'messages',
                fn($q) => $q->where('sender_id', '!=', $me)
            )
            ->with(['messages.userStates', 'messages.sender'])
            ->orderBy('timestamp', 'desc')
            ->get()
            ->flatMap(function ($thread) use ($me, $img) {
                // latest message in this thread that was **not** sent by me
                $msg = $thread->messages
                    ->where('sender_id', '!=', $me)
                    ->sortByDesc('timestamp')
                    ->first();

                // if sender was deleted somehow, skip the row
                if (!$msg?->sender) return [];

                // unread? -> any state row for *this* msg + me that is_read==0
                $state = $msg->userStates
                    ->firstWhere('user_id', $me);
                $unread = $state ? !$state->is_read : true;

                return [[
                    'message_id'  => $msg->message_id,
                    'sender_id'   => $msg->sender_id,
                    'sender_name' => trim($msg->sender->first_name . ' ' . $msg->sender->last_name),
                    'image_blob'  => $img($msg->sender),
                    'subject'     => $msg->subject,
                    'message'     => $msg->body,
                    'date'        => Carbon::parse($msg->timestamp)->format('Y-m-d H:i:s'),
                    'unread'      => (bool) $unread,
                ]];
            })
            ->values();

        /* -------------------------------------------------------------
     * SENT  (latest message *from* me in each thread I participate)
     * ----------------------------------------------------------- */
        $sent = Inbox::whereHas(
            'messages',
            fn($q) => $q->where('sender_id', $me)
        )
            ->with(['participants.user', 'messages'])
            ->orderBy('timestamp', 'desc')
            ->get()
            ->flatMap(function ($thread) use ($me, $img) {
                // latest message **sent by me**
                $msg = $thread->messages
                    ->where('sender_id', $me)
                    ->sortByDesc('timestamp')
                    ->first();

                if (!$msg) return [];

                // pick *all* recipients except me
                return $thread->participants
                    ->where('participant_id', '!=', $me)
                    ->map(function ($p) use ($msg, $img) {
                        $u = $p->user;
                        return [
                            'message_id'  => $msg->message_id,
                            'recipient_id'   => $u->user_id,
                            'recipient_name' => trim($u->first_name . ' ' . $u->last_name),
                            'image_blob'     => $img($u),
                            'subject'        => $msg->subject,
                            'message'        => $msg->body,
                            'date'           => Carbon::parse($msg->timestamp)->format('Y-m-d H:i:s'),
                        ];
                    });
            })
            ->values();

        /* -------- final payload ------------------------------------ */
        return response()->json([
            'messages' => [
                'incoming' => $incoming,
                'sent'     => $sent,
            ],
        ]);
    }

    /* ========================================================================
 |  OPTIONAL HELPERS  (wire them if you like)
 |=======================================================================*/

    /** POST /api/v1/mark-read   body: { "message_id": "…" } */
    public function markMessageRead(Request $r)
    {
        $r->validate([
            'message_id' => 'required|exists:message,message_id',
            'user_id' => 'required|exists:user,user_id',
        ]);
        $me = $r->user_id;

        MessageUserState::where([
            'message_id' => $r->message_id,
            'user_id'    => $me,
        ])->update(['is_read' => 1]);

        return response()->json(['message' => 'Message Marked Read'], 201);
    }

    /** POST /api/v1/delete-message   body: { "message_id": "…" } */
    public function deleteMessage(Request $r)
    {
        $r->validate([
            'message_id' => 'required|exists:message,message_id',
            'user_id' => 'required|exists:user,user_id',
        ]);

        $me = $r->user_id;

        $msg = Message::with('inbox.participants')->findOrFail($r->message_id);

        // author OR any participant may hard-delete
        abort_unless(
            $msg->sender_id == $me ||
                $msg->inbox->participants->contains('participant_id', $me),
            403
        );

        DB::transaction(function () use ($msg) {
            MessageUserState::where('message_id', $msg->message_id)->delete();
            $msg->delete();
        });

        return response()->json(['message' => 'Message Marked Read'], 201);
    }

    // TEACHER SIDE
    public function getCoursesTeacher(Request $r)
    {
        /* 1. validate --------------------------------------------------- */
        $r->validate([
            'teacher_id' => 'required|exists:teacher,user_id',   // adjust table if different
        ]);

        /* 2. pull every course–section pair for this teacher ------------ */
        $rows = CourseSection::with([
            'course.image',   // eager-load blob
            'section',              // eager-load name
        ])
            ->where('teacher_id', $r->teacher_id)
            ->get();

        /* 3. transform to the required payload ------------------------- */
        $data = $rows->map(function ($row) {
            return [
                'course_id'     => $row->course_id,
                'course_name'   => $row->course?->course_name,
                'image_blob'    => optional($row->course?->image?->image)
                    ? base64_encode($row->course->image?->image)
                    : null,
                'section_id'    => $row->section_id,
                'section_name'  => $row->section?->section_name,
            ];
        });

        return response()->json(['data' => $data], 200);
    }

    public function getModulesTeacher(Request $r)
    {
        $r->validate([
            'course_id'  => 'required|exists:course,course_id',
            'section_id' => 'required|exists:section,section_id',
        ]);

        $sectionId = $r->section_id;
        $sectionName = Sections::find($sectionId)?->section_name;

        /* 2️⃣  pull every module in that course (with its image blob) ------ */
        $modules = Modules::with('moduleimage')              // eager-load blob
            ->where('course_id', $r->course_id)
            ->orderBy('module_name')                         // “Module 1 …” order
            ->get()
            ->sortBy(
                fn($m) =>
                (int) preg_replace('/^.*?(\d+).*$/', '$1', $m->module_name)
            )
            ->values();

        /* 3️⃣  respond as a collection resource ---------------------------- */
        return response()->json([
            'section_id' => $sectionId,
            'section_name' => $sectionName,
            'data' => ModuleTeacherCollectionResource::collection($modules),
        ]);
    }

    public function getActivitiesTeacher(Request $r)
    {
        $r->validate([
            'module_id'  => 'required|exists:module,module_id',
            'section_id' => 'sometimes|exists:section,section_id',  // front-end only
        ]);

        /* ── helper for natural numeric ordering ───────────────────────── */
        $seq = fn(string $s) => (int) (preg_match('/\d+/', $s, $m) ? $m[0] : 0);

        /* ── pull activities + quiz_type_id in one query ───────────────── */
        $activities = Activities::query()
            ->where('module_id', $r->module_id)
            ->leftJoin('quiz as q', 'q.activity_id', '=', 'activity.activity_id')
            ->selectRaw('
            activity.*,
            q.quiz_type_id      as quiz_type_id     -- 1 = SHORT, 2 = PRACTICE
        ')
            ->get()
            ->sortBy(fn($a) => $seq($a->activity_name))
            ->values();

        /* ── top-level payload ───────────────────────────────────────────── */
        return response()->json([
            'section_id'   => $r->section_id,                 // front-end will keep it
            'section_name' => $r->section_id,
            'data'         => ActivityCollectionResource::collection($activities),
        ]);
    }

    public function getStudentQuizResultBySection(Request $r)
    {
        /* 1. validate ---------------------------------------------------- */
        $r->validate([
            'activity_id' => 'required|exists:activity,activity_id',
            'section_id'  => 'required|exists:section,section_id',
        ]);

        /* 2. fetch section name once ------------------------------------ */
        $section      = Sections::findOrFail($r->section_id);
        $sectionName  = $section->section_name;

        /* 3. kept-attempt rows for *this* section + activity ------------ */
        $rows = AssessmentResult::with([
            'student.user',            // need first/last names
            'activity.quiz',           // to fetch #-of-questions
        ])
            ->where([
                ['activity_id', $r->activity_id],
                ['is_kept',     1],
            ])
            ->whereHas(
                'student',
                fn($q) =>
                $q->where('section_id', $r->section_id)
            )
            ->orderByDesc('score_percentage')   // optional: rank by score
            ->get();

        /* 4. transform --------------------------------------------------- */
        $data = $rows->map(function ($r) {
            $u = $r->student->user;
            return [
                'student_id'         => $r->student_id,
                'student_name'       => trim($u->last_name . ', ' . $u->first_name),
                'earned_score'       => (int) $r->earned_points,
                'number_of_questions' => (int) optional($r->activity->quiz)->number_of_questions,
                'score_percentage'   => (float) $r->score_percentage,
                'attempt_number'     => (int) $r->attempt_number,
                'date_taken'         => $r->date_taken,            // keep raw datetime
            ];
        })->values();                                              // tidy JSON array

        /* 5. payload ----------------------------------------------------- */
        return response()->json([
            'section_name' => $sectionName,
            'students'     => $data,
        ]);
    }

    public function getStudentLongQuizResultBySection(Request $r)
    {
        $r->validate([
            'long_quiz_id' => 'required|exists:longquiz,long_quiz_id',
            'section_id'   => 'required|exists:section,section_id',
        ]);

        /* ----- fetch section once (name needed for payload) ----- */
        $section = Sections::findOrFail($r->section_id);

        /* ----- kept attempts for every student in that section -- */
        $rows = LongQuizAssessmentResult::with('student.user')          // eager load names
            ->where('long_quiz_id', $r->long_quiz_id)
            ->where('is_kept', 1)
            ->whereHas(
                'student',
                fn($q) =>
                $q->where('section_id', $r->section_id)
            )
            ->orderByDesc('date_taken')                                 // newest first (optional)
            ->get()
            ->map(function ($row) {
                $u = $row->student->user;                               // Users relation
                return [
                    'student_id'          => $row->student_id,
                    'student_name'        => trim("$u->last_name, $u->first_name"),
                    'earned_score'        => (int) $row->earned_points,
                    'number_of_questions' => (int) $row->longquiz->number_of_questions,
                    'score_percentage'    => (float) $row->score_percentage,
                    'attempt_number'      => (int) $row->attempt_number,
                    'date_taken'          => $row->date_taken,          // leave as raw datetime
                ];
            })
            ->values();                                                 // tidy JSON array

        return response()->json([
            'section_name' => $section->section_name,
            'students'     => $rows,
        ]);
    }

    /* =========================================================
 |  SCREENING-EXAM  ➜  per-section score sheet
 |  GET /api/v1/get-student-screening-result-by-section
 * =======================================================*/
    public function getStudentScreeningResultBySection(Request $r)
    {
        $r->validate([
            'screening_id' => 'required|exists:screening,screening_id',
            'section_id'   => 'required|exists:section,section_id',
        ]);

        $section = Sections::findOrFail($r->section_id);

        $rows = ScreeningResult::with('student.user')
            ->where('screening_id', $r->screening_id)
            ->where('is_kept', 1)
            ->whereHas(
                'student',
                fn($q) =>
                $q->where('section_id', $r->section_id)
            )
            ->orderByDesc('date_taken')
            ->get()
            ->map(function ($row) {
                $u = $row->student->user;
                return [
                    'student_id'          => $row->student_id,
                    'student_name'        => trim("$u->last_name, $u->first_name"),
                    'earned_score'        => (int) $row->earned_points,
                    'number_of_questions' => (int) $row->screening->number_of_questions,
                    'score_percentage'    => (float) $row->score_percentage,
                    'attempt_number'      => (int) $row->attempt_number,
                    'date_taken'          => $row->date_taken,
                ];
            })
            ->values();

        return response()->json([
            'section_name' => $section->section_name,
            'students'     => $rows,
        ]);
    }

    public function getStudentList(Request $r)
    {
        /* 1 ─── validate ----------------------------------------------------- */
        $r->validate([
            'course_id'  => 'required|exists:course,course_id',
            'section_id' => 'required|exists:section,section_id',
        ]);

        $courseId  = $r->course_id;
        $sectionId = $r->section_id;

        /* 2 ─── section name ------------------------------------------------- */
        $sectionName = Sections::where('section_id', $sectionId)
            ->value('section_name') ?? '';

        /* 3 ─── fetch students (+ user + image in one query) ----------------- */
        $students = Students::with(['user.image'])
            ->where('section_id', $sectionId)
            ->get();

        /* 4 ─── look-up points per student for this course ------------------- */
        $pointsLookup = StudentProgress::where('course_id', $courseId)
            ->whereIn('student_id', $students->pluck('user_id'))
            ->pluck('total_points', 'student_id');          // [ student_id => pts ]

        /* 5 ─── shape the payload ------------------------------------------- */
        $data = $students->map(function ($s) use ($pointsLookup) {

            /* image  → base64 or null */
            $imgBlob = !empty($s->user->image?->image)
                ? base64_encode($s->user->image->image)
                : null;

            return [
                'student_id'   => $s->user_id,
                'student_name' => trim($s->user->last_name . ', ' . $s->user->first_name),
                'image_blob'   => $imgBlob,
                'points'       => (int) ($pointsLookup[$s->user_id] ?? 0),
            ];
        })
            /* sort A-Z by last name for nicer UX */
            ->sortBy('student_name')
            ->values();

        /* 6 ─── respond ------------------------------------------------------ */
        return response()->json([
            'section_id'   => $sectionId,
            'section_name' => $sectionName,
            'data'         => $data,
        ]);
    }

    /* =========================================================================
 |  MODULE CRUD
 |======================================================================== */
    public function showModule(Request $r)
    {
        $r->validate([
            'module_id' => 'required|exists:module,module_id',
        ]);

        $module = Modules::with('image')
            ->findOrFail($r->module_id);

        return response()->json([
            'data' => new ModuleResource($module)
        ]);
    }

    public function storeModule(ModuleStoreRequest $r)
    {
        $moduleID = (string) Str::uuid();
        $courseID = $r->course_id;
        $moduleName = $r->module_name;
        $moduleDesc = $r->module_description;
        $imageBlob = $r->image;

        $module = Modules::create([
            'module_id'             => $moduleID,
            'course_id'             => $courseID,
            'module_name'           => $moduleName,
            'module_description'    => $moduleDesc
        ]);

        if ($imageBlob) {
            ModuleImage::create([
                'module_id'             => $module->module_id,
                'image'                 => $imageBlob,
            ]);
        }

        return response()->json(['message' => 'Module ' . $moduleName . 'created.']);
    }

    public function updateModule(ModuleUpdateRequest $r)
    {
        $module = Modules::findOrFail($r->module_id);

        $module->update($r->only([
            'module_name',
            'module_details',
            'image'
        ]));

        return response()->json(['message' => 'Module ' . $r->module_name . ' updated']);
    }

    public function destroyModule(Request $r)
    {
        $moduleName = Modules::where('module_id', $r->module_id)
            ->value('module_name');

        $r->validate([
            'module_id' => 'required|exists:module,module_id',
        ]);

        DB::transaction(function () use ($r) {
            Activities::where('module_id', $r->module_id)->delete();
            Modules::where('module_id', $r->module_id)->delete();
            ModuleImage::where('module_id', $r->module_id)->delete();
        });

        return response()->json(['message' => 'Module ' . $moduleName . ' deleted']);
    }

    /* =========================================================================
 |  LONG-QUIZ CRUD
 |======================================================================== */
    public function storeLongQuiz(LongQuizStoreRequest $r)
    {
        $lq = LongQuizzes::create([
            'long_quiz_id'        => (string) Str::uuid(),
            'course_id'           => $r->course_id,
            'long_quiz_name'      => $r->long_quiz_name,
            'long_quiz_instructions' => $r->long_quiz_instructions,
            'number_of_attempts'  => $r->number_of_attempts,
            'time_limit'          => $r->time_limit,
            'number_of_questions' => $r->number_of_questions,
            'overall_points'      => $r->overall_points,
            'has_answers_shown'   => $r->has_answers_shown,
            'unlock_date'         => $r->unlock_date,
            'deadline_date'       => $r->deadline_date,
        ]);

        return response()->json(['message' => 'Long-quiz created'], 201);
    }

    public function updateLongQuiz(LongQuizUpdateRequest $r)
    {
        LongQuizzes::findOrFail($r->long_quiz_id)
            ->update($r->only([
                'long_quiz_name',
                'long_quiz_instructions',
                'number_of_attempts',
                'time_limit',
                'number_of_questions',
                'overall_points',
                'has_answers_shown',
                'unlock_date',
                'deadline_date',
            ]));

        return response()->json(['message' => 'Long-quiz updated']);
    }

    public function destroyLongQuiz(Request $r)
    {
        $r->validate([
            'long_quiz_id' => 'required|exists:longquiz,long_quiz_id',
        ]);

        DB::transaction(function () use ($r) {
            LongQuizQuestions::where('long_quiz_id', $r->long_quiz_id)->delete();
            LongQuizzes::where('long_quiz_id', $r->long_quiz_id)->delete();
        });

        return response()->json(['message' => 'Long-quiz deleted']);
    }

    /* =========================================================================
 |  SCREENING EXAM CRUD
 |======================================================================== */
    public function storeScreeningExam(ScreeningStoreRequest $r)
    {
        Screening::create($r->validated() + [
            'screening_id' => (string) Str::uuid(),
        ]);

        return response()->json(['message' => 'Screening-exam created'], 201);
    }

    public function updateScreeningExam(ScreeningUpdateRequest $r)
    {
        Screening::findOrFail($r->screening_id)->update($r->validated());

        return response()->json(['message' => 'Screening-exam updated']);
    }

    public function destroyScreeningExam(Request $r)
    {
        $r->validate([
            'screening_id' => 'required|exists:screening,screening_id',
        ]);

        DB::transaction(function () use ($r) {
            ScreeningConcept::where('screening_id', $r->screening_id)->delete();
            Screening::where('screening_id', $r->screening_id)->delete();
        });

        return response()->json(['message' => 'Screening-exam deleted']);
    }

    /* =========================================================================
 |  QUIZ  (SHORT / PRACTICE)  CRUD
 |======================================================================== */
    public function storeQuiz(QuizStoreRequest $r)
    {
        $activity = Activities::create([
            'activity_id'      => (string) Str::uuid(),
            'module_id'        => $r->module_id,
            'activity_name'    => $r->activity_name,
            'activity_type_id' => 2,                // QUIZ
            'activity_description' => $r->activity_description,
            'unlock_date'         => $r->unlock_date,
            'deadline_date'       => $r->deadline_date,
        ]);

        Quizzes::create([
            'activity_id'   => $activity->activity_id,
            'quiz_type_id'  => $r->quiz_type_id,    // 1 = SHORT, 2 = PRACTICE
        ]);

        return response()->json(['message' => 'Quiz created'], 201);
    }

    public function updateQuiz(QuizUpdateRequest $r)
    {
        Activities::findOrFail($r->activity_id)
            ->update($r->only([
                'activity_name',
                'activity_description',
                'unlock_date',
                'deadline_date',
            ]));

        Quizzes::where('activity_id', $r->activity_id)
            ->update(['quiz_type_id' => $r->quiz_type_id]);

        return response()->json(['message' => 'Quiz updated']);
    }

    public function destroyQuiz(Request $r)
    {
        $r->validate([
            'activity_id' => 'required|exists:activity,activity_id',
        ]);

        DB::transaction(function () use ($r) {
            Questions::where('activity_id', $r->activity_id)->delete();
            Activities::where('activity_id', $r->activity_id)->delete();
        });

        return response()->json(['message' => 'Quiz deleted']);
    }

    /* =========================================================================
 |  LECTURE  &  TUTORIAL  CRUD  (activity_type_id = 3 | 4)
 |======================================================================== */
    private function storeGenericActivity(array $data, int $typeId): Activities
    {
        return Activities::create($data + [
            'activity_id'       => (string) Str::uuid(),
            'activity_type_id'  => $typeId,                // 3 = LECTURE, 4 = TUTORIAL
        ]);
    }

    public function storeLecture(LectureStoreRequest $r)
    {
        $this->storeGenericActivity($r->validated(), 3);
        return response()->json(['message' => 'Lecture created'], 201);
    }

    public function storeTutorial(TutorialStoreRequest $r)
    {
        $this->storeGenericActivity($r->validated(), 4);
        return response()->json(['message' => 'Tutorial created'], 201);
    }

    public function updateLecture(LectureUpdateRequest $r)
    {
        Activities::findOrFail($r->activity_id)->update($r->validated());
        return response()->json(['message' => 'Lecture updated']);
    }

    public function updateTutorial(TutorialUpdateRequest $r)
    {
        Activities::findOrFail($r->activity_id)->update($r->validated());
        return response()->json(['message' => 'Tutorial updated']);
    }

    public function destroyLecture(Request $r)
    {
        $r->validate(['activity_id' => 'required|exists:activity,activity_id']);
        Activities::where('activity_id', $r->activity_id)->delete();
        return response()->json(['message' => 'Lecture deleted']);
    }

    public function destroyTutorial(Request $r)
    {
        $r->validate(['activity_id' => 'required|exists:activity,activity_id']);
        Activities::where('activity_id', $r->activity_id)->delete();
        return response()->json(['message' => 'Tutorial deleted']);
    }
}
