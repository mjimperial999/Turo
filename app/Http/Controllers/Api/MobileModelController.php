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
    ModuleStudentResource,
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
    LongQuizAssessmentResultStoreRequest,
    ScreeningResultStoreRequest
};

use App\Models\{
    Courses,
    Modules,
    Activities,
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
    Students,
    Users,
    ModuleProgress
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

            $best = ScreeningResult::where([
                ['student_id',  $r->student_id],
                ['screening_id', $r->screening_id],
            ])
                ->orderByDesc('score_percentage')
                ->orderBy('date_taken')
                ->first();

            /* 3️⃣  flag that one as kept */
            $best?->update(['is_kept' => 1]);

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

        return response()->json(['message' => 'Result saved'], 201);
    }

    public function screeningExamResults(Request $r)
    {
        $r->validate([
            'student_id'   => 'required|exists:student,user_id',
            'screening_id' => 'required|exists:screening,screening_id',
        ]);

        $best = ScreeningResult::where([
            'student_id'   => $r->student_id,
            'screening_id' => $r->screening_id,
            'is_kept'      => 1,
        ])->first();

        return response()->json(['data' => $best ?? []]);
    }

    /* ---------- POST create_module.php ---------- */
    public function store(ModuleStoreRequest $req)
    {
        $mod = Modules::create($req->validated());

        return (new ResultResource($mod))
            ->response()->setStatusCode(201);   // Created
    }
}
