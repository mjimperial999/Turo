<?php $title = "Add New Screening Exam";
include __DIR__ . '/../partials/head.php'; ?>
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
    /* ───────────────────────────
   Dynamic field helpers
   ─────────────────────────*/
    let cIndex = 0; // concept idx
    function addOption(wrap, cI, tI, qI) {
        const oCnt = wrap.children.length;
        const tpl = document.getElementById('opt-template').content.cloneNode(true);
        tpl.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace(/__c__/g, cI)
                .replace(/__t__/g, tI)
                .replace(/__q__/g, qI)
                .replace(/__o__/g, oCnt);
            if (el.type === 'radio') el.value = oCnt;
        });
        wrap.appendChild(tpl);
    }

    function addQuestion(topicBlock, cI, tI) {
        const qWrap = topicBlock.querySelector('.question-list');
        const qI = qWrap.children.length;
        const tpl = document.getElementById('q-template').content.cloneNode(true).firstElementChild;
        tpl.dataset.q = qI;
        tpl.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace(/__c__/g, cI)
                .replace(/__t__/g, tI)
                .replace(/__q__/g, qI);
        });
        // remove btn
        tpl.querySelector('.q-remove').onclick = e => e.currentTarget.closest('.q-block').remove();
        // +option btn
        tpl.querySelector('.opt-add').onclick = e => addOption(tpl.querySelector('.opt-wrap'), cI, tI, qI);
        // seed one option
        addOption(tpl.querySelector('.opt-wrap'), cI, tI, qI);
        qWrap.appendChild(tpl);
    }

    function addTopic(conceptBlock, cI) {
        const tWrap = conceptBlock.querySelector('.topic-list');
        const tI = tWrap.children.length;
        const tpl = document.getElementById('topic-template').content.cloneNode(true).firstElementChild;
        tpl.dataset.t = tI;
        tpl.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace(/__c__/g, cI).replace(/__t__/g, tI);
        });
        // remove topic
        tpl.querySelector('.topic-remove').onclick = e => e.currentTarget.closest('.topic-block').remove();
        // +question btn
        tpl.querySelector('.q-add').onclick = e => addQuestion(tpl, cI, tI);
        // seed first question
        addQuestion(tpl, cI, tI);
        tWrap.appendChild(tpl);
    }

    function addConcept() {
        const cWrap = document.getElementById('concept-list');
        const tpl = document.getElementById('concept-template').content.cloneNode(true).firstElementChild;
        const cI = cIndex++;
        tpl.dataset.c = cI;
        tpl.querySelectorAll('[data-repl]').forEach(el => {
            el.name = el.name.replace(/__c__/g, cI);
        });
        // remove concept btn
        tpl.querySelector('.concept-remove').onclick = e => e.currentTarget.closest('.concept-block').remove();
        // +topic btn
        tpl.querySelector('.topic-add').onclick = e => addTopic(tpl, cI);
        // seed first topic+question
        addTopic(tpl, cI);
        cWrap.appendChild(tpl);
    }
    // init
    document.addEventListener('DOMContentLoaded', () => addQuestion()); // first block
</script>
</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav-teach.php';
    ?>

    <div class="screen">
        <div class="spacing main">
            <form method="POST" action="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>/store-screening" enctype="multipart/form-data">
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
                                <div class="form-input"><input type="text" name="screening_name" required></div>
                            </div>
                            <div class="form-box">
                                <div class="form-label"><label>Instructions:</label></div>
                                <div class="form-input"><textarea name="screening_instructions"></textarea></div>
                            </div>
                            <div class="form-box">
                                <div class="form-label"><label>Number of Questions:</label></div>
                                <div class="form-input"><input type="number" name="number_of_questions" min="1" required></div>
                            </div>
                            <div class="form-box">
                                <div class="form-label"><label>Time Limit (minutes):</label></div>
                                <div class="form-input"><input type="number" name="time_limit_minutes" min="1" value="60" required></div>
                            </div>
                            <div class="form-box checkbox">
                                <div class="form-label checkbox"><label>Show Answers After Submission:</label></div>
                                <div class="form-input"><input type="checkbox" name="has_answers_shown" value="1"></div>
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
                    <div class="content"><button class="edit" onclick="return confirm('Create screening exam?');">Create Screening Exam</button></div>
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