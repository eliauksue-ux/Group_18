<?php
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/flash.php';

$email    = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$pass_raw = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? 'buyer';

if (!$email || !$username || !$pass_raw) {
  flash_set('error','Missing fields');
  header('Location: register.php'); exit;
}

$pdo = get_db();
try {
  $stmt = $pdo->prepare("INSERT INTO Users(email, username, password, role) VALUES (?,?,?,?)");
  // 存哈希（你现有测试账号还是明文，互不影响）
  $stmt->execute([$email, $username, password_hash($pass_raw, PASSWORD_DEFAULT), $role]);
  flash_set('ok','Registered. Please login.');
  header('Location: login.php'); exit;
} catch (Throwable $e) {
  flash_set('error','Register failed: '.$e->getMessage());
  header('Location: register.php'); exit;
}
