<?php include 'header.php'; ?>
<div class="page active" id="page-profile" style="max-width:900px; margin:40px auto; padding:0 20px;">
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:30px; display:flex; gap:30px; align-items:center; margin-bottom:40px; flex-wrap:wrap;">
        <img id="myProfilePic" src="https://via.placeholder.com/130" style="width:130px; height:130px; border-radius:50%; object-fit:cover; border:4px solid var(--accent);">
        <div style="flex:1; min-width:250px;">
            <h2 id="myName" style="font-family:'Syne'; margin-bottom:5px; font-size:2rem;">Loading...</h2>
            <h4 id="myStudentId" style="color:var(--accent); margin-bottom:15px;">BCB-25F-XXX</h4>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:15px; background:rgba(0,0,0,0.3); padding:20px; border-radius:12px;">
                <div><small style="color:var(--muted); text-transform:uppercase; font-size:0.75rem;">Father's Name</small><br><span id="myFatherName" style="font-weight:500;">-</span></div>
                <div><small style="color:var(--muted); text-transform:uppercase; font-size:0.75rem;">Email</small><br><span id="myEmail" style="font-weight:500;">-</span></div>
                <div><small style="color:var(--muted); text-transform:uppercase; font-size:0.75rem;">Contact No.</small><br><span id="myContact" style="font-weight:500;">-</span></div>
                <div><small style="color:var(--muted); text-transform:uppercase; font-size:0.75rem;">Semester</small><br><span id="mySemester" style="font-weight:500;">-</span></div>
            </div>
        </div>
    </div>
    
    <h3 style="font-family:'Syne'; font-size:1.4rem; color:var(--accent); border-bottom:1px solid var(--border); padding-bottom:10px; margin-bottom:20px;">My Reported Items</h3>
    <div class="items-grid" id="myItemsGrid" style="padding:0;"></div>
</div>
<?php include 'footer.php'; ?>