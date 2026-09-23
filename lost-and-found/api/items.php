<?php
session_start(); header('Content-Type: application/json'); require_once '../config/db.php';
$action = $_REQUEST['action'] ?? ''; $db = getDB();

if ($action === 'list') {
    $status = $_GET['status'] ?? 'open'; $type = $_GET['type'] ?? ''; $cat = $_GET['category'] ?? ''; $search = $_GET['search'] ?? '';
    $query = "SELECT i.*, u.name AS reporter_name FROM items i JOIN users u ON i.user_id = u.id WHERE i.status = ?";
    $params = [$status]; $types = "s";
    if ($type) { $query .= " AND i.type = ?"; $params[] = $type; $types .= "s"; }
    if ($cat && $cat !== 'all') { $query .= " AND i.category = ?"; $params[] = $cat; $types .= "s"; }
    if ($search) { $query .= " AND (i.title LIKE ? OR i.description LIKE ?)"; $sp = "%$search%"; $params[] = $sp; $params[] = $sp; $types .= "ss"; }
    $query .= " ORDER BY i.created_at DESC";
    $stmt = $db->prepare($query); if(!empty($params)) $stmt->bind_param($types, ...$params); $stmt->execute();
    echo json_encode(['success' => true, 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]); exit;
}

if ($action === 'create') {
    if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false, 'message'=>'Login required.']); exit; }
    $userId = $_SESSION['user_id']; $type = $_POST['type'] ?? 'lost'; $title = trim($_POST['title'] ?? '');
    $cat = $_POST['category'] ?? 'other'; $desc = trim($_POST['description'] ?? ''); $loc = trim($_POST['location'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');
    
    $dup = $db->prepare("SELECT id FROM items WHERE user_id=? AND type=? AND title=? AND status IN ('pending','open')");
    $dup->bind_param('iss', $userId, $type, $title); $dup->execute();
    if ($dup->get_result()->num_rows > 0) { echo json_encode(['success'=>false, 'message'=>'Duplicate report blocked!']); exit; }

    $img = null; if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) { $dir = '../uploads/'; if (!is_dir($dir)) mkdir($dir, 0777, true); $img = 'uploads/'.time().'_'.basename($_FILES['image']['name']); move_uploaded_file($_FILES['image']['tmp_name'], '../'.$img); }

    $stmt = $db->prepare("INSERT INTO items (user_id, type, title, category, description, location, date_lost_found, image_path, status) VALUES (?,?,?,?,?,?,?,?,'pending')");
    $stmt->bind_param('isssssss', $userId, $type, $title, $cat, $desc, $loc, $date, $img);
    if ($stmt->execute()) {
        $nId = $stmt->insert_id; $safeTitle = $db->real_escape_string($title);
        $db->query("INSERT INTO notifications (user_id, message) VALUES ($userId, '📝 REPORT: $safeTitle submitted. Admin will verify.')");

        $opType = ($type === 'lost') ? 'found' : 'lost';
        $mStmt = $db->prepare("SELECT id, title, user_id FROM items WHERE type=? AND category=? AND status='open' AND id!=?");
        $mStmt->bind_param('ssi', $opType, $cat, $nId); $mStmt->execute(); $matches = $mStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (count($matches) > 0) {
            $admin = $db->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetch_assoc()['id'] ?? 1;
            foreach ($matches as $m) {
                $lId = ($type==='lost') ? $nId : $m['id']; $fId = ($type==='found') ? $nId : $m['id'];
                $db->query("INSERT INTO item_matches (lost_item_id, found_item_id, status) VALUES ($lId, $fId, 'pending')");
                $db->query("INSERT INTO notifications (user_id, message) VALUES ($admin, '⚠️ MATCH: Verification required for $safeTitle.')");
                $db->query("INSERT INTO notifications (user_id, message) VALUES ($userId, '🔔 ALERT: Potential match found for $safeTitle!')");
            }
        }
        echo json_encode(['success'=>true, 'message'=>'Submitted!']);
    } else { echo json_encode(['success'=>false, 'message'=>'DB Error.']); }
    exit;
}

if ($action === 'my_items') { $uid = $_SESSION['user_id']; echo json_encode(['success'=>true, 'data'=>$db->query("SELECT * FROM items WHERE user_id=$uid ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC)]); exit; }
if ($action === 'delete_my' || $action === 'resolve_my') { $id = intval($_POST['id']); $uid = $_SESSION['user_id']; $st = ($action === 'resolve_my') ? "UPDATE items SET status='resolved' WHERE id=$id AND user_id=$uid" : "DELETE FROM items WHERE id=$id AND user_id=$uid"; $db->query($st); echo json_encode(['success'=>true]); exit; }
if ($action === 'update_status') { if ($_SESSION['user_role'] !== 'admin') exit; $id = intval($_POST['id']); $status = $_POST['status']; $db->query("UPDATE items SET status='$status' WHERE id=$id"); echo json_encode(['success'=>true, 'message'=>'Status updated.']); exit; }
?>