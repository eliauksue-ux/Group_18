<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';

$aid = (int)($_GET['id'] ?? 0);
$pdo = get_db();

$sql = "SELECT a.*, i.title, i.item_id, i.item_description, i.start_price, i.reserve_price,
        (SELECT image_url FROM ItemImage im WHERE im.item_id=i.item_id ORDER BY is_primary DESC, image_id ASC LIMIT 1) AS img
        FROM Auction a JOIN Item i ON i.item_id=a.item_id
        WHERE a.auction_id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$aid]);
$au = $stmt->fetch();
if (!$au) { echo "<p>Not found.</p>"; require __DIR__.'/includes/footer.php'; exit; }

// 最近出价
$bids = $pdo->prepare("SELECT b.*, u.username FROM Bid b JOIN Users u ON u.user_id=b.bidder_id
                       WHERE b.auction_id=? ORDER BY b.bid_amount DESC, b.bid_time ASC LIMIT 10");
$bids->execute([$aid]);
?>
<h2><?= htmlspecialchars($au['title']) ?></h2>
<div class="gallery">
  <?php if ($au['img']): ?>
    <img class="main" src="<?= htmlspecialchars($au['img']) ?>" alt="">
  <?php endif; ?>
</div>

<p><b>Current price:</b> £<?= number_format($au['current_price'],2) ?></p>
<p><b>Ends:</b> <?= htmlspecialchars($au['end_date']) ?></p>
<p><?= nl2br(htmlspecialchars($au['item_description'])) ?></p>

<h3>Top bids</h3>
<table class="form">
  <tr><th>Bidder</th><th>Amount</th><th>Time</th></tr>
  <?php foreach($bids as $b): ?>
  <tr>
    <td><?= htmlspecialchars($b['username']) ?></td>
    <td>£<?= number_format($b['bid_amount'],2) ?></td>
    <td><?= htmlspecialchars($b['bid_time']) ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php if ($au['status']==='ongoing'): ?>
  <?php if (current_user() && current_user()['role']==='buyer'): ?>
    <h3>Place a bid</h3>
    <form class="form" method="post" action="bid_process.php">
      <input type="hidden" name="auction_id" value="<?= (int)$au['auction_id'] ?>">
      <label>Your bid (must be > current price)</label>
      <input name="bid_amount" type="number" step="0.01" min="<?= number_format($au['current_price']+0.01,2,'.','') ?>" required>
      <button class="btn" type="submit">Bid</button>
    </form>
  <?php else: ?>
    <p class="form">Login as <b>buyer</b> to place a bid.</p>
  <?php endif; ?>
<?php else: ?>
  <p class="form"><b>Completed.</b> Winner: 
  <?php
    if ($au['winner_id']) {
      $w = $pdo->prepare("SELECT username FROM Users WHERE user_id=?");
      $w->execute([$au['winner_id']]);
      $wn = $w->fetchColumn();
      echo htmlspecialchars($wn ?: ('#'.$au['winner_id']));
    } else echo '-';
  ?>
  , Final price: £<?= number_format($au['final_price'],2) ?></p>
<?php endif; ?>

<?php require_once __DIR__.'/includes/footer.php'; ?>
