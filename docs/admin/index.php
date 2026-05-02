<?php
session_start();

define('ADMIN_PASSWORD', 'cairn2026');
define('SESSION_KEY', 'cairn_admin_auth');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION[SESSION_KEY] = true;
        header('Location: dashboard.php');
        exit;
    }
    $error = true;
}

if (!empty($_SESSION[SESSION_KEY])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cairn CMS — Sign In</title>
<link rel="stylesheet" href="admin.css">
</head>
<body class="login-page">
  <div class="login-wrap">
    <div class="login-logo">
      <img src="../homepage-assets/cairn-mark-vibrant.png" alt="Cairn" class="login-mark">
      <div class="login-wordmark">
        <span class="lw-cairn">Cairn</span>
        <span class="lw-advisory">Advisory</span>
      </div>
    </div>
    <h1 class="login-title">Content Studio</h1>
    <?php if (!empty($error)): ?>
      <p class="login-error">Wrong password. Try again.</p>
    <?php endif; ?>
    <form method="post" class="login-form">
      <label class="field-label" for="password">Password</label>
      <input type="password" id="password" name="password" class="field-input" autofocus autocomplete="current-password" placeholder="Enter password">
      <button type="submit" class="btn-primary">Sign In →</button>
    </form>
  </div>
</body>
</html>
