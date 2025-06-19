<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Turo</title>
  <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<?php
    $loginUrl = app()->environment('production') 
        ? secure_url('/auth') 
        : url('/auth');
?>
<body>
  <div class="login-page-screen">
    <div class="login-side-container">
      <div class="login-side-con-box">
        <div class="login-side-logo-con">
          <img src="icons/title-logo.svg" width="200em" height="auto">
        </div>
        <div class="login-side-form">
          <h4 id="login-title-font">Administrator's<br>Panel</h4>
            <?php if (session()->has('error')): ?>
              <div class="alert alert-danger alert-message" role="alert">
                <?= session('error') ?>
              </div>
            <?php elseif (session()->has('success')): ?>
                  <div class="alert alert-success alert-message" role="alert">
                <?= session('success') ?>
              </div>
            <?php endif; ?>
          <form class="login-form-box" action="<?= $loginUrl ?>" method="POST">
            <?= csrf_field() ?>
            <p class="input-placeholder">EMAIL</p>
            <input type="text" id="email" name="email" required />

            <p class="input-placeholder">PASSWORD</p>
            <input type="password" id="password" name="password" required />

            <a id="forgot-password" href="#">Forgot Password?</a>
            <button id="login-submit" type="submit">Sign In</button>
          </form>
        </div>
      </div>
    </div>
    <div class="login-side-decoration">

    </div>
  </div>
</body>

</html>