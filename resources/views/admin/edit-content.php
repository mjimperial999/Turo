<?php $title = "Edit Content";
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
                <div class="mini-navigation">
                    <div class="text title">
                        <h6><a href="/admin-panel">Back to Menu Page</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>Edit Content</h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container">
                <div class="content padding heading box-gray crud">

                    <div class="header">
                        <div class="text title">
                            <h5> Courses </h5>
                        </div>
                    </div>

                    <div class="crud-header">
                        <form action="/admin-panel/edit-content/create-course" method="GET">
                            <button type="submit" class="crud-button-add">
                                New Course <img src="/icons/new-black.svg" width="25em" height="25em" />
                            </button>
                        </form>
                    </div>

                </div>

                <div class="content padding box-page">

                    <div class="flex-area">
                        <?php foreach ($courses as $course) {
                            include __DIR__ . '/../partials/course-hero.php';
                        }
                        ?>
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