<?php $title = "Edit $activity->activity_name";
include __DIR__ . '/../partials/head.php'; ?>
<style>

</style>
</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav-teach.php';
    ?>

    <div class="screen">
        <div class="spacing main">
            <form method="POST"
                enctype="multipart/form-data">
                <?= csrf_field(); ?>

                <!-- ▸ breadcrumb header ---------------------------------------------------->
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
                            <h6><a href="/admin-panel/edit-content/course/<?= $course->course_id ?>">
                                    <?= $course->course_name ?></a></h6>
                            <div class="line"></div>
                        </div>
                        <div class="divider">
                            <h6> > </h6>
                        </div>
                        <div class="text title">
                            <h6><a href="/admin-panel/edit-content/course/<?= $course->course_id ?>/module/<?= $module->module_id ?>">
                                    <?= $module->module_name ?></a></h6>
                            <div class="line"></div>
                        </div>
                        <div class="divider">
                            <h6> > </h6>
                        </div>
                        <div class="text title">
                            <h6>Edit Lecture <?=$activity->activity_name?></h6>
                            <div class="line active"></div>
                        </div>
                    </div>
                </div><br>

                <!-- ▸ page title ----------------------------------------------------------->
                <div class="content-container">
                    <div class="content padding box-gray">
                        <h4>Edit Lecture: <?=$activity->activity_name?></h4>
                    </div>
                </div><br>

                <!-- ▸ feedback / validation ----------------------------------------------->
                                <div class="content-container box-page">
                    <div class="content">
                        <?php if ($errors->any()): ?>
                            <div class="alert alert-danger alert-message padding">
                                <ul><?php foreach ($errors->all() as $msg): ?><li><?= htmlspecialchars($msg) ?></li><?php endforeach; ?></ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ▸ form body ------------------------------------------------------------>
                <div class="content-container box-page">
                    <div class="content padding main box-page">
                        <div class="content flex-column">

                            <div class="form-box">
                                <div class="form-label"><label for="name">Name:</label></div>
                                <div class="form-input">
                                    <input id="name" type="text" name="activity_name" value="<?= htmlspecialchars($activity->activity_name) ?>" required>
                                </div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label for="desc">Description:</label></div>
                                <div class="form-input">
                                    <textarea id="desc" name="activity_description" required><?= htmlspecialchars($activity->activity_description) ?></textarea>
                                </div>
                            </div>


                            <div class="form-box">
                                <div class="form-label"><label for="name">Unlock Date:</label></div>
                                <div class="form-input">
                                    <input type="datetime-local" name="unlock_date" value="<?= htmlspecialchars($activity->unlock_date) ?>" required>
                                </div>
                            </div>


                            <div class="form-box">
                                <div class="form-label"><label for="pdf">PDF Upload:</label></div>
                                <div class="form-input">
                                    <input id="pdf" type="file" name="pdf" accept="application/pdf">
                                </div>
                            </div>

                        </div>
                    </div>
                </div><br>

                <!-- ▸ submit --------------------------------------------------------------->
                <div class="content-container">
                    <div class="content">
                        <div class="form-button">
                            <button class="edit" onclick="return confirm('Edit changes?');">Save Changes</button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="content-container">
                <div class="content">
                    <form method="POST" action="/admin-panel/edit-content/course/<?= $course->course_id ?>/module/<?= $module->module_id ?>/delete"
                        onsubmit="return confirm('Really delete this lecture?');">
                        <?= csrf_field(); ?>
                        <div class="form-button">
                            <button class="delete">Delete Lecture</button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>

        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
        </div>
    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>