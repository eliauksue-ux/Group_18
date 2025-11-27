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
  <!-- 左侧：Logo + Browse 下拉 -->
  <div class="topbar-left">
    <!-- Logo：把你的图片存成 assets/logo.png，这里就能显示 -->
    <a href="index.php" class="logo-link">
      <img src="assets/logo.png" alt="Auction logo" class="logo">
    </a>

    <?php if ($user): ?>
      <!-- 登录后：Browse 下拉菜单，内容根据角色不同 -->
      <div class="nav-dropdown">
        <button class="nav-dropdown-toggle" type="button">
          Browse <span class="nav-arrow">▾</span>
        </button>
        <div class="nav-dropdown-menu">
          <?php if ($user['role'] === 'buyer'): ?>
            <a href="index.php">All items</a>
            <a href="watchlist.php">Watchlist</a>
            <a href="recommendations.php">Recommendations</a>
          <?php elseif ($user['role'] === 'seller'): ?>
            <a href="index.php">All items</a>
            <a href="item_create.php">New item</a>
            <a href="auction_create.php">New auction</a>
            <a href="my_auctions.php">My auctions</a>
          <?php else: ?>
            <!-- 其他角色（例如 admin）暂时只给一个主页入口 -->
            <a href="index.php">All items</a>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <!-- 未登录：简单的 Browse 文本，点击回首页 -->
      <a href="index.php" class="brand">Browse</a>
    <?php endif; ?>
  </div>

  <!-- 右侧：通知 + 用户名 + Logout / Login/Register -->
  <div class="topbar-right">
  <?php if ($user): ?>

      <?php
      // 使用外层页面提供的 $pdo，如果没有则这里加载
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

      <span class="username">Hi, <?= htmlspecialchars($user['username']) ?></span>
      <a href="logout.php">Logout</a>

  <?php else: ?>
      <a href="register.php">Register</a>
      <a href="login.php">Login</a>
  <?php endif; ?>
  </div>
</header>

<script>
// 简单的“点击展开 / 收起”下拉菜单逻辑
document.addEventListener('DOMContentLoaded', function () {
  var dropdown = document.querySelector('.nav-dropdown');
  if (!dropdown) return;

  var toggle = dropdown.querySelector('.nav-dropdown-toggle');

  toggle.addEventListener('click', function (e) {
    e.stopPropagation();
    dropdown.classList.toggle('open');
  });

  // 点击页面其他地方时收起菜单
  document.addEventListener('click', function () {
    dropdown.classList.remove('open');
  });
});
</script>

<main class="container">
<?php flash_show(); ?>
