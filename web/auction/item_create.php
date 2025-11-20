<?php
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/auth.php';
require_login();
if (current_user()['role'] !== 'seller') {
  echo "<p>Only sellers can create items.</p>";
  require_once __DIR__.'/includes/footer.php';
  exit;
}
?>
<h2>New Item</h2>
<form class="form" action="item_create_process.php" method="post" enctype="multipart/form-data">
  <label>Title</label>
  <input name="title" required>
  <label>Description</label>
  <textarea name="item_description" rows="4"></textarea>
  <label>Category</label>
  <select name="category_id" required>
    <?php
      require_once __DIR__.'/includes/database.php';
      $pdo=get_db();
      foreach($pdo->query("SELECT category_id,category_name FROM Category ORDER BY category_name") as $c){
        echo '<option value="'.$c['category_id'].'">'.htmlspecialchars($c['category_name']).'</option>';
      }
    ?>
  </select>
  <label>Start price</label>
  <input name="start_price" type="number" step="0.01" required>
  <label>Reserve price</label>
  <input name="reserve_price" type="number" step="0.01" required>
  <label>Images (multiple)</label>
  <input type="file" name="images[]" accept="image/*" multiple>
  <button class="btn" type="submit">Create Item</button>
</form>
<?php require_once __DIR__.'/includes/footer.php'; ?>
