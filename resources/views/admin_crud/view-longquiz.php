<?php
$title = $longquiz->long_quiz_name;
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
    }

    .table {
        margin: 0;
    }

    .table-left-padding {
        width: 2em;
    }

    .table-right-padding {
        padding: 1em 1.5em;
    }

    @keyframes anim {
        100% {
            stroke-dashoffset: var(--num);
        }
    }
</style>

</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav-admin.php';
    include __DIR__ . '/../partials/time-lock-check-modules.php';

    $seconds = $longquiz->time_limit;
    $minutes = floor($seconds / 60);
    $fTimeLimit = sprintf("%2d", $minutes);
    ?>

    <div class="screen">
        <div class="spacing main">
            <div class="content-container box-page">
                <div class="mini-navigation">
                    <div class="text title">
                        <h6><a href="/admin-panel/edit-content">Courses</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><a href="/admin-panel/edit-content/course/<?= $course->course_id ?>"><?= $course->course_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>(LONG QUIZ) <?= $longquiz->long_quiz_name ?></a></h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-gold">

                <div class="content padding">
                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/long-quiz.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4><?= $longquiz->long_quiz_name ?></h4>
                                <h6>Long Quiz</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-container long-quiz">
                <div class="content flex-column">

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>QUESTIONS: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= $longquiz->number_of_questions ?></p>
                        </div>
                    </div>

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>TOTAL ATTEMPTS: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= $longquiz->number_of_attempts ?></p>
                        </div>
                    </div>

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>TIME LIMIT: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= $longquiz->time_limit / 60 ?> minutes</p>
                        </div>
                    </div>

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>OPENS: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= $formattedUnlockDate ?></p>
                        </div>
                    </div>

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>DUE: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= $formattedDeadline ?></p>
                        </div>
                    </div>

                    <hr class="divider-hr">

                    <p class="description"><b>INSTUCTIONS:</b><br><?= ($longquiz->long_quiz_instructions) ?></p>


                </div>

            </div>

            <div class="content-container box-page">
                <div class="content padding heading box-gray">
                    <div class="header">
                        <div class="text title">
                            <h5> Quiz Answers </h5>
                        </div>
                    </div>
                </div>

                <div class="content padding">
                    <?php foreach ($questions as $index => $q): ?>
                        <?php $num = $index + 1; /* 1-based counter */ ?>
                        <div class="question-card">
                            <h6><b>
                                    Q<?= $num ?>. <?= htmlspecialchars($q->question_text) ?>
                            </h6></b>

                            <?php if ($q->longquizimage && $q->longquizimage->image):        // BLOB present 
                            ?>
                                <?php
                                $blob     = $q->longquizimage->image;
                                $mimeType = getMimeTypeFromBlob($blob);
                                $imgURL   = "data:$mimeType;base64," . base64_encode($blob);
                                ?>
                                <img src="<?= $imgURL ?>" style="max-width:10em;margin:.4rem 0">
                            <?php endif; ?>

                            <?php foreach ($q->longquizoptions as $opt): ?>
                                <?php $isCorrect = $opt->is_correct; ?>
                                <div style="
                                    padding:.35rem .6rem;
                                    border:1px solid #ccc;
                                    border-radius:.3rem;
                                    margin:.2rem 0;
                                    background:<?= $isCorrect ? '#c7e5ff' : '#fff' ?>;
                                    color:<?= $isCorrect ? '#273341' : '#333' ?>;
                                ">
                                    <?= htmlspecialchars($opt->option_text) ?>
                                    <?php if ($isCorrect): ?>
                                        <span style="font-size:.75rem;font-weight:700;margin-left:.4rem">&#10004;</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <hr class="divider-hr">
                    <?php endforeach; ?>
                </div>

            </div>

        </div>



        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
        </div>

    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>

<?php /*

    <div class="home-tutor-screen">
        <div class="home-tutor-main">
            <table>
                <tr class="module-title">
                    <th class="table-left-padding"></th>
                    <th class="table-right-padding">
                        <div class="first-th">
                            <div class="module-heading">
                                <div class="module-logo">
                                    <img class="svg" src="/icons/long-quiz.svg" width="50em" height="auto" />
                                </div>
                                <div class="heading-context">
                                    <h5><b><?= $longquiz->long_quiz_name ?></b></h5>
                                    <p>Long Quiz</p>
                                </div>
                            </div>
                            <div class="return-prev-container">
                                <?= '<a class="activity-link" href="/home-tutor/course/' . $course->course_id . '/"> ' ?>
                                <div class="return-prev"><- BACK to Course: <?= $course->course_name ?> Page</div>
                                        </a>
                                </div>
                            </div>
                    </th>
                </tr>
                <tr>
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger alert-message" role="alert">
                                <?= session('error') ?>
                            </div>
                        <?php elseif (session()->has('success')): ?>
                            <div class="alert alert-success alert-message" role="alert">
                                <?= session('success') ?>
                            </div>
                        <?php endif; ?>
                        <div class="module-section quiz-background long-quiz">
                            <div class="module-section quiz-header">
                                <div class="quiz-description">
                                    <div class="quiz-categories-top">
                                        <div class="quiz-categories">
                                            <div class="quiz-categories-desc">
                                                <p class="description"><b>QUESTIONS: </b><?= $longquiz->number_of_questions ?></p>
                                                <p class="description"><b>TOTAL ATTEMPTS: </b><?= $longquiz->number_of_attempts ?></p>
                                                <p class="description"><b>TIME LIMIT: </b><?= $fTimeLimit ?> min/s</p>
                                            </div>
                                        </div>
                                        <div class="quiz-categories">
                                            <div class="quiz-categories-desc">
                                                <p class="description"><b>OPENS: </b><?= $formattedUnlockDate ?></p>
                                                <p class="description"><b>DUE: </b><?= $formattedDeadline ?></p>
                                            </div>
                                        </div>
                                        <br>
                                    </div>
                                    <hr>
                                    <p class="description">Instructions: <?= $longquiz->long_quiz_instructions ?></p>
                                </div>
                                <div class="quiz-graphics">
                                    <div class="percentage-container">
                                        <div class="percent" style="--clr:<?= $color ?>; --num:<?= $circle_display ?>">
                                            <svg>
                                                <circle cx="70" cy="70" r="70"></circle>
                                                <circle cx="70" cy="70" r="70"></circle>
                                            </svg>
                                        </div>
                                        <div class="percent-number">
                                            <h1><?= $percentage_display ?><span>%</span></h1>
                                        </div>
                                    </div>
                                    <div class="points-container">
                                        <br>
                                        <p class="description" style="text-align: center;">Total Points Garnered</p>
                                        <p class="description points" style="text-align: center;"><b><?= ($assessment->earned_points * 10) ?? 0 ?></b></p>
                                    </div>
                                </div>
                            </div>
                            <div class="module-section quiz-button-section">
                                <?= '<a class="activity-link" href="/home-tutor/long-quiz/' . $course->course_id . '/' . $longquiz->long_quiz_id . '/s"> ' ?>
                                <div class="quiz-button activity-button quiz-long-activity">TAKE QUIZ</div>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <div class="module-section">
                            <p class="description" style="color: #492C2C;"><b>ANALYSIS</b></p>
                            <p class="description" style="color: #492C2C;"><b>ATTEMPTS TAKEN: </b><?= $attempts ?></p>
                            <table class="attempts-table">
                                <thead>
                                    <tr class="attempts-table-header" style="background-color: rgba(176, 176, 176, 0.4);">
                                        <th>ATTEMPT</th>
                                        <th>SCORE</th>
                                        <th>PERCENTAGE</th>
                                        <th>DATE TAKEN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php include __DIR__ . '/../partials/long-quiz-assessments.php'; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

        </div>
        <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
    </div>
</body>

</html>

*/ ?>