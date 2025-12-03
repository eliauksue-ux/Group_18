<?php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function current_user() {
  return $_SESSION['user'] ?? null;
}
function require_login(): void {
  if (!current_user()) {
    header('Location: /auction/login.php');
    exit;
  }
}
function login_user(array $row): void {
  $_SESSION['user'] = [
    'user_id'  => $row['user_id'],
    'username' => $row['username'],
    'role'     => $row['role'],
  ];
}
function logout_user(): void {
  $_SESSION = [];
  session_destroy();
}
