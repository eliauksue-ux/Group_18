<?php
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';

require_login();

$user = current_user();
if ($user['role'] !== 'buyer') {
    flash_set('error', 'Only buyers can modify watchlist.');
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
    $stmt = $pdo->prepare("DELETE FROM Watchlist WHERE user_id=? AND auction_id=?");
    $stmt->execute([$user['user_id'], $aid]);

    flash_set('ok', 'Removed from watchlist.');
} catch (Throwable $e) {
    flash_set('error', 'Failed to remove: '.$e->getMessage());
}

$ref = $_SERVER['HTTP_REFERER'] ?? 'watchlist.php';
header('Location: '.$ref);
exit;