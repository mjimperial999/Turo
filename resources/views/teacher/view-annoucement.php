<?php $title = 'Announcement';                         /* page <title> */
include __DIR__ . '/../partials/head.php'; ?>

<body>
    <?php 
    use Carbon\Carbon;
    include __DIR__ . '/../partials/nav-teach.php'; ?>

    <div class="screen">
        <div class="spacing main">
            <div class="content-container padding box-gold"></div>
            <div class="content-container box-gray">
                <div class="content padding">
                    <div class="header">
                        <div class="text title">
                            <h5>Announcement</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ──────────────────────────────────────────────── -->
            <div class="content-container box-page">
                <div class="content padding">
                    <h5><?= $announcement->title ?></h5>
                </div>
            </div>

            <div class="content-container box-page">
                <div class="content padding" style="min-height: 20rem;">
                    <p>Posted on: <?= Carbon::parse($announcement->date)->format('M j, Y g:i A') ?></p>
                    <hr class="divider-hr">
                    <p><?= $announcement->description ?></p>

                </div>
            </div>

            <div class="content-container padding box-gold"></div>
        </div>

        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>