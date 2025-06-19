<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssessmentResult;
use App\Models\LongQuizAssessmentResult;
use App\Models\StudentProgress;


class ComputeScores extends Controller
{
    public function computeStudentAnalytics($studentId)
{
    // Fetch short quiz scores from assessmentresult (is_kept = 1)
    $shortResults = AssessmentResult::
        where('student_id', $studentId)
        ->where('is_kept', 1)
        ->select('score_percentage', 'earned_points', 'module_id')
        ->get();

    // Fetch long quiz scores from long_assessmentresult (is_kept = 1)
    $longResults = LongQuizAssessmentResult::
        where('student_id', $studentId)
        ->where('is_kept', 1)
        ->select('score_percentage', 'earned_points', 'module_id')
        ->get();

    // Combine scores
    $allResults = $shortResults->concat($longResults);

    // Calculate Averages
    $avgShort = $shortResults->avg('score_percentage');
    $avgLong = $longResults->avg('score_percentage');
    $avgAll = $allResults->avg('score_percentage');

    // Total Points (earned_points * 10)
    $totalPoints = $allResults->sum('earned_points') * 10;

    // Optional: Print or debug
    // dd($avgShort, $avgLong, $avgAll, $totalPoints);

    // Save to studentprogress
    StudentProgress::updateOrInsert(
        ['student_id' => $studentId],
        [
            'average_score' => $avgAll,
            'score_percentage' => $avgAll,
            'total_points' => $totalPoints,
            'updated_at' => now()
        ]
    );

    // Return values (for display or view)
    return [
        'average_short' => round($avgShort, 2),
        'average_long' => round($avgLong, 2),
        'average_all' => round($avgAll, 2),
        'total_points' => $totalPoints,
    ];
}
}
