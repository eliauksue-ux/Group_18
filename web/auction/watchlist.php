<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/database.php';

require_login();

$user = current_user();
if ($user['role'] !== 'buyer') {
  echo "<p>Only buyers can view watchlist.</p>";
  require_once __DIR__.'/includes/footer.php';
  exit;
}

$pdo = get_db();

$sql = "
SELECT
  w.watch_id,
  a.auction_id,
  a.start_date,
  a.end_date,
  a.status,
  a.current_price,
  i.item_id,
  i.title,
  (
    SELECT image_url
    FROM ItemImage im
    WHERE im.item_id = i.item_id
    ORDER BY im.is_primary DESC, im.image_id ASC
    LIMIT 1
  ) AS img
FROM Watchlist w
JOIN Auction a ON a.auction_id = w.auction_id
JOIN Item    i ON i.item_id    = a.item_id
WHERE w.user_id = ?
ORDER BY a.end_date ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user['user_id']]);
$rows = $stmt->fetchAll();

$now = time();

/* 与首页同样的倒计时格式函数 */
function wl_format_interval(int $sec): string {
  if ($sec < 0) $sec = 0;
  $d = intdiv($sec, 86400); $sec %= 86400;
  $h = intdiv($sec, 3600);  $sec %= 3600;
  $m = intdiv($sec, 60);
  if ($d > 0) return sprintf('%dd %02dh %02dm', $d, $h, $m);
  return sprintf('%02dh %02dm', $h, $m);
}
?>

<h2>My watchlist</h2>

<?php if (!$rows): ?>
  <p>You have not added any auctions to your watchlist yet.</p>
<?php endif; ?>

<div class="grid">
  <?php foreach ($rows as $r): ?>
    <?php
      $img = $r['img'] ?: '/auction/assets/placeholder.jpg';

      $startTs = $r['start_date'] ? strtotime($r['start_date']) : null;
      $endTs   = $r['end_date']   ? strtotime($r['end_date'])   : null;

      $displayStatus = 'upcoming';
      $badgeClass    = 'status-noauction';
      $badgeText     = 'No auction time';

      if ($startTs && $endTs) {
        if ($now < $startTs) {
          $displayStatus = 'upcoming';
          $badgeClass    = 'status-future';
          $badgeText     = 'Starts in '.wl_format_interval($startTs - $now);
        } elseif ($now < $endTs) {
          $displayStatus = 'ongoing';
          $badgeClass    = 'status-ongoing';
          $badgeText     = 'Ends in '.wl_format_interval($endTs - $now);
        } else {
          $displayStatus = 'completed';
          $badgeClass    = 'status-ended';
          $badgeText     = 'Ended';
        }
      }

      $startsLabel = $r['start_date'] ?: '—';
      $endsLabel   = $r['end_date']   ?: '—';
      $priceLabel  = $r['current_price'] !== null
                     ? '£'.number_format($r['current_price'], 2)
                     : '—';
    ?>

    <div class="card">
      <div class="card-status <?= $badgeClass ?>">
        <?= htmlspecialchars($badgeText) ?>
      </div>

      <a class="card-link" href="auction.php?id=<?= (int)$r['auction_id'] ?>">
        <img src="<?= htmlspecialchars($img) ?>" alt="">

        <div class="p">
          <div class="title"><?= htmlspecialchars($r['title']) ?></div>
          <div class="price"><?= $priceLabel ?></div>
          <div>Status: <?= htmlspecialchars($displayStatus) ?></div>
          <div>Starts: <?= htmlspecialchars($startsLabel) ?></div>
          <div>Ends: <?= htmlspecialchars($endsLabel) ?></div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__.'/includes/footer.php'; ?>
