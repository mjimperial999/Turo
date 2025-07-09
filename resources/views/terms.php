<?php $title="Terms & Conditions"; include __DIR__.'/../partials/head.php'; ?>
</head>
<body>
<div class="screen-login">
  <div class="login admin">
    <div class="login-container" style="max-width:480px">
      <h4>Confidentiality Agreement</h4>
      <p>The Turo Tutorship Platform collects … <em>(full text you supplied)</em></p>

      <form action="/terms/accept" method="POST">
        <?= csrf_field() ?>
        <label style="display:block;margin:.6rem 0">
          <input type="radio" name="agree" value="1" required> I understand and agree.
        </label>
        <label style="display:block;margin:.6rem 0">
          <input type="radio" name="agree" value="0" disabled> I do not agree.
        </label>
        <button class="btn btn-primary">Continue</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
