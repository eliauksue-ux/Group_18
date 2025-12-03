<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';

$pdo = get_db();

/* Read get parameters: search keywords and Classification&sorting method */
$keyword = trim($_GET['q'] ?? '');
$catId   = (int)($_GET['category_id'] ?? 0);
$sort    = $_GET['sort'] ?? 'default';
$sort    = in_array($sort, ['price_desc', 'price_asc'], true) ? $sort : 'default';

/* Query item and latest auction */
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

// Search by title
if ($keyword !== '') {
  $conds[]  = "i.title LIKE ?";
  $params[] = '%'.$keyword.'%';
}

// Filter by category
if ($catId > 0) {
  $conds[]  = "i.category_id = ?";
  $params[] = $catId;
}

if ($conds) {
  $sql .= " WHERE ".implode(" AND ", $conds);
}

/**
 * Sorting logic：
 *  - default：sort by status (in progress/not started/no auction/closed)+time
 *  - price_desc：from high to low according to current_price 
 *  - price_asc：from low to high according to current_price 
 */
if ($sort === 'price_desc') {
  $sql .= "
  ORDER BY
    la.current_price IS NULL,     
    la.current_price DESC,
    i.item_id ASC
  ";
} elseif ($sort === 'price_asc') {
  $sql .= "
  ORDER BY
    la.current_price IS NULL,
    la.current_price ASC,
    i.item_id ASC
  ";
} else {
  $sql .= "
  ORDER BY
    CASE
      WHEN la.auction_id IS NOT NULL
           AND la.start_date <= NOW()
           AND la.end_date   >  NOW()
        THEN 0
      WHEN la.auction_id IS NOT NULL
           AND la.start_date > NOW()
        THEN 1
      WHEN la.auction_id IS NULL
        THEN 2
      ELSE 3
    END,
    la.end_date IS NULL,
    la.end_date ASC,
    i.item_id ASC
  ";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

/* Format interval */
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

// Category drop-down menu
$cats = $pdo->query("SELECT category_id, category_name FROM Category ORDER BY category_name")
            ->fetchAll();

$now  = time();
$user = current_user();

// Watchlist of current buyer
$watchIds = [];
if ($user && $user['role'] === 'buyer') {
  $w = $pdo->prepare("SELECT auction_id FROM Watchlist WHERE user_id=?");
  $w->execute([$user['user_id']]);
  $watchIds = array_column($w->fetchAll(), 'auction_id');
}
?>

<style>
.card .title {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 3px;
}

.card .price {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 6px;
}

.card .meta {
    font-size: 0.85rem;
    color: #555;
    line-height: 1.4;
}

.card .meta b {
    color: #333;
}
</style>

<h2>All items & auctions</h2>

<!-- Search, sort filter and sort -->
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

  <select name="sort" aria-label="Sort items">
    <option value="default" <?= $sort === 'default' ? 'selected' : '' ?>>
      Sort by time (default)
    </option>
    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>
      Price: high to low
    </option>
    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>
      Price: low to high
    </option>
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

      // 状态逻辑
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

        <!-- image -->
        <img src="<?= htmlspecialchars($img) ?>" alt="">

        <div class="p">

          <!-- Large title -->
          <div class="title"><?= htmlspecialchars($r['title']) ?></div>

          <!-- Bold price -->
          <div class="price">
            <?= $priceLabel ?>
          </div>

          <!-- Status and time -->
          <div class="meta">
            <b>Status:</b> <?= htmlspecialchars($displayStatus) ?><br>

            <?php
              $startPretty = $r['start_date']
                              ? date("M j, Y H:i", strtotime($r['start_date']))
                              : "—";

              $endPretty = $r['end_date']
                              ? date("M j, Y H:i", strtotime($r['end_date']))
                              : "—";
            ?>

            <b>Starts:</b> <?= $startPretty ?><br>
            <b>Ends:</b> <?= $endPretty ?>
          </div>

        </div>
      </a>


            <!-- ⭐ Watchlist: add and remove (for buyer only) -->
      <?php if ($user && $user['role'] === 'buyer' && $hasAuction): ?>
        <?php if ($inWatchlist): ?>
          <!-- Remove -->
          <form class="watch-form" method="post" action="watchlist_remove.php"
                onsubmit="event.stopPropagation();">
            <input type="hidden" name="auction_id" value="<?= (int)$r['auction_id'] ?>">
            <button class="watch-btn in" type="submit" onclick="event.stopPropagation();">
              ★ Remove
            </button>
          </form>
        <?php else: ?>
          <!-- Add -->
          <form class="watch-form" method="post" action="watchlist_add.php"
                onsubmit="event.stopPropagation();">
            <input type="hidden" name="auction_id" value="<?= (int)$r['auction_id'] ?>">
            <button class="watch-btn" type="submit" onclick="event.stopPropagation();">
              ☆ Add to watchlist
            </button>
          </form>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Delete button visible only to admin -->
      <?php if ($user && $user['role'] === 'admin'): ?>
        <form class="admin-delete-form"
              method="post"
              action="item_delete.php"
              onsubmit="event.stopPropagation(); return confirm('Delete this item and all its auctions permanently?');">
          <input type="hidden" name="item_id" value="<?= (int)$r['item_id'] ?>">
          <button class="btn outline" type="submit">
            Delete item
          </button>
        </form>
      <?php endif; ?>

      
    </div>


  <?php endforeach; ?>
</div>

<?php require_once __DIR__.'/includes/footer.php'; ?>
