<?php
require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';
require_login();

$pdo = get_db();
$userId = current_user()['user_id'];

/* STEP 1：Mark all notifications as read */
$pdo->prepare("UPDATE Notifications SET is_read = 1 WHERE user_id = ?")
    ->execute([$userId]);

/* STEP 2：Retrieve all notifications (read/unread) */
$stmt = $pdo->prepare("
    SELECT message_id, message, created_at
    FROM Notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$rows = $stmt->fetchAll();
?>
<?php require_once __DIR__.'/includes/header.php'; ?>

<h2>Your Notifications</h2>

<?php if (empty($rows)): ?>
    <p>No notifications yet.</p>
<?php else: ?>
    <ul>
    <?php foreach ($rows as $n): ?>
        <li>
            <b><?= htmlspecialchars($n['created_at']) ?></b><br>
            <?= nl2br(htmlspecialchars($n['message'])) ?>
        </li>
        <hr>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require_once __DIR__.'/includes/footer.php'; ?>