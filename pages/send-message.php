<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$receiver_id = (int)($_POST['receiver_id'] ?? 0);
$body = trim($_POST['body'] ?? '');

if (empty($body) || $receiver_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$message = new Message($conn);
$result = $message->sendMessage($_SESSION['user_id'], $receiver_id, 'Reply', $body);

echo json_encode($result);
exit;