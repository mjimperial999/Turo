<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MobileModelController;

Route::prefix('v1')->group(function () {

    // ── 🔓 Public endpoints ───────────────────────────────────────
    Route::post('auth/login', [AuthController::class, 'login']);

    // ── 🔐 Protected endpoints (require Bearer token) ─────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('get-courses',                       [MobileModelController::class, 'getCourses']);

        // STUDENTS
        Route::get('check-if-student-is-catch-up',    [MobileModelController::class, 'getCatchUpStatus']);

        Route::get('get-course_modules-for-student',    [MobileModelController::class, 'indexStudent']);
        Route::get('get-activities-in-module',          [MobileModelController::class, 'activities']);
        Route::get('get-scores-for-student-and-quiz',   [MobileModelController::class, 'scoresForStudentAndQuiz']);

        Route::get('get-lecture',                       [MobileModelController::class, 'showLecture']);
        Route::get('get-tutorial',                      [MobileModelController::class, 'showTutorial']);

        Route::get('get-quiz',                                      [MobileModelController::class, 'showQuiz']);
        Route::get('get-quiz-content',                              [MobileModelController::class, 'showQuizContent']);
        Route::post('save-assessment-result',                       [MobileModelController::class, 'saveAssessmentResult']);
        Route::get('get-assessment-result-for-student-and-quiz',    [MobileModelController::class, 'assessmentResults']);

        Route::get('get-long-quiz-list',                                    [MobileModelController::class, 'showLongQuizList']);
        Route::get('get-long-quiz',                                         [MobileModelController::class, 'showLongQuiz']);
        Route::get('get-long-quiz-content',                                 [MobileModelController::class, 'showLongQuizContent']);
        Route::post('save-long-quiz-assessment-result',                     [MobileModelController::class, 'saveLongAssessmentResult']);
        Route::get('get-long-quiz-assessment-result-for-student-and-quiz',  [MobileModelController::class, 'longAssessmentResults']);

        // TEACHER
        Route::get('get_course_modules_for_teacher',      [MobileModelController::class, 'indexTeacher']);

        Route::delete('delete_module_in_course.php',      [MobileModelController::class, 'destroy']);
        Route::post('create_module.php',                  [MobileModelController::class, 'store']);
        Route::get('get_module.php',                      [MobileModelController::class, 'show']);
        Route::post('update_module.php',                  [MobileModelController::class, 'update']);
        Route::get('get-current-module',                  [MobileModelController::class, 'current']);

        Route::get('course', [MobileModelController::class, 'course']);
        Route::get('modules', [MobileModelController::class, 'modules']);
        Route::get('activities', [MobileModelController::class, 'activities']);

        // add more protected routes here, e.g.
        // Route::post('modules/{module}/complete', …);
    });
});
