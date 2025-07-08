<?php $title = "Student's List";
include __DIR__ . '/../partials/head.php'; ?>
<style>
    .card {
        border: 1px solid #ddd;
        padding: .8rem;
        border-radius: .5rem;
        margin: .5rem 0
    }
</style>
</style>

</style>
</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav-admin.php';
    ?>

    <div class="screen">

        <div class="spacing whole">
            <div class="content-container box-page">
                <div class="mini-navigation">
                    <div class="text title">
                        <h6><a href="/admin-panel">Back to Menu Page</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>Teacher List</h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-gray">

                <div class="content padding flex-row" style="align-items: center; gap: 1rem">
                    <h6> Add Teacher Entry: </h6>
                    <form action="/admin-panel/teacher-list/add" method="GET">
                        <button class="self-button">Add Manually</button>
                    </form>
                    <form action="/admin-panel/teacher-list/import-csv" method="GET">
                        <button class="self-button">Import CSV</button>
                    </form>
                    <h6> Add Section: </h6>
                    <form action="/admin-panel/teacher-list/add-section" method="GET">
                        <button class="self-button">Add Section</button>
                    </form>

                </div>

            </div>

            <div class="content-container box-page">

                <div class="content padding box-page">

                    <?php foreach ($teachers as $t):
                        $u = $t->user;
                        $img = empty($u->image?->image)
                            ? '/icons/no-img.jpg'
                            : "data:" . getMimeTypeFromBlob($u->image->image) . ";base64," . base64_encode($u->image->image);
                    ?>
                        <div class="card">
                            <img src="<?= $img ?>" style="width:50px;height:50px;border-radius:50%">
                            <b><?= e("$u->last_name, $u->first_name") ?></b>
                            <small>ID: (<?= e($t->user_id) ?>)</small>
                            <hr class="divider-hr">
                            <small>
                                <?php
                                $handled = $t->courseSections
                                    ->filter(fn($cs) => $cs->course && $cs->section)
                                    ->map(fn($cs) => [
                                        'course' => $cs->course->course_name,
                                        'section' => $cs->section->section_name
                                    ]);
                                ?>
                                <?php if ($handled->isEmpty()): ?>
                                    <b>
                                        — No courses yet —
                                    </b>
                                <?php else: ?>
                                    <b>
                                        All Courses
                                    </b><br>
                                    <?php foreach ($handled as $c): ?>
                                        <?= $c['course'] . ' [' . $c['section'] . ']' ?><br>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            </small>
                            <div class="manage-button">
                                <form action="/admin-panel/teacher-info/<?= $t->user_id ?>" method="GET">
                                    <button type="submit" class="edit">
                                        Manage
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?= $teachers->links('pagination::bootstrap-4'); ?>
                </div>

            </div>


            <div class="content-container padding box-gold">
            </div>
        </div>

    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>