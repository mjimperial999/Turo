<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

use App\Http\Resources\{
    ModuleResource,
    CoursesResource,
    AssessmentScoreResource,
    ModuleCollectionResource,
    ModuleStudentResource,
    ActivityCollectionResource,
    LectureResource,
    ResultResource,
    TutorialResource
};

use App\Http\Requests\{
    ModuleStoreRequest,
    ModuleUpdateRequest
};

use App\Models\{
    Courses,
    Modules,
    Activities,
    AssessmentResult,
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

    /* ---------- POST create_module.php ---------- */
    public function store(ModuleStoreRequest $req)
    {
        $mod = Modules::create($req->validated());

        return (new ResultResource($mod))
            ->response()->setStatusCode(201);   // Created
    }
}
