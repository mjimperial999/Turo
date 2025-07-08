<?php
$title = "{$student->user->last_name}, {$student->user->first_name} — Admin View";
include __DIR__ . '/../partials/head.php';
?>

<style>
    /* quick reusable table wrapper */
    .table-box {
        border: 1px solid #ddd;
        border-radius: .4rem;
        overflow: hidden
    }

    .table-box h6 {
        margin: 0;
        padding: .6rem 1rem;
        background: #fcfdf2;
        border-bottom: 1px solid #ddd
    }

    .table-box table {
        width: 100%;
        border-collapse: collapse
    }

    .table-box th,
    .table-box td {
        padding: .45rem .6rem;
        border: 1px solid #ddd;
        text-align: center
    }

    .table-box tr:nth-child(even) {
        background: #fffaf7
    }

    /* alt row colour :contentReference[oaicite:0]{index=0} */
    tr:hover {
        background: #ffe0c4
    }

    /* hover tint :contentReference[oaicite:1]{index=1} */

    .badge,
    img.ach {
        height: 40px;
        width: 40px;
        border-radius: 4px;
        object-fit: cover
    }

    .cred-wrap {
        display: flex;
        flex-direction: column;
        margin-bottom: 1.2rem
    }

    .cred-wrap label {
        font-weight: 600
    }
</style>
</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav-admin.php';

    if (empty($student->user->image?->image)) {;
        $imageURL = "/icons/no-img.jpg";
    } else {
        $blobData = $student->user->image?->image;
        $mimeType = getMimeTypeFromBlob($blobData);
        $base64Image = base64_encode($blobData);
        $imageURL = "data:$mimeType;base64,$base64Image";
    }
    ?>

    <div class="screen">
        <div class="spacing main">

            <!-- breadcrumb -->
            <div class="content-container box-page">
                <div class="mini-navigation">
                    <div class="text title">
                        <h6><a href="/admin-panel/student-list">Students</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>Student Info: <?= $student->user->last_name ?></h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <!-- credentials ------------------------------------------------------- -->
            <div class="content-container box-page">
                <div class="content padding heading box-gray">
                    <div class="header logo">
                        <div class="logo-and-title">
                            <div class="logo">
                                <div style="background-image: url('<?= $imageURL ?>'); width: 2.5em; height: 2.5em; background-size: cover; background-position: center; border-radius: 50%; cursor: pointer;"></div>
                            </div>
                            <div class="text title">
                                <h4><?= $student->user->last_name ?>, <?= $student->user->first_name ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content padding">
                    <div class="cred-wrap">
                        <label>Email:</label> <?= htmlspecialchars($student->user->email) ?>
                    </div>

                    <div class="cred-wrap">
                        <label>Password:</label>
                        <input type="password" id="pwd" value="<?= htmlspecialchars($student->user->password_hash) ?>" readonly>
                        <p style="font-size: 0.75rem; color: #888;">
                            (Plain-text password is **not** stored; you can only reset it.)
                        </p>
                        <button class="btn btn-sm" onclick="togglePwd()">Show / Hide</button>
                    </div>
                </div>
            </div>

            <!-- COURSES ----------------------------------------------------------- -->
            <?php foreach ($courses as $c): ?>
                <div class="content-container box-page table-box">
                    <h6><b><?= htmlspecialchars($c->course_name) ?></b> —
                        Points <?= number_format($c->total_points) ?> —
                        Rank #<?= $c->rank ?></h6>

                    <!-- practice -->
                    <?php if ($c->practice->isNotEmpty()): ?>
                        <h6 style="background:#fafafa;padding:.3rem 1rem">Practice-Quiz Avg</h6>
                        <table>
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Quiz</th>
                                    <th>Avg %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($c->practice as $row): ?>
                                    <tr>
                                        <td><?= $row->module_name ?></td>
                                        <td><?= $row->quiz_name ?></td>
                                        <td><?= round($row->avg, 1) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <!-- short -->
                    <?php if ($c->short->isNotEmpty()): ?>
                        <h6 style="background:#fafafa;padding:.3rem 1rem">Short-Quiz Avg</h6>
                        <table>
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Quiz</th>
                                    <th>Avg %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($c->short as $row): ?>
                                    <tr>
                                        <td><?= $row->module_name ?></td>
                                        <td><?= $row->quiz_name ?></td>
                                        <td><?= round($row->avg, 1) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <!-- long -->
                    <?php if ($c->long->isNotEmpty()): ?>
                        <h6 style="background:#fafafa;padding:.3rem 1rem">Long-Quiz Avg</h6>
                        <table>
                            <thead>
                                <tr>
                                    <th>Quiz</th>
                                    <th>Avg %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($c->long as $row): ?>
                                    <tr>
                                        <td><?= $row->quiz_name ?></td>
                                        <td><?= round($row->avg, 1) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <!-- screening -->
                    <?php if ($c->screening->isNotEmpty()): ?>
                        <h6 style="background:#fafafa;padding:.3rem 1rem">Best Screening Scores</h6>
                        <table>
                            <thead>
                                <tr>
                                    <th>Exam</th>
                                    <th>Best %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($c->screening as $row): ?>
                                    <tr>
                                        <td><?= $row->screening_name ?></td>
                                        <td><?= round($row->best_score, 1) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- BADGES ------------------------------------------------------------ -->
            <div class="content-container box-page table-box">
                <h6>Badges</h6>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;padding:1rem">
                    <?php foreach ($badges as $b): ?>
                        <img class="svg" src="/achievements/<?= ($b->badge_image) ?>.svg" width="30em" height="auto" alt="badge" class="badge">
                    <?php endforeach; ?>
                    <?php if ($badges->isEmpty()): ?><p>— none —</p><?php endif; ?>
                </div>
            </div>

            <!-- ACHIEVEMENTS ------------------------------------------------------ -->
            <div class="content-container box-page table-box">
                <h6>Achievements</h6>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;padding:1rem">
                    <?php foreach ($achievements as $a): ?>
                        <img class="svg" src="/achievements/<?= ($a->achievement_image) ?>.svg" width="30em" height="auto" alt="ach" class="ach">
                    <?php endforeach; ?>
                    <?php if ($achievements->isEmpty()): ?><p>— none —</p><?php endif; ?>
                </div>
            </div>

        </div>

        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
        </div>
    </div>

    <script>
        /* show / hide password toggle  :contentReference[oaicite:2]{index=2} */
        function togglePwd() {
            const f = document.getElementById('pwd');
            f.type = f.type === 'password' ? 'text' : 'password';
        }
    </script>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>