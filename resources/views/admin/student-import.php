<?php $title = 'Import Students';
include __DIR__ . '/../partials/head.php'; ?>
<style>
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
                        <h6>Import CSV</h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-page">
                <div class="content padding heading box-gray">
                    <div class="header">
                        <div class="text title">
                            <h4> Import w/ CSV </h4>
                        </div>
                    </div>
                </div>

                <div class="content padding">
                    <form action="/admin-panel/student-list/import-csv" method="POST" enctype="multipart/form-data"
                        style="max-width:420px;margin:2rem">
                        <?= csrf_field() ?>
                        <input type="file" name="csv" accept=".csv" required>
                        <p class="small text-muted">CSV columns: <code>last_name,first_name,email</code></p>
                        <button class="btn btn-primary">Upload &amp; import</button>
                    </form>
                </div>

            </div>


        </div>
    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>