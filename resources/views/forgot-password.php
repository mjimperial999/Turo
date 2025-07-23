<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$title = "Forgot Password";
include __DIR__ . '/partials/head.php'; ?>
<style>

</style>
</head>

<?php
/* point to PinController@send (route name pin.send) */
$actionUrl = app()->environment('production')
    ? secure_url('/forgot-password/send')
    : url('/forgot-password/send');
?>

<body>
  <div class="screen-login">
    <div class="login admin">
      <div class="login-container">
        <div class="login-logo">
          <img src="/icons/title-logo.svg" width="200em" height="auto">
        </div>

        <div class="login-form">
          <div class="content text-center">
            <div class="header">
              <div class="text title">
                <h4>Account Recovery</h4>
              </div>
            </div>
          </div>

          <div class="content">
            <hr class="divider-hr">
          </div>

          <div class="content">
            <?php if (session()->has('error')): ?>
              <div class="alert alert-danger alert-message" role="alert">
                <?= e(session('error')) ?>
              </div>
            <?php elseif (session()->has('success')): ?>
              <div class="alert alert-success alert-message" role="alert">
                <?= e(session('success')) ?>
              </div>
            <?php endif; ?>
          </div>

          <form class="login-form-box" action="<?= $actionUrl ?>" method="POST">
            <?= csrf_field() ?>

            <p class="input-placeholder">E-MAIL</p>
            <input type="email" id="email" name="email" autocomplete="email" required>

            <button id="forgot-submit" type="submit">Send PIN</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>