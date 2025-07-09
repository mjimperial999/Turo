<?php $title = "Terms & Conditions";
include __DIR__ . '/partials/head.php'; ?>
</head>

<body>

    <div class="screen-login">
        <div class="login admin">
            <div class="login-container">
                <div class="login-form">

                    <div class="content text-center">
                        <div class="header">
                            <div class="text title">
                                <h4> Confidentiality Agreement </h4>
                                <hr class="divider-hr">
                                <p style="text-align: left;">The Turo Tutorship Platform collects and processes certain user data to provide personalized educational content and gamified learning experiences. This includes your quiz scores, learning progress, and activity logs.
                                    By continuing, you acknowledge and consent to the collection of this data for the purpose of improving the educational experience and contributing to academic research.
                                    All personal data will be securely stored and handled only by authorized team members involved in the project. Your personal identity will not be revealed in any public report, paper, or publication related to this system.
                                    We are committed to protecting your privacy and will never share your information with outside parties.
                                    If you agree with this policy, please confirm below.</em></p>
                                <hr class="divider-hr">
                            </div>
                        </div>
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

                    <form action="/terms/accept" method="POST" class="login-form-box">
                        <?= csrf_field() ?>
                        <label>
                            <input type="radio" name="agree" value="1" required> I understand and agree.
                        </label>
                        <label>
                            <input type="radio" name="agree" value="0" disabled> I do not agree.
                        </label>
                        <button class="btn btn-primary">Continue</button>
                    </form>

                </div>
            </div>

        </div>
    </div>

</body>

</html>