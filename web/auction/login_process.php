<?php
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';

$user = trim($_POST['username'] ?? '');
$pass = $_POST['password'] ?? '';

$pdo = get_db();
$stmt = $pdo->prepare("SELECT * FROM Users WHERE username=? LIMIT 1");
$stmt->execute([$user]);
$row = $stmt->fetch();

$ok = false;
if ($row) {
  // Compatible: verify if hash is saved; If it is clear text, compare it directly
  $ok = password_verify($pass, $row['password']) || $pass === $row['password'];
}
if (!$ok) {
  flash_set('error','Invalid credentials');
  header('Location: login.php'); exit;
}
login_user($row);
flash_set('ok','Welcome back!');
header('Location: index.php');
