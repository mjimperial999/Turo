<?php $title = "Edit $course->course_name";
include __DIR__ . '/../partials/head.php'; ?>
<meta http-equiv="Cache-Control" content="no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<style>

</style>
</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav-admin.php';

    if (empty($course->image?->image)) {;
        $imageURL = "/images/no-image.jpeg";
    } else {
        $blobData = $course->image?->image;
        $mimeType = getMimeTypeFromBlob($blobData);
        $base64Image = base64_encode($blobData);
        $imageURL = "data:$mimeType;base64,$base64Image";
    }
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
                            <h6> Edit Course - <?= htmlspecialchars($course->course_name) ?> </h6>
                            <div class="line active"></div>
                        </div>
                    </div>
                </div>
                <br>

                <div class="content-container">

                    <div class="content padding box-gray">

                        <div class="header">
                            <div class="text title">
                                <h4> Edit Course: <?= htmlspecialchars($course->course_name) ?> </h4>
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

                    <div class="insider flex-row">
                        <div class="content padding heading side box-gray">
                            <div class="edit-image-display" style="background-image: url(' <?= $imageURL ?> ');"></div>
                            <hr class="header-hr">

                            <div class="header">
                                <div class="text title">
                                    <h6 class="text-center"> Change Image </h6>
                                </div>
                            </div>
                            <input class="file-upload" type="file" name="image" accept="image/*" />

                        </div>
                        <div class="content padding main box-page">
                            <div class="content flex-column">
                                <div class="form-box">
                                    <div class="form-label">
                                        <label>Name:</label>
                                    </div>
                                    <div class="form-input">
                                        <input type="text" name="course_name" value="<?= htmlspecialchars($course->course_name) ?>">
                                    </div>
                                </div>
                                <div class="form-box">
                                    <div class="form-label">
                                        <label>Course Code:</label>
                                    </div>
                                    <div class="form-input">
                                        <input type="text" name="course_code" value="<?= htmlspecialchars($course->course_code) ?>">
                                    </div>
                                </div>
                                <div class="form-box">
                                    <div class="form-label">
                                        <label>Description:</label>
                                    </div>
                                    <div class="form-input">
                                        <textarea type="text" name="course_description"><?= htmlspecialchars($course->course_description) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <br>
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
                    <form method="POST" action="/admin-panel/edit-content/course/<?= $course->course_id ?>/delete"
                        onsubmit="return confirm('Really delete this course?');">
                        <?= csrf_field(); ?>
                        <div class="form-button">
                            <button class="delete">Delete Course</button>
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