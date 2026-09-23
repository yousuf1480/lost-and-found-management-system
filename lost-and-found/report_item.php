<?php include 'header.php'; ?>

<div class="page active" id="page-report">
  <div class="form-container">
    <h2>Report an Item</h2>
    <p>Fill in the details below. The more info you provide, the higher the chance of a match.</p>
    
    <div class="type-select">
      <div class="type-opt lost-opt selected" id="type-lost" onclick="selectType('lost')">
        <span class="type-icon">😔</span>
        <div class="type-label">I Lost It</div>
        <div class="type-sub">Report something you lost</div>
      </div>
      <div class="type-opt found-opt" id="type-found" onclick="selectType('found')">
        <span class="type-icon">🎉</span>
        <div class="type-label">I Found It</div>
        <div class="type-sub">Report something you found</div>
      </div>
    </div>
    
    <div class="form-group"><label>Item Name *</label><input type="text" id="f-name" placeholder="e.g. Blue Nike Backpack"></div>
    <div class="form-row">
      <div class="form-group"><label>Category *</label><select id="f-category"><option value="electronics">Electronics</option><option value="wallet">Wallet</option><option value="keys">Keys</option><option value="books">Documents</option><option value="bags">Bags</option><option value="other" selected>Other</option></select></div>
      <div class="form-group"><label>Date *</label><input type="date" id="f-date"></div>
    </div>
    <div class="form-group"><label>Location *</label><input type="text" id="f-location" placeholder="e.g. Library 2nd Floor"></div>
    <div class="form-group"><label>Description *</label><textarea id="f-desc" placeholder="Describe the item in detail..."></textarea></div>
    
    <div class="form-group">
      <label>Photo (optional)</label>
      <input type="file" id="f-image" accept="image/*" style="display:none" onchange="previewImage(this)">
      <div class="upload-area" onclick="document.getElementById('f-image').click()">
        <div class="upload-icon" id="previewIcon">📷</div>
        <div class="upload-text" id="previewText"><strong>Click to upload</strong> or drag and drop<br>PNG, JPG up to 5MB</div>
      </div>
    </div>
    
    <button class="btn btn-primary form-submit" onclick="submitReport()">Submit Report</button>
  </div>
</div>

<?php include 'footer.php'; ?>