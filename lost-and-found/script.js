let currentFilter = 'all'; let currentType = 'lost'; let authMode = 'login'; let myUserId = null; let activeChatUser = null; let activeItem = null; let adminFilter = 'all'; 
let globalItemsList = []; // Array required for item details modal

function showToast(msg, type) {
    let t = document.getElementById('toast'); if(!t) { t=document.createElement('div'); t.id='toast'; document.body.appendChild(t); }
    t.textContent = msg; t.className = 'toast show ' + (type||''); setTimeout(() => t.className='toast', 3000);
}

// ── AUTH ──
async function checkLogin() {
    try {
        const r = await fetch('api/auth.php?action=check'); const j = await r.json();
        if(j.logged_in) {
            myUserId = j.user_id; if(j.role === 'admin' && document.getElementById('navAdminLink')) document.getElementById('navAdminLink').style.display='inline-block';
            ['loginBtn'].forEach(i=>{if(document.getElementById(i)) document.getElementById(i).style.display='none';});
            ['logoutBtn','navReportBtn','inboxBtn','notifyBtn','navProfileBtn'].forEach(i=>{if(document.getElementById(i)) document.getElementById(i).style.display='inline-block';});
            fetch('api/messages.php?action=unread_count').then(r=>r.json()).then(d=>{if(d.count>0){const b=document.getElementById('inboxBadge'); if(b){ b.innerText=d.count; b.style.display='flex';}}});
            fetch('api/notifications.php?action=unread_count').then(r=>r.json()).then(d=>{if(d.count>0){const b=document.getElementById('notifyBadge'); if(b){ b.innerText=d.count; b.style.display='flex';}}});
            return true;
        } else { if(document.getElementById('loginBtn')) document.getElementById('loginBtn').style.display='inline-block'; return false; }
    } catch(e) { return false; }
}

async function logoutUser() { await fetch('api/auth.php?action=logout'); window.location.href='login.php'; }

function switchAuth(m) {
    authMode = m; const l = (m==='login');
    document.getElementById('registerFields').style.display = l ? 'none' : 'block'; document.getElementById('loginFieldGroup').style.display = l ? 'block' : 'none';
    document.getElementById('loginTab').className = l ? 'active' : ''; document.getElementById('registerTab').className = !l ? 'active' : '';
}

async function doAuth() {
    const f = new FormData(); f.append('password', document.getElementById('authPass').value);
    if(authMode==='login') { f.append('action', 'login'); f.append('login', document.getElementById('authLogin').value); } 
    else {
        f.append('action', 'register'); f.append('name', document.getElementById('regName').value); f.append('email', document.getElementById('authEmail').value);
        f.append('father_name', document.getElementById('regFatherName').value); f.append('student_id', document.getElementById('regStudentId').value);
        f.append('contact', document.getElementById('regContact').value); f.append('semester', document.getElementById('regSemester').value);
        if(document.getElementById('regProfilePic').files[0]) f.append('profile_pic', document.getElementById('regProfilePic').files[0]); 
        if(document.getElementById('regIdProof').files[0]) f.append('id_proof', document.getElementById('regIdProof').files[0]);
    }
    const r = await fetch('api/auth.php', {method:'POST', body:f}); const j = await r.json();
    if(j.success) { showToast('Welcome!'); setTimeout(()=>window.location.href='index.php',800); } else showToast(j.message, 'error');
}

// ── ITEMS & REPORT ──
function selectType(t) { currentType = t; if(document.getElementById('type-lost')) document.getElementById('type-lost').classList.toggle('selected', t==='lost'); if(document.getElementById('type-found')) document.getElementById('type-found').classList.toggle('selected', t==='found'); }

async function submitReport() {
    const f = new FormData(); f.append('action','create'); f.append('type', currentType); f.append('title', document.getElementById('f-name').value);
    f.append('category', document.getElementById('f-category').value); f.append('date', document.getElementById('f-date').value);
    f.append('location', document.getElementById('f-location').value); f.append('description', document.getElementById('f-desc').value);
    const pic = document.getElementById('f-image'); if(pic && pic.files[0]) f.append('image', pic.files[0]);
    const r = await fetch('api/items.php', {method:'POST', body:f}); const j = await r.json();
    if(j.success) { showToast('Submitted!'); setTimeout(()=>window.location.href='index.php',1000); } else showToast(j.message, 'error');
}

function previewImage(input) {
    const icon = document.getElementById('previewIcon'); const text = document.getElementById('previewText');
    if(icon && text && input.files && input.files[0]) {
        const reader = new FileReader(); reader.onload = (e) => { icon.innerHTML = `<img src="${e.target.result}" style="max-height:80px;border-radius:8px;">`; text.innerHTML = input.files[0].name; }; reader.readAsDataURL(input.files[0]);
    }
}

async function loadItems() {
    if(!document.getElementById('itemsGrid')) return;
    const q = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
    let cat = ''; if(!['all','lost','found'].includes(currentFilter)) cat = currentFilter;
    let typ = ''; if(['lost','found'].includes(currentFilter)) typ = currentFilter;
    
    const r = await fetch(`api/items.php?action=list&status=open&search=${q}&category=${cat}&type=${typ}`); const j = await r.json();
    if(j.success) {
        globalItemsList = j.data; 
        if(j.data.length === 0) { document.getElementById('itemsGrid').innerHTML = '<p style="text-align:center; color:var(--muted); grid-column:1/-1; padding:40px;">No items found.</p>'; return; }
        
        document.getElementById('itemsGrid').innerHTML = j.data.map(i=>`
        <div class="item-card" onclick="showItemDetails(${i.id})">
            <span class="item-badge badge-${i.type}">${i.type.toUpperCase()}</span>
            ${i.image_path ? `<img src="${i.image_path}" class="item-img">` : `<div class="item-img">${i.type==='lost'?'❓':'📦'}</div>`}
            <h3 style="color:var(--accent); font-family:'Syne'; font-size:1.1rem; margin-bottom:10px;">${i.title}</h3>
            <p style="color:var(--muted); font-size:0.85rem; margin-bottom:15px;">📍 ${i.location}</p>
            <div style="margin-top:auto; padding-top:15px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <small style="color:var(--muted)">👤 ${i.reporter_name}</small>
                <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); openChatDirect(${i.user_id}, ${i.id}, '${i.title.replace(/'/g, "\\'")}')">Message</button>
            </div>
        </div>`).join('');
    }
}

function showItemDetails(id) {
    const item = globalItemsList.find(i => i.id === id); if(!item) return;
    const modal = document.getElementById('itemDetailsModal'); if(!modal) return;
    
    document.getElementById('modalItemTitle').textContent = item.title; 
    document.getElementById('modalItemDesc').textContent = item.description; 
    document.getElementById('modalItemLoc').textContent = item.location; 
    document.getElementById('modalItemDate').textContent = item.date_lost_found;
    document.getElementById('modalItemUser').textContent = item.reporter_name; 
    document.getElementById('modalUserInitial').textContent = item.reporter_name.charAt(0).toUpperCase();
    
    const badge = document.getElementById('modalItemBadge'); 
    badge.textContent = item.type.toUpperCase(); badge.className = `item-badge badge-${item.type}`;
    
    const imgDiv = document.getElementById('modalItemImage'); const fallback = document.getElementById('modalItemFallback');
    if (item.image_path && item.image_path !== 'null') { imgDiv.src = item.image_path; imgDiv.style.display = 'block'; fallback.style.display='none'; } 
    else { imgDiv.style.display = 'none'; fallback.style.display='block'; fallback.textContent = item.type==='lost'?'❓':'📦'; }
    
    document.getElementById('modalContactBtn').onclick = () => { modal.classList.remove('show'); openChatDirect(item.user_id, item.id, item.title); };
    modal.classList.add('show');
}

function filterItems() { setTimeout(loadItems, 300); }
function setFilter(el, val) { currentFilter = val; document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active')); el.classList.add('active'); filterItems(); }

async function loadStats() {
    if(!document.getElementById('s-total')) return;
    const r = await fetch('api/admin.php?action=stats'); const j = await r.json();
    if(j.success) {
        document.getElementById('s-total').textContent = j.data.total_items; document.getElementById('s-matched').textContent = j.data.resolved_items;
        document.getElementById('s-lost').textContent = j.data.lost_items; document.getElementById('s-found').textContent = j.data.found_items;
    }
}

// ── CHAT ENGINE ──
async function openChatDirect(uId, iId, title) {
    if(!myUserId) return showToast('Login required', 'error'); await openInbox(); activeChatUser = uId; activeItem = iId;
    document.getElementById('chatActiveUser').innerHTML = `<strong style="color:var(--accent)">Regarding: ${title}</strong>`;
    document.getElementById('chatMessagesArea').innerHTML = ''; document.getElementById('chatReplyArea').style.display='flex';
}

async function openInbox() {
    document.getElementById('inboxOverlay').classList.add('show');
    const r = await fetch('api/messages.php?action=get'); const j = await r.json();
    if(j.success) {
        const chats = {}; j.data.forEach(m=>{ let o = (m.sender_id==myUserId)?m.receiver_id:m.sender_id; if(!chats[o]) chats[o]={id:o, name:m.sender_id==myUserId?m.receiver_name:m.sender_name, pfp:m.sender_id==myUserId?m.receiver_pfp:m.sender_pfp, contact:m.sender_id==myUserId?m.receiver_contact:m.sender_contact, sem:m.sender_id==myUserId?m.receiver_sem:m.sender_sem, item:m.item_id, msgs:[]}; chats[o].msgs.push(m); });
        document.getElementById('chatContactsList').innerHTML = Object.values(chats).map(u=>`<div style="padding:15px; border-bottom:1px solid var(--border); cursor:pointer; display:flex; gap:10px; align-items:center;" onclick='openChatWith(${JSON.stringify(u).replace(/'/g, "&#39;")})'><img src="${u.pfp||'https://via.placeholder.com/40'}" style="width:45px;height:45px;border-radius:50%;object-fit:cover;"><div><div style="font-weight:bold; color:var(--text);">${u.name}</div><div style="font-size:0.75rem; color:var(--muted)">Chat</div></div></div>`).join('');
    }
}

function openChatWith(u) {
    activeChatUser = u.id; activeItem = u.item; document.getElementById('chatActiveUser').innerHTML = `<strong style="color:var(--accent)">${u.name}</strong><br><small style="color:var(--muted)">📞 ${u.contact} | ${u.sem}</small>`;
    document.getElementById('chatMessagesArea').innerHTML = u.msgs.map(m=>`<div class="chat-bubble ${m.sender_id==myUserId?'sent':'received'}">${m.message}</div>`).join('');
    document.getElementById('chatReplyArea').style.display='flex'; fetch('api/messages.php', {method:'POST', body:new URLSearchParams({action:'read',sender_id:u.id})});
}

async function sendQuickReply() {
    const t = document.getElementById('quickReplyText').value.trim(); if(!t) return; const f = new FormData(); f.append('action','send'); f.append('item_id',activeItem); f.append('receiver_id',activeChatUser); f.append('message',t);
    if((await (await fetch('api/messages.php',{method:'POST',body:f})).json()).success) { document.getElementById('quickReplyText').value=''; openInbox(); setTimeout(()=>{document.querySelector('#chatContactsList > div')?.click()}, 200); }
}
function closeInbox() { document.getElementById('inboxOverlay').classList.remove('show'); }

// ── ALERTS & PROFILE ──
async function openNotifications() {
    document.getElementById('notifyOverlay').classList.add('show');
    const r = await fetch('api/notifications.php?action=get'); const j = await r.json();
    if(j.success) {
        document.getElementById('notifyBody').innerHTML = j.data.length ? j.data.map(m=>`<div style="background:var(--surface2); padding:12px; border-radius:10px; margin-bottom:10px; border-left:4px solid var(--accent);"><small style="color:var(--muted)">${m.created_at}</small><p style="margin:0; color:var(--text);">${m.message}</p></div>`).join('') : '<p style="text-align:center; color:var(--muted);">No alerts</p>';
        fetch('api/notifications.php?action=read'); const b=document.getElementById('notifyBadge'); if(b) b.style.display='none';
    }
}

async function loadUserProfile() {
    const r = await fetch('api/auth.php?action=get_profile'); const j = await r.json();
    if(j.success) { ['myName','myStudentId','myFatherName','myEmail','myContact','mySemester'].forEach(id=>{const m={myName:'name',myStudentId:'student_id',myFatherName:'father_name',myEmail:'email',myContact:'contact_no',mySemester:'semester'}; if(document.getElementById(id)) document.getElementById(id).textContent=j.data[m[id]];}); if(j.data.profile_pic && document.getElementById('myProfilePic')) document.getElementById('myProfilePic').src=j.data.profile_pic; }
}

async function loadMyItems() {
    if(!document.getElementById('myItemsGrid')) return; const r = await fetch('api/items.php?action=my_items'); const j = await r.json();
    if(j.success) {
        const myStrictData = j.data.filter(item => item.user_id == myUserId);
        if(myStrictData.length === 0) { document.getElementById('myItemsGrid').innerHTML = '<div style="grid-column:1/-1; text-align:center; color:var(--muted); padding:40px;">No items reported.</div>'; return; }
        document.getElementById('myItemsGrid').innerHTML = myStrictData.map(i=>`<div class="item-card"><div style="display:flex;justify-content:space-between;margin-bottom:10px;"><span style="color:${i.type==='lost'?'var(--lost)':'var(--found)'}; font-weight:bold; text-transform:uppercase;">${i.type}</span><span class="status-pill ${i.status==='resolved'?'pill-resolved':'pill-pending'}">${i.status.toUpperCase()}</span></div><h4 style="color:var(--accent)">${i.title}</h4><div style="display:flex;gap:10px;margin-top:auto;padding-top:15px;border-top:1px solid var(--border)">${i.status!=='resolved'?`<button class="btn btn-outline btn-sm" style="flex:1;color:var(--found)" onclick="resolveMyItem(${i.id})">Mark Done</button>`:''}<button class="btn btn-outline btn-sm" style="flex:1;color:var(--lost)" onclick="deleteMyItem(${i.id})">Delete</button></div></div>`).join('');
    }
}

async function resolveMyItem(id) { if(confirm("Resolved?")) { await fetch('api/items.php', {method:'POST', body:new URLSearchParams({action:'resolve_my',id:id})}); loadMyItems(); } }
async function deleteMyItem(id) { if(confirm("Delete?")) { await fetch('api/items.php', {method:'POST', body:new URLSearchParams({action:'delete_my',id:id})}); loadMyItems(); } }

// ── ADMIN LOGIC ──
function switchAdminTab(t) { ['reports','users','matches'].forEach(x=>{const e=document.getElementById(`admin${x.charAt(0).toUpperCase()+x.slice(1)}View`); if(e) e.style.display=(x===t)?'block':'none';}); if(t==='reports')loadAdminData(); else if(t==='users')loadAdminUsers(); else loadAdminMatches(); }
async function loadAdminData() {
    if(!document.getElementById('adminTable')) return; const r = await fetch('api/admin.php?action=all_items'); const j = await r.json();
    if(j.success) document.getElementById('adminTable').innerHTML = j.data.map(i=>`<tr><td>${i.title}</td><td>${i.type}</td><td>${i.reporter_name}</td><td>${i.status}</td><td><button class="btn btn-sm btn-outline" onclick="viewAdminChats(${i.id})">Chats</button> <button class="btn btn-sm btn-outline" style="color:var(--found)" onclick="approveItem(${i.id})">Approve</button> <button class="btn btn-sm btn-outline" style="color:var(--lost)" onclick="deleteItem(${i.id})">Del</button></td></tr>`).join('');
}
async function loadAdminUsers() {
    const r = await fetch('api/admin.php?action=all_users'); const j = await r.json();
    if(j.success) document.getElementById('adminUsersTable').innerHTML = j.data.map(u=>`<tr><td>${u.name}</td><td>${u.email}</td><td>${u.is_blocked==1?'Blocked':'Active'}</td><td>${u.role!=='admin'?`<button class="btn btn-sm btn-outline" onclick="toggleBlock(${u.id}, ${u.is_blocked==1?0:1})">${u.is_blocked==1?'Unblock':'Block'}</button>`:''}</td></tr>`).join('');
}
async function loadAdminMatches() {
    const r = await fetch('api/admin.php?action=get_matches'); const j = await r.json();
    if(j.success) document.getElementById('adminMatchesContainer').innerHTML = j.data.map(m=>`<div style="background:var(--surface2); padding:15px; border-radius:10px; margin-bottom:15px; display:flex; justify-content:space-between;"><div><span style="color:var(--lost)">Lost: ${m.lost_title} (${m.lost_user})</span> <br> <span style="color:var(--found)">Found: ${m.found_title} (${m.found_user})</span></div><div><button class="btn btn-sm btn-primary" onclick="verifyMatch(${m.match_id}, 'approved')">Approve Match</button></div></div>`).join('');
}

async function toggleBlock(id, st) { await fetch('api/admin.php', {method:'POST', body:new URLSearchParams({action:'toggle_block',id:id,status:st})}); loadAdminUsers(); }
async function approveItem(id) { await fetch('api/admin.php', {method:'POST', body:new URLSearchParams({action:'approve',id:id})}); loadAdminData(); }
async function deleteItem(id) { await fetch('api/admin.php', {method:'POST', body:new URLSearchParams({action:'delete_item',id:id})}); loadAdminData(); }
async function verifyMatch(id, st) { await fetch('api/admin.php', {method:'POST', body:new URLSearchParams({action:'verify_match',match_id:id,status:st})}); loadAdminMatches(); }
async function viewAdminChats(id) {
    document.getElementById('adminChatModal').classList.add('show'); const r = await fetch(`api/admin.php?action=get_item_chats&item_id=${id}`); const j = await r.json();
    document.getElementById('adminChatBody').innerHTML = j.data.length ? j.data.map(c=>`<div style="background:var(--surface2); padding:10px; margin-bottom:10px; border-radius:8px;"><small style="color:var(--accent)">${c.sender_name} to ${c.receiver_name}</small><p>${c.message}</p></div>`).join('') : '<p>No chats.</p>';
}

// ── INIT ──
document.addEventListener("DOMContentLoaded", async () => {
    const isLoggedIn = await checkLogin(); 
    
    // Auto-select Type if coming from landing page
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('type')) { selectType(urlParams.get('type')); }
    
    if (document.getElementById('registerFields')) switchAuth('login');
    if (document.getElementById('itemsGrid')) { loadItems(); if(isLoggedIn) { const sb = document.querySelector('.stats-bar'); if(sb) sb.style.display='flex'; loadStats(); } }
    if (document.getElementById('page-profile')) { loadUserProfile(); loadMyItems(); }
    if (document.getElementById('f-date')) document.getElementById('f-date').value = new Date().toISOString().split('T')[0];
    
    if (document.getElementById('adminReportsView')) { 
        switchAdminTab('reports'); 
        fetch('api/admin.php?action=stats').then(r=>r.json()).then(j=>{if(j.success){document.getElementById('s-total').textContent=j.data.total_items; document.getElementById('s-matched').textContent=j.data.resolved_items; document.getElementById('s-lost').textContent=j.data.lost_items; document.getElementById('s-found').textContent=j.data.found_items;}}).catch(e=>{}); 
    }
    
    document.querySelectorAll('.modal-overlay').forEach(el => el.addEventListener('click', function(e) { if(e.target===this) this.classList.remove('show'); }));

    // Drag & Drop Image Logic
    const dropZone = document.querySelector('.upload-area'); 
    const fileInput = document.getElementById('f-image');
    if (dropZone && fileInput) {
        window.addEventListener("dragover", function(e){ e.preventDefault(); }, false);
        window.addEventListener("drop", function(e){ e.preventDefault(); }, false);
        dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.classList.add('drag-active'); });
        dropZone.addEventListener('dragleave', function(e) { e.preventDefault(); dropZone.classList.remove('drag-active'); });
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault(); dropZone.classList.remove('drag-active');
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files; previewImage(fileInput); 
            }
        });
    }

    // 🔥 FIX 1: ENTER KEY FOR LOGIN & REGISTER 🔥
    const authBox = document.querySelector('.auth-container');
    if (authBox) {
        authBox.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Browser ko reload hone se rokta hai
                doAuth(); // Login/Register button ka function chala dega
            }
        });
    }

    // 🔥 FIX 2: ENTER KEY FOR CHAT MESSAGES 🔥
    const chatInput = document.getElementById('quickReplyText');
    if (chatInput) {
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendQuickReply(); // Message send kar dega
            }
        });
    }
});