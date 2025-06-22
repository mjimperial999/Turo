<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LearningResource;          // Your existing resources table

class ScreeningResourcesController extends Controller
{
    public function show(string $courseId, string $screeningId, string $resourceId)
{
    $resource = LearningResource::findOrFail($resourceId);

    return view('student.screening-resources', [
        'courseId'    => $courseId,
        'screeningId' => $screeningId,         
        'resources'   => collect([$resource])
    ]);
}
}
