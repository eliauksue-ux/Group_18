<?php
// includes/header.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/flash.php';

$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Auction demo</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <div>
    <a href="index.php" class="brand">Auction</a>
    <a href="index.php">Browse</a>

    <?php if ($user && $user['role'] === 'buyer'): ?>
      <a href="watchlist.php">Watchlist</a>
    <?php endif; ?>

    <?php if ($user && $user['role'] === 'seller'): ?>
      <a href="item_create.php">New Item</a>
      <a href="auction_create.php">New Auction</a>
      <a href="my_auctions.php">My auctions</a>
    <?php endif; ?>
  </div>

  <div>
    <?php if ($user): ?>
      <span>Hi, <?= htmlspecialchars($user['username']) ?></span>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="register.php">Register</a>
      <a href="login.php">Login</a>
    <?php endif; ?>
  </div>
</header>

<main class="container">
<?php flash_show(); ?>
