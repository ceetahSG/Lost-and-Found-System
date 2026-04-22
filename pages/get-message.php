<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

$other_user_id = (int)($_GET['with'] ?? 0);
$last_id = (int)($_GET['last_id'] ?? 0);

if (!$other_user_id) {
    echo json_encode([]);
    exit;
}

$message = new Message($conn);
$all = $message->getConversation($_SESSION['user_id'], $other_user_id);

// Only return new messages
$new = array_filter($all, fn($m) => $m['id'] > $last_id);

// Mark received ones as read
foreach ($new as $msg) {
    if ($msg['receiver_id'] == $_SESSION['user_id'] && !$msg['is_read']) {
        $message->markAsRead($msg['id']);
    }
}

echo json_encode(array_values($new));
exit;