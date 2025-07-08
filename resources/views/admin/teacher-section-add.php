<?php $title = "Create Teacher Account";
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
            <form action="/admin-panel/teacher-list/add-section" method="POST">
                <?= csrf_field() ?>
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
                            <h6><a href="/admin-panel/teacher-list">Teacher List</a></h6>
                            <div class="line"></div>
                        </div>
                        <div class="divider">
                            <h6> > </h6>
                        </div>
                        <div class="text title">
                            <h6>Create New Section</h6>
                            <div class="line active"></div>
                        </div>
                    </div>
                </div>
                <br>

                <div class="content-container">

                    <div class="content padding heading box-gray">

                        <div class="header">
                            <div class="text title">
                                <h4> Create New Section </h4>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="content-container box-page">
                    <div class="content">
                        <?php if ($errors->any()): ?>
                            <div class="alert alert-danger alert-message padding">
                                <ul><?php foreach ($errors->all() as $msg): ?><li><?= htmlspecialchars($msg) ?></li><?php endforeach; ?></ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="content-container box-page">

                    <div class="insider flex-row">
                        <div class="content padding box-page">
                            <div class="content flex-column">
                                <div class="form-box">
                                    <div class="form-label">
                                        <label>Section ID:<br>
                                        (naming sense: g#-"section name")<br>
                                    </div>
                                    <div class="form-input">
                                        <input type="text" name="section_id" placeholder="ex: g7-integrity" value="">
                                    </div>
                                </div>
                                <div class="form-box">
                                    <div class="form-label">
                                        <label>Section Name:</label>
                                    </div>
                                    <div class="form-input">
                                        <input type="text" name="section_name" placeholder="ex: Grade 7 - Integrity" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <br>

                <div class="content-container">
                    <div class="content">
                        <div class="form-button">
                            <button class="edit" onclick="return confirm('Create a new section?');">Create Section</button>
                        </div>
                    </div>
                </div>

            </form>

            <div class="content-container padding box-gold">
            </div>
        </div>

    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>