<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$title = "Enter PIN";
include __DIR__ . '/partials/head.php'; ?>
</head>

<body>

    <div class="screen-login">
        <div class="login admin">
            <div class="login-container">
                <div class="login-logo">
                    <img src="icons/title-logo.svg" width="200em" height="auto">
                </div>
                <div class="login-form">

                    <div class="content text-center">
                        <div class="header">
                            <div class="text title">
                                <h4> Welcome to Turo </h4>
                                <hr class="divider-hr">
                                <p style="text-align: left;">To ensure access, please verify that you are the right person for this email.
                                Click the button below to send a pin to your email to verify if this is your email.</em></p>
                            </div>
                        </div>
                    </div>

                    <div class="content">
                        <hr class="divider-hr">
                    </div>

                    <div class="content">
                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger alert-message" role="alert">
                                <?= session('error') ?>
                            </div>
                        <?php elseif (session()->has('success')): ?>
                            <div class="alert alert-success alert-message" role="alert">
                                <?= session('success') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form action="/pin" method="POST" class="login-form-box" style="margin-bottom:1rem">
                        <?= csrf_field() ?>
                        <input type="hidden" name="email" value="<?= htmlspecialchars(session('email')) ?>">
                        <button class="btn btn-primary">Send PIN to my Email</button>
                    </form>

                    <form action="/pin/verify" method="POST" class="login-form-box">
                        <?= csrf_field() ?>
                        <p class="input-placeholder">ENTER 6-DIGIT PIN</p>
                        <input name="pin" maxlength="6" pattern="\d{6}" required>
                        <button id="login-submit" type="submit">Confirm</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</body>

</html>