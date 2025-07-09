<?php $title = "Set new password";
include __DIR__ . '/../partials/head.php'; ?>
</head>

<body>
    <div class="screen-login">
        <div class="login admin">
            <div class="login-container">
                <div class="login-logo"><img src="/icons/title-logo.svg" width="200"></div>

                <?php if (session('error')): ?>
                    <div class="alert alert-danger"><?= session('error') ?></div>
                <?php endif; ?>

                <form action="/replace-password" method="POST" class="login-form-box">
                    <?= csrf_field() ?>
                    <p class="input-placeholder">New Password</p>
                    <input type="password" name="password" required minlength="8">
                    <p class="input-placeholder">Confirm Password</p>
                    <input type="password" name="password_confirmation" required minlength="8">
                    <button id="login-submit" type="submit">Save Password</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>