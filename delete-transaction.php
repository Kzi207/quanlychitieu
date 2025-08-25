<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

checkLogin();

$transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$transaction_id) {
    header('Location: transactions.php');
    exit();
}

// Xử lý xóa giao dịch
$result = deleteTransaction($transaction_id, $_SESSION['user_id']);

// Chuyển hướng về trang transactions với thông báo
if ($result['success']) {
    header('Location: transactions.php?success=' . urlencode($result['message']));
} else {
    header('Location: transactions.php?error=' . urlencode($result['message']));
}
exit();
?>
