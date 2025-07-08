<?php $title = "Admin's Dashboard";
include __DIR__ . '/../partials/head.php'; ?>
<style>

</style>
</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav-admin.php';
    ?>

    <div class="screen">

        <div class="spacing main">

            <div class="content-container box-page">

                <div class="content padding heading box-gold">

                    <div class="header">
                        <div class="text title">
                            <h4> Menu </h4>
                        </div>
                    </div>

                </div>
                <div class="content padding box-page">

                    <div class="flex-area">

                        <div class="flex-box admin">
                            <form action="/admin-panel/student-list/" method="GET">
                                <button type="submit" class="box admin">

                                    <div class="box-filler">
                                        <img class="svg" src="/admin/student-list.svg" width="100em" height="auto" />
                                    </div>
                                    <div class="box-details">
                                        <div class="box-title">
                                            <div class="title">
                                                <h6>Student List</h6>
                                            </div>
                                        </div>
                                    </div>

                                </button>
                            </form>
                        </div>

                        <div class="flex-box admin">
                            <form action="/admin-panel/teacher-list/" method="GET">
                                <button type="submit" class="box admin">

                                    <div class="box-filler">
                                        <img class="svg" src="/admin/edit-schedule.svg" width="100em" height="auto" />
                                    </div>
                                    <div class="box-details">
                                        <div class="box-title">
                                            <div class="title">
                                                <h6>Teacher List and Sections</h6>
                                            </div>
                                        </div>
                                    </div>

                                </button>
                            </form>
                        </div>

                        <div class="flex-box admin">
                            <form action="/admin-panel/edit-content/" method="GET">
                                <button type="submit" class="box admin">

                                    <div class="box-filler">
                                        <img class="svg" src="/admin/edit-content.svg" width="120em" height="auto" />
                                    </div>
                                    <div class="box-details">
                                        <div class="box-title">
                                            <div class="title">
                                                <h6>Modules, Quizzes, and Screening Exams</h6>
                                            </div>
                                        </div>
                                    </div>

                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>


            <div class="content-container padding box-gold">
            </div>
        </div>

        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
        </div>
    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>