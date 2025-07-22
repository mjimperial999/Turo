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

        Route::get('get-screening-exam-list',       [MobileModelController::class, 'showScreeningExamList']);
        Route::get('get-screening-exam',            [MobileModelController::class, 'showScreeningExam']);
        Route::get('get-screening-exam-content',    [MobileModelController::class, 'showScreeningExamContent']);
        Route::post('save-screening-exam-result',   [MobileModelController::class, 'saveScreeningResults']);
        Route::get('get-screening-exam-result',     [MobileModelController::class, 'screeningExamResults']);
        Route::get('get-learning-resources',        [MobileModelController::class, 'fetchLearningResources']);

        Route::post('set-catch-up',     [MobileModelController::class, 'setCatchUp']);

        Route::get('get-student-analysis',      [MobileModelController::class, 'showStudentAnalysis']);
        Route::get('get-gamified-elements',     [MobileModelController::class, 'showGamifiedElements']);

        Route::get('get-inbox',         [MobileModelController::class, 'showInbox']);
        Route::post('mark-read',        [MobileModelController::class, 'markMessageRead']);
        Route::post('delete-message',   [MobileModelController::class, 'deleteMessage']);

        Route::get('get-calendar-events',     [MobileModelController::class, 'showCalendarEvents']);
        Route::get('get-calendar-events-teacher',     [MobileModelController::class, 'showCalendarEventsTeacher']);

        // TEACHER
        Route::get('get-courses-for-teacher',      [MobileModelController::class, 'getCoursesTeacher']);
        Route::get('get-modules-for-teacher',      [MobileModelController::class, 'getModulesTeacher']);
        Route::get('get-activities-for-teacher',   [MobileModelController::class, 'getActivitiesTeacher']);

        Route::get('get-lecture-for-teacher',      [MobileModelController::class, 'getCoursesTeacher']);
        Route::get('get-tutorial-for-teacher',     [MobileModelController::class, 'getCoursesTeacher']);

        Route::get('get-student-quiz-result-by-section',      [MobileModelController::class, 'getStudentQuizResultBySection']);
        Route::get('get-student-long-quiz-result-by-section', [MobileModelController::class, 'getStudentLongQuizResultBySection']);
        Route::get('get-student-screening-result-by-section', [MobileModelController::class, 'getStudentScreeningResultBySection']);

        Route::get('get-section-student-list-for-teacher',      [MobileModelController::class, 'getStudentList']);

        Route::get('get-section-analytics',     [MobileModelController::class, 'showSectionAnalytics']);

        // TEACHER CRUD OPERATIONS
        /* MODULE */
        Route::get('get-module',          [MobileModelController::class, 'showModule']);
        Route::post('create-module',      [MobileModelController::class, 'storeModule']);
        Route::post('update-module',      [MobileModelController::class, 'updateModule']);
        Route::post('delete-module',      [MobileModelController::class, 'destroyModule']);

        /* LONG QUIZ */
        Route::post('create-long-quiz',   [MobileModelController::class, 'storeLongQuiz']);
        Route::post('update-long-quiz',   [MobileModelController::class, 'updateLongQuiz']);
        Route::post('delete-long-quiz',   [MobileModelController::class, 'destroyLongQuiz']);

        /* SCREENING   */
        Route::post('create-screening-exam', [MobileModelController::class, 'storeScreeningExam']);
        Route::post('update-screening-exam', [MobileModelController::class, 'updateScreeningExam']);
        Route::post('delete-screening-exam', [MobileModelController::class, 'destroyScreeningExam']);

        /* QUIZ  (short / practice) */
        Route::post('create-quiz',        [MobileModelController::class, 'storeQuiz']);
        Route::post('update-quiz',        [MobileModelController::class, 'updateQuiz']);
        Route::post('delete-quiz',        [MobileModelController::class, 'destroyQuiz']);

        /* LECTURE / TUTORIAL */
        Route::post('create-lecture',     [MobileModelController::class, 'storeLecture']);
        Route::post('update-lecture',     [MobileModelController::class, 'updateLecture']);
        Route::post('delete-lecture',     [MobileModelController::class, 'destroyLecture']);

        Route::post('create-tutorial',    [MobileModelController::class, 'storeTutorial']);
        Route::post('update-tutorial',    [MobileModelController::class, 'updateTutorial']);
        Route::post('delete-tutorial',    [MobileModelController::class, 'destroyTutorial']);

        Route::get('course', [MobileModelController::class, 'course']);
        Route::get('modules', [MobileModelController::class, 'modules']);
        Route::get('activities', [MobileModelController::class, 'activities']);

        // add more protected routes here, e.g.
        // Route::post('modules/{module}/complete', …);
    });
});
