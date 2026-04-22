<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

var_dump([
    'session' => $_SESSION ?? 'NO SESSION',
    'item_id' => $_GET['id'] ?? 'NO ID',
    'file_exists' => file_exists(__DIR__ . '/../includes/functions.php')
]);
die();
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$item_id = $_GET['id'] ?? 0;

if ($item_id) {
    $item = new Item($conn);
    $result = $item->deleteItem($item_id, $_SESSION['user_id']);
    
    if ($result['success']) {
        header('Location: ' . BASE_URL . 'dashboard.php?msg=deleted');
    } else {
        header('Location: ' . BASE_URL . 'dashboard.php?err=' . urlencode($result['message']));
    }
} else {
    header('Location: ' . BASE_URL . 'dashboard.php');
}
exit;
?>