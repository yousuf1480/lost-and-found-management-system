<?php include 'header.php'; ?>
<div class="admin-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;"><h2 style="font-family:'Syne';">Admin Dashboard</h2></div>
    
    <div style="display:flex; gap:10px; margin-bottom:25px;">
        <button class="btn btn-outline" onclick="switchAdminTab('reports')">Reports</button>
        <button class="btn btn-outline" onclick="switchAdminTab('matches')">Auto-Matches</button>
        <button class="btn btn-outline" onclick="switchAdminTab('users')">Users</button>
    </div>

    <div class="admin-stats">
        <div class="admin-stat-card"><h2 style="color:var(--text)" id="s-total">0</h2><small style="color:var(--muted)">Total Items</small></div>
        <div class="admin-stat-card"><h2 style="color:var(--lost)" id="s-lost">0</h2><small style="color:var(--muted)">Lost</small></div>
        <div class="admin-stat-card"><h2 style="color:var(--found)" id="s-found">0</h2><small style="color:var(--muted)">Found</small></div>
        <div class="admin-stat-card"><h2 style="color:var(--accent)" id="s-matched">0</h2><small style="color:var(--muted)">Resolved</small></div>
    </div>

    <div id="adminReportsView">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:var(--surface2);"><tr><th>Title</th><th>Type</th><th>Reporter</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="adminTable"></tbody>
        </table>
    </div>

    <div id="adminUsersView" style="display:none;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:var(--surface2);"><tr><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr></thead>
            <tbody id="adminUsersTable"></tbody>
        </table>
    </div>

    <div id="adminMatchesView" style="display:none;">
        <div id="adminMatchesContainer"></div>
    </div>
</div>

<!-- Admin Chat Monitor Modal -->
<div id="adminChatModal" class="modal-overlay">
    <div class="modal">
        <h3 style="font-family:'Syne'; color:var(--accent); margin-bottom:15px;">Monitor Chats</h3>
        <button class="close-btn" onclick="document.getElementById('adminChatModal').classList.remove('show')">&times;</button>
        <div id="adminChatBody" style="max-height:60vh; overflow-y:auto;"></div>
    </div>
</div>
<?php include 'footer.php'; ?>