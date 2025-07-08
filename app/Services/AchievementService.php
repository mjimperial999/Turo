<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/* === models we need =========================================== */
use App\Models\{
    Students,                    // total_points, isCatchUp etc.
    StudentProgress,             // course-level stats
    ModuleProgress,              // module-level stats
    AssessmentResult,            // short / practice
    LongQuizAssessmentResult,    // long
    StudentAchievements,
    StudentBadges,
    Achievements,                // static look-up
    Badges
};

class AchievementService
{
    /* ----------------------------------------------------------------
       Call this after any action that **might** unlock something.
       ----------------------------------------------------------------*/
    public static function evaluate(string $studentId): void
    {
        // Pull *once* the stats we’ll need ↓↓↓
        $totPoints   = Students::where('user_id',$studentId)->value('total_points') ?? 0;
        $moduleDone  = ModuleProgress::where('student_id',$studentId)
                        ->where('is_completed',1)->count();
        $quizKept    = AssessmentResult::where('student_id',$studentId)
                        ->where('is_kept',1)->count();                 // short+practice
        $quiz85plus  = AssessmentResult::where('student_id',$studentId)
                        ->where('is_kept',1)->where('score_percentage','>=',85)->count();
        $quizPerfect = AssessmentResult::where('student_id',$studentId)
                        ->where('is_kept',1)->where('score_percentage',100)->exists();
        $longKept    = LongQuizAssessmentResult::where('student_id',$studentId)
                        ->where('is_kept',1)->count();
        /* add more counters if you later need them (leaderboard rank, …) */

        /* ------------------------------------------------------------
           1) BADGES – purely point based
           -----------------------------------------------------------*/
        $alreadyBadges = StudentBadges::where('student_id',$studentId)
                          ->pluck('badge_id')->toArray();

        Badges::all()->each(function($b) use($studentId,$totPoints,$alreadyBadges){
            if ($totPoints >= $b->points_required * 100 && !in_array($b->badge_id,$alreadyBadges)) {
                StudentBadges::create([
                    'student_id' => $studentId,
                    'badge_id'   => $b->badge_id,
                    'unlocked_at'=> Carbon::now()
                ]);
                self::flash("Unlocked badge: <b>{$b->badge_name}</b>!");
            }
        });

        /* ------------------------------------------------------------
           2) ACHIEVEMENTS – driven by condition_type_id
           -----------------------------------------------------------*/
        $alreadyAch = StudentAchievements::where('student_id',$studentId)
                         ->pluck('achievement_id')->toArray();

        Achievements::with('conditionType')->get()->each(function($a)
            use($studentId,$moduleDone,$quizKept,$quiz85plus,$quizPerfect,
                $totPoints,$alreadyAch,$longKept)
        {
            if (in_array($a->achievement_id,$alreadyAch)) return; // skip

            $val = (int)$a->condition_value;
            $ok  = match($a->condition_type_id) {
                1 => $totPoints   >= $val,              // POINTS
                2 => $moduleDone  >= $val,              // MODULE_COMPLETION
                3 => match(true){                       // QUIZ_SCORE
                         $val==100    => $quizPerfect,
                         default      => $quiz85plus >= $val
                     },
                4 => $quizKept    >= $val,              // ACTIVITY_COMPLETION (# of quizzes)
                /* 6,7,8: GRADE_ABOVE, BADGES_EARNED, LEADERBOARD_RANK
                   add when you have data */
                default => false
            };

            if ($ok) {
                StudentAchievements::create([
                    'student_id'     => $studentId,
                    'achievement_id' => $a->achievement_id,
                    'unlocked_at'    => Carbon::now()
                ]);
                self::flash("Achievement unlocked: <b>{$a->achievement_name}</b>!");
            }
        });
    }

    /* helper – push message into the flash-stack (no redirect needed) */
    private static function flash(string $html): void
    {
        /* keep an array so multiple unlocks show together */
        $stack = session()->get('ach_flash',[]);
        $stack[] = $html;
        session()->flash('ach_flash',$stack);
    }
}
