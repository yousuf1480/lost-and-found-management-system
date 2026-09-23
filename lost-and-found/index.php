<?php include 'header.php'; ?>

<div class="page active" id="page-home">
  <div class="hero">
    <h1>Find What You've <em>Lost.</em><br>Return What You've Found.</h1>
    <p>A community-powered lost and found platform for your campus. Post, search, and reconnect with your belongings.</p>
    <div class="hero-btns">
      <button class="btn btn-lost" onclick="window.location.href='report_item.php?type=lost'">🔍 I Lost Something</button>
      <button class="btn btn-found" onclick="window.location.href='report_item.php?type=found'">📦 I Found Something</button>
    </div>
  </div>
  
  <div class="stats-bar" style="display:none;">
    <div class="stat"><div class="stat-num" id="s-total">0</div><div class="stat-label">Total Reports</div></div>
    <div class="stat"><div class="stat-num" id="s-matched">0</div><div class="stat-label">Items Matched</div></div>
    <div class="stat"><div class="stat-num" id="s-lost">0</div><div class="stat-label">Items Lost</div></div>
    <div class="stat"><div class="stat-num" id="s-found">0</div><div class="stat-label">Items Found</div></div>
  </div>
  
  <div class="search-section">
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search for wallet, phone, keys, bag..." oninput="filterItems()">
      <button class="btn btn-primary" onclick="filterItems()">Search</button>
    </div>
    <div class="filters">
      <button class="filter-chip active" onclick="setFilter(this,'all')">All Items</button>
      <button class="filter-chip" onclick="setFilter(this,'lost')">🔴 Lost</button>
      <button class="filter-chip" onclick="setFilter(this,'found')">🟢 Found</button>
      <button class="filter-chip" onclick="setFilter(this,'electronics')">Electronics</button>
      <button class="filter-chip" onclick="setFilter(this,'bags')">Bags</button>
      <button class="filter-chip" onclick="setFilter(this,'keys')">Keys</button>
      <button class="filter-chip" onclick="setFilter(this,'books')">Documents & Books</button>
    </div>
  </div>
  
  <div class="items-grid" id="itemsGrid"></div>
</div>

<?php include 'footer.php'; ?>