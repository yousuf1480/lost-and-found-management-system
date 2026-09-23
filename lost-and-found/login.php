<?php include 'header.php'; ?>

<div class="page active" id="page-auth">
  <div class="auth-container">
    <h2>Welcome back 👋</h2>
    <p>Sign in to report items and track your listings.</p>

    <div class="auth-toggle">
      <button class="active" id="loginTab" onclick="switchAuth('login')">Login</button>
      <button id="registerTab" onclick="switchAuth('register')">Register</button>
    </div>

    <div id="loginFieldGroup">
      <div class="form-group"><label>Email or Student ID</label><input type="text" id="authLogin" placeholder="you@university.edu or BCB-25F-000"></div>
    </div>

    <div id="registerFields" style="display:none">
      <div class="form-row">
          <div class="form-group"><label>Full Name *</label><input type="text" id="regName" placeholder="Ali Raza"></div>
          <div class="form-group"><label>Father's Name *</label><input type="text" id="regFatherName"></div>
      </div>
      <div class="form-row">
          <div class="form-group"><label>Student ID *</label><input type="text" id="regStudentId" placeholder="BCB-25F-000"></div>
          <div class="form-group"><label>Contact *</label><input type="text" id="regContact" placeholder="0300-XXXXXXX"></div>
      </div>
      <div class="form-group" id="emailGroup"><label>Email Address *</label><input type="email" id="authEmail" placeholder="you@university.edu"></div>
      <div class="form-group"><label>Semester *</label><select id="regSemester"><option>1st</option><option>2nd</option><option>3rd</option><option>4th</option></select></div>
      <div class="form-row">
          <div class="form-group"><label>Profile Pic *</label><input type="file" id="regProfilePic" accept="image/*"></div>
          <div class="form-group"><label>ID Card *</label><input type="file" id="regIdProof" accept="image/*"></div>
      </div>
    </div>

    <div class="form-group"><label>Password *</label><input type="password" id="authPass" placeholder="••••••••"></div>
    <button class="btn btn-primary form-submit" onclick="doAuth()">Login</button>
  </div>
</div>

<?php include 'footer.php'; ?>