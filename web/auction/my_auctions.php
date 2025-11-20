<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/database.php';
require_login();

$user = current_user();
if ($user['role'] !== 'seller') {
  echo "<p>Only sellers can view this page.</p>";
  require_once __DIR__.'/includes/footer.php';
  exit;
}

$pdo = get_db();

// 取出该 seller 的所有拍卖，连同 item 信息 + 封面图
$sql = "
SELECT
  a.auction_id,
  a.item_id,
  a.start_date,
  a.end_date,
  a.status,
  a.current_price,
  i.title,
  i.start_price,
  i.reserve_price,
  (
    SELECT image_url
    FROM ItemImage im
    WHERE im.item_id = i.item_id
    ORDER BY im.is_primary DESC, im.image_id ASC
    LIMIT 1
  ) AS img
FROM Auction a
JOIN Item i ON i.item_id = a.item_id
WHERE i.seller_id = ?
ORDER BY a.end_date ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user['user_id']]);
$rows = $stmt->fetchAll();

$now = time();
?>

<h2>My auctions</h2>

<?php if (!$rows): ?>
  <p>You have not created any auctions yet.</p>
<?php endif; ?>

<?php foreach ($rows as $r): ?>
  <?php
    $startTs = strtotime($r['start_date']);
    $endTs   = strtotime($r['end_date']);

    $hasStarted  = $startTs !== false && $now >= $startTs;
    $hasEnded    = $endTs   !== false && $now >= $endTs;
    $canEditTime  = !$hasEnded;   // 已经结束的就不让改时间了
    $canEditPrice = !$hasStarted; // 还没开始才允许改价格

    // 统一逻辑状态：upcoming / ongoing / completed
    $displayStatus = 'upcoming';
    if ($startTs !== false && $endTs !== false) {
      if ($now < $startTs) {
        $displayStatus = 'upcoming';
      } elseif ($now < $endTs) {
        $displayStatus = 'ongoing';
      } else {
        $displayStatus = 'completed';
      }
    } elseif ($hasEnded) {
      $displayStatus = 'completed';
    }

    $img = $r['img'] ?: '/auction/assets/placeholder.jpg';
  ?>

  <form class="form" method="post" action="my_auctions_update.php">
    <input type="hidden" name="auction_id" value="<?= (int)$r['auction_id'] ?>">

    <!-- 缩略图 -->
    <img src="<?= htmlspecialchars($img) ?>" alt="" class="my-auction-thumb">

    <h3>
      [#<?= (int)$r['auction_id'] ?>] <?= htmlspecialchars($r['title']) ?>
    </h3>

    <p>
      Status:
      <b><?= htmlspecialchars($displayStatus) ?></b>,
      Current price: £<?= number_format($r['current_price'], 2) ?>
    </p>

    <!-- 拍卖时间 -->
    <label>Start time</label>
    <input
      type="datetime-local"
      name="start_date"
      value="<?= date('Y-m-d\TH:i', $startTs) ?>"
      <?= $canEditTime ? '' : 'readonly' ?>
      required
    >

    <label>End time</label>
    <input
      type="datetime-local"
      name="end_date"
      value="<?= date('Y-m-d\TH:i', $endTs) ?>"
      <?= $canEditTime ? '' : 'readonly' ?>
      required
    >

    <?php if ($canEditTime === false): ?>
      <p><i>This auction has ended, time cannot be changed.</i></p>
    <?php endif; ?>

    <!-- 价格设置：只有还没开始的拍卖可以改 -->
    <label>Start price</label>
    <input
      type="number"
      step="0.01"
      name="start_price"
      value="<?= htmlspecialchars($r['start_price']) ?>"
      <?= $canEditPrice ? '' : 'readonly' ?>
      required
    >

    <label>Reserve price</label>
    <input
      type="number"
      step="0.01"
      name="reserve_price"
      value="<?= htmlspecialchars($r['reserve_price']) ?>"
      <?= $canEditPrice ? '' : 'readonly' ?>
      required
    >

    <?php if ($canEditPrice): ?>
      <p><i>This auction has not started yet. You can still change start price and reserve price.</i></p>
    <?php else: ?>
      <p><i>This auction has already started. Prices cannot be changed.</i></p>
    <?php endif; ?>

    <button class="btn" type="submit">Save changes</button>
  </form>

<?php endforeach; ?>

<?php require_once __DIR__.'/includes/footer.php'; ?>
