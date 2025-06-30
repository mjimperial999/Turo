<?php
namespace App\Services;

use App\Models\AssessmentResult;           // short-quiz results
use App\Models\LongQuizAssessmentResult;   // long-quiz results
use App\Models\StudentProgress;
use Illuminate\Support\Facades\DB;

class StudentAnalytics
{
    /**
     * Recalculate – or create – a StudentProgress row
     * for the given student & course.
     *
     * @param  string $studentId
     * @param  string $courseId
     * @return void
     */
    public function update(string $studentId, string $moduleId = null, string $courseId = null,): void
    {
        /* ----------------------------------------------------------
         | 1. pull aggregates with *one* query per table
         |    (scoped to this course)
         |--------------------------------------------------------- */
        $shortAgg = AssessmentResult::query()
            ->selectRaw('
                AVG(score_percentage)  AS avg_pct,
                SUM(score_percentage)  AS sum_pct,
                SUM(earned_points)     AS sum_pts,
                COUNT(*)               AS cnt
            ')
            ->where([
                ['student_id', $studentId],
                ['module_id',  $moduleId],      // make sure there is such a column
                ['is_kept',    1],
            ])
            ->first();

        $longAgg  = LongQuizAssessmentResult::query()
            ->selectRaw('
                AVG(score_percentage)  AS avg_pct,
                SUM(score_percentage)  AS sum_pct,
                SUM(earned_points)     AS sum_pts,
                COUNT(*)               AS cnt
            ')
            ->where([
                ['student_id', $studentId],
                ['course_id',  $courseId],
                ['is_kept',    1],
            ])
            ->first();

        /* ----------------------------------------------------------
         | 2. derived numbers
         |--------------------------------------------------------- */
        $combinedCnt = $shortAgg->cnt + $longAgg->cnt;
        $combinedAvg = $combinedCnt
            ? ($shortAgg->sum_pct + $longAgg->sum_pct) / $combinedCnt
            : 0;

        $totalPts = ($shortAgg->sum_pts + $longAgg->sum_pts) * 10;

        /* ----------------------------------------------------------
         | 3. UPSERT the progress row (composite key)
         |--------------------------------------------------------- */
        StudentProgress::updateOrCreate(
            ['student_id' => $studentId, 'course_id' => $courseId],
            [
                'average_score'   => $combinedAvg,
                'score_percentage'=> $combinedAvg,   // keep if you still need both
                'total_points'    => $totalPts,
            ]
        );
    }
}
