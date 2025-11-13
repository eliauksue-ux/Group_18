<?php
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';
require_login();

if (current_user()['role'] !== 'buyer') {
  flash_set('error','Only buyers can bid'); header('Location: index.php'); exit;
}

$aid = (int)($_POST['auction_id'] ?? 0);
$amount = (float)($_POST['bid_amount'] ?? 0);

$pdo = get_db();
try {
  $pdo->beginTransaction();

  // 锁拍卖行，拿最新价格和截止时间
  $stmt = $pdo->prepare("SELECT a.auction_id, a.item_id, a.current_price, a.end_date, a.status, i.seller_id
                         FROM Auction a JOIN Item i ON i.item_id=a.item_id
                         WHERE a.auction_id=? FOR UPDATE");
  $stmt->execute([$aid]);
  $row = $stmt->fetch();
  if (!$row) throw new RuntimeException('Auction not found');
  if ($row['status'] !== 'ongoing') throw new RuntimeException('Auction not open');
  if (time() >= strtotime($row['end_date'])) throw new RuntimeException('Auction ended');
  if ($amount <= (float)$row['current_price']) throw new RuntimeException('Bid must be greater than current price');
  if ((int)$row['seller_id'] === (int)current_user()['user_id']) throw new RuntimeException('Seller cannot bid own item');

  // 插入出价（每次一行）
  $pdo->prepare("INSERT INTO Bid(auction_id,bidder_id,bid_amount,bid_time) VALUES (?,?,?,NOW())")
      ->execute([$aid, current_user()['user_id'], $amount]);

  // 同步当前价格为最高价（稳妥法）
  $pdo->prepare("UPDATE Auction a
                 JOIN (SELECT auction_id, MAX(bid_amount) AS max_bid FROM Bid WHERE auction_id=? GROUP BY auction_id) b
                   ON a.auction_id=b.auction_id
                 SET a.current_price=b.max_bid
                 WHERE a.auction_id=?")->execute([$aid,$aid]);

  $pdo->commit();
  flash_set('ok','Bid placed!');
  header('Location: auction.php?id='.$aid);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  flash_set('error','Bid failed: '.$e->getMessage());
  header('Location: auction.php?id='.$aid);
}
