<?php
$title = $module->module_name;
include __DIR__ . '/../partials/head.php';  ?>
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

    .table-left-padding {
        width: 2em;
    }

    .table-right-padding {
        padding: 1em 1.5em;
    }

    .module-section {
        display: flex;
        flex-direction: column;
        align-items: flex-start
    }

    tr.module-title {
        height: 1em;
    }

    tr:nth-child(odd) {
        background-color: rgba(211, 211, 211, 0.30);
    }

    tr:nth-child(even) {
        background-color: rgba(232, 232, 232, 0.3);
    }
</style>
</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav.php'; ?>


    <div class="screen">
        <div class="spacing main">
            <div class="content-container box-page">
                <div class="mini-navigation">
                    <div class="text title">
                        <h6><a href="/home-tutor">Courses</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><a href="/home-tutor/course/<?= $course->course_id ?>"><?= $course->course_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><?= $module->module_name ?></h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-gray">
                <div class="content padding">

                    <div class="header">
                        <div class="text title">
                            <h4><?= $module->module_name ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-container box-page activities-content">
                <div class="content padding heading box-gray">

                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/lecture.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h5> LECTURES AND VIDEOS </h5>
                                <p> Read and learn with the resources and watch tutorials. </p>
                            </div>
                        </div>
                    </div>

                </div>



                <div class="content padding activity-list">
                    <div class="lecture-flex-area">
                        <?php foreach ($module->activities->where('activity_type', 'LECTURE') as $activity) {
                            include __DIR__ . '/../partials/time-lock-check.php';
                            include __DIR__ . '/../partials/lecture-hero.php';
                        }; ?>
                        <?php foreach ($module->activities->where('activity_type', 'TUTORIAL') as $activity) {
                            include __DIR__ . '/../partials/time-lock-check.php';
                            include __DIR__ . '/../partials/tutorial-hero.php';
                        }; ?>
                    </div>
                </div>
            </div>

            <div class="content-container box-page activities-content">
                <div class="content padding heading box-gray">

                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/practice-quiz.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h5>SKILL-HONING TUTORIALS</h5>
                                <p>Learn how to solve problems and hone your skills in these tutorials:</p>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="content padding activity-list">
                    <?php foreach ($module->activities->where('activity_type', 'QUIZ') as $activity): {
                            if ($activity->quiz->quiz_type_id == 2): {
                                    include __DIR__ . '/../partials/time-lock-check.php';
                                    include __DIR__ . '/../partials/quiz-practice-hero.php';
                                }
                            endif;
                        };
                    endforeach; ?>
                </div>
            </div>

            <div class="content-container box-page activities-content">
                <div class="content padding heading box-gray">

                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/short-quiz.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h5> SHORT QUIZ </h5>
                                <p> Test your skills. </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content padding activity-list">
                    <div class="quiz-flex-area">
                        <?php foreach ($module->activities->where('activity_type', 'QUIZ') as $activity): {
                                if ($activity->quiz->quiz_type_id == 1): {
                                        include __DIR__ . '/../partials/time-lock-check.php';
                                        include __DIR__ . '/../partials/quiz-short-hero.php';
                                    };
                                endif;
                            };
                        endforeach; ?>
                    </div>
                </div>

            </div>
            <?php /*
            <div class="home-tutor-main">
            <table>

                <tr class="module-subtitle">
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <div class="module-section">
                            <div class="module-heading">
                                <div class="module-logo">
                                    <img class="svg" src="/icons/lecture.svg" width="50em" height="auto" />
                                    <img class="svg" src="/icons/vid.svg" width="50em" height="auto" />
                                </div>
                                <div class="heading-context">
                                    <h5>LECTURES AND VIDEOS</h5>
                                    <p>Read and learn with the resources and watch tutorials.</p>
                                </div>
                            </div>
                            <div class="module-divider">
                                <hr>
                            </div>
                            <div class="module-content">
                                <?php foreach ($module->activities->where('activity_type', 'LECTURE') as $activity) {
                                    include __DIR__ . '/../partials/time-lock-check.php';
                                    include __DIR__ . '/../partials/lecture-hero.php';
                                }; ?>
                                <?php foreach ($module->activities->where('activity_type', 'TUTORIAL') as $activity) {
                                    include __DIR__ . '/../partials/time-lock-check.php';
                                    include __DIR__ . '/../partials/tutorial-hero.php';
                                }; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <div class="module-section">
                            <div class="module-heading">
                                <div class="module-logo">
                                    <img class="svg" src="/icons/practice.svg" width="50em" height="auto" />
                                </div>
                                <div class="heading-context">
                                    <h5>SKILL-HONING TUTORIALS</h5>
                                    <p>Learn how to solve problems and hone your skills in these tutorials:</p>
                                </div>
                            </div>
                            <div class="module-divider">
                                <hr>
                            </div>
                            <div class="module-content">
                                <?php foreach ($module->activities->where('activity_type', 'QUIZ') as $activity): {
                                        if ($activity->quiz->quiz_type_id == 2): {
                                                include __DIR__ . '/../partials/time-lock-check.php';
                                                include __DIR__ . '/../partials/quiz-practice-hero.php';
                                            }
                                        endif;
                                    };
                                endforeach; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <div class="module-section">
                            <div class="module-heading">
                                <div class="module-logo">
                                    <img class="svg" src="/icons/short-quiz.svg" width="50em" height="auto" />
                                </div>
                                <div class="heading-context">
                                    <h5>SHORT QUIZZES</h5>
                                    <p>Test your skills. You can do infinite attempts to keep honing your skills. You can infinitely retake these quizzes to increase your scores.</p>
                                </div>
                            </div>
                            <div class="module-divider">
                                <hr>
                            </div>
                            <div class="module-content">
                                <?php foreach ($module->activities->where('activity_type', 'QUIZ') as $activity): {
                                        if ($activity->quiz->quiz_type_id == 1): {
                                                include __DIR__ . '/../partials/time-lock-check.php';
                                                include __DIR__ . '/../partials/quiz-short-hero.php';
                                            };
                                        endif;
                                    };
                                endforeach; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
                                */ ?>
        </div>
        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
        </div>
    </div>
    <?php include __DIR__ . '/../partials/footer.php';  ?>

</body>

</html>