<?php
$title = $screening->screening_name;
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

    $seconds = $screening->time_limit;
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
                        <h6>(Screening) <?= $screening->screening_name ?></a></h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-gold">

                <div class="content padding">
                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/screener.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4><?= $screening->screening_name ?></h4>
                                <h6>Screening Exam</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-container screening-exam">
                <div class="content flex-column">

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>QUESTIONS: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= $screening->number_of_questions ?></p>
                        </div>
                    </div>

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>TOTAL ATTEMPTS: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= '3' ?></p>
                        </div>
                    </div>

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>TIME LIMIT: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= $screening->time_limit / 60 ?> minutes</p>
                        </div>
                    </div>

                    <hr class="divider-hr">

                    <p class="description"><b>INSTUCTIONS:</b><br><?= nl2br(htmlspecialchars($screening->screening_instructions)) ?></p>


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
                    <?php

                    /* ---------- iterate Concept → Topic → Question --------------------------- */
                    $qNum = 1;
                    foreach ($screening->concepts as $concept): ?>
                        <h5 style="margin:.6rem 0 .3rem"><?= e($concept->concept_name) ?></h5>

                        <?php foreach ($concept->topics as $topic): ?>
                            <h6 style="margin:.35rem 0 .25rem;padding-left:.4rem">
                                &nbsp;&nbsp;[<?= e($topic->topic_name)?>]
                            </h6>

                            <?php foreach ($topic->questions as $q): ?>
                                <div class="question-card" style="margin-left:1.2rem">
                                    <b>Q<?= $qNum++ ?>.</b> <?= e($q->question_text) ?>

                                    <?php if (!empty($q->image?->image)): ?>
                                        <?php
                                        $blob     = $q->image->image;
                                        $mimeType = getMimeTypeFromBlob($blob);
                                        $imgURL   = "data:$mimeType;base64," . base64_encode($blob);
                                        ?>
                                        <br><img src="<?= $imgURL ?>" style="max-width:280px;margin:.4rem 0">
                                    <?php endif; ?>

                                    <?php foreach ($q->options as $opt): ?>
                                        <?php $isCorrect = $opt->is_correct == 1; ?>
                                        <div style="
                                            padding:.35rem .6rem;margin:.2rem 0;
                                            border:1px solid #ccc;border-radius:.3rem;
                                            background:<?= $isCorrect ? '#eaf8e3' : '#fff' ?>;
                                            color:<?= $isCorrect ? '#256029' : '#333' ?>;">
                                            <?= e($opt->option_text) ?>
                                            <?php if ($isCorrect): ?>
                                                <span style="font-size:.75rem;font-weight:700;margin-left:.4rem">
                                                    &#10004;
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <hr class="divider-hr">
                            <?php endforeach; /* questions */ ?>
                        <?php endforeach; /* topics */ ?>
                    <?php endforeach; /* concepts */ ?>
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