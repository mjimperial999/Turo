<?php $title = "Add New Practice Quiz";
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
<script>
    let qIndex = 0;

    function addOption(wrap, qI) {
        const optCount = wrap.children.length; // 0-based
        const tpl = document.getElementById('opt-template')
            .content.cloneNode(true);

        tpl.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace('__i__', qI).replace('__o__', optCount);
            if (el.type === 'radio') el.value = optCount; // int value
        });
        wrap.appendChild(tpl);
    }

    function addQuestion(prefill = null) {
        const tpl = document.getElementById('q-template')
            .content.cloneNode(true).firstElementChild;

        tpl.dataset.qi = qIndex; // store idx
        tpl.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace('__i__', qIndex);
            if (prefill) {
                if (el.dataset.repl === 'text') el.value = prefill.text ?? '';
            }
        });

        /* remove button */
        tpl.querySelector('.q-remove').onclick = e => {
            const total = document.querySelectorAll('.q-block').length;
            if (total > 1) e.currentTarget.closest('.q-block').remove();
        };

        /* “+ Option” button */
        tpl.querySelector('.opt-add').onclick = e => {
            const block = e.currentTarget.closest('.q-block');
            addOption(block.querySelector('.opt-wrap'), block.dataset.qi);
        };

        /* put an initial option row */
        addOption(tpl.querySelector('.opt-wrap'), qIndex);

        document.getElementById('question-list').appendChild(tpl);
        qIndex++;
    }

    document.addEventListener('DOMContentLoaded', () => addQuestion()); // first block
</script>
</head>

<body>

    <?php
    include __DIR__ . '/../partials/nav-teach.php';
    ?>
    <div class="screen">
        <div class="spacing main">
            <form method="POST" action="/teachers-panel/course/<?= $course->course_id ?>/module/<?= $module->module_id ?>/store-practicequiz" enctype="multipart/form-data">
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
                            <h6><a href="/teachers-panel/course/<?= $course->course_id ?>">
                                    <?= $course->course_name ?></a></h6>
                            <div class="line"></div>
                        </div>
                        <div class="divider">
                            <h6> > </h6>
                        </div>
                        <div class="text title">
                            <h6><a href="/teachers-panel/course/<?= $course->course_id ?>/module/<?= $module->module_id ?>">
                                    <?= $module->module_name ?></a></h6>
                            <div class="line"></div>
                        </div>
                        <div class="divider">
                            <h6> > </h6>
                        </div>
                        <div class="text title">
                            <h6>Create Practice Quiz</h6>
                            <div class="line active"></div>
                        </div>
                    </div>
                </div><br>
                <br>

                <div class="content-container">

                    <div class="content padding box-gray">

                        <div class="header">
                            <div class="text title">
                                <h4> Add A New Practice Quiz </h4>
                            </div>
                        </div>

                    </div>
                </div>
                <br>

                <div class="content-container box-page">
                    <div class="content">
                        <?php if ($errors->any()): ?>
                            <div class="alert alert-danger alert-message padding">
                                <ul style="margin:0; padding-left:1.2rem; color:#000000;">
                                    <?php foreach ($errors->all() as $msg): ?>
                                        <li style="color:#000000;"><?= htmlspecialchars($msg) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger alert-message padding" role="alert">
                                <?= session('error') ?>
                            </div>
                        <?php elseif (session()->has('success')): ?>
                            <div class="alert alert-success alert-message padding" role="alert">
                                <?= session('success') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="content-container box-page">
                    <div class="content padding box-page">
                        <div class="content flex-column">
                            <div class="form-box">
                                <div class="form-label"><label>Name:</label></div>
                                <div class="form-input"><input type="text" name="quiz_name" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Instructions:</label></div>
                                <div class="form-input"><textarea name="quiz_instructions"></textarea></div>
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
                            <h5>Questions</h5>
                        </div>
                        <div id="question-list"></div>
                        <button type="button" class="edit" onclick="addQuestion()">+ Add Question</button>
                    </div>
                </div>
                <br>

                <div class="content-container">
                    <div class="content">
                        <div class="form-button">
                            <button class="edit" onclick="return confirm('Create a new practice quiz?');">Create Practice Quiz</button>
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