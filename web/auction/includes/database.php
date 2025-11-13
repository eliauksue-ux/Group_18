<?php
// includes/database.php
function get_db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $host = '127.0.0.1';
  $db   = 'auction_db';
  $user = 'root';
  $pass = ''; // XAMPP 默认空密码；如果你改了这里填新密码
  $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

  $opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];
  $pdo = new PDO($dsn, $user, $pass, $opt);
  return $pdo;
}
