<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/database.php';

require_login();
if (current_user()['role'] !== 'seller') {
  echo "<p>Only sellers can create auctions.</p>";
  require_once __DIR__.'/includes/footer.php';
  exit;
}

$pdo    = get_db();
$seller = current_user()['user_id'];
$preselect = (int)($_GET['item_id'] ?? 0);

/* Only items with "no auction in progress/about to start" are selected */
$sql = "
  SELECT i.item_id, i.title
  FROM Item i
  WHERE i.seller_id = ?
    AND NOT EXISTS (
      SELECT 1
      FROM Auction a
      WHERE a.item_id = i.item_id
        AND a.end_date > NOW()
    )
  ORDER BY i.item_id DESC
";

$stmt  = $pdo->prepare($sql);
$stmt->execute([$seller]);
$items = $stmt->fetchAll();
?>

<h2>New Auction</h2>

<?php if (!$items): ?>
  <p>You currently have no items available for a new auction.</p>
  <p>All of your items already have ongoing or upcoming auctions.</p>
<?php else: ?>

  <form class="form" action="auction_create_process.php" method="post">
    <label>Item</label>
    <select name="item_id" required>
      <?php foreach($items as $it): ?>
        <option
          value="<?= (int)$it['item_id'] ?>"
          <?= $preselect === (int)$it['item_id'] ? 'selected' : '' ?>
        >
          [#<?= (int)$it['item_id'] ?>] <?= htmlspecialchars($it['title']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Start time</label>
    <input
      type="datetime-local"
      name="start_date"
      value="<?= date('Y-m-d\TH:i') ?>"
      required
    >

    <label>End time</label>
    <input
      type="datetime-local"
      name="end_date"
      value="<?= date('Y-m-d\TH:i', time()+86400) ?>"
      required
    >

    <button class="btn" type="submit">Create Auction</button>
  </form>

<?php endif; ?>

<?php require_once __DIR__.'/includes/footer.php'; ?>
