<?php $title = 'My Courses';
include __DIR__ . '/../partials/head.php'; ?>
<style>

</style>
</head>

<body>
    <?php include __DIR__ . '/../partials/nav.php'; ?>

    <div class="screen">
        <div class="spacing main">
            <div class="content-container">
                <div class="content padding heading box-gray">

                    <div class="header">
                        <div class="text title">
                            <h5> Courses </h5>
                        </div>
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