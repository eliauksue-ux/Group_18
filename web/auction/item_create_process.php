<?php
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';
require_login();

$title   = trim($_POST['title'] ?? '');
$desc    = trim($_POST['item_description'] ?? '');
$cat_id  = (int)($_POST['category_id'] ?? 0);
$sp      = (float)($_POST['start_price'] ?? 0);
$rp      = (float)($_POST['reserve_price'] ?? 0);

if (!$title || !$cat_id || $sp<=0 || $rp<=0) {
  flash_set('error','Missing fields'); header('Location: item_create.php'); exit;
}

$pdo = get_db();
$pdo->beginTransaction();
try {
  $stmt = $pdo->prepare("INSERT INTO Item(title,item_description,seller_id,start_price,reserve_price,status,category_id,publish_time)
                         VALUES (?,?,?,?,?,'published',?,NOW())");
  $stmt->execute([$title,$desc,current_user()['user_id'],$sp,$rp,$cat_id]);
  $item_id = (int)$pdo->lastInsertId();

  // Upload images
  $uploadDir = __DIR__."/uploads/items/$item_id";
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $first = true;

  if (!empty($_FILES['images']) && is_array($_FILES['images']['error'])) {
    foreach ($_FILES['images']['error'] as $i=>$err) {
      if ($err === UPLOAD_ERR_NO_FILE) continue;
      if ($err !== UPLOAD_ERR_OK) throw new RuntimeException('Upload error '.$err);

      $tmp = $_FILES['images']['tmp_name'][$i];
      $mime= $finfo->file($tmp);
      $ext = match($mime){
        'image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp',
        default => throw new RuntimeException('Unsupported image type')
      };
      if ($_FILES['images']['size'][$i] > 10*1024*1024) throw new RuntimeException('Image too large');

      $name = $item_id.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(3)).'.'.$ext;
      $dest = $uploadDir.'/'.$name;
      if (!move_uploaded_file($tmp, $dest)) throw new RuntimeException('move_uploaded_file fail');

      $rel  = "uploads/items/$item_id/$name";
      $pdo->prepare("INSERT INTO ItemImage(item_id,image_url,is_primary) VALUES (?,?,?)")
          ->execute([$item_id,$rel,$first?1:0]);
      $first = false;
    }
  }

  $pdo->commit();
  flash_set('ok','Item created, now create an auction.');
  header('Location: auction_create.php?item_id='.$item_id);
} catch(Throwable $e) {
  $pdo->rollBack();
  flash_set('error','Create item failed: '.$e->getMessage());
  header('Location: item_create.php');
}
