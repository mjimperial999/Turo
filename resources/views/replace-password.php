<?php $title = "Set new password";
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
                <h4> Replace Password </h4>

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
            <?php if ($errors->any()): ?>
              <div class="alert alert-danger alert-message padding">
                <ul><?php foreach ($errors->all() as $msg): ?><li><?= htmlspecialchars($msg) ?></li><?php endforeach; ?></ul>
              </div>
            <?php endif; ?>
          </div>

          <form action="/replace-password" method="POST" class="login-form-box">
            <?= csrf_field() ?>
            <p class="input-placeholder">NEW PASSWORD</p>
            <input type="password" name="password" required />

            <p class="input-placeholder">COMFIRM PASSWORD</p>
            <input type="password" name="password_confirmation" required minlength="8">

            <button id="login-submit" type="submit">Save</button>
          </form>
        </div>
      </div>

    </div>
  </div>

</body>

</html>