<?php
session_start(); header('Content-Type: application/json'); require_once '../config/db.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }
$action = $_REQUEST['action'] ?? ''; $db = getDB();

if ($action === 'stats') {
    echo json_encode(['success' => true, 'data' => [
        'total_items' => $db->query("SELECT COUNT(*) FROM items")->fetch_row()[0],
        'lost_items' => $db->query("SELECT COUNT(*) FROM items WHERE type='lost'")->fetch_row()[0],
        'found_items' => $db->query("SELECT COUNT(*) FROM items WHERE type='found'")->fetch_row()[0],
        'resolved_items' => $db->query("SELECT COUNT(*) FROM items WHERE status='resolved'")->fetch_row()[0],
        'total_users' => $db->query("SELECT COUNT(*) FROM users")->fetch_row()[0]
    ]]); exit;
}
if ($action === 'all_items') { echo json_encode(['success' => true, 'data' => $db->query("SELECT i.*, u.name AS reporter_name FROM items i JOIN users u ON i.user_id = u.id ORDER BY i.created_at DESC")->fetch_all(MYSQLI_ASSOC)]); exit; }
if ($action === 'all_users') { echo json_encode(['success' => true, 'data' => $db->query("SELECT id, name, email, role, is_blocked FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC)]); exit; }
if ($action === 'toggle_block') { $id = intval($_POST['id']); $st = intval($_POST['status']); $db->query("UPDATE users SET is_blocked=$st WHERE id=$id"); echo json_encode(['success' => true, 'message' => 'User status updated.']); exit; }
if ($action === 'approve') { $id = intval($_POST['id']); $db->query("UPDATE items SET status='open' WHERE id=$id"); echo json_encode(['success'=>true, 'message'=>'Approved!']); exit; }
if ($action === 'delete_item') { $id = intval($_POST['id']); $db->query("DELETE FROM items WHERE id=$id"); echo json_encode(['success'=>true, 'message'=>'Deleted!']); exit; }

if ($action === 'get_matches') {
    $q = "SELECT m.id as match_id, l.title as lost_title, f.title as found_title, ul.name as lost_user, uf.name as found_user, ul.student_id as lost_sid, uf.student_id as found_sid, ul.id_proof as lost_proof, uf.id_proof as found_proof FROM item_matches m JOIN items l ON m.lost_item_id=l.id JOIN items f ON m.found_item_id=f.id JOIN users ul ON l.user_id=ul.id JOIN users uf ON f.user_id=uf.id WHERE m.status='pending'";
    echo json_encode(['success' => true, 'data' => $db->query($q)->fetch_all(MYSQLI_ASSOC)]); exit;
}
if ($action === 'verify_match') {
    $id = intval($_POST['match_id']); $st = $_POST['status']; $db->query("UPDATE item_matches SET status='$st' WHERE id=$id");
    if($st === 'approved') {
        $m = $db->query("SELECT lost_item_id, found_item_id FROM item_matches WHERE id=$id")->fetch_assoc();
        $db->query("UPDATE items SET status='resolved' WHERE id IN ({$m['lost_item_id']}, {$m['found_item_id']})");
    }
    echo json_encode(['success'=>true, 'message'=>"Match $st!"]); exit;
}
if ($action === 'get_item_chats') {
    $iId = intval($_GET['item_id']); $q = "SELECT m.message, m.created_at, s.name as sender_name, r.name as receiver_name FROM messages m JOIN users s ON m.sender_id=s.id JOIN users r ON m.receiver_id=r.id WHERE m.item_id=$iId ORDER BY m.created_at ASC";
    echo json_encode(['success' => true, 'data' => $db->query($q)->fetch_all(MYSQLI_ASSOC)]); exit;
}
?>