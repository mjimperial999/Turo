<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $module->module_name; ?> | Turo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="/css/styles.css">
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

    use Carbon\Carbon;

    include('partials/navibar.php');
    ?>

    <div class="home-tutor-screen">
        <div class="home-tutor-main">
            <table>
                <tr class="module-title">
                    <th class="table-left-padding"></th>
                    <th class="table-right-padding">
                        <div class="first-th">
                            <h5><b><?php echo $module->module_name ?></b></h5>
                            <div class="return-prev-cont">
                                <?= '<a class="activity-link" href="/home-tutor/course/'. $module->course_id .'/"> 
                                <div class="return-prev">BACK to Course: '. $module->course_name. ' Page</div>
                                        </a>' ?>
                                </div>
                            </div>
                        </div>
                    </th>
                </tr>
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
                                    include('partials/time-lock-check.php');
                                    include('partials/lecture-hero.php');
                                }; ?>
                                <?php foreach ($module->activities->where('activity_type', 'TUTORIAL') as $activity) {
                                    include('partials/time-lock-check.php');
                                    include('partials/tutorial-hero.php');
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
                                                include('partials/time-lock-check.php');
                                                include('partials/quiz-practice-hero.php');
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
                                                include('partials/time-lock-check.php');
                                                include('partials/quiz-short-hero.php');
                                            };
                                        endif;
                                    };
                                endforeach; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

        </div>
        <?php include('partials/right-side-notifications.php'); ?>
    </div>
</body>
</html>