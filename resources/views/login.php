<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Turo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="css/styles.css">
  <style>
    html,
    body {
      width: 100%;
      height: 100%;
      margin: 0 auto;
    }

    .login-page-screen {
      width: 100%;
      height: 100%;
      padding: 0 auto;

      display: flex;
    }

    .login-side-container {
      background-color: #C9000A;
      width: 40%;
      height: 100%;
      padding: 0 auto;

      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: nowrap;
    }

    .login-side-con-box {
      width: 95%;
      max-width: 30em;
      min-height: 70%;
      height: 80%;
      padding: 0 2em 0 2em;

      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .login-side-logo-con {
      padding: 0.5em 0 auto;
    }

    .login-side-form {
      margin-top: 1.4em;
      padding: 0.8em 1.8em 0.4em 1.8em;
      width: 100%;
      height: 100%;
      background-color: #FFFFFF;
      border-radius: 0.2em;

      display: flex;
      flex-direction: column;
      align-items: center;

      box-shadow: rgba(0, 0, 0, 0.15) 1.95rem 1.95rem 2.6rem;
    }

    #login-title-font {
      padding: 0.8em 0 0.4em 0;
      font-family: Albert-Sans, sans-serif;
      font-weight: bold;
      color: #FFB61D;
      text-align: center;
    }

    .login-side-decoration {
      width: 60%;
      height: 100%;
      padding: 0 auto;

      background-image: url('images/login-page.jpeg');
      background-repeat: no-repeat;
      background-position-x: right;
      background-position-y: top;
      background-size: cover;

      box-shadow: 116px 10px 177px -64px rgba(0, 0, 0, 0.71) inset;
      -moz-box-shadow: 116px 10px 177px -64px rgba(0, 0, 0, 0.71) inset;
    }

    .login-form-box {
      width: 100%;
      height: 100%;

      font-family: Albert-Sans, sans-serif;
      display: flex;
      flex-direction: column;
    }

    input {
      width: 100%;
      height: 2.5em;
      margin-bottom: 1.2em;

      padding: 0em 1em 0em 1em;
      background-color: rgb(243, 243, 243);
      border: solid rgb(181, 181, 181) 1px;
      border-radius: 0.5em;
      box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px;

      transition: all 0.3s ease 0s;
    }

    input[type="text"],
    input[type="password"] {
      color: rgb(79, 79, 79);
      font-size: 1em;
    }

    input:hover {
      background-color: rgb(236, 236, 236);
      transition: all 0.3s ease 0s;
    }

    .input-placeholder {
      margin: 0 0 0.25em 0;
      font-family: Albert-Sans, sans-serif;
      font-size: 0.75em;
      font-weight: bold;
    }

    #login-submit {
      margin: 1em auto;
      width: 6em;
      height: 2.5em;
      padding: 0em 0.5em 0em 0.5em;
      background-color: #FFB61D;
      border: solid rgb(220, 161, 32) 1px;
      border-radius: 0.5em;
      box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px;
      color: #FFFFFF;

      transition: all 0.3s ease 0s;
    }

    #login-submit:hover {
      background-color: rgb(232, 167, 29);
      border: solid rgb(203, 148, 30) 1px;
      border-radius: 0.5em;

      transition: all 0.3s ease 0s;
    }

    #forgot-password {
      width: auto;
      height: auto;
      color: #FFB61D;
      font-size: 0.9em;
    }

    #forgot-password:hover {
      color: rgb(233, 168, 27);
      font-size: 0.9em;
    }
  </style>
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
          <h4 id="login-title-font">Welcome Back!<br>LOGIN to Turo</h4>
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