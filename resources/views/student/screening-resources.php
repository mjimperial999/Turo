<?php
/* ─────────────────────────  PAGE SET-UP  ───────────────────────── */
$firstTitle = $resources[0]->title ?? 'Learning Materials';
$title      = "Resources – " . $firstTitle;
include __DIR__ . '/../partials/head.php';
?>

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
        border: .04em solid #C9C9C9;
        border-collapse: collapse;
    }

    table {
        width: 100%;
    }
</style>
</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav.php';
    include __DIR__ . '/../partials/flash-stack.php';
    ?>

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
                        <h6><a href="/home-tutor/course/<?= $course->course_id ?>/<?= $screening->screening_id ?>">(SCREENER) <?= $screening->screening_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><a href="/home-tutor/course/<?= $course->course_id ?>/<?= $screening->screening_id ?>/summary">SUMMARY: <?= $screening->screening_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>Resource: <?= $resources[0]->title ?? 'Learning Materials' ?></a></h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-page">
                <div class="content heading padding box-gray">
                    <div class="header logo">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/bulb.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4><?= htmlspecialchars($firstTitle) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-container box-page">
                <div class="content padding">
                    <?php
                    // helper converts a YouTube watch URL to embed src 
                    function toEmbedUrl(string $url): string
                    {
                        if (preg_match('/watch\\?v=([a-zA-Z0-9_-]+)/', $url, $m)) {
                            return 'https://www.youtube.com/embed/' . $m[1];
                        }
                        return $url;
                    }
                    ?>

                    <?php foreach ($resources as $res): ?>


                        <?php if (!empty($res->description)): ?>
                            <p class="description italic">
                                <?= nl2br(htmlspecialchars($res->description)) ?>
                            </p>
                            <hr>
                        <?php endif; ?>

                        <?php /* ----------- VIDEO --------------  */ ?>
                        <?php if (!empty($res->video_url)): ?>
                            <?php $embed = toEmbedUrl($res->video_url); ?>
                            <p class="description">
                                <a class="video-link" target="_blank" rel="noopener noreferrer"
                                    href="<?= htmlspecialchars($res->video_url) ?>">
                                    Watch on YouTube
                                </a>
                            </p>
                            <iframe class="video-placeholder" width="100%" height="500em"
                                src="<?= htmlspecialchars($embed) ?>" allowfullscreen></iframe>
                        <?php endif; ?>
                    <?php endforeach; ?>
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

            <?php if (empty($resources)): ?>
                <p class="description mt-4">
                    No supplementary materials have been uploaded for this topic yet.
                </p>

            <?php else: ?>
                <table>
                    <tr class="module-title">
                        <th class="table-left-padding"></th>
                        <th class="table-right-padding">
                            <div class="first-th">
                                <div class="module-heading">
                                    <div class="module-logo">
                                        <img class="svg" src="/icons/bulb.svg"
                                            width="50em" height="auto"
                                            style="filter: drop-shadow(0 0.2rem 0.25rem rgba(0,0,0,.2));">
                                    </div>
                                    <div class="heading-context">
                                        <h5><b><?= htmlspecialchars($firstTitle) ?></b></h5>
                                    </div>
                                </div>

                                <div class="return-prev-cont">
                                    <a class="activity-link" href="javascript:history.back()">
                                        <div class="return-prev">← Return</div>
                                    </a>
                                </div>
                            </div>
                        </th>
                    </tr>

                    <?php
                    /* helper converts a YouTube watch URL to embed src 
                    function toEmbedUrl(string $url): string
                    {
                        if (preg_match('/watch\\?v=([a-zA-Z0-9_-]+)/', $url, $m)) {
                            return 'https://www.youtube.com/embed/' . $m[1];
                        }
                        return $url;
                    }
                    ?>

                    <?php foreach ($resources as $res): ?>
                        <tr class="module-subtitle">
                            <td class="table-left-padding"></td>
                            <td class="table-right-padding">
                                <?php if (!empty($res->description)): ?>
                                    <p class="description italic">
                                        <?= nl2br(htmlspecialchars($res->description)) ?>
                                    </p><hr>
                                <?php endif; ?>

                                <?php /* ----------- VIDEO --------------  ?>
                                <?php if (!empty($res->video_url)): ?>
                                    <?php $embed = toEmbedUrl($res->video_url); ?>
                                    <p class="description">
                                        <a class="video-link" target="_blank" rel="noopener noreferrer"
                                            href="<?= htmlspecialchars($res->video_url) ?>">
                                            Watch on YouTube
                                        </a>
                                    </p>
                                    <iframe class="video-placeholder" width="100%" height="500em"
                                        src="<?= htmlspecialchars($embed) ?>" allowfullscreen></iframe>
                                <?php endif; ?>

                                <?php /* ----------- PDF ---------------  ?>
                                <?php if (!empty($res->pdf_blob)): ?>
                                    <?php
                                    $mime = 'application/pdf';
                                    $b64  = base64_encode($res->pdf_blob);
                                    $dataUrl = "data:$mime;base64,$b64";
                                    ?>
                                    <iframe class="video-placeholder" width="100%" height="500em"
                                        src="<?= $dataUrl ?>"></iframe>
                                <?php endif; ?>

                                <?php /* ----------- DESCRIPTION --------  ?>


                            </td>
                        </tr>
                    <?php endforeach; ?>

                </table>
            <?php endif; ?>

        </div>
        <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</hmtl>

*/ ?>