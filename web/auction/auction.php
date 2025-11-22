<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';

$aid = (int)($_GET['id'] ?? 0);
$pdo = get_db();

$sql = "SELECT a.*, i.title, i.item_id, i.item_description, i.start_price, i.reserve_price,
        u.username AS seller_username,
        (SELECT image_url FROM ItemImage im WHERE im.item_id=i.item_id ORDER BY is_primary DESC, image_id ASC LIMIT 1) AS img
        FROM Auction a
        JOIN Item i ON i.item_id=a.item_id
        JOIN Users u ON u.user_id=i.seller_id
        WHERE a.auction_id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$aid]);
$au = $stmt->fetch();
if (!$au) { echo "<p>Not found.</p >"; require __DIR__.'/includes/footer.php'; exit; }

// 美化时间格式
$startFormatted = date("M j, Y H:i", strtotime($au['start_date']));
$endFormatted   = date("M j, Y H:i", strtotime($au['end_date']));

// 最近出价
$bids = $pdo->prepare("SELECT b.*, u.username FROM Bid b JOIN Users u ON u.user_id=b.bidder_id
                      WHERE b.auction_id=? ORDER BY b.bid_amount DESC, b.bid_time ASC LIMIT 10");
$bids->execute([$aid]);

// 当前时间
$now   = new DateTime();
$start = new DateTime($au['start_date']);
$end   = new DateTime($au['end_date']);
?>

<style>
.info-card {
    background: #fff;
    padding: 18px 22px;
    border-radius: 10px;
    border: 1px solid #eee;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    margin: 20px 0;
    line-height: 1.7;
}
.info-row b { width: 160px; display: inline-block; }
.bid-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 6px;
}
.bid-table th {
    text-align: left;
    padding: 8px 12px;
    font-weight: 700;
}
.bid-row td {
    background: #fff;
    padding: 10px 12px;
    border-radius: 6px;
}
</style>

<h2><?= htmlspecialchars($au['title']) ?></h2>

<div class="gallery">
  <?php if (!empty($au['img'])): ?>
      <img class="main" src="<?= htmlspecialchars($au['img']) ?>" alt="">
  <?php endif; ?>
</div>

<div class="info-card">
    <div class="info-row"><b>Seller:</b> <?= htmlspecialchars($au['seller_username']) ?></div>
    <div class="info-row"><b>Current price:</b> £<?= number_format($au['current_price'],2) ?></div>
    <div class="info-row"><b>Auction starts:</b> <?= htmlspecialchars($startFormatted) ?></div>
    <div class="info-row"><b>Auction ends:</b> <?= htmlspecialchars($endFormatted) ?></div>
    <div class="info-row"><b>Description:</b> <?= nl2br(htmlspecialchars($au['item_description'])) ?></div>
</div>

<h3>Top bids</h3>

<table class="bid-table">
  <tr>
    <th>Bidder</th>
    <th>Amount</th>
    <th>Time</th>
  </tr>
  <?php foreach($bids as $b): ?>
    <?php $bidTime = date("M j, Y H:i", strtotime($b['bid_time'])); ?>
    <tr class="bid-row">
      <td><?= htmlspecialchars($b['username']) ?></td>
      <td>£<?= number_format($b['bid_amount'],2) ?></td>
      <td><?= htmlspecialchars($bidTime) ?></td>
    </tr>
  <?php endforeach; ?>
</table>

<?php
// ===============================
//   正确状态显示（唯一版本）
// ===============================

// 状态计算来自时间，而不是数据库 status 字段
if ($now < $start): ?>

    <p class="form"><b>Upcoming.</b> Auction has not started yet.</p >

<?php elseif ($now >= $start && $now < $end): ?>

    <p class="form"><b>Ongoing.</b> Auction is currently running.</p >

    <?php if (current_user() && current_user()['role']==='buyer'): ?>
        <h3>Place a bid</h3>
        <form class="form" method="post" action="bid_process.php">
          <input type="hidden" name="auction_id" value="<?= (int)$au['auction_id'] ?>">
          <label>Your bid (must be > current price)</label>
          <input name="bid_amount" type="number" step="0.01"
                 min="<?= number_format($au['current_price']+0.01,2,'.','') ?>" required>
          <button class="btn" type="submit">Bid</button>
        </form>
    <?php else: ?>
        <p class="form">Login as <b>buyer</b> to place a bid.</p >
    <?php endif; ?>

<?php else: ?>

    <?php
        $isSuccess   = ($au['final_price'] !== null && $au['final_price'] >= $au['reserve_price']);
        $statusLabel = $isSuccess ? "Completed" : "Failed";
    ?>

    <p class="form"><b><?= $statusLabel ?>.</b>
        <?php if ($isSuccess): ?>
            Winner:
            <?php
                if ($au['winner_id']) {
                    $w = $pdo->prepare("SELECT username FROM Users WHERE user_id=?");
                    $w->execute([$au['winner_id']]);
                    echo htmlspecialchars($w->fetchColumn());
                } else echo '-';
            ?>
            , Final price: £<?= number_format($au['final_price'],2) ?>
        <?php else: ?>
            No winner. Final price: £<?= number_format($au['final_price'],2) ?>
        <?php endif; ?>
    </p >

<?php endif; ?>

<?php if (current_user() && current_user()['role'] === 'admin'): ?>
  <form class="form" method="post" action="auction_delete.php" onsubmit="return confirm('Are you sure to delete this auction?');">
    <input type="hidden" name="auction_id" value="<?= (int)$au['auction_id'] ?>">
    <button class="btn outline" type="submit">Delete Auction</button>
  </form>
<?php endif; ?>

<?php require_once __DIR__.'/includes/footer.php'; ?>