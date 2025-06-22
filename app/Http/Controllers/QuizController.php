<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activities;
use App\Models\AssessmentResult;
use App\Models\Questions;
use App\Models\Options;
use App\Models\LongQuizAssessmentResult;
use App\Models\StudentProgress;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;


class QuizController extends Controller
{
    public function computeStudentAnalytics($studentId)
    {
        // Short quiz data (with module_id)
        $short = AssessmentResult::where('student_id', $studentId)->where('is_kept', 1);

        // Long quiz data (no module_id)
        $long = LongQuizAssessmentResult::where('student_id', $studentId)->where('is_kept', 1)
            ->select('score_percentage', 'earned_points');

        // Averages
        $shortAvg = $short->avg('score_percentage');
        $longAvg = $long->avg('score_percentage');

        $shortEarned = $short->sum('earned_points');
        $longEarned = $long->sum('earned_points');

        $shortCount = $short->count();
        $longCount = $long->count();

        $combinedTotal = $short->sum('score_percentage') + $long->sum('score_percentage');
        $combinedCount = $shortCount + $longCount;
        $combinedAvg = $combinedCount > 0 ? $combinedTotal / $combinedCount : 0;

        $totalPoints = ($shortEarned + $longEarned) * 10;

        StudentProgress::updateOrCreate(
            ['student_id' => $studentId],
            [
                'average_score' => $combinedAvg,
                'score_percentage' => $combinedAvg,
                'total_points' => $totalPoints,
            ]
        );
    }

    public function startQuiz($activityID)
    {
        $activity = Activities::with('quiz.questions.options')->findOrFail($activityID);

        $questions = $activity->quiz->questions->shuffle()->take($activity->quiz->number_of_questions)->values();
        Session::put("quiz_{$activityID}_questions", $questions->pluck('question_id')->toArray());

        // Start timer (store deadline in session)
        $timeLimit = $activity->quiz->time_limit;
        Session::put("quiz_{$activityID}_started_at", Carbon::now('Asia/Manila'));
        $deadline = Carbon::now('Asia/Manila')->addSeconds($timeLimit);
        Session::put("quiz_{$activityID}_deadline", $deadline);
        Session::put("quiz_{$activityID}_in_progress", true);

        return redirect("/home-tutor/quiz/{$activityID}/s/q/0");
    }

    public function showQuestion($activityID, $index)
    {
        $studentID = session('user_id');
        $activity = Activities::with('quiz')->findOrFail($activityID);
        $questionIDs = Session::get("quiz_{$activityID}_questions");
        $deadline = Session::get("quiz_{$activityID}_deadline");

        // Get number of attempts the student has taken
        $currentAttempts = AssessmentResult::where('student_id', $studentID)
            ->where('activity_id', $activityID)
            ->count();

        // Get the max number of allowed attempts for the quiz
        $maxAttempts = $activity->quiz->number_of_attempts;

        if ($currentAttempts >= $maxAttempts) {
            return redirect("/home-tutor/quiz/{$activityID}")
                ->with('error', 'You have reached the maximum number of quiz attempts.');
        }

        if (!Session::get("quiz_{$activityID}_in_progress")) {
            return redirect("/home-tutor/module/{$activity->module_id}")
                ->with('error', 'Quiz has already ended or you accessed an invalid link.');
        }

        // 🔒 Protection: No session = Not taking quiz
        if (!$questionIDs || !$deadline) {
            return redirect("/home-tutor/quiz/{$activityID}")
                ->with('error', 'You must start the quiz first.');
        }

        // 🔒 Protection: Time already expired
        if (Carbon::now('Asia/Manila')->gt(Carbon::parse($deadline))) {
            Session::forget("quiz_{$activityID}_questions");
            Session::forget("quiz_{$activityID}_answers");
            Session::forget("quiz_{$activityID}_deadline");

            return redirect("/home-tutor/quiz/{$activityID}")
                ->with('error', 'Your quiz session has expired.');
        }

        // 🔒 Protection: Invalid index (out of bounds)
        if (!isset($questionIDs[$index])) {
            return redirect("/home-tutor/quiz/{$activityID}")
                ->with('error', 'Invalid question number.');
        }

        $questionID = $questionIDs[$index];
        $question = Questions::with(['options', 'questionimage'])->findOrFail($questionID);
        $remainingSeconds = (int) max(0, Carbon::now('Asia/Manila')->diffInSeconds(Carbon::parse($deadline), false));

        return response()->view('student.quiz-interface', [
            'activity' => $activity,
            'question' => $question,
            'index' => $index,
            'total' => count($questionIDs),
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    public function submitAnswer(Request $request, $activityID, $index)
    {
        $selectedOption = $request->input('answer');
        $answers = session()->get("quiz_{$activityID}_answers", []);
        $answers[$index] = $selectedOption;
        session()->put("quiz_{$activityID}_answers", $answers);

        $questionIDs = session("quiz_{$activityID}_questions");
        $nextIndex = $index + 1;

        $deadline = session("quiz_{$activityID}_deadline");
        $isAutoSubmit = $request->input('auto_submit') == 1;

        if ($isAutoSubmit || Carbon::now('Asia/Manila')->gt(Carbon::parse($deadline))) {
            $nextIndex = count($questionIDs); // force finish quiz
        }

        if (!$questionIDs || !$deadline) {
            return redirect("/home-tutor/quiz/{$activityID}")
                ->with('error', 'Invalid quiz session.');
        }

        if ($nextIndex < count($questionIDs)) {
            return redirect("/home-tutor/quiz/{$activityID}/s/q/{$nextIndex}");
        } else {
            $correct = 0;
            foreach ($answers as $i => $selectedOptionID) {
                $questionID = $questionIDs[$i] ?? null;
                if (!$questionID) continue;
                $correctOptionID = Options::where('question_id', $questionID)->where('is_correct', 1)->value('option_id');
                if ($selectedOptionID == $correctOptionID) $correct++;
            }

            $scorePercentage = round(($correct / count($questionIDs)) * 100);
            $earnedPoints = $correct;
            $studentID = session('user_id');

            $prevAttempts = AssessmentResult::where('student_id', $studentID)->where('activity_id', $activityID)->count();

            AssessmentResult::create([
                'result_id' => uniqid(),
                'student_id' => $studentID,
                'module_id' => Activities::find($activityID)->module_id,
                'activity_id' => $activityID,
                'score_percentage' => $scorePercentage,
                'date_taken' => Carbon::now('Asia/Manila'),
                'attempt_number' => $prevAttempts + 1,
                'tier_level_id' => 1,
                'earned_points' => $earnedPoints,
                'is_kept' => 0,
            ]);

            AssessmentResult::where('student_id', $studentID)->where('activity_id', $activityID)->update(['is_kept' => 0]);
            AssessmentResult::where('student_id', $studentID)->where('activity_id', $activityID)->orderByDesc('score_percentage')->first()->update(['is_kept' => 1]);

            Session::forget("quiz_{$activityID}_questions");
            Session::forget("quiz_{$activityID}_answers");
            Session::forget("quiz_{$activityID}_deadline");
            Session::forget("quiz_{$activityID}_in_progress");

            $this->computeStudentAnalytics($studentID);

            return redirect("/home-tutor/quiz/{$activityID}/summary")
                ->with('success', 'Quiz has been submitted.');
        }
    }
}
