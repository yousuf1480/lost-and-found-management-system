<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FindIt — Lost & Found System</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
  <a href="index.php" class="logo" style="text-decoration: none;">Find<span>It</span></a>
  <div class="nav-links">
    <a href="index.php" class="<?php echo ($currentPage == 'index.php' || $currentPage == '') ? 'active' : ''; ?>">Browse</a>
    <a href="report_item.php" class="<?php echo ($currentPage == 'report_item.php') ? 'active' : ''; ?>">Report Item</a>
    <a href="admin_dashboard.php" class="<?php echo ($currentPage == 'admin_dashboard.php') ? 'active' : ''; ?>" id="navAdminLink" style="display:none;">Admin</a>
    <a href="profile.php" class="<?php echo ($currentPage == 'profile.php') ? 'active' : ''; ?>" id="navProfileBtn" style="display:none;">👤 Profile</a>
    
    <button class="btn btn-outline btn-sm" id="notifyBtn" style="display:none; position:relative; border-color:var(--accent); color:var(--accent);" onclick="openNotifications()">
        🔔 Alerts <span id="notifyBadge" class="badge-bubble">0</span>
    </button>
    
    <button class="btn btn-outline btn-sm" id="inboxBtn" style="display:none; position:relative;" onclick="openInbox()">
        ✉️ Inbox <span id="inboxBadge" class="badge-bubble" style="background:#ff6b6b;">0</span>
    </button>

    <a href="login.php" class="btn btn-outline btn-sm" id="loginBtn" style="text-decoration: none;">Login</a>
    <button class="btn btn-primary btn-sm" onclick="window.location.href='report_item.php'" style="display:none;" id="navReportBtn">+ Report</button>
    <button class="btn btn-lost btn-sm" onclick="logoutUser()" style="display:none;" id="logoutBtn">Logout</button>
  </div>
</nav>

<!-- ALERTS MODAL -->
<div id="notifyOverlay" class="modal-overlay">
    <div class="modal">
        <h3 style="font-family:'Syne'; color:var(--accent); margin-bottom:15px; border-bottom:1px solid var(--border); padding-bottom:10px;">🔔 Alerts</h3>
        <button class="close-btn" onclick="document.getElementById('notifyOverlay').classList.remove('show')">&times;</button>
        <div id="notifyBody" style="max-height: 60vh; overflow-y: auto;"></div>
    </div>
</div>

<!-- CHAT MODAL (WHATSAPP STYLE) -->
<div id="inboxOverlay" class="modal-overlay">
    <div class="modal chat-modal" style="padding:0; max-width:900px; width:95%; height:80vh;">
        <div class="chat-sidebar">
            <div style="padding:20px; border-bottom:1px solid var(--border);"><h3 style="font-family:'Syne'; color:var(--accent); margin:0;">✉️ Messages</h3></div>
            <div id="chatContactsList" style="flex:1; overflow-y:auto;"></div>
        </div>
        <div class="chat-main" style="position:relative;">
            <div style="padding:15px 20px; background:var(--surface2); border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <div id="chatActiveUser">Select a chat</div>
                <button class="close-btn" style="position:relative; top:0; right:0;" onclick="closeInbox()">&times;</button>
            </div>
            <div id="chatMessagesArea" style="flex:1; padding:20px; overflow-y:auto; display:flex; flex-direction:column;"></div>
            <div id="chatReplyArea" style="padding:15px; background:var(--surface); border-top:1px solid var(--border); display:none; gap:10px;">
                <input type="text" id="quickReplyText" style="flex:1; padding:12px; border-radius:20px; border:1px solid var(--border); background:var(--surface2); color:var(--text); outline:none;" placeholder="Type message...">
                <button class="btn btn-primary" onclick="sendQuickReply()">Send</button>
            </div>
        </div>
    </div>
</div>