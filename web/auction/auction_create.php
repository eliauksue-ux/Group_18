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
$pdo = get_db();
$seller = current_user()['user_id'];
$preselect = (int)($_GET['item_id'] ?? 0);
$items = $pdo->prepare("SELECT item_id, title FROM Item WHERE seller_id=? ORDER BY item_id DESC");
$items->execute([$seller]);
?>
<h2>New Auction</h2>
<form class="form" action="auction_create_process.php" method="post">
  <label>Item</label>
  <select name="item_id" required>
    <?php foreach($items as $it): ?>
      <option value="<?= (int)$it['item_id'] ?>" <?= $preselect===$it['item_id']?'selected':'' ?>>
        [#<?= (int)$it['item_id'] ?>] <?= htmlspecialchars($it['title']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <label>Start time</label>
  <input type="datetime-local" name="start_date" value="<?= date('Y-m-d\TH:i') ?>" required>
  <label>End time</label>
  <input type="datetime-local" name="end_date" value="<?= date('Y-m-d\TH:i', time()+86400) ?>" required>
  <button class="btn" type="submit">Create Auction</button>
</form>
<?php require_once __DIR__.'/includes/footer.php'; ?>
