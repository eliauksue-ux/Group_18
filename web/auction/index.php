<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/database.php';

$pdo = get_db();
$sql = "SELECT a.auction_id, a.end_date, a.current_price,
        i.item_id, i.title,
        (SELECT image_url FROM ItemImage im
         WHERE im.item_id=i.item_id
         ORDER BY is_primary DESC, image_id ASC
         LIMIT 1) AS img
        FROM Auction a
        JOIN Item i ON i.item_id=a.item_id
        WHERE a.status='ongoing'
        ORDER BY a.end_date ASC
        LIMIT 30";
$rows = $pdo->query($sql)->fetchAll();
?>
<h2>Ongoing Auctions</h2>
<div class="grid">
  <?php foreach ($rows as $r): ?>
  <a class="card" href="auction.php?id=<?= (int)$r['auction_id'] ?>">
    <img src="<?= htmlspecialchars($r['img'] ?: '/auction/assets/placeholder.jpg') ?>" alt="">
    <div class="p">
      <div class="title"><?= htmlspecialchars($r['title']) ?></div>
      <div class="price">£<?= number_format($r['current_price'],2) ?></div>
      <div>Ends: <?= htmlspecialchars($r['end_date']) ?></div>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?>
