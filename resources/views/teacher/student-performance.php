<?php
$title = "{$student->user->last_name}, {$student->user->first_name} — {$course->course_name}";
include __DIR__ . '/../partials/head.php'; ?>

<style>
    /* quick utility styles (reuse your existing palette) */
    .table-box {
        border: 1px solid #ddd;
        border-radius: .4rem;
        overflow: hidden
    }

    .table-box h6 {
        margin: 0;
        padding: .6rem 1rem;
        background: #fcfdf2;
        border-bottom: 1px solid #ddd
    }

    .table-box table {
        width: 100%;
        border: 1px solid #ddd;
        font-size: .9rem
    }

    .table-box th,
    .table-box td {
        padding: .45rem .6rem;
        border: 1px solid #ddd;
        text-align: center
    }

    .table-box tr:nth-child(even) {
        background: #fffaf7
    }

    .img {
        width: 4rem;
        height: 4rem;
        border-radius: 25%;
        background-size: cover;
        background-position: center;
        margin-right: .4rem
    }
</style>
</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav-teach.php';

    if (empty($student->user->image?->image)) {;
        $imageURL = "/icons/no-img.jpg";
    } else {
        $blobData = $student->user->image?->image;
        $mimeType = getMimeTypeFromBlob($blobData);
        $base64Image = base64_encode($blobData);
        $imageURL = "data:$mimeType;base64,$base64Image";
    }

    ?>

    <div class="screen">
        <!-- MAIN -->
        <div class="spacing main">

            <!-- breadcrumb -------------------------------------------------------- -->
            <div class="content-container box-page">
                <div class="mini-navigation">
                    <div class="text title">
                        <h6><a href="/teachers-panel">Courses</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>
                            <a href="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>">
                                <?= $course->course_name ?> (Section: <?= strtoupper($section->section_name) ?>)
                            </a>
                        </h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>Performance: <?= $student->user->last_name ?></h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <!-- header ------------------------------------------------------------- -->
            <div class="content-container box-page">
                <div class="content padding heading box-gray">
                    <div class="header logo">
                        <div class="logo-and-title">
                            <div class="img" style="background-image:url('<?= $imageURL ?>')"></div>
                            <div class="text title">
                                <h4><?= $student->user->last_name ?>, <?= $student->user->first_name ?></h4>
                            </div>
                        </div>
                        <hr class="divider-hr">
                        <div class="subtitle">
                            <h6><b><?= strtoupper($section->section_name) ?></b></h6>
                            <h6>Total points in this course: <b><?= number_format($overall->total_points ?? 0) ?></b></h6>
                            <h6>
                                Leaderboard rank:
                                <b><?= $overall->rank !== null ? "#$overall->rank" : '—' ?></b>
                            </h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRACTICE QUIZZES --------------------------------------------------- -->
            <?php if (!empty($practice)): ?>
                <div class="content-container box-page table-box">
                    <h6>Practice-Quiz Averages</h6>
                    <table>
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Quiz</th>
                                <th>Avg %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($practice as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row->module_name) ?></td>
                                    <td><?= htmlspecialchars($row->quiz_name) ?></td>
                                    <td><?= round($row->avg, 1) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- SHORT QUIZZES ------------------------------------------------------ -->
            <?php if (!empty($short)): ?>
                <div class="content-container box-page table-box">
                    <h6>Short-Quiz Averages</h6>
                    <table>
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Quiz</th>
                                <th>Avg %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($short as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row->module_name) ?></td>
                                    <td><?= htmlspecialchars($row->quiz_name) ?></td>
                                    <td><?= round($row->avg, 1) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- LONG QUIZZES ------------------------------------------------------- -->
            <?php if (!empty($long)): ?>
                <div class="content-container box-page table-box">
                    <h6>Long-Quiz Averages</h6>
                    <table>
                        <thead>
                            <tr>
                                <th>Quiz</th>
                                <th>Avg %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($long as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row->quiz_name) ?></td>
                                    <td><?= round($row->avg, 1) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- SCREENING EXAMS ---------------------------------------------------- -->
            <?php if (!empty($screening)): ?>
                <div class="content-container box-page table-box">
                    <h6>Best Screening-Exam Scores</h6>
                    <table>
                        <thead>
                            <tr>
                                <th>Screening Exam</th>
                                <th>Best Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($screening as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row->screening_name) ?></td>
                                    <td><?= round($row->best_score, 1) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>


        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>