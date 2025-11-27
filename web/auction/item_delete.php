<?php
// item_delete.php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/flash.php';

require_login();

$user = current_user();
if (!$user || $user['role'] !== 'admin') {
    flash_set('error', 'Only admin can delete items.');
    header('Location: index.php');
    exit;
}

$itemId = (int)($_POST['item_id'] ?? 0);
if ($itemId <= 0) {
    flash_set('error', 'Invalid item id.');
    header('Location: index.php');
    exit;
}

$pdo = get_db();
$images = [];

try {
    $pdo->beginTransaction();

    // 1) 找到这个 item 关联的所有 auction
    $stmt = $pdo->prepare("SELECT auction_id FROM Auction WHERE item_id = ?");
    $stmt->execute([$itemId]);
    $auctionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($auctionIds) {
        $placeholders = implode(',', array_fill(0, count($auctionIds), '?'));

        // 1.1 删除这些 auction 的出价
        $delBids = $pdo->prepare("DELETE FROM Bid WHERE auction_id IN ($placeholders)");
        $delBids->execute($auctionIds);

        // 1.2 删除 watchlist 中的对应记录
        $delWatch = $pdo->prepare("DELETE FROM Watchlist WHERE auction_id IN ($placeholders)");
        $delWatch->execute($auctionIds);

        // 1.3 删除 auction 本身
        $delAuctions = $pdo->prepare("DELETE FROM Auction WHERE auction_id IN ($placeholders)");
        $delAuctions->execute($auctionIds);
    }

    // 2) 先把图片路径取出来
    $stmtImg = $pdo->prepare("SELECT image_url FROM ItemImage WHERE item_id = ?");
    $stmtImg->execute([$itemId]);
    $images = $stmtImg->fetchAll(PDO::FETCH_COLUMN);

    // 2.1 删掉 ItemImage 记录
    $pdo->prepare("DELETE FROM ItemImage WHERE item_id = ?")->execute([$itemId]);

    // 3) 删除 Item 记录
    $pdo->prepare("DELETE FROM Item WHERE item_id = ?")->execute([$itemId]);

    $pdo->commit();

    flash_set('ok', 'Item and related auctions have been deleted.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash_set('error', 'Delete failed: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}

// 4) 数据库已经删掉，这里再尝试删除文件本身（不会影响事务）
if (!empty($images)) {
    foreach ($images as $relPath) {
        if (!$relPath) continue;
        $path = __DIR__ . '/' . ltrim($relPath, '/');
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

// 删除完成后统一回到首页
header('Location: index.php');
exit;
