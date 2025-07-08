<?php
$title = $activity->activity_name;
include __DIR__ . '/../partials/head.php';
?>
<style>
</style>
</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav-teach.php';
    ?>

    <div class="screen">
        <div class="spacing main">
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
                        <h6><a href="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>"><?= $course->course_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><a href="/teachers-panel/course/<?= $course->course_id ?>/section/<?= $section->section_id ?>/module/<?= $module->module_id ?>/"><?= $module->module_name ?></a></h6>
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
                    <?php function toEmbedUrl($url)
                        {
                            // Convert standard YouTube URL to embed format
                            if (preg_match('/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
                                return 'https://www.youtube.com/embed/' . $matches[1];
                            }
                            return $url; // fallback
                        }
                        $embedUrl = toEmbedUrl($activity->tutorial->video_url);

                        echo '<a class="video-link" target="_blank" rel="noopener noreferrer" href="' . $activity->tutorial->video_url . '">' . $activity->tutorial->video_url . '</a>
                            <iframe class="video-placeholder" width="100%" height="500em"
                            src="' . $embedUrl . '"></iframe>' ?>
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