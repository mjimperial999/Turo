<?php $title = "Add New Long Quiz";
include __DIR__ . '/../partials/head.php'; ?>
<style>
    /* simple demo styles – tweak/add your own */
    .q-block {
        border: 1px dashed #bbb;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .opt-row {
        display: flex;
        gap: .3rem;
        align-items: center;
        margin: .3rem 0;
    }

    .opt-row input[type=text] {
        flex: 1;
    }
</style>
</head>

<body>

    <?php
    include __DIR__ . '/../partials/nav-teach.php';
    ?>
    <div class="screen">
        <div class="spacing main">
            <form method="POST" action="/admin-panel/edit-content/course/<?= $course->course_id ?>/store-longquiz" enctype="multipart/form-data">
                <?= csrf_field(); ?>
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
                            <h6>Add New Long Quiz</h6>
                            <div class="line active"></div>
                        </div>
                    </div>
                </div>
                <br>

                <div class="content-container">

                    <div class="content padding box-gray">

                        <div class="header">
                            <div class="text title">
                                <h4> Add A New Long Quiz </h4>
                            </div>
                        </div>

                    </div>
                </div>
                <br>

                <div class="content-container box-page">
                    <div class="content">
                        <?php if ($errors->any()): ?>
                            <div class="alert alert-danger alert-message padding">
                                <ul><?php foreach ($errors->all() as $msg): ?><li><?= htmlspecialchars($msg) ?></li><?php endforeach; ?></ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="content-container box-page">
                    <div class="content padding box-page">
                        <div class="content flex-column">
                            <div class="form-box">
                                <div class="form-label"><label>Name:</label></div>
                                <div class="form-input"><input type="text" name="long_quiz_name" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Instructions:</label></div>
                                <div class="form-input"><textarea name="long_quiz_instructions"></textarea></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Number of Attempts:</label></div>
                                <div class="form-input"><input type="number" name="number_of_attempts" min="1" value="10" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Number of Questions:</label></div>
                                <div class="form-input"><input type="number" name="number_of_questions" min="1" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Time Limit (minutes):</label></div>
                                <div class="form-input"><input type="number" name="time_limit_minutes" min="1" value="30" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Unlock Date:</label></div>
                                <div class="form-input"><input type="datetime-local" name="unlock_date" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Deadline:</label></div>
                                <div class="form-input"><input type="datetime-local" name="deadline_date" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label checkbox"><label>Show Answers After Submission:</label></div>
                                <div class="form-input"><input type="checkbox" name="has_answers_shown" value="1"></div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="content-container box-page">
                    <div class="content padding box-page">
                        <div class="header">
                            <h5>Question Banks</h5>
                        </div>
                        <p>Select one or more existing quizzes:</p>

                        <table class="tbl">
                            <tbody>
                                <?php foreach ($quizzes as $moduleName => $group): ?>
                                    <tr>
                                        <td class="module" colspan="2">
                                            <?= e($moduleName) ?>
                                        </td>
                                    </tr>
                                    <?php foreach ($group as $quiz): ?>
                                        <tr>
                                            <td class="checkbox">
                                                <input
                                                    type="checkbox"
                                                    name="source_quizzes[]"
                                                    value="<?= $quiz->activity_id ?>">
                                            </td>
                                            <td class="name">
                                                <?= e($quiz->activity->activity_name) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <br>

                <div class="content-container">
                    <div class="content">
                        <div class="form-button">
                            <button class="edit" onclick="return confirm('Create a new long quiz?');">Create Long Quiz</button>
                        </div>
                    </div>
                </div>

            </form>

        </div>

        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
        </div>
    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>