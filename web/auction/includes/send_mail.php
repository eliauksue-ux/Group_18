<?php
require_once __DIR__.'/database.php';

function send_mail($toUserId, $subject, $body) {
    $pdo = get_db();
    $msg = "$subject: $body";

    $stmt = $pdo->prepare("INSERT INTO Notifications (user_id, message) VALUES (?, ?)");
    $stmt->execute([$toUserId, $msg]);

    return true;
}