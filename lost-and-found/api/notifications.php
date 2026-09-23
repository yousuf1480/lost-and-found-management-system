<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';
$db = getDB();

if ($action === 'get') {
    $stmt = $db->prepare("SELECT message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $msgs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'data' => $msgs]);
} 
elseif ($action === 'unread_count') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_row()[0];
    echo json_encode(['success' => true, 'count' => $count]);
}
elseif ($action === 'read') {
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    echo json_encode(['success' => true]);
}

$db->close();
?>