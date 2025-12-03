<?php
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';
require_login();

$user = current_user();
if ($user['role'] !== 'seller') {
  flash_set('error', 'Only sellers can update their auctions.');
  header('Location: index.php');
  exit;
}

$aid   = (int)($_POST['auction_id'] ?? 0);
$start = $_POST['start_date'] ?? '';
$end   = $_POST['end_date'] ?? '';
$spRaw = $_POST['start_price'] ?? null;
$rpRaw = $_POST['reserve_price'] ?? null;

if (!$aid || !$start || !$end) {
  flash_set('error', 'Missing fields.');
  header('Location: my_auctions.php');
  exit;
}

$startTs = strtotime($start);
$endTs   = strtotime($end);
if ($startTs === false || $endTs === false || $endTs <= $startTs) {
  flash_set('error', 'End time must be after start time.');
  header('Location: my_auctions.php');
  exit;
}

$pdo = get_db();
$now = time();

try {
  $pdo->beginTransaction();

  // Lock the auction and the corresponding item
  $stmt = $pdo->prepare("
    SELECT a.*, i.seller_id, i.start_price, i.reserve_price
    FROM Auction a
    JOIN Item i ON i.item_id = a.item_id
    WHERE a.auction_id = ?
    FOR UPDATE
  ");
  $stmt->execute([$aid]);
  $row = $stmt->fetch();

  if (!$row) {
    throw new RuntimeException('Auction not found.');
  }
  if ((int)$row['seller_id'] !== (int)$user['user_id']) {
    throw new RuntimeException('You can only edit your own auctions.');
  }

  $oldStartTs = strtotime($row['start_date']);
  $oldEndTs   = strtotime($row['end_date']);

  $hasStarted = $oldStartTs !== false && $now >= $oldStartTs;
  $hasEnded   = $oldEndTs !== false && $now >= $oldEndTs;

  // 1) Update auction time: allowed as long as it is not over
  if ($hasEnded) {
    throw new RuntimeException('This auction has ended. Time cannot be changed.');
  }

  $updA = $pdo->prepare("UPDATE Auction SET start_date = ?, end_date = ? WHERE auction_id = ?");
  $updA->execute([$start, $end, $aid]);

  // 2) Update price: only "not started" auctions can be changed
  if (!$hasStarted) {
    $sp = (float)$spRaw;
    $rp = (float)$rpRaw;
    if ($sp <= 0 || $rp <= 0) {
      throw new RuntimeException('Prices must be positive.');
    }

    $updI = $pdo->prepare("UPDATE Item SET start_price = ?, reserve_price = ? WHERE item_id = ?");
    $updI->execute([$sp, $rp, $row['item_id']]);
  }

  $pdo->commit();

  $msg = 'Auction times updated.';
  if (!$hasStarted) {
    $msg .= ' Prices updated as well (auction has not started yet).';
  }
  flash_set('ok', $msg);
  header('Location: my_auctions.php');
} catch (Throwable $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  flash_set('error', 'Update failed: '.$e->getMessage());
  header('Location: my_auctions.php');
}
