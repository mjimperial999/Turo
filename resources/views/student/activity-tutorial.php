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

<?php include __DIR__ . '/../partials/footer.php';  ?>