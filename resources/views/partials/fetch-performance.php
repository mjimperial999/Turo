<?php

use Illuminate\Support\Facades\DB;
use App\Models\StudentProgress;

include __DIR__ . '/../partials/nav.php';

$studentID = session()->get('user_id');
$progress = StudentProgress::where('student_id', $studentID)->first();

// 1. Get course list enrolled by the student
$courses = DB::table('course')
    ->join('enrollment', 'course.course_id', '=', 'enrollment.course_id')
    ->where('enrollment.student_id', $studentID)
    ->select('course.course_id', 'course.course_name')
    ->get();

// 2. Get module-level averages (short quizzes)
$moduleAverages = DB::table('assessmentresult')
    ->where('assessmentresult.student_id', $studentID)
    ->where('assessmentresult.is_kept', 1)
    ->join('module', 'assessmentresult.module_id', '=', 'module.module_id')
    ->join('course', 'module.course_id', '=', 'course.course_id')
    ->select(
        'assessmentresult.module_id',
        'module.module_name',
        'module.course_id',
        DB::raw('AVG(score_percentage) as average_score')
    )
    ->groupBy('assessmentresult.module_id', 'module.module_name', 'module.course_id')
    ->get();

// 3. Get short quiz average per course
$shortAverages = DB::table('assessmentresult')
    ->where('assessmentresult.student_id', $studentID)
    ->where('assessmentresult.is_kept', 1)
    ->join('module', 'assessmentresult.module_id', '=', 'module.module_id')
    ->groupBy('module.course_id')
    ->select('module.course_id', DB::raw('AVG(score_percentage) as short_avg'))
    ->get()
    ->keyBy('course_id');

// 4. Get long quiz average per course
$longAverages = DB::table('long_assessmentresult')
    ->where('student_id', $studentID)
    ->where('is_kept', 1)
    ->join('longquiz', 'long_assessmentresult.long_quiz_id', '=', 'longquiz.long_quiz_id')
    ->groupBy('longquiz.course_id')
    ->select('longquiz.course_id', DB::raw('AVG(score_percentage) as long_avg'))
    ->get()
    ->keyBy('course_id');


$longQuizzes = DB::table('long_assessmentresult')
    ->join('longquiz', 'long_assessmentresult.long_quiz_id', '=', 'longquiz.long_quiz_id')
    ->where('long_assessmentresult.student_id', $studentID)
    ->where('long_assessmentresult.is_kept', 1)
    ->select(
        'longquiz.course_id',
        'longquiz.long_quiz_name',
        DB::raw('AVG(long_assessmentresult.score_percentage) as average_score')
    )
    ->groupBy('longquiz.course_id', 'longquiz.long_quiz_name')
    ->get();

$percentage = $progress ? round($progress->average_score ?? 0, 2) : null;
