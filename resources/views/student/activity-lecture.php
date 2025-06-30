<?php
$title = $activity->activity_name;
include __DIR__ . '/../partials/head.php';
?>
<style>
</style>
</head>

<body>
    <?php include __DIR__ . '/../partials/nav.php'; ?>

    <div class="screen">
        <div class="spacing main">
            <div class="content-container box-page">
                <div class="mini-navigation">
                    <div class="text title">
                        <h6><a href="/home-tutor">Courses</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><a href="/home-tutor/course/<?= $course->course_id ?>"><?= $course->course_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><a href="/home-tutor/course/<?= $course->course_id ?>/module/<?= $module->module_id ?>/"><?= $module->module_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><?= $activity->activity_name ?></a></h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-page">

                <div class="content heading padding box-gray">
                    <div class="header logo">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/lecture.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4><?= $activity->activity_name ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content padding">
                    <p><?= $activity->activity_description ?></p>
                </div>




                <div class="module-content">
                    <?php
                    // fallback icon if the lecture record has no file
                    if (empty($activity->lecture?->file_url)) {
                        echo '<img src="/icons/no-img.jpg" alt="No lecture file" style="max-width:240px;">';
                    } else {
                        $blob      = $activity->lecture->file_url;
                        $mimeType  = getMimeTypeFromBlob($blob);
                        $sizeBytes = strlen($blob);                      // ~~ file size in DB

                        /* ---------- choose preview vs. link ---------- */
                        if ($sizeBytes <= 3 * 1024 * 1024) {             // ≤ 3 MiB → inline
                            $base64 = base64_encode($blob);
                            $dataSrc = "data:$mimeType;base64,$base64";

                            echo <<<HTML
                <object
                    data="$dataSrc"
                    type="application/pdf"
                    width="100%"
                    height="500em">
                    <p>Preview unavailable &mdash;
                       <a href="/lecture-file/{$activity->activity_id}" target="_blank">
                          open full PDF
                       </a>.</p>
                </object>
            HTML;
                        } else {                                         // big file → link only
                            echo <<<HTML
                <p>File is large (&gt;3 MiB).  
                   <a href="/lecture-file/{$activity->activity_id}" target="_blank">
                      Click here to open the PDF
                   </a>.
                </p>
            HTML;
                        }
                    }
                    ?>
                </div>

            </div>
        </div>


        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
        </div>

    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>