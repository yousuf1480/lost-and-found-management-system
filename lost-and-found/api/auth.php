<?php
session_start(); header('Content-Type: application/json'); require_once '../config/db.php';
$action = $_POST['action'] ?? $_GET['action'] ?? ''; $db = getDB();

if ($action === 'check') {
    echo json_encode(['success' => true, 'logged_in' => isset($_SESSION['user_id']), 'user_id' => $_SESSION['user_id'] ?? null, 'user_name' => $_SESSION['user_name'] ?? null, 'role' => $_SESSION['user_role'] ?? 'user']); exit;
}

if ($action === 'get_profile') {
    if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false]); exit; }
    $stmt = $db->prepare('SELECT name, father_name, email, student_id, contact_no, semester, profile_pic FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']); $stmt->execute();
    echo json_encode(['success' => true, 'data' => $stmt->get_result()->fetch_assoc()]); exit;
}

if ($action === 'register') {
    $name = trim($_POST['name'] ?? ''); $fname = trim($_POST['father_name'] ?? ''); $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? ''; $stdId = strtoupper(trim($_POST['student_id'] ?? ''));
    $contact = trim($_POST['contact'] ?? ''); $sem = trim($_POST['semester'] ?? '');

    if (!$name || !$fname || !$email || !$pass || !$stdId || !$contact || !$sem) { echo json_encode(['success'=>false, 'message'=>'All fields required!']); exit; }
    if (!preg_match('/^BCB-25F-(\d{1,4})$/', $stdId, $matches) || intval($matches[1]) > 1000) { echo json_encode(['success'=>false, 'message'=>'Invalid ID Format (e.g., BCB-25F-000).']); exit; }

    $idProof = null; $pfp = null; $dir = '../uploads/users/'; if (!is_dir($dir)) mkdir($dir, 0777, true);
    if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] === 0) { $idProof = 'uploads/users/'.time().'_id_'.basename($_FILES['id_proof']['name']); move_uploaded_file($_FILES['id_proof']['tmp_name'], '../'.$idProof); }
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) { $pfp = 'uploads/users/'.time().'_pfp_'.basename($_FILES['profile_pic']['name']); move_uploaded_file($_FILES['profile_pic']['tmp_name'], '../'.$pfp); }

    $stmt = $db->prepare('SELECT id FROM users WHERE email=? OR student_id=?'); $stmt->bind_param('ss', $email, $stdId); $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) { echo json_encode(['success'=>false, 'message'=>'Email or ID already exists.']); exit; }

    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $db->prepare('INSERT INTO users (name, father_name, email, contact_no, semester, password, student_id, id_proof, profile_pic) VALUES (?,?,?,?,?,?,?,?,?)');
    $stmt->bind_param('sssssssss', $name, $fname, $email, $contact, $sem, $hash, $stdId, $idProof, $pfp);
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $db->insert_id; $_SESSION['user_name'] = $name; $_SESSION['user_role'] = 'user';
        echo json_encode(['success'=>true, 'message'=>'Registered!', 'name'=>$name, 'role'=>'user']);
    } else { echo json_encode(['success'=>false, 'message'=>'Failed.']); }
    exit;
}

if ($action === 'login') {
    $login = trim($_POST['login'] ?? ''); $pass = $_POST['password'] ?? '';
    $stmt = filter_var($login, FILTER_VALIDATE_EMAIL) ? $db->prepare('SELECT * FROM users WHERE email=?') : $db->prepare('SELECT * FROM users WHERE student_id=?');
    $stmt->bind_param('s', $login); $stmt->execute(); $u = $stmt->get_result()->fetch_assoc();
    if ($u && password_verify($pass, $u['password'])) {
        if ($u['is_blocked'] == 1) { echo json_encode(['success'=>false, 'message'=>'Account blocked.']); exit; }
        $_SESSION['user_id'] = $u['id']; $_SESSION['user_name'] = $u['name']; $_SESSION['user_role'] = $u['role'];
        echo json_encode(['success'=>true, 'name'=>$u['name'], 'role'=>$u['role']]);
    } else { echo json_encode(['success'=>false, 'message'=>'Invalid credentials.']); }
    exit;
}
if ($action === 'logout') { session_destroy(); echo json_encode(['success'=>true]); exit; }
?>