<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';

require_login(); // Only buyer can access recommendations
$user = current_user();
if ($user['role'] !== 'buyer') {
    echo "<p>You must be a buyer to see recommendations.</p >";
    require __DIR__.'/includes/footer.php';
    exit;
}

$pdo = get_db();

/* STEP 1: Get the watchlist of the current user */
$w = $pdo->prepare("
    SELECT A.auction_id, I.category_id
    FROM Watchlist W
    JOIN Auction A ON A.auction_id = W.auction_id
    JOIN Item I    ON I.item_id = A.item_id
    WHERE W.user_id = ?
");
$w->execute([$user['user_id']]);
$wl = $w->fetchAll();

/* STEP 2: If watchlist is not empty → find the most categories */
$targetCategory = null;
if (!empty($wl)) {

    // Count the categories with the most occurrences
    $count = [];
    foreach ($wl as $row) {
        $cat = (int)$row['category_id'];
        if (!isset($count[$cat])) $count[$cat] = 0;
        $count[$cat]++;
    }

    // Sort by occurrence
    arsort($count);
    $targetCategory = array_key_first($count);
}

/* STEP 3: Construct recommended query */

/* Case 1: Watchlist is not empty → recommending the same category of ascending/ongoing */
if ($targetCategory !== null) {

    $sql = "
    SELECT A.*, I.title, I.category_id,
           (SELECT image_url
            FROM ItemImage im
            WHERE im.item_id = I.item_id
            ORDER BY im.is_primary DESC, im.image_id ASC
            LIMIT 1) AS img
    FROM Auction A
    JOIN Item I ON I.item_id = A.item_id
    WHERE I.category_id = ?
      AND A.status IN ('Upcoming','Ongoing')
      AND A.auction_id NOT IN (
            SELECT auction_id FROM Watchlist WHERE user_id = ?
      )
    ORDER BY A.start_date ASC
    LIMIT 12
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$targetCategory, $user['user_id']]);
    $recs = $stmt->fetchAll();

}

/* Case 2: If the watchlist is empty → recommend going + rising */
else {

    $sql = "
    SELECT A.*, I.title, I.category_id,
           (SELECT image_url
            FROM ItemImage im
            WHERE im.item_id = I.item_id
            ORDER BY im.is_primary DESC, im.image_id ASC
            LIMIT 1) AS img
    FROM Auction A
    JOIN Item I ON I.item_id = A.item_id
    WHERE A.status IN ('Upcoming','Ongoing')
    ORDER BY A.status='Ongoing' DESC, A.end_date ASC
    LIMIT 12
    ";

    $recs = $pdo->query($sql)->fetchAll();
}

$watchIdsStmt = $pdo->prepare("SELECT auction_id FROM Watchlist WHERE user_id=?");
$watchIdsStmt->execute([$user['user_id']]);
$watchIds = array_column($watchIdsStmt->fetchAll(), 'auction_id');

$now = time();

/* Time format function */
function pretty_time($t) {
    return date("M j, Y H:i", strtotime($t));
}
?>

<!-- ===== Page title ===== -->
<h2>Recommended for you</h2>

<?php if (empty($recs)): ?>
    <p>No recommendations yet.</p >
<?php else: ?>

<style>
.card .title {
    font-size: 1.15rem;
    font-weight: 700;
    margin-bottom: 4px;
    color: #222;
}
.card .price {
    font-size: 1.05rem;
    font-weight: 600;
    margin-bottom: 8px;
}
.card .meta {
    font-size: 0.87rem;
    color: #555;
    line-height: 1.45;
}
.card .meta b {
    color: #333;
    font-weight: 600;
}
.card .p {
    padding: 10px 12px 14px 12px;
}
.card:hover {
    box-shadow: 0 3px 10px rgba(0,0,0,0.12);
    transform: translateY(-2px);
    transition: 0.2s ease;
}
</style>

<div class="grid">
<?php foreach ($recs as $r): ?>

    <?php
    $aid = (int)$r['auction_id'];
    $img = $r['img'] ?: '/auction/assets/placeholder.jpg';

    $startTs = strtotime($r['start_date']);
    $endTs   = strtotime($r['end_date']);

    // Status is only taken as updating and ongoing (SQL filtered)
    $displayStatus = ($now < $startTs) ? 'upcoming' : 'ongoing';

    $priceLabel = '£'.number_format($r['current_price'],2);

    $inWatchlist = in_array($aid, $watchIds);
    ?>

    <div class="card">

        <a class="card-link" href=" >">
            <img src="<?= htmlspecialchars($img) ?>" alt="">

            <div class="p">
                <div class="title"><?= htmlspecialchars($r['title']) ?></div>
                <div class="price"><?= $priceLabel ?></div>

                <div class="meta">
                    <b>Status:</b> <?= $displayStatus ?><br>
                    <b>Starts:</b> <?= pretty_time($r['start_date']) ?><br>
                    <b>Ends:</b> <?= pretty_time($r['end_date']) ?>
                </div>
            </div>
        </a >

        <!-- Watchlist button -->
        <?php if ($inWatchlist): ?>
            <form class="watch-form" method="post" action="watchlist_remove.php"
                  onsubmit="event.stopPropagation();">
                <input type="hidden" name="auction_id" value="<?= $aid ?>">
                <button class="watch-btn in" onclick="event.stopPropagation();">★ Remove</button>
            </form>
        <?php else: ?>
            <form class="watch-form" method="post" action="watchlist_add.php"
                  onsubmit="event.stopPropagation();">
                <input type="hidden" name="auction_id" value="<?= $aid ?>">
                <button class="watch-btn" onclick="event.stopPropagation();">☆ Add to watchlist</button>
            </form>
        <?php endif; ?>

    </div>

<?php endforeach; ?>
</div>

<?php endif; ?>

<?php require __DIR__.'/includes/footer.php'; ?>