<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Services\StudentAnalytics;

use App\Models\Users;
use App\Models\Courses;
use App\Models\Students;
use App\Models\Screening;
use App\Models\ScreeningQuestion;
use App\Models\ScreeningOption;
use App\Models\ScreeningResult;
use App\Models\ScreeningResultAnswer;
use App\Models\LearningResource;

class ScreeningController extends Controller
{
    private function attemptsTaken(string $studentId, string $screeningId): int
    {
        return ScreeningResult::where([
            ['student_id',   $studentId],
            ['screening_id', $screeningId],
        ])->count();
    }

    /*--------------------------------------------------------
    | LIST
    *-------------------------------------------------------*/
    public function screeningPage(Courses $course, $screeningId)
    {
        $userId = session()->get('user_id');
        $users = Users::with('image')->findOrFail($userId);
        $screening = Screening::with([
            'results' => fn($q) => $q->where('student_id', $userId)
                ->orderByDesc('attempt_number')
                ->limit(1)
        ])->findOrFail($screeningId);

        $latestResult = $screening->results->first();

        return view('student.screening', [
            'users' => $users,
            'course'  => $course,
            'screening' => $screening,
            'latestResult' => $latestResult,
        ]);
    }

    /*--------------------------------------------------------
    | START ATTEMPT
    *-------------------------------------------------------*/
    /*--------------------------------------------------------
| START ATTEMPT  (fixed: always builds the exact
|                 number_of_questions list)
*-------------------------------------------------------*/
    public function start(Request $request, $courseId, $screeningId)
    {
        $studentId = session()->get('user_id');

        /* ①  eager-load everything (options + image) ------------- */
        $screening = Screening::with([
            'concepts.topics.questions' => fn($q) => $q->with(['options', 'image']),
        ])
            ->where('course_id', $courseId)
            ->findOrFail($screeningId);

        /* ②  determine attempt # and tier ----------------------- */
        $attemptNo = ScreeningResult::where([
            ['student_id',   $studentId],
            ['screening_id', $screeningId],
        ])->max('attempt_number') ?? 0;
        $attemptNo++;
        $tierId = min($attemptNo, 2);

        /* ③  build shuffled question pools per-topic ------------ */
        $topicPools = [];
        foreach ($screening->concepts as $concept) {
            foreach ($concept->topics as $topic) {
                $topicPools[$topic->screening_topic_id] =
                    $topic->questions->shuffle();          // random inside topic
            }
        }

        /* ④  work out how many we can really serve -------------- */
        $totalAvailable = collect($topicPools)->flatten()->count();
        $totalNeeded    = min($screening->number_of_questions, $totalAvailable);

        $topics = array_keys($topicPools);
        $base   = intdiv($totalNeeded, count($topics));
        $extra  = $totalNeeded % count($topics);

        /* ⑤  first pass: proportional pick per-topic ------------ */
        $questions = [];
        $remaining = $totalNeeded;
        foreach ($topics as $idx => $tId) {
            $take = $base + ($idx < $extra ? 1 : 0);
            $slice = $topicPools[$tId]->take($take)
                ->pluck('screening_question_id')
                ->all();
            $questions   = array_merge($questions, $slice);
            // remove what we just used so leftovers stay unique
            $topicPools[$tId] = $topicPools[$tId]->slice($take);
            $remaining  -= count($slice);
        }

        /* ⑥  second pass: fill any shortfall with random spares -- */
        if ($remaining > 0) {
            $leftovers = collect($topicPools)
                ->flatten()
                ->shuffle()
                ->pluck('screening_question_id')
                ->take($remaining)
                ->all();

            $questions = array_merge($questions, $leftovers);
        }

        /* ⑦  store state in the session ------------------------- */
        Session::put("se_$screeningId", [
            'questions'   => $questions,                 // exact count guaranteed
            'answers'     => [],
            'started_at'  => Carbon::now(),
            'deadline'    => Carbon::now()->addSeconds($screening->time_limit),
            'attempt_no'  => $attemptNo,
            'tier_id'     => $tierId,
        ]);

        return redirect("/home-tutor/course/$courseId/$screeningId/q/0");
    }


    /*--------------------------------------------------------
    | SHOW / SUBMIT QUESTION
    *-------------------------------------------------------*/
    public function play(Request $request, $courseId, $screeningId, $index)
    {
        $studentId = session()->get('user_id');
        $key   = "se_$screeningId";
        $state = Session::get($key);
        if (!$state) {
            return redirect("/home-tutor/course/$courseId/$screeningId")
                ->with('error', 'Quiz has already finished. Invalid url access.');
        }

        if (Carbon::now()->gt($state['deadline'])) {
            return $this->finaliseAttempt($courseId, $screeningId, $state);
        }

        /* POST: store answer then redirect */
        if ($request->isMethod('post')) {
            $data = $request;
            $state['answers'][$index] = $data['answer'];
            Session::put($key, $state);

            $next = $index + 1;
            if ($next >= count($state['questions'])) {
                return $this->finaliseAttempt($courseId, $screeningId, $state);
            }
            return redirect("/home-tutor/course/$courseId/$screeningId/q/$next");
        }

        /* GET: show question */
        if (!isset($state['questions'][$index])) {
            return redirect("/home-tutor/course/$courseId/$screeningId/summary");
        }

        $questionId = $state['questions'][$index];

        $screeningName = Screening::where('screening_id', $screeningId)
            ->value('screening_name');

        /* now eager-loaded with options + image */
        $question = ScreeningQuestion::with(['options', 'image'])
            ->findOrFail($questionId);

        return view('student.screening-interface', [
            'users'      => $studentId,
            'courseId'   => $courseId,
            'screeningID'  => $screeningId,
            'screening_name'  => $screeningName,
            'index'      => $index,
            'total'      => count($state['questions']),
            'question'   => $question,
            'deadlineTs' => $state['deadline']->timestamp,
        ]);
    }

    /*--------------------------------------------------------
    | FINALISE ATTEMPT
    *-------------------------------------------------------*/
    protected function finaliseAttempt(
        string $courseId,
        string $screeningId,
        array  $state
    ) {
        $studentId = session('user_id');

        DB::transaction(function () use ($courseId, $screeningId, $state, $studentId) {

            /* -----------------------------------------------------------------
         * 1 ▸ Gather correct-option IDs for this attempt
         * ----------------------------------------------------------------- */
            $correctIds = ScreeningOption::whereIn(
                'screening_question_id',
                $state['questions']
            )
                ->where('is_correct', 1)
                ->pluck('screening_option_id', 'screening_question_id')
                ->toArray();

            /* -----------------------------------------------------------------
         * 2 ▸ Initialise counters
         * ----------------------------------------------------------------- */
            $earned  = 0;                 // # of correct answers
            $possible = 0;                 // # of questions (always increments)
            $conceptTotals  = $conceptCorrect  = [];
            $topicTotals    = $topicCorrect    = [];

            /* -----------------------------------------------------------------
         * 3 ▸ Pre-create header row so we have a non-null result_id
         * ----------------------------------------------------------------- */
            $result = ScreeningResult::create([
                'result_id'        => Str::uuid(),            // non-null PK
                'screening_id'     => $screeningId,
                'student_id'       => $studentId,
                'tier_id'          => $state['attempt_no'],
                'attempt_number'   => $state['attempt_no'],
                'score_percentage' => 0,                      // provisional
                'earned_points'    => 0,
                'date_taken'       => Carbon::now(),
                'is_kept'          => 0                       // provisional
            ]);

            /* -----------------------------------------------------------------
         * 4 ▸ Loop over each question and insert answers
         * ----------------------------------------------------------------- */
            foreach ($state['questions'] as $i => $qId) {

                $question   = ScreeningQuestion::with('topic.concept')->find($qId);
                $conceptId  = $question->topic->screening_concept_id;
                $topicId    = $question->topic->screening_topic_id;

                // ensure array keys exist
                $conceptTotals[$conceptId] ??= 0;
                $conceptCorrect[$conceptId] ??= 0;
                $topicTotals[$topicId]     ??= 0;
                $topicCorrect[$topicId]    ??= 0;

                $conceptTotals[$conceptId]++;     // always +1
                $topicTotals[$topicId]++;         // always +1
                $possible++;                      // global total

                $picked     = $state['answers'][$i] ?? null;        // null if blank
                $isCorrect  = $picked &&
                    $picked === ($correctIds[$qId] ?? null);

                if ($isCorrect) {
                    $earned++;
                    $conceptCorrect[$conceptId]++;
                    $topicCorrect[$topicId]++;
                }

                ScreeningResultAnswer::create([
                    'result_id'             => $result->result_id,   // FK now valid
                    'screening_question_id' => $qId,
                    'screening_option_id'   => $picked,
                    'is_correct'            => $isCorrect ? 1 : 0
                ]);
            }

            /* -----------------------------------------------------------------
         * 5 ▸ Compute final percentage
         * ----------------------------------------------------------------- */
            $percent = $possible
                ? round(($earned / $possible) * 100, 2)
                : 0;

            /* -----------------------------------------------------------------
 * 6 ▸ Always keep the **latest** attempt
 * ----------------------------------------------------------------- */
            $prevKept = ScreeningResult::where([
                ['screening_id', $screeningId],
                ['student_id',   $studentId],
                ['is_kept',      1],
            ])->first();

            if ($prevKept) {
                // demote the previously-kept row
                $prevKept->update(['is_kept' => 0]);
            }

            // this ( newly-inserted ) result is now the kept one
            $isKept = 1;

            /* -----------------------------------------------------------------
 * 7 ▸ Final-update header row
 * ----------------------------------------------------------------- */
            $result->update([
                'score_percentage' => $percent,
                'earned_points'    => $earned,
                'is_kept'          => $isKept,   // always 1 for the newest
            ]);

            $failed   = $percent < 70;
            $attempts = $state['attempt_no'];          // you already pass 1 | 2 | 3

            if ($failed && $attempts >= 3) {
                // send the learner into catch-up mode
                Students::where('user_id', $studentId)
                    ->update(['isCatchUp' => 1]);
            }

            Session::forget("se_{$screeningId}");
        });

        return redirect("/home-tutor/course/{$courseId}/{$screeningId}/summary")
            ->with([
                'success'   => 'Exam has been submitted.',
            ]);
    }

    /*--------------------------------------------------------
    | SUMMARY
    *-------------------------------------------------------*/
    public function summary(Courses $course, $screeningId)
    {
        $studentId = session()->get('user_id');
        $users = Users::with('image')->findOrFail($studentId);

        $latest = ScreeningResult::where([
            ['screening_id', $screeningId],
            ['student_id',   $studentId],
        ])->orderByDesc('attempt_number')
            ->firstOrFail();

        $answers = ScreeningResultAnswer::where('result_id', $latest->result_id)
            ->with([
                'question.topic.concept',
                'question.image',          // eager-load blob too
            ])->get();


        $conceptData = $topicData = [];
        foreach ($answers as $ans) {
            $concept = $ans->question->topic->concept;
            $topic   = $ans->question->topic;

            $cId = $concept->screening_concept_id;
            $tId = $topic->screening_topic_id;

            $conceptData[$cId]['name']  = $concept->concept_name;
            $topicData[$tId]['name']    = $topic->topic_name;
            $topicData[$tId]['concept_id'] = $cId;

            $conceptData[$cId]['total'] = ($conceptData[$cId]['total'] ?? 0) + 1;
            $topicData[$tId]['total']   = ($topicData[$tId]['total']   ?? 0) + 1;

            if ($ans->is_correct) {
                $conceptData[$cId]['correct'] =
                    ($conceptData[$cId]['correct'] ?? 0) + 1;
                $topicData[$tId]['correct'] =
                    ($topicData[$tId]['correct'] ?? 0) + 1;
            }
        }

        foreach ($conceptData as $id => &$row) {
            $correct             = $row['correct'] ?? 0;      // fallback to 0
            $row['percent']      = round($correct / $row['total'] * 100, 2);
        }
        unset($row);   // break the reference

        foreach ($topicData as $id => &$row) {
            $correct             = $row['correct'] ?? 0;
            $row['percent']      = round($correct / $row['total'] * 100, 2);
        }
        unset($row);

        /* -------------------------------------------------------------
 * Attach resource_id to every concept + topic in one query
 * ------------------------------------------------------------- */
        $conceptRes = LearningResource::whereIn(
            'screening_concept_id',
            array_keys($conceptData)
        )
            ->whereNull('screening_topic_id')        // ⬅️ only real “tier-1” rows
            ->orderBy('learning_resource_id')        // deterministic for pluck()
            ->pluck('learning_resource_id', 'screening_concept_id')
            ->toArray();

        $topicRes   = LearningResource::whereIn('screening_topic_id', array_keys($topicData))
            ->pluck('learning_resource_id', 'screening_topic_id')
            ->toArray();

        foreach ($conceptData as $cid => &$row) {
            $row['resource_id'] = $conceptRes[$cid] ?? null;
        }
        foreach ($topicData   as $tid => &$row) {
            $row['resource_id'] = $topicRes[$tid] ?? null;
        }
        unset($row);

        $screening = Screening::where('screening_id', $screeningId)
            ->findOrFail($screeningId);

        $screeningName = Screening::where('screening_id', $screeningId)
            ->value('screening_name');

        $attempts = session('attempts', $latest->attempt_number);
        $passed   = session('passed',   $latest->score_percentage >= 70);

        return view('student.screening-summary', [
            'users'       => $users,
            'course'    => $course,
            'screening'   => $screening,
            'screeningId' => $screeningId,
            'screening_name'  => $screeningName,
            'result'      => $latest,
            'conceptData' => $conceptData,
            'topicData'   => $topicData,
            'attempts'   => $attempts,
            'passed'     => $passed,
        ]);
    }
}
