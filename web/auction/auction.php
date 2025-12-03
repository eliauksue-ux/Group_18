<?php

require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';

$pdo  = get_db();
$user = current_user();

$aid = (int)($_GET['id'] ?? 0);
if ($aid <= 0) {
    echo "<p>Auction not found.</p>";
    require __DIR__.'/includes/footer.php';
    exit;
}

// Take auction, items, seller information and main map
$sql = "
SELECT
  a.auction_id,
  a.item_id,
  a.start_date,
  a.end_date,
  a.current_price,
  a.winner_id,
  a.final_price,
  i.title,
  i.item_description,
  i.seller_id,
  u.username AS seller_name,
  (
    SELECT image_url
    FROM ItemImage im
    WHERE im.item_id = i.item_id
    ORDER BY im.is_primary DESC, im.image_id ASC
    LIMIT 1
  ) AS main_image
FROM Auction a
JOIN Item   i ON i.item_id = a.item_id
JOIN Users  u ON u.user_id = i.seller_id
WHERE a.auction_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$aid]);
$au = $stmt->fetch();

if (!$au) {
    echo "<p>Auction not found.</p>";
    require __DIR__.'/includes/footer.php';
    exit;
}

// Calculation status (time based)
$now     = time();
$startTs = $au['start_date'] ? strtotime($au['start_date']) : null;
$endTs   = $au['end_date']   ? strtotime($au['end_date'])   : null;

$statusKey   = 'upcoming';
$statusShort = 'Upcoming';
$statusLong  = 'Upcoming. Auction has not started yet.';

if ($startTs && $endTs) {
    if ($now < $startTs) {
        $statusKey   = 'upcoming';
        $statusShort = 'Upcoming';
        $statusLong  = 'Upcoming. Auction has not started yet.';
    } elseif ($now < $endTs) {
        $statusKey   = 'ongoing';
        $statusShort = 'Ongoing';
        $statusLong  = 'Ongoing. Auction is currently running.';
    } else {
        $statusKey   = 'completed';
        $statusShort = 'Completed';

        if ($au['winner_id']) {
            $wStmt = $pdo->prepare("SELECT username FROM Users WHERE user_id=?");
            $wStmt->execute([$au['winner_id']]);
            $winnerName = $wStmt->fetchColumn() ?: 'unknown';

            $statusLong = 'Completed. Winner: '.$winnerName
                        .' , Final price: £'.number_format((float)$au['final_price'], 2).'.';
        } else {
            $statusLong = 'Completed. No winning bids.';
        }
    }
}

// Read the bid list (the highest price is above)
$bStmt = $pdo->prepare("
    SELECT b.bid_amount, b.bid_time, u.username
    FROM Bid b
    JOIN Users u ON u.user_id = b.bidder_id
    WHERE b.auction_id = ?
    ORDER BY b.bid_amount DESC, b.bid_time ASC
");
$bStmt->execute([$aid]);
$bids = $bStmt->fetchAll();

$mainImage = $au['main_image'] ?: '/auction/assets/placeholder.jpg';

$startPretty = $au['start_date'] ? date("M j, Y H:i", strtotime($au['start_date'])) : '—';
$endPretty   = $au['end_date']   ? date("M j, Y H:i", strtotime($au['end_date']))   : '—';

?>

<h2><?= htmlspecialchars($au['title']) ?></h2>

<div class="gallery">
  <img class="main" src="<?= htmlspecialchars($mainImage) ?>" alt="">
</div>

<div class="detail-status">
  <span class="detail-status-chip <?= htmlspecialchars($statusKey) ?>">
    <?= htmlspecialchars($statusShort) ?>
  </span>
</div>

<div class="form">
  <p><b>Seller:</b> <?= htmlspecialchars($au['seller_name']) ?></p>
  <p><b>Current price:</b> £<?= number_format((float)$au['current_price'], 2) ?></p>
  <p><b>Auction starts:</b> <?= htmlspecialchars($startPretty) ?></p>
  <p><b>Auction ends:</b> <?= htmlspecialchars($endPretty) ?></p>
  <p><b>Description:</b> <?= nl2br(htmlspecialchars($au['item_description'])) ?></p>

  <?php if ($user && $user['role'] === 'admin'): ?>
    <form method="post"
          action="item_delete.php"
          onsubmit="return confirm('Delete this item and all its auctions permanently?');">
      <input type="hidden" name="item_id" value="<?= (int)$au['item_id'] ?>">
      <button class="btn outline" type="submit">Delete item</button>
    </form>
  <?php endif; ?>
</div>

<h3>Top bids</h3>

<?php if ($bids): ?>
  <table class="form" style="border-collapse:collapse; width:100%;">
    <thead>
      <tr>
        <th style="text-align:left; padding:4px 8px;">Bidder</th>
        <th style="text-align:left; padding:4px 8px;">Amount</th>
        <th style="text-align:left; padding:4px 8px;">Time</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bids as $b): ?>
        <tr>
          <td style="padding:4px 8px;"><?= htmlspecialchars($b['username']) ?></td>
          <td style="padding:4px 8px;">£<?= number_format((float)$b['bid_amount'], 2) ?></td>
          <td style="padding:4px 8px;"><?= htmlspecialchars(date("M j, Y H:i", strtotime($b['bid_time']))) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p>No bids yet.</p>
<?php endif; ?>

<p><b><?= htmlspecialchars($statusLong) ?></b></p>

<?php
// Only the auction in progress, 
// and the current user is a buyer and not a seller, can the bidding form be displayed
$canBid = $statusKey === 'ongoing'
          && $user
          && $user['role'] === 'buyer'
          && (int)$user['user_id'] !== (int)$au['seller_id'];

if ($canBid):
  $minBid = max(0.01, (float)$au['current_price'] + 0.01);
?>
  <h3>Place a bid</h3>
  <form class="form" method="post" action="bid_process.php">
    <input type="hidden" name="auction_id" value="<?= (int)$au['auction_id'] ?>">
    <label>Your bid (£)</label>
    <input
      type="number"
      name="bid_amount"
      step="0.01"
      min="<?= number_format($minBid, 2, '.', '') ?>"
      required
    >
    <button class="btn" type="submit">Submit bid</button>
  </form>
<?php elseif ($statusKey === 'ongoing'): ?>
  <p><i>Login as a buyer (and not the seller) to place a bid.</i></p>
<?php endif; ?>

<?php require __DIR__.'/includes/footer.php'; ?>
