<?php $title = "Add New Long Quiz";
include __DIR__ . '/../partials/head.php'; ?>
<style>

    .tbl {
        width: 100%;
        border-collapse: collapse;
    }

    td {
        padding: 1rem;
        border:1px solid #ddd;
    }

    .module {
        padding-left: 2rem;
        font-weight: bold;
    }

    .checkbox {
        padding: 0.1rem;
        text-align: center;
    }

    input[type="checkbox"] {
        margin: 0;
    }

</style>
</head>

<body>

    <?php
    include __DIR__ . '/../partials/nav-teach.php';
    ?>
    <div class="screen">
        <div class="spacing main">
            <form method="POST" action="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>/store-longquiz" enctype="multipart/form-data">
                <?= csrf_field(); ?>
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
                            <h6><a href="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>"><?= $course->course_name ?></a></h6>
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

        <!-- templates (hidden) ---------------------------------------------->
        <template id="q-template">
            <div class="q-block">
                <div class="q-remove crud-button-delete">🗑 Remove Question</div>
                <div class="form-box">
                    <div class="form-label"><label>Question Text:</label></div>
                    <div class="form-input"><textarea data-repl="text" name="questions[__i__][text]" required></textarea></div>
                </div>

                <div class="form-box">
                    <div class="form-label"><label>Image (optional):</label></div>
                    <div class="form-input"><input type="file" data-repl name="questions[__i__][image]" accept="image/*"></div>
                </div>

                <div class="opt-wrap">
                    <!-- options will appear here -->
                </div>
                <button type="button" class="edit opt-add">+ Add Option</button>
            </div>
        </template>

        <template id="opt-template">
            <div class="opt-row">
                <input type="radio" data-repl name="questions[__i__][correct]" value="__o__">
                <input type="text" data-repl name="questions[__i__][options][__o__]"
                    placeholder="Option text" required>
            </div>
        </template>


        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
        </div>
    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>