<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    PinController,
    TermsController,
    MainController,
    QuizController,
    LongQuizController,
    LoginController,
    AdminController,
    ScreeningController,
    ScreeningResourcesController,
    TeacherController,
    InboxController
};

// GENERAL
Route::get('/', [LoginController::class, 'landingRedirect']);

Route::get('/health', function () {
    return response()->json(['status' => 'OK']);
});

Route::get('/login', [LoginController::class, 'showLoginPage']);
Route::post('/auth', [LoginController::class, 'login']);
Route::get('/auth', [LoginController::class, 'redirectAuth']);
Route::get('/logout', [LoginController::class, 'logout']);

Route::get('/pin',                 fn() => view('pin'));
Route::post('/pin',                [PinController::class, 'send'])->name('pin.send');
Route::post('/pin/verify',         [PinController::class, 'verify'])->name('pin.verify');

Route::get('/pin-forgot',           fn() => view('pin-forgot'));

Route::get('/replace-password',    [PinController::class, 'passwordForm'])->name('pw.form');
Route::post('/replace-password',    [PinController::class, 'passwordSave'])->name('pw.save');

Route::get('/terms',               [TermsController::class, 'show'])->name('terms.form');
Route::post('/terms/accept',        [TermsController::class, 'accept'])->name('terms.accept');

Route::get('/forgot-password', fn() => view('forgot-password'));
Route::get('/forgot-password/pin', fn() => view('pin-forgot'));
Route::post('/forgot-password/send', [PinController::class, 'sendRecovery'])->name('recovery.send');
Route::post('/forgot-password/verify', [PinController::class, 'verifyRecovery'])->name('recovery.verify');

Route::get('/replace-pass-view', fn() => view('replace-password-view'));
Route::get('/terms-view', fn() => view('terms'));


// ADMIN 
Route::get('/admin-login', [AdminController::class, 'showLoginPage']);
Route::post('/auth-admin', [AdminController::class, 'login']);

Route::get('/admin-panel', [AdminController::class, 'adminPanel']);

Route::get('/admin-logout', [AdminController::class, 'logout']);

Route::prefix('/admin-panel')->group(function () {

    // Student List ------------------------------------------
    Route::get('/student-list', [AdminController::class, 'studentList']);
    Route::get('/student-list/student/{student}', [AdminController::class, 'viewStudentInfo']);

    Route::get('/student-list/student-bulk-section', [AdminController::class, 'bulkSectionForm']);
    Route::post('/student-list/student-bulk-section', [AdminController::class, 'bulkSectionUpdate']);

    // Manual Add
    Route::get('/student-list/add',        [AdminController::class, 'createStudentForm']);
    Route::post('/student-list/add',        [AdminController::class, 'storeStudent']);

    // CSV Import
    Route::get('/student-list/import-csv', [AdminController::class, 'importForm']);
    Route::post('/student-list/import-csv', [AdminController::class, 'importCsv']);

    // Teacher List ------------------------------------------
    Route::get('/teacher-list',         [AdminController::class, 'teacherList']);

    Route::get('/teacher-list/add',        [AdminController::class, 'createTeacherForm']);
    Route::post('/teacher-list/add',            [AdminController::class, 'storeTeacher']);

    Route::get('/teacher-list/import-csv',      [AdminController::class, 'importCsvTeacher']);
    Route::post('/teacher-list/import-csv',     [AdminController::class, 'importTeachersCsv']);

    Route::get('/teacher-list/add-section',    [AdminController::class, 'createSectionForm']);
    Route::post('/teacher-list/add-section',    [AdminController::class, 'addSection']);

    Route::get('/teacher-info/{teacher}',    [AdminController::class, 'teacherInfo']);
    Route::post('/teacher-info/{teacher}/attach',  [AdminController::class, 'attachCourseSection']);
    Route::post('/teacher-info/{teacher}/detach',  [AdminController::class, 'detachCourseSection']);

    // ADMIN CRUD
    Route::get('/edit-content', [AdminController::class, 'editContentPage']);

    Route::prefix('/edit-content')->group(function () {

        // Course
        Route::get('/course/{course}', [AdminController::class, 'viewCourse']);

        Route::get('/create-course', [AdminController::class, 'createCourse']);
        Route::post('/store-course', [AdminController::class, 'storeCourse']);

        Route::get('/course/{course}/edit',   [AdminController::class, 'editCourse']);
        Route::post('/course/{course}/edit',   [AdminController::class, 'updateCourse']);
        Route::post('/course/{course}/delete', [AdminController::class, 'deleteCourse']);

        Route::prefix('course/{course}/')->group(function () {

            // Modules
            Route::get('/module/{module}', [AdminController::class, 'viewModule']);

            Route::get('/create-module',   [AdminController::class, 'createModule']);
            Route::post('/store-module',  [AdminController::class, 'storeModule']);

            Route::get('/module/{module}/edit',      [AdminController::class, 'editModule']);
            Route::post('/module/{module}/edit',     [AdminController::class, 'updateModule']);
            Route::post('/module/{module}/delete',   [AdminController::class, 'deleteModule']);


            // Long Quizzes
            Route::get('/longquiz/{longquiz}',            [AdminController::class, 'viewLongQuiz']);

            Route::get('/create-longquiz',               [AdminController::class, 'createLongQuiz']);
            Route::post('/store-longquiz',                [AdminController::class, 'storeLongQuiz']);

            Route::get('/longquiz/{longquiz}/edit',      [AdminController::class, 'editLongQuiz']);
            Route::post('/longquiz/{longquiz}/edit',      [AdminController::class, 'updateLongQuiz']);
            Route::post('/longquiz/{longquiz}/delete',    [AdminController::class, 'deleteLongQuiz']);

            // Screening Exam
            Route::get('/screening/{screening}', [AdminController::class, 'viewScreening']);

            Route::get('/create-screening', [AdminController::class, 'createScreening']);
            Route::post('/store-screening', [AdminController::class, 'storeScreening']);

            Route::get('/screening/{screening}/edit', [AdminController::class, 'editScreening']);
            Route::post('/screening/{screening}/edit', [AdminController::class, 'updateScreening']);
            Route::post('/screening/{screening}/delete', [AdminController::class, 'deleteScreening']);

            Route::get('/screening/{screening}/add-resource',  [AdminController::class, 'editScreeningResource']);
            Route::post('/screening/{screening}/add-resource',  [AdminController::class, 'updateScreeningResource']);

            // Student Performance
            Route::get('/student/{student}/performance', [AdminController::class, 'viewStudentCoursePerformance']);


            Route::prefix('module/{module}')->group(function () {

                // Lecture
                Route::get('/lecture/{activity}', [AdminController::class, 'viewLecture']);

                Route::get('/create-lecture',   [AdminController::class, 'createLecture']);
                Route::post('/store-lecture',  [AdminController::class, 'storeLecture']);

                Route::get('/lecture/{activity}/edit',      [AdminController::class, 'editLecture']);
                Route::post('/lecture/{activity}/edit',     [AdminController::class, 'updateLecture']);
                Route::post('/lecture/{activity}/delete',   [AdminController::class, 'deleteLecture']);


                // Tutorial
                Route::get('/tutorial/{activity}',            [AdminController::class, 'viewTutorial']);

                Route::get('/create-tutorial',                [AdminController::class, 'createTutorial']);
                Route::post('/store-tutorial',                [AdminController::class, 'storeTutorial']);

                Route::get('/tutorial/{activity}/edit',       [AdminController::class, 'editTutorial']);
                Route::post('/tutorial/{activity}/edit',      [AdminController::class, 'updateTutorial']);
                Route::post('/tutorial/{activity}/delete',    [AdminController::class, 'deleteTutorial']);


                // Practice Quiz
                Route::get('/create-practicequiz',            [AdminController::class, 'createPracticeQuiz']);
                Route::post('/store-practicequiz',            [AdminController::class, 'storePracticeQuiz']);

                Route::get('/practicequiz/{activity}/edit',   [AdminController::class, 'editPracticeQuiz']);
                Route::post('/practicequiz/{activity}/edit',  [AdminController::class, 'updatePracticeQuiz']);
                Route::post('/practicequiz/{activity}/delete', [AdminController::class, 'deletePracticeQuiz']);

                Route::get('/practicequiz/{activity}',        [AdminController::class, 'viewPracticeQuiz']);

                // Short Quiz
                Route::get('/create-shortquiz',               [AdminController::class, 'createShortQuiz']);
                Route::post('/store-shortquiz',               [AdminController::class, 'storeShortQuiz']);

                Route::get('/shortquiz/{activity}/edit',      [AdminController::class, 'editShortQuiz']);
                Route::post('/shortquiz/{activity}/edit',     [AdminController::class, 'updateShortQuiz']);
                Route::post('/shortquiz/{activity}/delete',   [AdminController::class, 'deleteShortQuiz']);

                Route::get('/shortquiz/{activity}',           [AdminController::class, 'viewShortQuiz']);
            });
        });
    });
});


// STUDENT
Route::get('/home-screening', function () {
    return view('home-screening');
});

Route::get('/profile', [MainController::class, 'profilePage']);
Route::post('/profile', [MainController::class, 'profilePage']);
Route::get('/performance', [MainController::class, 'performancePage']);
Route::get('/leaderboards', [MainController::class, 'leaderboardPage']);

Route::get('/inbox',                [InboxController::class, 'index'])->name('inbox.index');
Route::get('/inbox/sent',           [InboxController::class, 'sent'])->name('inbox.sent');
Route::get('/inbox/{inbox}',        [InboxController::class, 'show'])->name('inbox.show');
Route::post('/inbox',                [InboxController::class, 'store'])->name('inbox.store');
Route::post('/inbox/{inbox}/reply',  [InboxController::class, 'reply'])->name('inbox.reply');
Route::post('/message/{message}',      [InboxController::class, 'destroy'])->name('message.destroy');
Route::patch('/message/{message}/read', [InboxController::class, 'toggleRead'])->name('message.toggleRead');


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
    Route::get('/profile', [TeacherController::class, 'profilePage']);
    Route::post('/profile', [TeacherController::class, 'profilePage']);

    Route::get('/course/{course}/section/{section}', [TeacherController::class, 'viewCourse']);

    Route::get('/create-course', [TeacherController::class, 'createCourse']);
    Route::post('/store-course', [TeacherController::class, 'storeCourse']);

    Route::get('/course/{course}/edit',   [TeacherController::class, 'editCourse']);
    Route::post('/course/{course}/edit',   [TeacherController::class, 'updateCourse']);
    Route::post('/course/{course}/delete', [TeacherController::class, 'deleteCourse']);

    Route::prefix('course/{course}/section/{section}')->group(function () {

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
        Route::get('/screening/{screening}', [TeacherController::class, 'viewScreening']);

        Route::get('/create-screening', [TeacherController::class, 'createScreening']);
        Route::post('/store-screening', [TeacherController::class, 'storeScreening']);

        Route::get('/screening/{screening}/edit', [TeacherController::class, 'editScreening']);
        Route::post('/screening/{screening}/edit', [TeacherController::class, 'updateScreening']);
        Route::post('/screening/{screening}/delete', [TeacherController::class, 'deleteScreening']);

        Route::get('/screening/{screening}/add-resource',  [TeacherController::class, 'editScreeningResource']);
        Route::post('/screening/{screening}/add-resource',  [TeacherController::class, 'updateScreeningResource']);

        // Student Performance
        Route::get('/student/{student}/performance', [TeacherController::class, 'viewStudentCoursePerformance']);


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

Route::get('/home-tutor/announcement/{annoucement}', [MainController::class, 'showAnnouncement']);
Route::get('/teachers-panel/announcement/{annoucement}', [TeacherController::class, 'showAnnouncement']);
Route::get('/admin-panel/announcement/{annoucement}', [AdminController::class, 'showAnnouncement']);

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
