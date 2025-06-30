<?php $title = "Add New Course";
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
            <form method="POST" action="/teachers-panel/store-course" enctype="multipart/form-data">
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
                            <h6> Add New Course </h6>
                            <div class="line active"></div>
                        </div>
                    </div>
                </div>
                <br>

                <div class="content-container">

                    <div class="content padding box-gray">

                        <div class="header">
                            <div class="text title">
                                <h4> Add A New Course </h4>
                            </div>
                        </div>

                    </div>
                </div>
                <br>

                <div class="content-container box-page">
                    <div class="content">
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

                    <div class="insider flex-row">
                        <div class="content padding heading side box-gray">
                            <div class="edit-image-display" style="background-image: url('/images/no-image.jpeg');"></div>
                            <hr class="header-hr">

                            <div class="header">
                                <div class="text title">
                                    <h6 class="text-center"> Add Image </h6>
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
                                        <input type="text" name="course_name" value="">
                                    </div>
                                </div>
                                <div class="form-box">
                                    <div class="form-label">
                                        <label>Course Code:</label>
                                    </div>
                                    <div class="form-input">
                                        <input type="text" name="course_code" value="">
                                    </div>
                                </div>
                                <div class="form-box">
                                    <div class="form-label">
                                        <label>Description:</label>
                                    </div>
                                    <div class="form-input">
                                        <textarea type="text" name="course_description"></textarea>
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
                            <button class="edit" onclick="return confirm('Create a new course?');">Create Course</button>
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