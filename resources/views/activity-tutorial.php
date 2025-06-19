<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $activity->activity_name ?> | Turo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="/css/styles.css">
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

        .table-left-padding {
            width: 2em;
        }

        .table-right-padding {
            padding: 1em 1.5em;
        }

        .module-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start
        }

        tr.module-title {
            height: 1em;
        }

        tr:nth-child(odd) {
            background-color: rgba(211, 211, 211, 0.30);
        }

        tr:nth-child(even) {
            background-color: rgba(232, 232, 232, 0.3);
        }
    </style>
</head>

<body>
    <?php include('partials/navibar.php'); ?>

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
        <?php include('partials/right-side-notifications.php'); ?>
    </div>
</body>
</html>