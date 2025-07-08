<?php
$title = "Resources – {$screening->screening_name}";
include __DIR__ . '/../partials/head.php'; ?>
<style>
    .c-block,
    .t-block {
        border: 1px dashed #bbb;
        padding: 1rem;
        margin-bottom: 1rem
    }

    .t-block {
        margin-left: 1.2rem;
        background: #fcfdf2
    }
</style>
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
                            <h6>Edit Learning Resources</h6>
                            <div class="line active"></div>
                        </div>
                    </div>
                </div>
                <br>

                <div class="content-container box-gray">
                    <div class="content padding">
                        <div class="header">
                            <h4>Edit Learning Resources</h4>
                        </div>
                    </div>
                </div><br>

                <?php foreach ($screening->concepts as $c): ?>
                    <?php
                    $cRes = $c->resources->first();                    // concept-level (may be null)
                    $indexC = htmlspecialchars($c->screening_concept_id);
                    ?>
                    <div class="content-container box-page">
                        <div class="content padding c-block">
                            <h5>Concept: <?= htmlspecialchars($c->concept_name) ?></h5>

                            <div class="form-box">
                                <div class="form-label">
                                    <label>Video URL</label>
                                </div>
                                <div class="form-input">
                                    <input type="url" name="concepts[<?= $indexC ?>][video_url]"
                                        value="<?= htmlspecialchars($cRes->video_url ?? '') ?>">
                                </div>
                            </div>
                            <div class="form-box">
                                <div class="form-label">
                                    <label>PDF&nbsp;File</label>
                                </div>
                                <div class="form-input"><input type="file" name="concepts[<?= $indexC ?>][pdf_file]" accept="application/pdf">
                                    <?php if ($cRes && $cRes->pdf_blob): ?>
                                        <small>✓ PDF exists</small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- ── TOPICS ───────────────────────── -->
                            <?php foreach ($c->topics as $t):
                                $tRes = $t->resources->first();
                                $indexT = htmlspecialchars($t->screening_topic_id); ?>
                                <div class="t-block">
                                    <h6>Topic: <?= htmlspecialchars($t->topic_name) ?></h6>

                                    <div class="form-box">
                                        <div class="form-label">
                                            <label>Video URL</label>
                                        </div>
                                        <div class="form-input">
                                            <input type="url"
                                                name="concepts[<?= $indexC ?>][topics][<?= $indexT ?>][video_url]"
                                                value="<?= htmlspecialchars($tRes->video_url ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="form-box">
                                        <div class="form-label">
                                            <label>PDF&nbsp;File</label>
                                        </div>
                                        <div class="form-input">
                                            <input type="file"
                                                name="concepts[<?= $indexC ?>][topics][<?= $indexT ?>][pdf_file]"
                                                accept="application/pdf">
                                            <?php if ($tRes && $tRes->pdf_blob): ?>
                                                <small>✓ PDF exists</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <br>
                <div class="content-container">
                    <div class="content">
                        <div class="form-button">
                            <button class="edit">Save Resources</button>
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