<?php $title = "Enter PIN";
include __DIR__ . '/../partials/head.php'; ?>
</head>

<body>
    <div class="screen-login">
        <div class="login admin">
            <div class="login-container">
                <div class="login-logo"><img src="/icons/title-logo.svg" width="200"></div>

                <?php if (session('error')): ?>
                    <div class="alert alert-danger"><?= session('error') ?></div>
                <?php elseif (session('success')): ?>
                    <div class="alert alert-success"><?= session('success') ?></div>
                <?php endif; ?>

                <!-- ask to send pin -->
                <form action="/pin/send" method="POST" style="margin-bottom:1rem">
                    <?= csrf_field() ?>
                    <input type="hidden" name="email" value="<?= htmlspecialchars(session('email')) ?>">
                    <button class="btn btn-primary">Send new PIN to my e-mail</button>
                </form>

                <!-- enter pin -->
                <form action="/pin/verify" method="POST" class="login-form-box">
                    <?= csrf_field() ?>
                    <p class="input-placeholder">Enter 6-digit PIN</p>
                    <input name="pin" maxlength="6" pattern="\d{6}" required>
                    <button id="login-submit" type="submit">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>