<?php
// includes/header.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__.'/flash.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Auction</title>
  <link rel="stylesheet" href="/auction/assets/style.css">
</head>
<body>
<header class="topbar">
  <a href="/auction/index.php" class="brand">Auction</a>
  <nav>
    <a href="/auction/index.php">Browse</a>
    <?php if (!empty($_SESSION['user'])): ?>
      <a href="/auction/item_create.php">New Item</a>
      <a href="/auction/auction_create.php">New Auction</a>
      <span class="user">Hi, <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
      <a href="/auction/logout.php">Logout</a>
    <?php else: ?>
      <a href="/auction/register.php">Register</a>
      <a href="/auction/login.php">Login</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
  <?php flash_show(); ?>
