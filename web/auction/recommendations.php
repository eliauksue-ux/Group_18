<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';

require_login(); // 只有 buyer 可以访问推荐
$user = current_user();
if ($user['role'] !== 'buyer') {
    echo "<p>You must be a buyer to see recommendations.</p >";
    require __DIR__.'/includes/footer.php';
    exit;
}

$pdo = get_db();

/* ===========================================================
   STEP 1: 获取当前用户的 watchlist
   =========================================================== */
$w = $pdo->prepare("
    SELECT A.auction_id, I.category_id
    FROM Watchlist W
    JOIN Auction A ON A.auction_id = W.auction_id
    JOIN Item I    ON I.item_id = A.item_id
    WHERE W.user_id = ?
");
$w->execute([$user['user_id']]);
$wl = $w->fetchAll();

/* ===========================================================
   STEP 2: 如果 watchlist 不为空 → 找最多的 category
   =========================================================== */
$targetCategory = null;
if (!empty($wl)) {

    // 统计出现最多的 category
    $count = [];
    foreach ($wl as $row) {
        $cat = (int)$row['category_id'];
        if (!isset($count[$cat])) $count[$cat] = 0;
        $count[$cat]++;
    }

    // 按出现次数排序
    arsort($count);
    $targetCategory = array_key_first($count);
}

/* ===========================================================
   STEP 3: 构造推荐查询
   =========================================================== */

/* Case 1: watchlist 不为空 → 推荐同分类的 upcoming/ongoing */
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

/* Case 2: 如果 watchlist 为空 → 推荐 Ongoing + Upcoming */
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

/* 当前用户 watchlist ID，用于按钮状态 */
$watchIdsStmt = $pdo->prepare("SELECT auction_id FROM Watchlist WHERE user_id=?");
$watchIdsStmt->execute([$user['user_id']]);
$watchIds = array_column($watchIdsStmt->fetchAll(), 'auction_id');

$now = time();

/* 时间格式函数 */
function pretty_time($t) {
    return date("M j, Y H:i", strtotime($t));
}
?>

<!-- ===== 页面标题 ===== -->
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

    // 状态只取 upcoming & ongoing（SQL 已过滤）
    $displayStatus = ($now < $startTs) ? 'upcoming' : 'ongoing';

    $priceLabel = '£'.number_format($r['current_price'],2);

    $inWatchlist = in_array($aid, $watchIds);
    ?>

    <div class="card">

        <a class="card-link" href=" >">
            < img src="<?= htmlspecialchars($img) ?>" alt="">

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

        <!-- Watchlist 按钮 -->
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