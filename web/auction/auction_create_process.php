<?php
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';
require_login();

$item_id = (int)($_POST['item_id'] ?? 0);
$start   = $_POST['start_date'] ?? '';
$end     = $_POST['end_date'] ?? '';

if (!$item_id || !$start || !$end) { flash_set('error','Missing fields'); header('Location: auction_create.php'); exit; }

$pdo = get_db();
try {
  // 初始 current_price 设为该 Item 的 start_price
  $stmt = $pdo->prepare("INSERT INTO Auction(item_id,start_date,end_date,winner_id,final_price,current_price,status)
                         SELECT i.item_id, ?, ?, NULL, NULL, i.start_price, 'ongoing'
                         FROM Item i WHERE i.item_id=? LIMIT 1");
  $stmt->execute([$start,$end,$item_id]);
  flash_set('ok','Auction created');
  header('Location: auction.php?id='.$pdo->lastInsertId());
} catch (Throwable $e) {
  flash_set('error','Create auction failed: '.$e->getMessage());
  header('Location: auction_create.php');
}
