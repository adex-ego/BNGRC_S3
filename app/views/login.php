<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="container" id="container">
  <div class="form-container sign-up-container">
    <form action="/register" method="post" id="registerForm" novalidate>
      <span>Inscrivez-vous pour avoir un compte</span>
      <?php if (!empty($register_error)): ?>
        <p class="error" style="color: #c0392b; margin: 8px 0;">
          <?= htmlspecialchars($register_error, ENT_QUOTES, 'UTF-8') ?>
        </p>
      <?php endif; ?>
      <?php if (!empty($register_success)): ?>
        <p class="success" style="color: #27ae60; margin: 8px 0;">
          <?= htmlspecialchars($register_success, ENT_QUOTES, 'UTF-8') ?>
        </p>
      <?php endif; ?>
      <p class="client-error" aria-live="polite"></p>
      <input type="text" name="username" placeholder="Name" minlength="3" maxlength="50" required />
      <input type="email" name="email" placeholder="Email" maxlength="255" value="<?= htmlspecialchars($default_email ?? '', ENT_QUOTES, 'UTF-8') ?>" required />
      <input type="password" name="password" placeholder="Password" minlength="6" maxlength="255" required />
      <button>Sign Up</button>
    </form>
  </div>
  <div class="form-container sign-in-container">
    <form action="/login" method="post" id="loginForm" novalidate>
      <h1>Sign in</h1>
      <span>Connectez-vous pour continuer</span>
      <?php if (!empty($error)): ?>
        <p class="error" style="color: #c0392b; margin: 8px 0;">
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
      <?php endif; ?>
      <p class="client-error" aria-live="polite"></p>
      <input type="email" name="email" placeholder="Email" maxlength="255" required />
      <input type="password" name="password" placeholder="Password" minlength="6" maxlength="255" required />
      <a href="#">Forgot your password?</a>
      <button>Sign In</button>
    </form>
  </div>
  <div class="overlay-container">
    <div class="overlay">
      <div class="overlay-panel overlay-left">
        <h1>Welcome Back!</h1>
        <p>To keep connected with us please login with your personal info</p>
        <button class="ghost" id="signIn">Sign In</button>
      </div>
      <div class="overlay-panel overlay-right">
        <h1>Hello, Friend!</h1>
        <p>Enter your personal details and start journey with us</p>
        <button class="ghost" id="signUp">Sign Up</button>
      </div>
    </div>
  </div>
</div>

    <script src="assets/js/script.js"></script>
    
</body>
</html>