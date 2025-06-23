<!DOCTYPE html>
<html lang="en">

<?php
    $title = 'Performance';
    include __DIR__ . '/../partials/head.php';
?>
    <style>
        table,
        th,
        td {
            border: 0.04em solid #C9C9C9;
            border-collapse: collapse;
        }

        table {
            width: 100%;
            margin: 0;
        }

        .table-left-padding {
            width: 2em;
        }

        .table-right-padding {
            padding: 1em 1.5em;
        }

        h5 {
            font-family: Alata, sans-serif;
        }

        .performance-container {
            width: 100%;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            gap: 2rem;

            font-family: Albert-Sans, sans-serif;
        }

        .performance-details {
            width: 75%;
            display: flex;
            flex-direction: column;

            font-size: 1rem;
            color: #492C2C;
        }

        .performance-graphics {
            width: 25%;
            display: flex;
            flex-direction: column;
        }

        .performance-course-element {
            margin: 0;
            border-radius: 0.4em;
            gap: 1rem;
        }

        .performance-course-element b {
            color: rgb(45, 45, 45);
        }

        .performance-table {
            border-radius: 1rem;
        }

        .performance-table th,
        .performance-table td {
            border: 0.04em solid #492C2C;
            margin: 0;
            padding: 0.1rem 0.5rem;
        }

        .performance-table .results {
            width: 2.5rem;
            text-align: right;
        }

        .performance-table .results-sub {
            text-align: right;
            font-size: 0.8rem;
            color:rgb(116, 94, 94);
        }

        .performance-table .results-main {
            text-align: right;
            font-size: 1.2rem;
            background-color: rgba(214, 118, 16, 0.14);
            color:rgb(44, 40, 40);
        }

        .performance-course {
            font-size: 1.2rem;
            background-color: rgba(120, 79, 74, 0.4);
            color: #492C2C;
            margin: 0;
            font-weight: 700;
        }

        .performance-overall {
            font-size: 1rem;
            color: #492C2C;
            margin: 0;
        }

        .performance-module {
            font-size: 0.9rem;
            color: #492C2C;
            color:rgb(108, 96, 96);
            margin: 0;
        }

        .performance-module.span {
            padding-left: 1.5rem;
        }

        .performance-overall-both {
            font-size: 1.1rem;
            color: #492C2C;
            margin: 0;
        }

        .performance-points {
            display: flex;
            flex-direction: column;
            align-items: center;

            color: #492C2C;
            text-align: center;
            line-height: 1.2;
        }

        .performance-points img {
            margin-bottom: 1rem;
        }

        p.points-total-header {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        p.points-total-points {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 800;
        }

    </style>
</head>

<body>
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
    ?>

    <div class="home-tutor-screen">
        <div class="home-tutor-main">
            <table>
                <tr class="module-title">
                    <th class="table-left-padding"></th>
                    <th class="table-right-padding">
                        <div class="module-heading">
                            <div class="module-logo">
                                <img class="svg" src="/icons/graph.svg" width="50em" height="auto" />
                            </div>
                            <div class="heading-context">
                                <h5><b>PERFORMANCE ANALYTICS</b></h5>
                            </div>
                        </div>
                    </th>
                </tr>
                <tr class="module-subtitle">
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <div class="module-section quiz-background profile-color">
                            <div class="performance-container">
                                <div class="performance-details">
                                    <b>ALL COURSES</b>
                                    <?php
                                    foreach ($courses as $course) {
                                        $shortAvg = $shortAverages[$course->course_id]->short_avg ?? null;
                                        $longAvg = $longAverages[$course->course_id]->long_avg ?? null;
                                        $combinedAvg = null;

                                        if (!is_null($shortAvg) && !is_null($longAvg)) {
                                            $combinedAvg = ($shortAvg + $longAvg) / 2;
                                        } elseif (!is_null($shortAvg)) {
                                            $combinedAvg = $shortAvg;
                                        } elseif (!is_null($longAvg)) {
                                            $combinedAvg = $longAvg;
                                        }
                                        echo '<div class="performance-course-element"><table class="performance-table">';
                                        echo '<tr>
                                                <th colspan="2" class="performance-course">' . $course->course_name . '</th>
                                            </tr>
                                            <tr>
                                                <th class="performance-overall">Short Quiz Average</th>
                                                <th class="results">' . (!is_null($shortAvg) ? round($shortAvg, 2) . "%" : "No data") . '</th>
                                            </tr>';

                                        foreach ($moduleAverages as $m) {
                                            if ($m->course_id === $course->course_id) {
                                                echo '<tr>
                                                        <th class="performance-module span">> ' . $m->module_name . '</th>
                                                        <th class="results-sub">' . round($m->average_score, 2) . '%</th>
                                                    </tr>';
                                            }
                                        }

                                        echo '<tr>               
                                            <th class="performance-overall">Long Quiz Average:</th>
                                            <th class="results">' . (!is_null($longAvg) ? round($longAvg) . "%" : "No data") . '</th>
                                        </tr>';

                                        foreach ($longQuizzes as $lq) {
                                            if ($lq->course_id === $course->course_id) {
                                                echo '<tr>
                                                        <th class="performance-module span">> '.$lq->long_quiz_name.'</th>
                                                        <th class="results-sub">'.round($lq->average_score, 2).'%</th>
                                                    </tr>';
                                            }
                                        }
                                        
                                        echo '<tr>               
                                            <th class="performance-overall-both">Course Average:</th>
                                            <th class="results-main">' . (!is_null($percentage) ? round($percentage) . "%" : "No data") . '</th>
                                        </tr>
                                    </table></div>';
                                    }

                                    ?>
                                </div>
                                <div class="performance-graphics">
                                    <div class="performance-points">
                                        <img class="svg" src="/icons/points.svg" width="100em" height="auto" />
                                        <p class="points-total-header">Total Points Gained</p>
                                        <p class="points-total-points"><?= $progress->total_points ?? 0 ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="module-title">
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <div class="module-heading">
                            <div class="module-logo">
                                <img class="svg" src="/icons/achievements.svg" width="50em" height="auto" style="filter: drop-shadow(0 0.2rem 0.25rem rgba(0, 0, 0, 0.2));" />
                            </div>
                            <div class="heading-context">
                                <h5><b>ACHIEVEMENTS</b></h5>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="module-subtitle">
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                    </td>
            </table>
        </div>
        <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
    </div>
</body>

</html>