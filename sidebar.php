<?php $current = basename($_SERVER['PHP_SELF']); ?>

<!-- Sidebar -->
<aside class="sidebar collapsed" id="mainSidebar">
  <h4 class="mb-4 d-flex align-items-center">
    <i class="bi bi-speedometer2 me-2 text-info"></i><span class="menu-label">DASHBOARD</span>
  </h4>

  <a href="dashboard.php" class="<?= $current == 'dashboard.php' ? 'active' : '' ?>">
    <i class="bi bi-house-door me-2 text-warning"></i><span class="menu-label">Dashboard</span>
  </a>

  <a href="smtp_campaigns.php" class="<?= $current == 'smtp_campaigns.php' ? 'active' : '' ?>">
    <i class="bi bi-envelope-at me-2 text-success"></i><span class="menu-label">SMTP</span>
  </a>

  <a href="contact_campaign.php" class="<?= $current == 'contact_campaign.php' ? 'active' : '' ?>">
    <i class="bi bi-people me-2 text-primary"></i><span class="menu-label">Contacts</span>
  </a>

  <!-- Sender Submenu -->
  <div class="submenu-wrapper">
    <a class="d-flex justify-content-between align-items-center submenu-toggle" data-bs-toggle="collapse"
       href="#senderMenu" role="button"
       aria-expanded="<?= in_array($current, ['template.php', 'campaign.php']) ? 'true' : 'false' ?>"
       aria-controls="senderMenu">
      <span><i class="bi bi-send me-2 text-danger"></i><span class="menu-label">Sender</span></span>
      <i class="bi bi-chevron-down small text-dark"></i>
    </a>
    <div class="collapse submenu-box <?= in_array($current, ['template.php', 'campaign.php']) ? 'show' : '' ?>" id="senderMenu">
      <a href="template.php" class="submenu <?= $current == 'template.php' ? 'active' : '' ?>">&bull; Template</a>
      <a href="campaign.php" class="submenu <?= $current == 'campaign.php' ? 'active' : '' ?>">&bull; Campaign</a>
    </div>
  </div>

  <!-- Shortener Submenu -->
  <div class="submenu-wrapper">
    <a class="d-flex justify-content-between align-items-center submenu-toggle" data-bs-toggle="collapse"
       href="#shortenerMenu" role="button"
       aria-expanded="<?= in_array($current, ['domain.php', 'url.php']) ? 'true' : 'false' ?>"
       aria-controls="shortenerMenu">
      <span><i class="bi bi-link-45deg me-2 text-secondary"></i><span class="menu-label">Shortener</span></span>
      <i class="bi bi-chevron-down small text-dark"></i>
    </a>
    <div class="collapse submenu-box <?= in_array($current, ['domain.php', 'url.php']) ? 'show' : '' ?>" id="shortenerMenu">
      <a href="domain.php" class="submenu <?= $current == 'domain.php' ? 'active' : '' ?>">&bull; Domain</a>
      <a href="url.php" class="submenu <?= $current == 'url.php' ? 'active' : '' ?>">&bull; URL</a>
    </div>
  </div>

  <a href="settings.php" class="<?= $current == 'settings.php' ? 'active' : '' ?>">
    <i class="bi bi-gear me-2 text-info"></i><span class="menu-label">Settings</span>
  </a>

  <hr>

  <a href="logout.php" class="btn btn-primary w-100 mt-2">
    <i class="bi bi-box-arrow-right me-1"></i> Logout
  </a>
</aside>

<!-- Sidebar CSS -->
<style>
.sidebar {
  height: 100vh;
  width: 220px;
  background: linear-gradient(135deg, #d7f0fa, #a2d4ec);
  color: #1a1a2e;
  position: fixed;
  top: 0;
  left: 0;
  padding: 2rem 1rem;
  overflow-y: auto;
  transition: width 0.3s ease, padding 0.3s ease;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
  z-index: 1030;
}

.sidebar.collapsed {
  width: 60px;
  padding: 2rem 0.5rem;
}

.sidebar a {
  display: flex;
  align-items: center;
  padding: 0.5rem;
  border-radius: 6px;
  margin-bottom: 0.3rem;
  color: inherit;
  text-decoration: none;
  transition: 0.3s;
}

.sidebar a:hover,
.sidebar a.active {
  background-color: #00bcd4;
  color: #fff;
  font-weight: 600;
}

.sidebar i {
  font-size: 1.1rem;
  min-width: 20px;
  margin-right: 8px;
}

.menu-label {
  transition: opacity 0.3s ease;
  white-space: nowrap;
}

.sidebar.collapsed .menu-label {
  opacity: 0;
  pointer-events: none;
}

/* Submenu */
.submenu-box {
  padding-left: 1.5rem;
}
.submenu {
  display: block;
  padding: 0.35rem 0.4rem;
  font-size: 0.85rem;
  border-radius: 4px;
  color: #1a1a2e;
}
.submenu:hover,
.submenu.active {
  background: rgba(0, 0, 0, 0.07);
  color: #000;
}

.sidebar hr {
  border-color: rgba(0, 0, 0, 0.1);
}
</style>

<!-- Sidebar JS -->
<script>
const sidebar = document.getElementById('mainSidebar');

// Auto-collapse on mouseleave
sidebar.addEventListener('mouseleave', () => {
  sidebar.classList.add('collapsed');
});

// Auto-expand on mouseenter
sidebar.addEventListener('mouseenter', () => {
  sidebar.classList.remove('collapsed');
});

// Submenu toggle logic
document.querySelectorAll('.submenu-toggle').forEach(toggle => {
  toggle.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    const instance = bootstrap.Collapse.getOrCreateInstance(target);
    instance.toggle();

    // Close others
    document.querySelectorAll('.submenu-box').forEach(box => {
      if (box !== target && box.classList.contains('show')) {
        bootstrap.Collapse.getInstance(box)?.hide();
      }
    });
  });
});
</script>
