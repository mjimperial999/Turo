<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

use App\Http\Resources\{
    ModuleResource,
    CoursesResource,
    ModuleCollectionResource,
    ModuleStudentResource,
    ActivityCollectionResource,
    ResultResource
};

use App\Http\Requests\{
    ModuleStoreRequest,
    ModuleUpdateRequest
};

use App\Models\{
    Courses,
    Modules,
    Activities,
    Students,
    Users,
    ModuleProgress
};

class MobileModelController extends Controller
{
    public function modules()
    {
        return ModuleResource::collection(Modules::all());
    }

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

    public function indexTeacher(Request $r)
    {
        $mods = Modules::where('course_id', $r->course_id)
            ->orderBy('position')
            ->get();

        return new ModuleCollectionResource($mods);
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

    /* ---------- GET get_activities_in_module.php ---------- */
    public function activities(Request $r)
    {
        $acts = Activities::where('module_id', $r->module_id)
            ->orderBy('position')->get();

        return new ActivityCollectionResource($acts);
    }

    /* ---------- POST create_module.php ---------- */
    public function store(ModuleStoreRequest $req)
    {
        $mod = Modules::create($req->validated());

        return (new ResultResource($mod))
            ->response()->setStatusCode(201);   // Created
    }

    /* ---------- GET get_module.php ---------- */
    public function show(Request $r)
    {
        $mod = Modules::where([
            'course_id' => $r->course_id,
            'module_id' => $r->module_id
        ])->firstOrFail();

        return new ModuleResource($mod);
    }

    /* ---------- POST update_module.php ----------
    public function update(ModuleUpdateRequest $req)
    {
        $mod = Modules::findOrFail($req->module_id)
            ->update($req->validated());

        return new ResultResource($mod);
    } */

    /* ---------- DELETE delete_module_in_course.php ---------- */
    public function destroy(Request $r)
    {
        Modules::where('module_id', $r->module_id)->delete();

        return response()->noContent();            // 204 No Content
    }
}
