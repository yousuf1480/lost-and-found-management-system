<?php
session_start(); header('Content-Type: application/json'); require_once '../config/db.php';
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }
$userId = $_SESSION['user_id']; $action = $_REQUEST['action'] ?? ''; $db = getDB();

if ($action === 'get') {
    $stmt = $db->prepare("SELECT m.*, s.name as sender_name, s.profile_pic as sender_pfp, s.contact_no as sender_contact, s.semester as sender_sem, r.name as receiver_name, r.profile_pic as receiver_pfp, r.contact_no as receiver_contact, r.semester as receiver_sem, i.title as item_title FROM messages m JOIN users s ON m.sender_id=s.id JOIN users r ON m.receiver_id=r.id LEFT JOIN items i ON m.item_id=i.id WHERE m.sender_id=? OR m.receiver_id=? ORDER BY m.created_at ASC");
    $stmt->bind_param('ii', $userId, $userId); $stmt->execute();
    echo json_encode(['success'=>true, 'data'=>$stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'my_id'=>$userId]);
} 
elseif ($action === 'send') {
    $iId = intval($_POST['item_id']); $rId = intval($_POST['receiver_id']); $msg = trim($_POST['message']);
    if ($iId && $rId && $msg && $userId !== $rId) {
        $s = $db->prepare("INSERT INTO messages (item_id, sender_id, receiver_id, message) VALUES (?,?,?,?)");
        $s->bind_param('iiis', $iId, $userId, $rId, $msg); $s->execute(); echo json_encode(['success'=>true]);
    }
}
elseif ($action === 'read') { $db->query("UPDATE messages SET is_read=1 WHERE receiver_id=$userId AND sender_id=".intval($_POST['sender_id'])); echo json_encode(['success'=>true]); }
elseif ($action === 'unread_count') { echo json_encode(['success'=>true, 'count'=>$db->query("SELECT COUNT(*) FROM messages WHERE receiver_id=$userId AND is_read=0")->fetch_row()[0]]); }
?>