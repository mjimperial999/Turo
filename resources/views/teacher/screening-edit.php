<?php $title = "Edit $screening->screening_name";
include __DIR__ . '/../partials/head.php';

$initialData = [];
foreach ($screening->concepts as $c) {
    $cArr = [
        'concept_id'   => $c->screening_concept_id,
        'concept_name' => $c->concept_name,
        'topics'       => [],
    ];
    foreach ($c->topics as $t) {
        $tArr = [
            'topic_id'   => $t->screening_topic_id,
            'topic_name' => $t->topic_name,
            'questions'  => [],
        ];
        foreach ($t->questions as $q) {
            $qArr = [
                'question_id' => $q->screening_question_id,
                'text'        => $q->question_text,
                'options'     => [],
                'option_ids'  => [],
                'correct'     => null,
            ];
            foreach ($q->options as $idx => $opt) {
                $qArr['options'][]    = $opt->option_text;
                $qArr['option_ids'][] = $opt->screening_option_id;
                if ($opt->is_correct) $qArr['correct'] = $idx;
            }
            $tArr['questions'][] = $qArr;
        }
        $cArr['topics'][] = $tArr;
    }
    $initialData[] = $cArr;
} ?>
<style>
    /* --- basic layout tweaks --- */
    .concept-block,
    .topic-block,
    .q-block {
        border: 1px dashed #bbb;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .concept-block {
        background: #f5fbff;
    }

    .topic-block {
        background: #fcfdf2;
        margin-left: 1rem;
    }

    .q-block {
        background: #fff;
        margin-left: 2rem;
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
    /* ============================================================
   Dynamic helpers   (addX  +  populateX)
   ============================================================ */
    let cIndex = 0; // running concept counter

    /* ---------- option ---------- */
    function addOption(wrap, cI, tI, qI, prefill = null) {
        const oCnt = wrap.children.length;
        const tpl = document.getElementById('opt-template').content.cloneNode(true);

        tpl.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace(/__c__/g, cI)
                .replace(/__t__/g, tI)
                .replace(/__q__/g, qI)
                .replace(/__o__/g, oCnt);
            if (el.type === 'radio') el.value = oCnt;
            if (prefill) {
                if (el.type === 'text') el.value = prefill.text;
                if (prefill.isCorrect && el.type === 'radio') el.checked = true;
            }
        });

        /* keep option id (hidden) */
        const hid = document.createElement('input');
        hid.type = 'hidden';
        hid.name = `concepts[${cI}][topics][${tI}][questions][${qI}][option_ids][${oCnt}]`;
        hid.value = prefill?.id ?? '';
        tpl.firstElementChild.appendChild(hid);

        wrap.appendChild(tpl);
    }

    /* ---------- question ---------- */
    function addQuestion(topicBlock, cI, tI, prefill = null) {
        const qWrap = topicBlock.querySelector('.question-list');
        const qI = qWrap.children.length;
        const tpl = document.getElementById('q-template').content.cloneNode(true).firstElementChild;

        tpl.dataset.q = qI;
        tpl.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace(/__c__/g, cI).replace(/__t__/g, tI).replace(/__q__/g, qI);
            if (prefill && el.name.endsWith('[text]')) el.value = prefill.text;
        });

        /* hidden question_id (empty for new rows) */
        tpl.insertAdjacentHTML('afterbegin',
            `<input type="hidden" name="concepts[${cI}][topics][${tI}][questions][${qI}][question_id]"
                value="${prefill?.question_id ?? ''}">`);

        /* delete button */
        tpl.querySelector('.q-remove').onclick =
            e => e.currentTarget.closest('.q-block').remove();

        /* + Option */
        tpl.querySelector('.opt-add').onclick =
            () => addOption(tpl.querySelector('.opt-wrap'), cI, tI, qI);

        /* seed options */
        if (prefill) {
            prefill.options.forEach((txt, idx) => {
                addOption(
                    tpl.querySelector('.opt-wrap'),
                    cI, tI, qI, {
                        text: txt,
                        id: prefill.option_ids[idx],
                        isCorrect: (idx === prefill.correct)
                    }
                );
            });
        } else addOption(tpl.querySelector('.opt-wrap'), cI, tI, qI);

        qWrap.appendChild(tpl);
    }

    /* ---------- topic ---------- */
    function addTopic(conceptBlock, cI, prefill = null) {
        const tWrap = conceptBlock.querySelector('.topic-list');
        const tI = tWrap.children.length;
        const tpl = document.getElementById('topic-template').content.cloneNode(true).firstElementChild;

        tpl.dataset.t = tI;
        tpl.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace(/__c__/g, cI).replace(/__t__/g, tI);
            if (prefill && el.name.endsWith('[topic_name]')) el.value = prefill.topic_name;
        });

        tpl.insertAdjacentHTML('afterbegin',
            `<input type="hidden" name="concepts[${cI}][topics][${tI}][topic_id]"
                value="${prefill?.topic_id ?? ''}">`);

        tpl.querySelector('.topic-remove').onclick =
            e => e.currentTarget.closest('.topic-block').remove();
        tpl.querySelector('.q-add').onclick =
            () => addQuestion(tpl, cI, tI);

        if (prefill) {
            prefill.questions.forEach(q => addQuestion(tpl, cI, tI, q));
        } else addQuestion(tpl, cI, tI);

        tWrap.appendChild(tpl);
    }

    /* ---------- concept ---------- */
    function addConcept(prefill = null) {
        const cWrap = document.getElementById('concept-list');
        const tpl = document.getElementById('concept-template').content.cloneNode(true).firstElementChild;
        const cI = cIndex++;

        tpl.dataset.c = cI;
        tpl.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace(/__c__/g, cI);
            if (prefill && el.name.endsWith('[concept_name]')) el.value = prefill.concept_name;
        });

        tpl.insertAdjacentHTML('afterbegin',
            `<input type="hidden" name="concepts[${cI}][concept_id]"
                value="${prefill?.concept_id ?? ''}">`);

        tpl.querySelector('.concept-remove').onclick =
            e => e.currentTarget.closest('.concept-block').remove();
        tpl.querySelector('.topic-add').onclick =
            () => addTopic(tpl, cI);

        if (prefill) {
            prefill.topics.forEach(t => addTopic(tpl, cI, t));
        } else addTopic(tpl, cI);

        cWrap.appendChild(tpl);
    }

    /* ---------- bootstrap on load ---------- */
    window.addEventListener('DOMContentLoaded', () => {
        const data = <?= json_encode(
                            $initialData,
                            JSON_UNESCAPED_UNICODE | JSON_HEX_APOS
                        ) ?>;
        if (data.length) {
            data.forEach(addConcept);
        } else addConcept(); // fallback (should not happen)
    });
</script>
</head>

<body>
    <?php include __DIR__ . '/../partials/nav-teach.php'; ?>
    <div class="screen">
        <div class="spacing main">
            <form method="POST" action="/teachers-panel/course/<?= $course->course_id ?>/store-screening" enctype="multipart/form-data">
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
                            <h6><a href="/teachers-panel/course/<?= $course->course_id ?>"><?= $course->course_name ?></a></h6>
                            <div class="line"></div>
                        </div>
                        <div class="divider">
                            <h6> > </h6>
                        </div>
                        <div class="text title">
                            <h6>Add New Screening Exam</h6>
                            <div class="line active"></div>
                        </div>
                    </div>
                </div>
                <br>

                <div class="content-container">

                    <div class="content padding box-gray">

                        <div class="header">
                            <div class="text title">
                                <h4> Add A New Screening Exam </h4>
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
                                <div class="form-input"><input type="text" name="screening_name" value="<?= htmlspecialchars($screening->screening_name) ?>" required></div>
                            </div>
                            <div class="form-box">
                                <div class="form-label"><label>Instructions:</label></div>
                                <div class="form-input"><textarea name="screening_instructions"><?= htmlspecialchars($screening->screening_instructions) ?></textarea></div>
                            </div>
                            <div class="form-box">
                                <div class="form-label"><label>Number of Questions:</label></div>
                                <div class="form-input"><input type="number" name="number_of_questions" min="1" value="<?= htmlspecialchars($screening->number_of_questions) ?>" required></div>
                            </div>
                            <div class="form-box">
                                <div class="form-label"><label>Time Limit (minutes):</label></div>
                                <div class="form-input"><input type="number" name="time_limit_minutes" min="1" value="<?= htmlspecialchars($screening->time_limit / 60) ?>" required></div>
                            </div>
                            <div class="form-box checkbox">
                                <div class="form-label checkbox"><label>Show Answers After Submission:</label></div>
                                <div class="form-input"><input type="checkbox" name="has_answers_shown" value="1" <?= $screening->has_answers_shown ? 'checked' : '' ?>></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- hierarchical builder -->
                <div class="content-container box-page">
                    <div class="content padding box-page">
                        <div class="header">
                            <h5>Concepts / Topics / Questions</h5>
                        </div>
                        <div id="concept-list"></div>
                        <button type="button" class="edit" onclick="addConcept()">+ Add Concept</button>
                    </div>
                </div>
                <br>

                <div class="content-container">
                    <div class="content">
                        <div class="form-button">
                            <button class="edit" onclick="return confirm('Edit this screening exam?');">Save Changes</button>
                        </div>
                    </div>
                </div>

            </form>

            <form method="POST" action="/teachers-panel/course/<?= $course->course_id ?>/screening/<?= $screening->screening_id ?>/delete"
                onsubmit="return confirm('Really delete this screening exam?');">
                <?= csrf_field(); ?>
                <div class="content-container">
                    <div class="content">
                        <div class="form-button delete">
                            <button class="delete" onclick="return confirm('Delete this short quiz?');">Delete Short Quiz</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
        <div class="spacing side"><?php include __DIR__ . '/../partials/right-side-notifications.php'; ?></div>
    </div>
    <!-- 🔻 templates -->
    <template id="concept-template">
        <div class="concept-block">
            <div class="concept-remove crud-button-delete">🗑 Remove Concept</div>
            <div class="form-box">
                <div class="form-label"><label>Concept Name:</label></div>
                <div class="form-input"><input type="text" data-repl name="concepts[__c__][concept_name]" required></div>
            </div>
            <div class="topic-list"></div>
            <button type="button" class="edit topic-add">+ Add Topic</button>
        </div>
    </template>
    <template id="topic-template">
        <div class="topic-block">
            <div class="topic-remove crud-button-delete">🗑 Remove Topic</div>
            <div class="form-box">
                <div class="form-label"><label>Topic Name:</label></div>
                <div class="form-input"><input type="text" data-repl name="concepts[__c__][topics][__t__][topic_name]" required></div>
            </div>
            <div class="question-list"></div>
            <button type="button" class="edit q-add">+ Add Question</button>
        </div>
    </template>
    <template id="q-template">
        <div class="q-block">
            <div class="q-remove crud-button-delete">🗑 Remove Question</div>
            <div class="form-box">
                <div class="form-label"><label>Question Text:</label></div>
                <div class="form-input"><textarea data-repl name="concepts[__c__][topics][__t__][questions][__q__][text]" required></textarea></div>
            </div>
            <div class="form-box">
                <div class="form-label"><label>Image (optional):</label></div>
                <div class="form-input"><input type="file" data-repl name="concepts[__c__][topics][__t__][questions][__q__][image]" accept="image/*"></div>
            </div>
            <div class="opt-wrap"></div>
            <button type="button" class="edit opt-add">+ Add Option</button>
        </div>
    </template>
    <template id="opt-template">
        <div class="opt-row">
            <input type="radio" data-repl name="concepts[__c__][topics][__t__][questions][__q__][correct]" value="__o__">
            <input type="text" data-repl name="concepts[__c__][topics][__t__][questions][__q__][options][__o__]" placeholder="Option text" required>
        </div>
    </template>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>