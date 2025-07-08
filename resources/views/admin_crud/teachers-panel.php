<?php $title = "Teacher's Dashboard";
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
            <div class="content-container box-gold">

                <div class="content padding">

                    <div class="header">
                        <div class="text title">
                            <h4> Dashboard </h4>
                        </div>
                    </div>

                </div>
            </div>

            <div class="content-container">
                <div class="content padding heading box-gray crud">

                    <div class="header">
                        <div class="text title">
                            <h5> Your Courses </h5>
                        </div>
                    </div>

                    <div class="crud-header">
                        <form action="/teachers-panel/create-course" method="GET">
                            <button type="submit" class="crud-button-add">
                                New Course <img src="/icons/new-black.svg" width="25em" height="25em" />
                            </button>
                        </form>
                    </div>

                </div>

                <div class="content padding box-page">

                    <div class="flex-area">
                        <?php foreach ($courseLinks as $link) {
                            $course  = $link->course;
                            $section = $link->section;

                            $timestamp = strtotime($course->start_date);
                            $formattedDate = date("F j, Y", $timestamp);

                            if (empty($course->image?->image)) {;
                                $imageURL = "/icons/no-img.jpg";
                            } else {
                                $blobData = $course->image?->image;
                                $mimeType = getMimeTypeFromBlob($blobData);
                                $base64Image = base64_encode($blobData);
                                $imageURL = "data:$mimeType;base64,$base64Image";
                            }

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