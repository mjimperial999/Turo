<?php $title = 'Bulk Section Update';
include __DIR__ . '/../partials/head.php'; ?>
<style>
    .tbl {
        width: 100%;
        border-collapse: collapse
    }

    .tbl th,
    .tbl td {
        border: 1px solid #ddd;
        padding: 0.05rem 0.5rem;
        font-size: .9rem
    }

    .tbl th {
        background: #f3f3f3
    }

    .sticky {
        position: sticky;
        top: 0;
        background: #fff
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
                        <h6><a href="/admin-panel/student-list">Student List</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>Set Students Section</h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-page">
                <form method="POST">
                    <?= csrf_field() ?>

                    <!-- ↳ bulk selector bar -->
                    <div class="content padding heading box-gold flex-row" style="gap: 1rem;">
                        <select name="section_id" required>
                            <option value="">— Move to section —</option>
                            <?php foreach ($sections as $s): ?>
                                <option value="<?= $s->section_id ?>"><?= e($s->section_name) ?></option>
                            <?php endforeach ?>
                            <option value="">❌ Clear section</option>
                        </select>
                        <button class="crud-button-add" type="submit">Apply to selected</button>
                    </div>

                    <div class="content padding">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th class="sticky"><input type="checkbox" id="chk-all"></th>
                                    <th>Img</th>
                                    <th>Last&nbsp;name</th>
                                    <th>First&nbsp;name</th>
                                    <th>ID</th>
                                    <th>Current section</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $st):
                                    $u   = $st->user;
                                    $img = empty($u->image?->image)
                                        ? '/icons/no-img.jpg'
                                        : "data:" . getMimeTypeFromBlob($u->image->image) . ';base64,' . base64_encode($u->image->image);
                                ?>
                                    <tr>
                                        <td><input type="checkbox" name="students[]" value="<?= $st->user_id ?>"></td>
                                        <td>
                                            <div class="std-img" style="background-image:url('<?= $img ?>')"></div>
                                        </td>
                                        <td><?= e($u->last_name) ?></td>
                                        <td><?= e($u->first_name) ?></td>
                                        <td><?= e($st->user_id) ?></td>
                                        <td><?= $st->section?->section_name ?? '—' ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        /* master checkbox */
        document.getElementById('chk-all').addEventListener('change', e => {
            document.querySelectorAll('input[name="students[]"]').forEach(c => c.checked = e.target.checked);
        });
    </script>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>