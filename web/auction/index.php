<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';

$pdo = get_db();

/**
 * 读取 GET 参数：搜索关键字 & 分类
 */
$keyword = trim($_GET['q'] ?? '');
$catId   = (int)($_GET['category_id'] ?? 0);

/**
 * 选出每个 item 最新的一场 auction（按 end_date 最大算“最新”），
 * 然后把所有 item 都列出来：
 *  - 有拍卖的：带 start / end / status / current_price
 *  - 没拍卖的：auction 字段为 NULL
 *
 * 排序规则：
 *  1) 正在进行中的拍卖（status='ongoing' 且 now 在 start–end 之间），按结束时间升序
 *  2) 还没开始的拍卖（start_date > now），按开始时间升序
 *  3) 没有拍卖的 item
 *  4) 已经结束的拍卖
 */

$sql = "
SELECT
  i.item_id,
  i.title,
  i.category_id,
  la.auction_id,
  la.start_date,
  la.end_date,
  la.status        AS auction_status,
  la.current_price,
  (
    SELECT image_url
    FROM ItemImage im
    WHERE im.item_id = i.item_id
    ORDER BY im.is_primary DESC, im.image_id ASC
    LIMIT 1
  ) AS img
FROM Item i
LEFT JOIN (
  SELECT a1.*
  FROM Auction a1
  JOIN (
    SELECT item_id, MAX(end_date) AS max_end
    FROM Auction
    GROUP BY item_id
  ) x
    ON x.item_id = a1.item_id AND x.max_end = a1.end_date
) la
  ON la.item_id = i.item_id
";

$params = [];
$conds  = [];

// 按标题搜索
if ($keyword !== '') {
  $conds[]  = "i.title LIKE ?";
  $params[] = '%'.$keyword.'%';
}

// 按分类过滤
if ($catId > 0) {
  $conds[]  = "i.category_id = ?";
  $params[] = $catId;
}

if ($conds) {
  $sql .= " WHERE ".implode(" AND ", $conds);
}

$sql .= "
ORDER BY
  CASE
    WHEN la.auction_id IS NOT NULL
         AND la.status = 'ongoing'
         AND la.start_date <= NOW()
         AND la.end_date   >  NOW()
      THEN 0              -- 正在进行
    WHEN la.auction_id IS NOT NULL
         AND la.start_date > NOW()
      THEN 1              -- 未来将开始
    WHEN la.auction_id IS NULL
      THEN 2              -- 还没有创建拍卖的 item
    ELSE 3                -- 已经结束的拍卖
  END,
  la.end_date IS NULL,
  la.end_date ASC,
  i.item_id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

/**
 * 把秒数格式化成 “Xd HH:MM” / “HH:MM”
 */
function format_interval(int $sec): string {
  if ($sec < 0) $sec = 0;
  $d = intdiv($sec, 86400); $sec %= 86400;
  $h = intdiv($sec, 3600);  $sec %= 3600;
  $m = intdiv($sec, 60);
  if ($d > 0) {
    return sprintf('%dd %02dh %02dm', $d, $h, $m);
  }
  return sprintf('%02dh %02dm', $h, $m);
}

// 分类下拉菜单
$cats = $pdo->query("SELECT category_id, category_name FROM Category ORDER BY category_name")
            ->fetchAll();

$now  = time();
$user = current_user();

// 当前 buyer 的 watchlist（只取 auction_id 列表）
$watchIds = [];
if ($user && $user['role'] === 'buyer') {
  $w = $pdo->prepare("SELECT auction_id FROM Watchlist WHERE user_id=?");
  $w->execute([$user['user_id']]);
  $watchIds = array_column($w->fetchAll(), 'auction_id');
}
?>

<h2>All items & auctions</h2>

<!-- 搜索 + 分类过滤 -->
<form class="search-bar" method="get" action="index.php">
  <input
    type="text"
    name="q"
    placeholder="Search items by title…"
    value="<?= htmlspecialchars($keyword) ?>"
  >
  <select name="category_id">
    <option value="0">All categories</option>
    <?php foreach ($cats as $c): ?>
      <option
        value="<?= (int)$c['category_id'] ?>"
        <?= $catId === (int)$c['category_id'] ? 'selected' : '' ?>
      >
        <?= htmlspecialchars($c['category_name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button class="btn" type="submit">Search</button>
</form>

<div class="grid">
  <?php foreach ($rows as $r): ?>
    <?php
      $hasAuction = !empty($r['auction_id']);
      $img = $r['img'] ?: '/auction/assets/placeholder.jpg';

      $startTs = $r['start_date'] ? strtotime($r['start_date']) : null;
      $endTs   = $r['end_date']   ? strtotime($r['end_date'])   : null;

      // 统一逻辑状态：upcoming / ongoing / completed
      $displayStatus = 'upcoming';
      $badgeClass    = 'status-noauction';
      $badgeText     = 'No auction';

      if ($hasAuction && $startTs && $endTs) {
        if ($now < $startTs) {
          $displayStatus = 'upcoming';
          $badgeClass    = 'status-future';
          $badgeText     = 'Starts in '.format_interval($startTs - $now);
        } elseif ($now < $endTs) {
          $displayStatus = 'ongoing';
          $badgeClass    = 'status-ongoing';
          $badgeText     = 'Ends in '.format_interval($endTs - $now);
        } else {
          $displayStatus = 'completed';
          $badgeClass    = 'status-ended';
          $badgeText     = 'Ended';
        }
      } elseif ($hasAuction) {
        $displayStatus = 'completed';
        $badgeClass    = 'status-ended';
        $badgeText     = 'Ended';
      }

      $startsLabel = $r['start_date'] ?: '—';
      $endsLabel   = $r['end_date']   ?: '—';
      $priceLabel  = $hasAuction && $r['current_price'] !== null
                     ? '£'.number_format($r['current_price'], 2)
                     : '—';

      $inWatchlist = $hasAuction && in_array((int)$r['auction_id'], $watchIds, true);
    ?>

    <div class="card">
      <div class="card-status <?= $badgeClass ?>">
        <?= htmlspecialchars($badgeText) ?>
      </div>

      <a class="card-link" <?= $hasAuction ? 'href="auction.php?id='.(int)$r['auction_id'].'"' : '' ?>>
        <img src="<?= htmlspecialchars($img) ?>" alt="">

        <div class="p">
          <div class="title"><?= htmlspecialchars($r['title']) ?></div>
          <div class="price"><?= $priceLabel ?></div>
          <div>Status: <?= htmlspecialchars($displayStatus) ?></div>
          <div>Starts: <?= htmlspecialchars($startsLabel) ?></div>
          <div>Ends: <?= htmlspecialchars($endsLabel) ?></div>
        </div>
      </a>

      <?php if ($user && $user['role'] === 'buyer' && $hasAuction): ?>
        <form class="watch-form" method="post" action="watchlist_add.php"
              onsubmit="event.stopPropagation();">
          <input type="hidden" name="auction_id" value="<?= (int)$r['auction_id'] ?>">
          <button class="watch-btn <?= $inWatchlist ? 'in' : '' ?>"
                  type="submit"
                  onclick="event.stopPropagation();">
            <?= $inWatchlist ? '★ In watchlist' : '☆ Add to watchlist' ?>
          </button>
        </form>
      <?php endif; ?>
    </div>

  <?php endforeach; ?>
</div>

<?php require_once __DIR__.'/includes/footer.php'; ?>
