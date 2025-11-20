<?php
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';
require_login();
if (current_user()['role'] !== 'admin') {
  flash_set('error','Only admin can delete auctions.');
  header('Location: index.php');
  exit;
}
$aid = (int)($_POST['auction_id'] ?? 0);
$pdo = get_db();
try {
  $pdo->beginTransaction();
  $pdo->prepare("DELETE FROM Bid WHERE auction_id=?")->execute([$aid]);
  $pdo->prepare("DELETE FROM Auction WHERE auction_id=?")->execute([$aid]);
  $pdo->commit();
  flash_set('ok','Auction deleted.');
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  flash_set('error','Delete failed: '.$e->getMessage());
}
header('Location: index.php');
