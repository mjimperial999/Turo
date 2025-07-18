<?php
$title = $course->course_name;
include __DIR__ . '/../partials/head.php'; ?>
<style>
    .module-display-flex-box {
        width: 100%;
        margin-bottom: 1rem;
        height: auto;

        white-space: nowrap;
        display: flex;
        flex-direction: row;

        overflow-x: auto;
        scroll-behavior: smooth;

        gap: 0 1.5vw;
    }

    .module-menu {
        margin: 0;
        border-radius: 0.4rem;
        width: 14rem;
        height: 5rem;
        font-size: 30px;
        text-align: center;
        box-shadow: 0rem 0rem 6rem -1rem rgba(0, 0, 0, 0.8) inset, 2rem -6rem 3rem -2rem rgba(0, 0, 0, 0.4) inset;
        filter: drop-shadow(0 0.2rem 0.25rem rgba(0, 0, 0, 0.2));
        cursor: pointer;
        background-size: cover;
        background-position: center;

        display: flex;
        flex-direction: column;

        transition: all 0.3s ease 0s;
    }

    .module-menu:hover {
        filter: drop-shadow(0 0.2rem 0.4rem rgba(0, 0, 0, 0.5));
        transform: translate(0, -0.15rem);
        transition: all 0.3s ease 0s;
    }

    table.std {
        width: 100%;
        border-collapse: collapse;
        font-size: .9rem
    }

    table.std th,
    table.std td {
        border: 1px solid #ddd;
        padding: 0.05rem 0.5rem;
        text-align: left
    }

    .std-img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-size: cover;
        background-position: center;
        margin-right: .4rem
    }
</style>
</head>

<body>
    <?php
    $title = $course->course_name;
    include __DIR__ . '/../partials/nav-teach.php';
    ?>

    <div class="screen">
        <div class="spacing main">
            <div class="content-container box-page">
                <div class="content">

                    <div class="mini-navigation">
                        <div class="text title">
                            <h6><a href="/teachers-panel">Courses</a></h6>
                            <div class="line"></div>
                        </div>
                        <div class="divider">
                            <h6> > </h6>
                        </div>
                        <div class="text title">
                            <h6><?= $course->course_name ?> (Section: <?= strtoupper($section->section_name) ?>)</h6>
                            <div class="line active"></div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="content-container">
                <div class="content padding heading box-gray crud">

                    <div class="header">
                        <div class="text title">
                            <h5> Modules </h5>
                        </div>
                    </div>

                    <div class="crud-header">
                        <form action="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>/create-module" method="GET">
                            <button type="submit" class="crud-button-add">
                                New Module <img src="/icons/new-black.svg" width="25em" height="25em" />
                            </button>
                        </form>
                    </div>

                </div>
                <div class="content padding box-page">
                    <div class="flex-area">
                        <?php if ($course->modules->isEmpty()): ?>
                            <div class="no-items">
                                <img class="svg" src="/icons/nothing.svg" width="50em" height="auto" />
                                No modules available for this course.
                            </div>

                        <?php else: foreach ($course->modules as $module):
                                include __DIR__ . '/../partials/module-hero.php';
                            endforeach;
                        endif; ?>

                    </div>
                </div>
            </div>

            <div class="content-container">
                <div class="content padding heading box-gray crud">

                    <div class="header">
                        <div class="text title">
                            <h5> Long Quizzes </h5>
                        </div>
                    </div>

                    <div class="crud-header">
                        <form action="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>/create-longquiz" method="GET">
                            <button type="submit" class="crud-button-add">
                                New Long Quiz <img src="/icons/new-black.svg" width="25em" height="25em" />
                            </button>
                        </form>
                    </div>

                </div>
                <div class="content padding box-page">
                    <div class="quiz-flex-area">
                        <?php if ($course->longquizzes->isEmpty()): ?>
                            <div class="no-items">
                                <img class="svg" src="/icons/nothing.svg" width="50em" height="auto" />
                                No long quizzes available for this course.
                            </div>

                        <?php else: foreach ($course->longquizzes as $longquiz):
                                include __DIR__ . '/../partials/time-lock-check-modules.php';
                                include __DIR__ . '/../partials/quiz-long-hero.php';

                            endforeach;
                        endif; ?>

                    </div>
                </div>

            </div>


            <div class="content-container box-page">
                <div class="content padding heading box-gray crud">

                    <div class="header">
                        <div class="text title">
                            <h5> Screening Exams </h5>
                        </div>
                    </div>

                    <div class="crud-header">
                        <form action="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>/create-screening" method="GET">
                            <button type="submit" class="crud-button-add">
                                New Screener <img src="/icons/new-black.svg" width="25em" height="25em" />
                            </button>
                        </form>
                    </div>

                </div>

                <div class="content padding">

                    <div class="flex-box">
                        <?php if ($course->screenings->isEmpty()): ?>
                            <div class="no-items">
                                <img class="svg" src="/icons/nothing.svg" width="50em" height="auto" />
                                No screening exams available for this course.
                            </div>

                        <?php else: foreach ($course->screenings as $screening):
                                $blobData = $screening->image->image ?? null;

                                if (!$blobData) {
                                    $backgroundImage = "/uploads/course/math.jpg";
                                } else {
                                    $mimeType = getMimeTypeFromBlob($blobData);
                                    $base64Image = base64_encode($blobData);
                                    $backgroundImage = "data:$mimeType;base64,$base64Image";
                                }

                                include __DIR__ . '/../partials/screening-hero.php';

                            endforeach;
                        endif; ?>

                    </div>
                </div>
            </div>

            <div class="content-container box-page">
                <div class="content padding heading box-gray">

                    <div class="header">
                        <div class="text title">
                            <h5> Students – <?= e($section->section_name) ?></h5>
                        </div>
                    </div>

                </div>

                <div class="content padding">
                    <table class="std">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Points</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $s):
                                $u  = $s->user;
                                if (empty($u->image?->image)) {
                                    $avatar = "/icons/no-img.jpg";
                                } else {
                                    $blob = $u->image->image;
                                    $avatar = "data:" . getMimeTypeFromBlob($blob) . ';base64,' . base64_encode($blob);
                                } ?>
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center">
                                            <div class="std-img" style="background-image:url('<?= $avatar ?>')"></div>
                                            <?= e($s->user->last_name . ', ' . $s->user->first_name) ?>
                                        </div>
                                    </td>
                                    <td><?= $s->total_points ?? 0 ?></td>
                                    <td>
                                        <form action="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>/student/<?= $s->user_id ?>/performance" method="GET">
                                            <button class="edit">
                                                View Performance
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

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