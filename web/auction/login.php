<?php require_once __DIR__.'/includes/header.php'; ?>
<h2>Login</h2>
<form class="form" method="post" action="login_process.php">
  <label>Username</label>
  <input name="username" required>
  <label>Password</label>
  <input name="password" type="password" required>
  <button class="btn" type="submit">Login</button>
</form>
<?php require_once __DIR__.'/includes/footer.php'; ?>
