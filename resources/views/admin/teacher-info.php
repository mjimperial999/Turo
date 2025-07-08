<?php $title = "Student's List";
include __DIR__ . '/../partials/head.php'; ?>
<style>
    .teacher-assignments-card {
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .teacher-assignments {
        display: flex;
        flex-direction: column;
        max-width: 40rem;
    }
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
                        <h6><a href="/admin-panel/teacher-list">Teacher List</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>Teacher Edit</h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-page">
                <div class="content padding heading box-gray">
                    <div class="header">
                        <div class="text title">
                            <h4> Manage Teacher Assignments </h4>
                        </div>
                    </div>
                </div>

                <div class="content padding">
                    <h4><?= $teacher->user->first_name . ' ' . $teacher->user->last_name ?></h4>
                    <small>ID: <?= $teacher->user_id ?></small>
                    <hr class="divider-hr">
                    <h5>Current Assignments:</h5><br>
                    <div class="teacher-assignments-card">
                        <?php foreach ($teacher->courseSections as $cs): ?>
                            <div class="teacher-assignments">
                                <h6><b>Course: </b><?= e($cs->course->course_name) ?></h6>
                                <h6><b>Section: </b><?= e($cs->section->section_name) ?></h6>
                            
                            <form style="display:inline" method="post" action="/admin-panel/teacher-info/<?= $teacher->user_id ?>/detach">
                                <?= csrf_field() ?>
                                <input type="hidden" name="course_id" value="<?= $cs->course_id ?>">
                                <input type="hidden" name="section_id" value="<?= $cs->section_id ?>">
                                <div class="manage-button" style="float: left;">
                                    <button class="delete"  onclick="return confirm('Remove assignment?')">Remove</button>
                                </div>
                                
                            </form><br>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr class="divider-hr">
                    <h5>Add Assignment</h5>
                    <form method="post" action="/admin-panel/teacher-info/<?= $teacher->user_id ?>/attach">
                        <?= csrf_field() ?>
                        <select name="course_id">
                            <?php foreach ($allCourses as $c): ?>
                                <option value="<?= $c->course_id ?>"><?= e($c->course_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="section_id">
                            <?php foreach ($allSections as $s): ?>
                                <option value="<?= $s->section_id ?>"><?= e($s->section_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="edit">Attach</button>
                    </form>
                </div>
            </div>


            <div class="content-container padding box-gold">
            </div>
        </div>

    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>