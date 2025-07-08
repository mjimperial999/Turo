<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Resources\{
    ModuleResource,
    CoursesResource,
    ModuleCollectionResource,
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
    Users
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

    /* ---------- GET get_course_modules_for_student.php ---------- */
    public function indexStudent(Request $r)
    {
        $mods = Modules::where('course_id', $r->course_id)
            ->with(['progress' => fn($q) => $q->where('student_id', $r->student_id)])
            ->orderBy('position')
            ->get();

        return new ModuleCollectionResource($mods, true);     // flag = student
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

    /* ---------- GET get-current-module ---------- */
    public function current(Request $r)
    {
        $current = Students::findOrFail($r->student_id)
            ->currentModule($r->course_id);   // assume a model scope

        return new ModuleResource($current);
    }
}
