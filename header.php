<?php
// --- header.php ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current = basename($_SERVER['PHP_SELF']);
$nav = [
    'dashboard.php'        => ['Dashboard',         'bi-speedometer2'],
    'smtp_campaigns.php'   => ['SMTP Servers',      'bi-hdd-network'],
    'template.php'         => ['Templates',         'bi-file-earmark-text'],
    'campaign.php'         => ['Campaigns',         'bi-megaphone'],
    'contact_campaign.php' => ['Contacts',          'bi-person-lines-fill'],
    'domain.php'           => ['Domains',           'bi-globe2'],
    'url.php'              => ['URLs',              'bi-link-45deg'],
    'settings.php'         => ['Settings',          'bi-gear'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $nav[$current][0] ?? 'Dashboard' ?> | MailBurst</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Custom Styling -->
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .navbar-brand {
      font-weight: bold;
      font-size: 1.2rem;
      color: #fff !important;
    }

    .navbar-dark .navbar-nav .nav-link {
      color: rgba(255, 255, 255, 0.85);
      transition: 0.2s ease-in-out;
    }

    .navbar-dark .navbar-nav .nav-link:hover,
    .navbar-dark .navbar-nav .nav-link.active {
      color: #fff !important;
      font-weight: 600;
    }

    .nav-icon {
      margin-right: 5px;
    }

    .offcanvas-title {
      font-weight: 600;
    }

    .btn-outline-light:hover {
      background-color: #ffffff11;
    }

    .nav-item .btn-outline-light {
      font-size: 14px;
      padding: 4px 10px;
    }
  </style>
</head>
<body>

<!-- 🔵 Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid">

    <!-- Brand -->
    <a class="navbar-brand" href="dashboard.php">
      <i class="bi bi-lightning-fill text-warning me-1"></i> MailBurst
    </a>

    <!-- Mobile Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Desktop Nav -->
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <?php foreach ($nav as $file => [$label, $icon]): ?>
          <li class="nav-item">
            <a class="nav-link <?= ($current === $file) ? 'active' : '' ?>" href="<?= $file ?>">
              <i class="bi <?= $icon ?> nav-icon"></i> <?= $label ?>
            </a>
          </li>
        <?php endforeach; ?>
        <li class="nav-item ms-lg-2">
          <a href="logout.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </li>
      </ul>
    </div>

  </div>
</nav>

<!-- 🔹 Offcanvas (Mobile Sidebar) -->
<div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title"><i class="bi bi-list me-1"></i> Menu</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="navbar-nav">
      <?php foreach ($nav as $file => [$label, $icon]): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($current === $file) ? 'active' : '' ?>" href="<?= $file ?>" data-bs-dismiss="offcanvas">
            <i class="bi <?= $icon ?> nav-icon"></i> <?= $label ?>
          </a>
        </li>
      <?php endforeach; ?>
      <li><hr class="dropdown-divider border-secondary"></li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php">
          <i class="bi bi-box-arrow-right nav-icon"></i> Logout
        </a>
      </li>
    </ul>
  </div>
</div>

<!-- 🧱 Begin Page Content Container -->
<div class="container mt-4">
