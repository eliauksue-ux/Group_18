<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';
require_login();

$aid = (int)($_GET['auction_id'] ?? 0);
$pdo = get_db();

// 查拍卖与赢家
$au = $pdo->prepare("SELECT a.*, i.title FROM Auction a JOIN Item i ON i.item_id=a.item_id WHERE a.auction_id=?");
$au->execute([$aid]);
$au = $au->fetch();

if (!$au) { echo "<p>Not found.</p>"; require __DIR__.'/includes/footer.php'; exit; }
if ($au['status']!=='completed' || !$au['winner_id']) {
  echo "<p>Only completed auction with a winner can pay.</p>"; require __DIR__.'/includes/footer.php'; exit;
}
?>
<h2>Pay for: <?= htmlspecialchars($au['title']) ?></h2>
<p>Final price: £<?= number_format($au['final_price'],2) ?></p>
<?php if (current_user()['user_id'] != $au['winner_id']): ?>
  <p>Only the winner can pay.</p>
<?php else: ?>
  <form class="form" method="post" action="payment.php?auction_id=<?= (int)$aid ?>">
    <label>Payment method</label>
    <select name="method">
      <option>PayPal</option>
      <option>CreditCard</option>
      <option>BankTransfer</option>
      <option>ApplePay</option>
    </select>
    <button class="btn" type="submit">Pay now (demo)</button>
  </form>
  <?php
    if ($_SERVER['REQUEST_METHOD']==='POST') {
      $pdo->prepare("INSERT INTO Payment(user_id,auction_id,item_id,paid_amount,payment_time,payment_method,status)
                     VALUES (?,?,?, ?, NOW(), ?, 'completed')")
          ->execute([$au['winner_id'],$aid,$au['item_id'],$au['final_price'], $_POST['method']]);
      flash_set('ok','Payment recorded (demo).');
      header('Location: auction.php?id='.$aid);
      exit;
    }
  ?>
<?php endif; ?>
<?php require_once __DIR__.'/includes/footer.php'; ?>
