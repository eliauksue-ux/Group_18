<?php

require_once __DIR__.'/includes/database.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/flash.php';
require_login();

$pdo = get_db();
$userId = current_user()['user_id'];

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