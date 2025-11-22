<?php
date_default_timezone_set('Europe/London');

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
    <a href="index.php"><b>Browse</b></a>

    <?php if ($user && $user['role'] === 'buyer'): ?>
      <a href="watchlist.php">Watchlist</a>
      <a href="recommendations.php">Recommendations</a>
    <?php endif; ?>

    <?php if ($user && $user['role'] === 'seller'): ?>
      <a href="item_create.php">New Item</a>
      <a href="auction_create.php">New Auction</a>
      <a href="my_auctions.php">My auctions</a>
    <?php endif; ?>
  </div>

  <div>
  <?php if ($user): ?>

      <?php
      // 使用外层页面提供的 $pdo
      if (!isset($pdo)) {
          require_once __DIR__ . '/database.php';
          $pdo = get_db();
      }

      $stmt = $pdo->prepare("SELECT COUNT(*) FROM Notifications WHERE user_id=? AND is_read=0");
      $stmt->execute([$user['user_id']]);
      $unread_count = (int)$stmt->fetchColumn();
      ?>

      <a href="notifications.php" class="notif-link">
        🔔 Notifications
        <?php if ($unread_count > 0): ?>
          <span class="notif-badge"><?= $unread_count ?></span>
        <?php endif; ?>
      </a>

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