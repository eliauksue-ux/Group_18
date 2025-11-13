<?php require_once __DIR__.'/includes/header.php'; ?>
<h2>Register</h2>
<form class="form" method="post" action="register_process.php">
  <label>Email</label>
  <input name="email" type="email" required>
  <label>Username</label>
  <input name="username" required>
  <label>Password</label>
  <input name="password" type="password" required>
  <label>Role</label>
  <select name="role">
    <option value="buyer">buyer</option>
    <option value="seller">seller</option>
  </select>
  <button class="btn" type="submit">Create account</button>
</form>
<?php require_once __DIR__.'/includes/footer.php'; ?>
