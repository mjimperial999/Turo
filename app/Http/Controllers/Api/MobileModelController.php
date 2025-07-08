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
    TutorialResource
};

use App\Http\Requests\{
    ModuleStoreRequest,
    ModuleUpdateRequest,
    AssessmentResultStoreRequest
};

use App\Models\{
    Courses,
    Modules,
    Activities,
    Questions,
    Options,
    AssessmentResult,
    AssessmentResultAnswer,
    Students,
    Users,
    ModuleProgress
};

class MobileModelController extends Controller
{

    public function getCourses()
    {
        return CoursesResource::collection(
            Courses::with('image')->get()
        );
    }

    public function course()
    {
        return CoursesResource::collection(Users::all());
    }

    /* ---------- GET get-course_modules-for-student ---------- */
    public function indexStudent(Request $r)
    {
        $student = Students::findOrFail($r->student_id);

        // 2) fetch modules + progress + image in **one** query
        $modules = Modules::query()
            ->where('module.course_id', $r->course_id)     // ← qualified
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

    /* ---------- GET get-activities-in-module.php ---------- */
    public function activities(Request $r)
    {
        $activities = Activities::query()
            ->where('module_id', $r->module_id)
            ->leftJoin('quiz as q', 'q.activity_id', '=', 'activity.activity_id')  // numeric id lives here
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

    public function saveAssessmentResult(AssessmentResultStoreRequest $r)
    {
        $now = Carbon::now()->timestamp;

        DB::transaction(function () use ($r, $now) {

            /* attempt # = existing rows +1 */
            $attemptNumber = AssessmentResult::where([
                'student_id'  => $r->student_id,
                'activity_id' => $r->activity_id,
            ])->count() + 1;

            /* create parent row */
            $result = AssessmentResult::create([
                'result_id' => (string) Str::uuid(),
                'student_id'          => $r->student_id,
                'module_id'           => $r->module_id,
                'activity_id'         => $r->activity_id,
                'attempt_number'      => $attemptNumber,
                'tier_level_id'      => 1,
                'score_percentage'    => $r->score_percentage,
                'earned_points'       => $r->earned_points,
                'date_taken'          => Carbon::now('Asia/Manila'),
                'is_kept'             => 0,          // change later if you keep best
            ]);

            AssessmentResult::where([
                ['student_id',  $r->student_id],
                ['activity_id', $r->activity_id],
            ])->update(['is_kept' => 0]);

            $best = AssessmentResult::where([
                ['student_id',  $r->student_id],
                ['activity_id', $r->activity_id],
            ])
                ->orderByDesc('score_percentage')
                ->orderBy('date_taken')          // earliest wins when scores tie
                ->first();

            /* 3️⃣  flag that one as kept */
            $best?->update(['is_kept' => 1]);

            /* store every answer */
            foreach ($r->input('answers') as $ans) {
                AssessmentResultAnswer::create([
                    'result_id' =>  $result->result_id,
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

        /* pull every attempt (latest first) + their answers */
        $best = AssessmentResult::where([
            'student_id'  => $r->student_id,
            'activity_id' => $r->activity_id,
            'is_kept'     => 1,
        ])
            ->with('answers')          // eager-load submitted answers
            ->first();

        return response()->json([
            'data' => $best ? new AssessmentResultResource($best) : null
        ]);
    }

    /* ---------- POST create_module.php ---------- */
    public function store(ModuleStoreRequest $req)
    {
        $mod = Modules::create($req->validated());

        return (new ResultResource($mod))
            ->response()->setStatusCode(201);   // Created
    }
}
