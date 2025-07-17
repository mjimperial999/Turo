<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$title = "Login";
include __DIR__ . '/partials/head.php';  ?>
<style>

</style>
</head>
<?php
$loginUrl = app()->environment('production')
  ? secure_url('/auth')
  : url('/auth');
?>

<body>

  <div class="screen-login">
    <div class="login">
      <div class="login-container">
        <div class="login-logo">
          <img src="icons/title-logo.svg" width="200em" height="auto">
        </div>
        <div class="login-form">

          <div class="content text-center">
            <div class="header">
              <div class="text title">
                <h4> Welcome Back! </h4>
                <h4> Login to Turo </h4>

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
              <br>
            <?php elseif (session()->has('success')): ?>
              <div class="alert alert-success alert-message" role="alert">
                <?= session('success') ?>
              </div>
              <br>
            <?php endif; ?>
          </div>
          <form class="login-form-box" action="<?= $loginUrl ?>" method="POST">
            <?= csrf_field() ?>
            <p class="input-placeholder">EMAIL</p>
            <input type="text" id="email" name="email" required />

            <p class="input-placeholder">PASSWORD</p>
            <input type="password" id="password" name="password" required />

            <a id="forgot-password" href="/forgot-password">Forgot Password?</a>
            <button id="login-submit" type="submit">Sign In</button>
          </form>
        </div>
      </div>

    </div>
    <div class="login-img-background mobile-display-disappear">

    </div>
  </div>

</body>

</html>
