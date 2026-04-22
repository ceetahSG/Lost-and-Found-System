<?php
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$item_id = $_GET['id'] ?? 0;

if ($item_id) {
    $item = new Item($conn);
    $result = $item->deleteItem($item_id, $_SESSION['user_id']);

    if ($result['success']) {
        header('Location: ' . BASE_URL . 'pages/dashboard.php?msg=deleted');
    } else {
        header('Location: ' . BASE_URL . 'pages/dashboard.php?err=' . urlencode($result['message']));
    }
} else {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
}
exit;
?>
