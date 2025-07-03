<?php
/* app/Services/StudentAnalytics.php */
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/* models ---------------------------------------------------------------*/
use App\Models\{
    ModuleProgress,
    StudentProgress,
    Students,
    AssessmentResult,
    LongQuizAssessmentResult,
    Activities,
    Modules,
    Courses
};

class StudentAnalytics
{
    /* ===================================================================
     |  PUBLIC API
     |===================================================================*/
    /**
     * Call this after ANY quiz submission that could change a score.
     */
    public static function updateAfterQuiz(string $studentId,
                                           string $courseId,
                                           ?string $moduleId = null): void
    {
        self::recalcModule ($studentId, $courseId, $moduleId); // short / practice
        self::recalcCourse ($studentId, $courseId);            // short + long
        self::recalcStudent($studentId);                       // total points
    }

    /** Re-calculate only the “grand-total” row shown on the profile page */
    public static function refreshStudentSummary(string $studentId): void
    {
        self::recalcStudent($studentId);
    }

    /* ===================================================================
     |  MODULE LEVEL  (short- & practice-quiz only)
     |===================================================================*/
    private static function recalcModule(string $studentId,
                                         string $courseId,
                                         ?string $moduleId): void
    {
        // If NULL was passed, iterate over *all* modules in the course
        $moduleIds = $moduleId
            ? [$moduleId]
            : Modules::where('course_id', $courseId)->pluck('module_id');

        foreach ($moduleIds as $mid) {

            /* ----------------------------------------------------------
             | 1.  Pull *kept* attempts for each quiz-type
             |---------------------------------------------------------*/
            $short   = self::kept($studentId, $mid, quizType: 1); // SHORT
            $practice= self::kept($studentId, $mid, quizType: 2); // PRACTICE

            $shortCnt     = $short->count();
            $practiceCnt  = $practice->count();

            $shortAvg     = $short->avg('score_percentage')   ?? 0; // only SHORT shown
            $notPassed    = $short->where('score_percentage',    '<', 70)->count()
                          + $practice->where('score_percentage', '<', 70)->count();

            /* ----------------------------------------------------------
             | 2.  Determine completion flag
             |     (needs ≥70 % in *all* short + practice attempts)
             |---------------------------------------------------------*/
            $attemptedAny = ($shortCnt + $practiceCnt) > 0;
            $isCompleted  = $attemptedAny && $notPassed === 0;

            /* ----------------------------------------------------------
             | 3.  Earned points (all quiz kinds in this module)
             |---------------------------------------------------------*/
            $points = AssessmentResult::where([
                           ['student_id', $studentId],
                           ['module_id',  $mid],
                           ['is_kept',    1],   
                       ])->sum('earned_points');

            /* ----------------------------------------------------------
             | 4.  Upsert – make sure EVERY module row exists
             |---------------------------------------------------------*/
            ModuleProgress::updateOrCreate(
                ['student_id' => $studentId, 'module_id' => $mid],
                [
                    'course_id'     => $courseId,         // new FK column
                    'average_score' => round($shortAvg,2),
                    'is_completed'  => $isCompleted ? 1 : 0,
                    'progress'      => round($shortAvg,2),// legacy column
                ]
            );
        }
    }

    /** Helper: kept attempts for a given quiz-type inside one module */
    private static function kept(string  $studentId,
                                 string  $moduleId,
                                 int     $quizType /*1=Short 2=Practice*/ )
    {
        return AssessmentResult::where([
                    ['student_id', $studentId],
                    ['module_id',  $moduleId],
                    ['is_kept',    1],
                ])->whereHas('activity.quiz', fn($q) => $q->where('quiz_type_id', $quizType));
    }

    /* ===================================================================
     |  COURSE LEVEL  (short module-avgs  +  long-quiz avgs)
     |===================================================================*/
    private static function recalcCourse(string $studentId,
                                         string $courseId): void
    {
        /* 1)  SHORT-quiz average = mean of module averages */
        $shortAvg = ModuleProgress::where([
                        ['student_id', $studentId],
                        ['course_id',  $courseId],
                    ])->avg('average_score') ?? 0;

        /* 2)  LONG-quiz average */
        $longAvg  = LongQuizAssessmentResult::query()
                      ->join('longquiz', 'long_assessmentresult.long_quiz_id','=','longquiz.long_quiz_id')
                      ->where([
                          ['long_assessmentresult.student_id', $studentId],
                          ['longquiz.course_id',               $courseId],
                          ['long_assessmentresult.is_kept',     1],
                      ])
                      ->avg('score_percentage') ?? 0;

        /* 3)  Points from *all* quiz kinds in this course */
        $shortPts = AssessmentResult::query()
                      ->join('module','assessmentresult.module_id','=','module.module_id')
                      ->where('assessmentresult.student_id', $studentId)
                      ->where('module.course_id',            $courseId)
                      ->where('assessmentresult.is_kept',    1)
                      ->sum('earned_points');

        $longPts  = LongQuizAssessmentResult::query()
                      ->join('longquiz','long_assessmentresult.long_quiz_id','=','longquiz.long_quiz_id')
                      ->where('long_assessmentresult.student_id', $studentId)
                      ->where('longquiz.course_id',               $courseId)
                      ->where('long_assessmentresult.is_kept',    1)
                      ->sum('earned_points');

        $points   = $shortPts + $longPts;

        $combined = ($shortAvg && $longAvg)
                  ? ($shortAvg + $longAvg) / 2
                  : ($shortAvg ?: $longAvg);

        StudentProgress::updateOrCreate(
            ['student_id' => $studentId, 'course_id' => $courseId],
            [
                'short_quiz_avg'   => round($shortAvg, 2),
                'long_quiz_avg'    => round($longAvg , 2),
                'average_score'    => round($combined, 2),
                'score_percentage' => round($combined   ), // whole %
                'total_points'     => $points * 100,
            ]
        );
    }

    /* ===================================================================
     |  STUDENT-WIDE TOTALS  (sum of course points)
     |===================================================================*/
    private static function recalcStudent(string $studentId): void
    {
        $totalPts = StudentProgress::where('student_id', $studentId)
                     ->sum('total_points');

        Students::where('user_id', $studentId)
            ->update(['total_points' => $totalPts]);
    }
}
