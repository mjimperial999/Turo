<?php
$title = "Edit $longquiz->long_quiz_name";
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
    /* ---------- helper that puts a fresh option row ------------------------ */
    function addOption(wrap, qI, optText = '', isCorrect = false) {
        const oCnt = wrap.children.length; // 0-based
        const t = document.getElementById('opt-template').content.cloneNode(true);

        t.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace('__i__', qI).replace('__o__', oCnt);

            if (el.dataset.repl === 'oid') el.value = ''; // new row
            if (el.type === 'radio') {
                el.value = oCnt;
                if (isCorrect) el.checked = true;
            }
            if (el.type === 'text') el.value = optText;
        });

        wrap.appendChild(t);
    }

    /* ---------- helper that adds an empty question ------------------------- */
    let qIdx = <?= count($longquiz->longquizquestions) ?>; // start after last

    function addQuestion(prefill = null) {
        const t = document.getElementById('q-template').content.cloneNode(true).firstElementChild;
        t.dataset.qi = qIdx; // remember its idx

        /* substitute __i__ placeholders */
        t.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace('__i__', qIdx);
            if (prefill) {
                if (el.dataset.repl === 'text') el.value = prefill.text;
            }
        });

        /* 🗑 remove */
        t.querySelector('.q-remove').onclick = e => {
            if (document.querySelectorAll('.q-block').length > 1)
                e.currentTarget.closest('.q-block').remove();
        };

        /* ➕ option ––––– ***FIXED: use block.dataset.qi, not global qIdx*** */
        t.querySelector('.opt-add').onclick = e => {
            const block = e.currentTarget.closest('.q-block');
            const wrap = block.querySelector('.opt-wrap');
            addOption(wrap, parseInt(block.dataset.qi, 10));
        };

        /* at least one blank option */
        addOption(t.querySelector('.opt-wrap'), qIdx);

        document.getElementById('question-list').appendChild(t);
        qIdx++;
    }
</script>
</head>

<body>

    <?php
    include __DIR__ . '/../partials/nav-teach.php';
    ?>
    
    <div class="screen">
        <div class="spacing main">
            <form method="POST" enctype="multipart/form-data">
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
                            <h6>Edit <?= $longquiz->long_quiz_name ?></h6>
                            <div class="line active"></div>
                        </div>
                    </div>
                </div>
                <br>

                <div class="content-container">

                    <div class="content padding box-gray">

                        <div class="header">
                            <div class="text title">
                                <h4> Edit Long Quiz: <?= $longquiz->long_quiz_name ?> </h4>
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
                                <div class="form-input"><input type="text" name="long_quiz_name" value="<?= htmlspecialchars($longquiz->long_quiz_name) ?>" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Instructions:</label></div>
                                <div class="form-input"><textarea name="long_quiz_instructions"><?= htmlspecialchars($longquiz->long_quiz_instructions) ?></textarea></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Number of Attempts:</label></div>
                                <div class="form-input"><input type="number" name="number_of_attempts" min="1" value="<?= htmlspecialchars($longquiz->number_of_attempts) ?>" required></div>
                            </div>

                            
                            <div class="form-box">
                                <div class="form-label"><label>Number of Questions:</label></div>
                                <div class="form-input"><input type="number" name="number_of_questions" min="1" value="<?= htmlspecialchars($longquiz->number_of_questions) ?>" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Time Limit (minutes):</label></div>
                                <div class="form-input"><input type="number" name="time_limit_minutes" min="1" value="<?= $longquiz->time_limit / 60 ?>" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Unlock Date:</label></div>
                                <div class="form-input"><input type="datetime-local" name="unlock_date" value="<?= $longquiz->unlock_date ?>" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label>Deadline:</label></div>
                                <div class="form-input"><input type="datetime-local" name="deadline_date" value="<?= $longquiz->deadline_date ?>" required></div>
                            </div>

                            <div class="form-box">
                                <div class="form-label checkbox"><label>Show Answers After Submission:</label></div>
                                <div class="form-input"><input type="checkbox" name="has_answers_shown" value="1" <?= $longquiz->has_answers_shown ? 'checked' : '' ?>></div>
                            </div>

                        </div>

                    </div>

                </div>
                <div class="content-container box-page">
                    <div class="content padding box-page">
                        <div class="header">
                            <h5>Questions</h5>
                        </div>
                        <div id="question-list">
                            <?php foreach ($longquiz->longquizquestions as $qi => $q): ?>
                                <div class="q-block" data-qi="<?= $qi ?>">
                                    <div class="q-remove crud-button-delete">🗑 Remove Question</div>

                                    <!-- hidden id to retain the record -->
                                    <input type="hidden" name="questions[<?= $qi ?>][qid]" value="<?= $q->long_quiz_question_id ?>">

                                    <!-- text / score -->
                                    <div class="form-box">
                                        <div class="form-label"><label>Question Text:</label></div>
                                        <div class="form-input"><textarea name="questions[<?= $qi ?>][text]" required><?= htmlspecialchars($q->question_text) ?></textarea></div>
                                    </div>

                                    <!-- replace image -->
                                    <div class="form-box">
                                        <div class="form-label"><label>Replace Image (optional):</label></div>
                                        <div class="form-input"><input type="file" name="questions[<?= $qi ?>][image]" accept="image/*"></div>
                                    </div>

                                    <!-- options -->
                                    <div class="opt-wrap">
                                        <?php foreach ($q->longquizoptions as $oi => $opt): ?>
                                            <div class="opt-row">
                                                <!-- keep option id -->
                                                <input type="hidden"
                                                    name="questions[<?= $qi ?>][options][<?= $oi ?>][oid]"
                                                    value="<?= $opt->long_quiz_option_id ?>" data-repl="oid">

                                                <input type="radio"
                                                    name="questions[<?= $qi ?>][correct]"
                                                    value="<?= $oi ?>" <?= $opt->is_correct ? 'checked' : '' ?>>

                                                <input type="text"
                                                    name="questions[<?= $qi ?>][options][<?= $oi ?>][text]"
                                                    value="<?= htmlspecialchars($opt->option_text) ?>" required>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <button type="button" class="edit opt-add"
                                        onclick="addOption(this.previousElementSibling,<?= $qi ?>)">
                                        + Add Option
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="edit" onclick="addQuestion()">+ Add Question</button>
                    </div>
                </div>
                <br>

                <div class="content-container">
                    <div class="content">
                        <div class="form-button">
                            <button class="edit" onclick="return confirm('Update long quiz?');">Update Long Quiz</button>
                        </div>
                    </div>
                </div>

            </form>

            <form method="POST" action="/admin-panel/edit-content/course/<?= $course->course_id ?>/longquiz/<?= $longquiz->long_quiz_id ?>/delete"
                onsubmit="return confirm('Really delete this long quiz?');">
                <?= csrf_field(); ?>
                <div class="content-container">
                    <div class="content">
                        <div class="form-button delete">
                            <button class="delete" onclick="return confirm('Delete this long quiz?');">Delete Long Quiz</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>

        <!-- templates (hidden) ---------------------------------------------->
        <template id="q-template">
            <div class="q-block">
                <div class="q-remove crud-button-delete">🗑 Remove Question</div>

                <input type="hidden" data-repl name="questions[__i__][qid]" value="">

                <div class="form-box">
                    <div class="form-label"><label>Question Text:</label></div>
                    <div class="form-input"><textarea data-repl="text" name="questions[__i__][text]" required></textarea></div>
                </div>

                <div class="form-box">
                    <div class="form-label"><label>Image (optional):</label></div>
                    <div class="form-input"><input type="file" data-repl name="questions[__i__][image]" accept="image/*"></div>
                </div>

                <div class="opt-wrap"></div>
                <button type="button" class="edit opt-add">+ Add Option</button>
            </div>
        </template>

        <template id="opt-template">
            <div class="opt-row">
                <input type="hidden" data-repl="oid" name="questions[__i__][options][__o__][oid]" value="">
                <input type="radio" data-repl name="questions[__i__][correct]" value="__o__">
                <input type="text" data-repl name="questions[__i__][options][__o__][text]"
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