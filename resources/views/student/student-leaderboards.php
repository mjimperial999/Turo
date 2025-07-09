<?php $title = 'Section Leaderboard';
include __DIR__ . '/../partials/head.php'; ?>
<style>
    .student-rank {
        display: flex;
        flex-direction: row;
        align-items: center;
        padding: .6rem;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
    }

    .student-details {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .student-rank:nth-child(odd) {
        background:
            linear-gradient(135deg,
                rgb(236, 228, 252) 0%,
                /* lavender-tint highlight */
                rgb(215, 203, 246) 50%,
                /* soft lilac              */
                rgb(198, 186, 238) 100%
                /* mellow violet            */
            ) padding-box,
            linear-gradient(135deg,
                rgb(160, 127, 220) 0%,
                rgb(139, 104, 202) 50%,
                rgb(119, 85, 181) 100%) border-box;
        border: 0.08rem solid transparent;
        border-radius: 0.5rem;
    }

    .student-rank:nth-child(even) {
        background:
            linear-gradient(135deg,
                rgb(247, 240, 237) 0%,
                /* user-supplied base */
                rgb(235, 222, 218) 50%,
                /* gentle shade      */
                rgb(224, 207, 203) 100%
                /* deeper complement */
            ) padding-box,
            linear-gradient(135deg,
                rgb(193, 135, 121) 0%,
                /* muted rose edge */
                rgb(175, 116, 102) 50%,
                rgb(157, 98, 84) 100%) border-box;
        border: 0.08rem solid transparent;
        border-radius: 0.5rem;

    }

    .student-rank:nth-child(1) {
        background:
            /* inner fill */
            linear-gradient(135deg,
                rgb(250, 234, 195) 0%,
                /* pale gold */
                rgb(248, 222, 142) 50%,
                /* mid gold  */
                rgb(244, 205, 122) 100%
                /* warm gold */
            ) padding-box,
            /* border stroke */
            linear-gradient(135deg,
                rgb(224, 168, 17) 0%,
                /* rich edge highlight */
                rgb(217, 145, 8) 50%,
                rgb(185, 118, 5) 100%) border-box;
        border: 0.08rem solid transparent;
        border-radius: 0.5rem;
    }

    .student-rank:nth-child(2) {
        background:
            linear-gradient(135deg,
                rgb(240, 240, 245) 0%,
                /* icy highlight */
                rgb(223, 223, 231) 50%,
                /* mid silver   */
                rgb(205, 205, 213) 100%
                /* cool depth   */
            ) padding-box,
            linear-gradient(135deg,
                rgb(176, 176, 185) 0%,
                /* outer edge */
                rgb(155, 155, 165) 50%,
                rgb(129, 129, 140) 100%) border-box;
        border: 0.08rem solid transparent;
        border-radius: 0.5rem;
    }

    .student-rank:nth-child(3) {
        background:
            linear-gradient(135deg,
                rgb(247, 225, 205) 0%,
                /* pale bronze */
                rgb(226, 173, 135) 50%,
                /* mid bronze  */
                rgb(206, 138, 96) 100%
                /* deep bronze */
            ) padding-box,
            linear-gradient(135deg,
                rgb(156, 95, 56) 0%,
                /* outer edge */
                rgb(139, 78, 42) 50%,
                rgb(121, 61, 26) 100%) border-box;
        border: 0.08rem solid transparent;
        border-radius: 0.5rem;
    }

    .rank {
        width: 2.2rem;
        font-weight: 700;
        text-align: right;
    }

    .medal-1 {
        color: #d4af37;
    }

    /* gold   */
    .medal-2 {
        color: #bec2cb;
    }

    /* silver */
    .medal-3 {
        color: #cd7f32;
    }

    /* bronze */
    .me {
        background:
            linear-gradient(135deg, rgb(224, 209, 209) 0%, rgb(207, 196, 183) 50%, rgb(196, 187, 163) 100%) border-box;

    }

    .pic {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-size: cover;
        background-position: center;
        margin: 0 .6rem;
    }

    .pts {
        font-weight: 700;
    }

    .foot {
        margin-top: 1rem;
        text-align: center;
    }
</style>
</head>

<body>
    <?php include __DIR__ . '/../partials/nav.php'; ?>

    <div class="screen">
        <div class="spacing main">
            <div class="content-container box-page">
                <div class="content padding heading box-gold">
                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/achievements.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4> Leaderboards </h4>
                                <h6> <?= e($me->section?->section_name) ?? null ?> </h6>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="content padding">
                    <?php foreach ($top15 as $idx => $s):
                        $rank = $idx + 1;
                        $cls  = $rank < 4 ? 'medal-' . $rank : '';
                        $rowC = $s->user_id === $me->user_id ? 'me' : '';
                        $img  = empty($s->user->image?->image)
                            ? '/icons/no-img.jpg'
                            : "data:" . getMimeTypeFromBlob($s->user->image->image) . ";base64," . base64_encode($s->user->image->image);
                    ?>
                        <div class="student-rank <?= $rowC ?>">
                            <div class="student-details">
                                <div class="rank <?= $cls ?>"><?= $rank ?></div>
                                <div class="pic" style="background-image:url('<?= $img ?>')"></div>
                                <div class="name"><?= e($s->user->first_name . ' ' . $s->user->last_name) ?></div>
                            </div>
                            <div class="student-points">
                                <div><?= number_format($s->total_points) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($myRank > 15): ?>
                    <div class="content padding foot">
                        Your current rank: <b>#<?= $myRank ?></b> out of <?= $ranked->count() ?> students.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>