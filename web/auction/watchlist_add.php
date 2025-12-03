<?php
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';

require_login();

$user = current_user();
if ($user['role'] !== 'buyer') {
  flash_set('error', 'Only buyers can use watchlist.');
  header('Location: index.php');
  exit;
}

$aid = (int)($_POST['auction_id'] ?? 0);
if ($aid <= 0) {
  flash_set('error', 'Invalid auction.');
  header('Location: index.php');
  exit;
}

$pdo = get_db();

try {
  // Insert only when it does not exist to avoid duplication
  $stmt = $pdo->prepare("
    INSERT INTO Watchlist(user_id, auction_id, created_at)
    SELECT ?, ?, NOW()
    WHERE NOT EXISTS (
      SELECT 1 FROM Watchlist WHERE user_id = ? AND auction_id = ?
    )
  ");
  $stmt->execute([$user['user_id'], $aid, $user['user_id'], $aid]);

  flash_set('ok', 'Added to watchlist.');
} catch (Throwable $e) {
  flash_set('error', 'Failed to update watchlist: '.$e->getMessage());
}

// Go back to the source page (if not, go back to the first page)
$ref = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: '.$ref);
