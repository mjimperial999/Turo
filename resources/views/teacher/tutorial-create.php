<?php $title = "Add New Tutorial";
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
                action="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>/module/<?= $module->module_id ?>/store-tutorial"
                enctype="multipart/form-data">
                <?= csrf_field(); ?>

                <!-- ▸ breadcrumb header ---------------------------------------------------->
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
                            <h6><a href="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>">
                                    <?= $course->course_name ?></a></h6>
                            <div class="line"></div>
                        </div>
                        <div class="divider">
                            <h6> > </h6>
                        </div>
                        <div class="text title">
                            <h6><a href="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>/module/<?= $module->module_id ?>">
                                    <?= $module->module_name ?></a></h6>
                            <div class="line"></div>
                        </div>
                        <div class="divider">
                            <h6> > </h6>
                        </div>
                        <div class="text title">
                            <h6>Add New Video</h6>
                            <div class="line active"></div>
                        </div>
                    </div>
                </div><br>

                <!-- ▸ page title ----------------------------------------------------------->
                <div class="content-container">
                    <div class="content padding box-gray">
                        <h4>Add a Tutorial Video</h4>
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
                        <?php if (session()->has('success')): ?>
                            <div class="alert alert-success alert-message padding"><?= session('success') ?></div>
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
                                    <input id="name" type="text" name="activity_name" required>
                                </div>
                            </div>

                            <div class="form-box">
                                <div class="form-label"><label for="desc">Description:</label></div>
                                <div class="form-input">
                                    <textarea id="desc" name="activity_description" required></textarea>
                                </div>
                            </div>


                            <div class="form-box">
                                <div class="form-label"><label for="name">Unlock Date:</label></div>
                                <div class="form-input">
                                    <input type="datetime-local" name="unlock_date" required>
                                </div>
                            </div>


                            <div class="form-box">
                                <div class="form-label"><label for="name">Youtube Video URL:</label></div>
                                <div class="form-input">
                                    <input id="name" type="text" name="video_url" required>
                                </div>
                            </div>


                        </div>
                    </div>
                </div><br>

                <!-- ▸ submit --------------------------------------------------------------->
                <div class="content-container">
                    <div class="content">
                        <div class="form-button">
                            <button class="edit" onclick="return confirm('Confirm this addition?')">
                                Post Tutorial
                            </button>
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