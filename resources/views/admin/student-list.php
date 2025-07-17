<?php $title = "Student's List";
include __DIR__ . '/../partials/head.php'; ?>
<style>
    .search-bar {
        display: flex;
        gap: .6rem;
        align-items: center
    }

    .search-bar input,
    .search-bar select {
        padding: .45rem .6rem;
        border: 1px solid #bbb;
        border-radius: .3rem
    }

    table.std {
        width: 100%;
        border-collapse: collapse;
        font-size: .9rem
    }

    table.std th,
    table.std td {
        border: 1px solid #ddd;
        padding: 0.05rem 0.5rem;
        text-align: left
    }

    .std-img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-size: cover;
        background-position: center;
        margin-right: .4rem
    }
</style>

</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav-admin.php';
    ?>

    <div class="screen">

        <div class="spacing whole">
            <div class="content-container box-page">
                <div class="mini-navigation">
                    <div class="text title">
                        <h6><a href="/admin-panel">Back to Menu Page</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>Student List</h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-gray">

                <div class="content padding flex-row" style="align-items: center; gap: 1rem">
                    <h6> Add Student Entry: </h6>
                    <form action="/admin-panel/student-list/add" method="GET">
                        <button class="self-button">Add Manually</button>
                    </form>
                    <form action="/admin-panel/student-list/import-csv" method="GET">
                        <button class="self-button">Import CSV</button>
                    </form>
                    <h6> Set Sections: </h6>
                    <form action="/admin-panel/student-list/student-bulk-section" method="GET">
                        <button class="self-button">Set Students Section</button>
                    </form>

                </div>

            </div>

            <div class="content-container box-page">

                <div class="content padding heading box-gold">

                    <form class="search-bar" method="GET">
                        <input
                            type="text"
                            name="q"
                            placeholder="Search: Name / ID"
                            value="<?= htmlspecialchars($term ?? '') ?>"
                            style="margin:0;">

                        <button class="self-button">Search</button>

                        <select name="section">
                            <option value="">All sections</option>
                            <?php foreach ($sections as $sid => $sname): ?>
                                <option <?= (string)$sid === (string)($sectionId ?? '') ? 'selected' : '' ?>
                                    value="<?= $sid ?>">
                                    <?= htmlspecialchars($sname) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                </div>

                <div class="content padding box-page">

                    <table class="std">
                        <?php
                        if (empty($students) || (is_object($students) && method_exists($students, 'isEmpty') && $students->isEmpty())): ?>
                            <div class="no-items" style="text-align:center;margin:2rem 0;">
                                <img class="svg" src="/icons/nothing.svg" width="50em" height="auto" />
                                <p style="font-family: Albert-Sans, sans-serif; font-size:1rem; color:#555;">
                                    No students found.
                                </p>
                            </div>

                        <?php /* --- otherwise loop the rows ----------------------------- */
                        else: ?>

                            <tr>
                                <th>Student</th>
                                <th>ID #</th>
                                <th>Section</th>
                                <th>Catch-Up</th>
                                <th></th>
                            </tr>
                            <?php foreach ($students as $stu):
                                $u  = $stu->user;
                                /* avatar helper -------------------------- */
                                if (empty($u->image?->image)) {
                                    $avatar = "/icons/no-img.jpg";
                                } else {
                                    $blob = $u->image->image;
                                    $avatar = "data:" . getMimeTypeFromBlob($blob) . ';base64,' . base64_encode($blob);
                                } ?>
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center">
                                            <div class="std-img" style="background-image:url('<?= $avatar ?>')"></div>
                                            <?= htmlspecialchars($u->last_name . ', ' . $u->first_name) ?>
                                        </div>
                                    </td>
                                    <td><?= $stu->user_id ?></td>
                                    <td><?= htmlspecialchars($stu->section?->section_name ?? '-') ?></td>
                                    <td><?= $stu->isCatchUp ? 'Yes' : 'No' ?></td>
                                    <td>
                                        <form action="/admin-panel/student-list/student/<?= $stu->user_id ?>" method="GET">
                                            <button class="edit">
                                                View
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                    </table>
                <?php endif; ?>

                <div style="margin-top:.6rem"><?= $students->links('pagination::bootstrap-4') ?></div>
                </div>

            </div>


            <div class="content-container padding box-gold">
            </div>
        </div>

    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>