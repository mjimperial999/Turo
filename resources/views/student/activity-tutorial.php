<?php
$title = $activity->activity_name;
include __DIR__ . '/../partials/head.php';  ?>
    <style>
        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0 auto;
        }

        table,
        th,
        td {
            border: 0.04em solid #C9C9C9;
            border-collapse: collapse;
        }

        table {
            width: 100%;
        }

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

    <?php /*
    <div class="home-tutor-screen">
        <div class="home-tutor-main">
            <table>
                <tr class="module-title">
                    <th class="table-left-padding"></th>
                    <th class="table-right-padding">
                        <div class="first-th">
                            <div class="module-heading">
                                <div class="module-logo">
                                    <img class="svg" src="/icons/vid.svg" width="50em" height="auto" style="filter: drop-shadow(0 0.2rem 0.25rem rgba(0, 0, 0, 0.2));" />
                                </div>
                                <div class="heading-context">
                                    <h5><b>Video Tutorial: <?= $activity->activity_name ?></b></h5>
                                </div>
                            </div>
                            <div class="return-prev-cont">
                                <?= '<a class="activity-link" href="/home-tutor/module/' . $activity->module_id . '/">
                                <div class="return-prev">BACK to Module Page</div>
                                        </a>' ?>
                                </div>
                            </div>
                        </div>
                    </th>
                </tr>
                <tr class="module-subtitle">
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <?php
                        function toEmbedUrl($url)
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
                    </td>
                </tr>
            </table>

        </div>
        <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
    </div>

<?php include __DIR__ . '/../partials/footer.php'; */ ?>
