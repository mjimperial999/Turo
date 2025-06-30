<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modules;
use App\Http\Resources\ModuleResource;
use App\Models\Users;
use App\Http\Resources\UsersResource;
use App\Models\Courses;
use App\Http\Resources\CoursesResource;
use App\Models\CourseImage;
use App\Http\Resources\CoursesImageResource;

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
}
