<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\LongQuizController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\ScreeningResourcesController;
use App\Http\Controllers\TeacherController;

// GENERAL
Route::get('/', [MainController::class, 'landingRedirect']);
Route::get('/test', function () {
    return view('navbar-test');
});

Route::get('/health', function () {
    return response()->json(['status' => 'OK']);
});

Route::get('/login', [LoginController::class, 'showLoginPage']);
Route::post('/auth', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout']);

// ADMIN 
Route::get('/admin-login', [AdminController::class, 'showLoginPage']);

Route::get('/admin-panel', [AdminController::class, 'showLoginPage']);

Route::get('/dashboard-math', function () {
    return view('dashboard-math');
});


// STUDENT
Route::get('/home-screening', function () {
    return view('home-screening');
});

Route::get('/profile', [MainController::class, 'profilePage']);
Route::get('/performance', [MainController::class, 'performancePage']);

Route::prefix('home-tutor')->group(function () {

    Route::get('/', [MainController::class, 'courseList']);

    Route::prefix('course/{course}')->group(function () {

        Route::get('/', [MainController::class, 'moduleList']);

        Route::prefix('/longquiz/{longquiz}')->group(function () {

            Route::get('/', [MainController::class, 'longquizPage']);
            Route::get('/s', [LongQuizController::class, 'startQuiz']);
            Route::get('/s/q/{index}', [LongQuizController::class, 'showQuestion']);
            Route::post('/s/q/{index}', [LongQuizController::class, 'submitAnswer']);
            Route::get('/summary', [MainController::class, 'longquizSummary']);
        });

        Route::prefix('module/{module}')->group(function () {

            Route::get('/', [MainController::class, 'activityList']);
            Route::get('lecture/{activity:activity_id}', [MainController::class, 'lecturePage']);
            Route::get('tutorial/{activity:activity_id}', [MainController::class, 'tutorialPage']);
            Route::prefix('quiz/{activity:activity_id}')->group(function () {

                Route::get('/', [MainController::class, 'quizPage']);
                Route::get('/s',          [QuizController::class, 'startQuiz']);
                Route::get('/s/q/{index}',   [QuizController::class, 'showQuestion']);
                Route::post('/s/q/{index}',  [QuizController::class, 'submitAnswer']);
                Route::get('/summary',     [MainController::class, 'summary']);
            });
        });
    });
});

Route::get('/lecture-file/{activity:activity_id}', function (App\Models\Activities $activity) {
    $lecture = $activity->lecture;
    abort_unless($lecture, 404);

    return response($lecture->file_url, 200, [
        'Content-Type'        => $lecture->file_mime_type ?? 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $lecture->file_name . '"',
    ]);
});


/*
Route::get('/home-tutor/course/{course}/long-quiz/{longquiz}', [MainController::class, 'longquizPage']);
Route::get('/home-tutor/course/{course}/long-quiz/{longquiz}/s', [LongQuizController::class, 'startQuiz']);
Route::get('/home-tutor/course/{course}/long-quiz/{longquiz}/s/q/{index}', [LongQuizController::class, 'showQuestion']);
Route::post('/home-tutor/course/{course}/long-quiz/{longquiz}/s/q/{index}', [LongQuizController::class, 'submitAnswer']);
Route::get('/home-tutor/course/{course}/long-quiz/{longquiz}/summary', [MainController::class, 'longquizSummary']);
*/

Route::prefix('/home-tutor/course/{course}/')->group(function () {

    Route::get('{screening}', [ScreeningController::class, 'screeningPage']);

    // start an attempt
    Route::post('{screening}/start', [ScreeningController::class, 'start']);

    // single-question player (same URI for GET to show & POST to submit)
    Route::match(['get', 'post'], '{screening}/q/{index}', [ScreeningController::class, 'play']);

    // end-of-attempt summary
    Route::get('{screening}/summary', [ScreeningController::class, 'summary']);

    // optional: resource links for weak concepts / topics
    Route::get(
        '{screening}/resources/{resource}',
        [ScreeningResourcesController::class, 'show']
    );
});



// TEACHER ROUTES
Route::prefix('teachers-panel')->group(function () {

    // Courses
    Route::get('/', [TeacherController::class, 'teacherPanel']);
    Route::get('/course/{course}', [TeacherController::class, 'viewCourse']);

    Route::get('/create-course', [TeacherController::class, 'createCourse']);
    Route::post('/store-course', [TeacherController::class, 'storeCourse']);

    Route::get('/course/{course}/edit',   [TeacherController::class, 'editCourse']);
    Route::post('/course/{course}/edit',   [TeacherController::class, 'updateCourse']);
    Route::post('/course/{course}/delete', [TeacherController::class, 'deleteCourse']);

    Route::prefix('course/{course}')->group(function () {

        // Modules
        Route::get('/module/{module}', [TeacherController::class, 'viewModule']);

        Route::get('/create-module',   [TeacherController::class, 'createModule']);
        Route::post('/store-module',  [TeacherController::class, 'storeModule']);

        Route::get('/module/{module}/edit',      [TeacherController::class, 'editModule']);
        Route::post('/module/{module}/edit',     [TeacherController::class, 'updateModule']);
        Route::post('/module/{module}/delete',   [TeacherController::class, 'deleteModule']);


        // Long Quizzes
        Route::get('/longquiz/{longquiz}',            [TeacherController::class, 'viewLongQuiz']);

        Route::get('/create-longquiz',               [TeacherController::class, 'createLongQuiz']);
        Route::post('/store-longquiz',                [TeacherController::class, 'storeLongQuiz']);

        Route::get('/longquiz/{longquiz}/edit',      [TeacherController::class, 'editLongQuiz']);
        Route::post('/longquiz/{longquiz}/edit',      [TeacherController::class, 'updateLongQuiz']);
        Route::post('/longquiz/{longquiz}/delete',    [TeacherController::class, 'deleteLongQuiz']);

        // Screening Exam
        Route::get('/screening/{screening}',[TeacherController::class, 'viewScreening']);

        Route::get('/create-screening', [TeacherController::class, 'createScreening']);
        Route::post('/store-screening',[TeacherController::class, 'storeScreening']);

        Route::get('/screening/{screening}/edit',[TeacherController::class, 'editScreening']);
        Route::post('/screening/{screening}/edit',[TeacherController::class, 'updateScreening']);
        Route::post('/screening/{screening}/delete',[TeacherController::class, 'deleteScreening']);


        Route::prefix('module/{module}')->group(function () {

            // Lecture
            Route::get('/lecture/{activity}', [TeacherController::class, 'viewLecture']);

            Route::get('/create-lecture',   [TeacherController::class, 'createLecture']);
            Route::post('/store-lecture',  [TeacherController::class, 'storeLecture']);

            Route::get('/lecture/{activity}/edit',      [TeacherController::class, 'editLecture']);
            Route::post('/lecture/{activity}/edit',     [TeacherController::class, 'updateLecture']);
            Route::post('/lecture/{activity}/delete',   [TeacherController::class, 'deleteLecture']);


            // Tutorial
            Route::get('/tutorial/{activity}',            [TeacherController::class, 'viewTutorial']);

            Route::get('/create-tutorial',                [TeacherController::class, 'createTutorial']);
            Route::post('/store-tutorial',                [TeacherController::class, 'storeTutorial']);

            Route::get('/tutorial/{activity}/edit',       [TeacherController::class, 'editTutorial']);
            Route::post('/tutorial/{activity}/edit',      [TeacherController::class, 'updateTutorial']);
            Route::post('/tutorial/{activity}/delete',    [TeacherController::class, 'deleteTutorial']);


            // Practice Quiz
            Route::get('/create-practicequiz',            [TeacherController::class, 'createPracticeQuiz']);
            Route::post('/store-practicequiz',            [TeacherController::class, 'storePracticeQuiz']);

            Route::get('/practicequiz/{activity}/edit',   [TeacherController::class, 'editPracticeQuiz']);
            Route::post('/practicequiz/{activity}/edit',  [TeacherController::class, 'updatePracticeQuiz']);
            Route::post('/practicequiz/{activity}/delete', [TeacherController::class, 'deletePracticeQuiz']);

            Route::get('/practicequiz/{activity}',        [TeacherController::class, 'viewPracticeQuiz']);

            // Short Quiz
            Route::get('/create-shortquiz',               [TeacherController::class, 'createShortQuiz']);
            Route::post('/store-shortquiz',               [TeacherController::class, 'storeShortQuiz']);

            Route::get('/shortquiz/{activity}/edit',      [TeacherController::class, 'editShortQuiz']);
            Route::post('/shortquiz/{activity}/edit',     [TeacherController::class, 'updateShortQuiz']);
            Route::post('/shortquiz/{activity}/delete',   [TeacherController::class, 'deleteShortQuiz']);

            Route::get('/shortquiz/{activity}',           [TeacherController::class, 'viewShortQuiz']);
        });
    });
});

// TEST
Route::get('/test-session', function () {
    session(['test' => 'it works']);
    return session('test');
});

Route::get('/test-session-db', function () {
    try {
        session(['check' => 'db works']);
        return session('check');
    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
        ]);
    }
});

Route::get('/session-driver', function () {
    return config('session.driver');  // should say 'database'
});
