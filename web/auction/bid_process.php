<?php
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';
require_once __DIR__.'/includes/send_mail.php';   // use station notification
require_login();

if (current_user()['role'] !== 'buyer') {
    flash_set('error','Only buyers can bid');
    header('Location: index.php');
    exit;
}

$aid = (int)($_POST['auction_id'] ?? 0);
$amount = (float)($_POST['bid_amount'] ?? 0);

$pdo = get_db();

try {
    $pdo->beginTransaction();

    // Get auction status, current price and seller information
    $stmt = $pdo->prepare("
        SELECT a.auction_id, a.item_id, a.current_price, a.start_date, a.end_date, a.status,
               i.seller_id, i.title
        FROM Auction a
        JOIN Item i ON i.item_id = a.item_id
        WHERE a.auction_id = ?
        FOR UPDATE
    ");
    $stmt->execute([$aid]);
    $row = $stmt->fetch();

    if (!$row) throw new RuntimeException('Auction not found');

    // Unified case handling
    $status = strtolower($row['status']);

    if ($status !== 'ongoing') throw new RuntimeException('Auction not open');
    if (time() >= strtotime($row['end_date'])) throw new RuntimeException('Auction ended');
    if ($amount <= (float)$row['current_price']) throw new RuntimeException('Bid must be higher');
    if ((int)$row['seller_id'] === current_user()['user_id']) throw new RuntimeException('Seller cannot bid own item');

    $itemTitle = $row['title'];

    // Insert a bid at this time
    $pdo->prepare("
        INSERT INTO Bid(auction_id, bidder_id, bid_amount, bid_time)
        VALUES (?, ?, ?, NOW())
    ")->execute([$aid, current_user()['user_id'], $amount]);

    // Update auction current price = highest bid
    $pdo->prepare("
        UPDATE Auction a
        JOIN (
            SELECT auction_id, MAX(bid_amount) AS max_bid
            FROM Bid WHERE auction_id = ?
            GROUP BY auction_id
        ) b ON a.auction_id = b.auction_id
        SET a.current_price = b.max_bid
        WHERE a.auction_id = ?
    ")->execute([$aid, $aid]);


    /*!!!!
      Send notification (station notification)
                                        !!!!*/

    // 1. Find the previous highest bidder (for outbid)
    $prevStmt = $pdo->prepare("
        SELECT b.bidder_id
        FROM Bid b
        WHERE b.auction_id = ?
        ORDER BY b.bid_amount DESC, b.bid_time ASC
        LIMIT 1 OFFSET 1
    ");
    $prevStmt->execute([$aid]);
    $prev = $prevStmt->fetch();

    if ($prev) {
        send_mail(
            $prev['bidder_id'],                  
            "You were outbid",
            "Another buyer placed a higher bid (£$amount) on '$itemTitle'."
        );
    }

    // 2. Notify watchlist users
    $watchStmt = $pdo->prepare("
        SELECT user_id
        FROM Watchlist
        WHERE auction_id = ?
    ");
    $watchStmt->execute([$aid]);
    $watchers = $watchStmt->fetchAll();

    foreach ($watchers as $w) {
        send_mail(
            $w['user_id'],
            "New bid on item",
            "A new bid (£$amount) was placed on '$itemTitle'."
        );
    }

    // 3. Notify seller
    send_mail(
        $row['seller_id'],
        "Your auction received a bid",
        "A buyer placed a new bid (£$amount) on your item '$itemTitle'."
    );

    $pdo->commit();
    flash_set('ok','Bid placed!');
    header('Location: auction.php?id='.$aid);
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash_set('error','Bid failed: '.$e->getMessage());
    header('Location: auction.php?id='.$aid);
    exit;
}