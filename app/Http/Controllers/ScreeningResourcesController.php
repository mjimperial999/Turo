<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Courses;
use App\Models\Screening;
use App\Models\LearningResource;

class ScreeningResourcesController extends Controller
{
    public function show(Courses $course, Screening $screening, string $resourceId)
    {
        $resource = LearningResource::findOrFail($resourceId);

        return view('student.screening-resources', [
            'course'    => $course,
            'screening' => $screening,
            'resources'   => collect([$resource])
        ]);
    }
}
