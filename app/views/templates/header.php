<?php
$navItems = [];
if (isset($user)) {
  if ($user['role'] === 'Admin') {
    $navItems = [
      ['url' => BASEURL . '/admin', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
      ['url' => BASEURL . '/admin/reservasi', 'label' => 'Reservasi', 'icon' => 'calendar'],
      ['url' => BASEURL . '/admin/dataservice', 'label' => 'Data Service', 'icon' => 'wrench'],
      ['url' => BASEURL . '/admin/pelanggan', 'label' => 'Pelanggan', 'icon' => 'users'],
      ['url' => BASEURL . '/admin/transaksi', 'label' => 'Transaksi', 'icon' => 'credit-card'],
    ];
  } else {
    $navItems = [
      ['url' => BASEURL . '/pelanggan', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
      ['url' => BASEURL . '/pelanggan/buatreservasi', 'label' => 'Buat Reservasi', 'icon' => 'plus-circle'],
      ['url' => BASEURL . '/pelanggan/riwayat', 'label' => 'Riwayat Service', 'icon' => 'clipboard-list'],
    ];
  }
}

$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
  . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AutoFix - <?= htmlspecialchars($title ?? 'Dashboard') ?></title>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/dashboard.css">
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/reservasi.css">
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/dataservice.css">
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/pelanggan.css">
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/transaksi.css">
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/buat-reservasi.css">
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/riwayat.css">
</head>

<body>
  <div id="main">
    <aside class="sidebar">

      <div class="sidebar-header">
        <div class="brand-container">
          <div class="brand-icon-wrapper">
            <i data-lucide="wrench" color="#2d6a4f" width="18" height="18"></i>
          </div>
          <div>
            <div class="brand-title">AutoFix</div>
            <div class="brand-subtitle">Manajemen Bengkel</div>
          </div>
        </div>
        <button id="sidebarClose" class="close-btn">
          <i data-lucide="x" color="rgba(255,255,255,0.7)" width="20" height="20"></i>
        </button>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-title">MENU</div>
        <?php foreach ($navItems as $nav):
          $isActive = (trim($currentUrl, '/') == trim($nav['url'], '/'));
          ?>
          <a href="<?= $nav['url'] ?>" class="nav-item <?= $isActive ? 'active' : '' ?>">
            <div class="nav-icon-wrapper">
              <i data-lucide="<?= $nav['icon'] ?>" width="17" height="17"></i>
            </div>
            <span class="nav-label"><?= $nav['label'] ?></span>
            <?php if ($isActive): ?>
              <div class="active-indicator"></div>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </nav>

      <?php if (isset($user)): ?>
        <div class="user-profile-section">
          <div class="user-profile-container">
            <div class="user-avatar">
              <?= strtoupper(htmlspecialchars(substr($user['nama'], 0, 1))) ?>
            </div>
            <div class="user-info">
              <div class="user-name"><?= htmlspecialchars($user['nama']) ?></div>
              <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <a href="<?= BASEURL ?>/auth/logout" class="logout-btn" title="Logout">
              <i data-lucide="log-out" color="rgba(255,255,255,0.7)" width="16" height="16"></i>
            </a>
          </div>
        </div>
      <?php endif; ?>

    </aside>

    <div class="main-content-wrapper">
      <header class="top-header">
        <div class="header-left">
          <button id="sidebarToggle" class="menu-btn">
            <i data-lucide="menu" width="20" height="20"></i>
          </button>
          <div>
            <h1 class="page-title"><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
            <p class="page-date"><?= date('l, d M Y') ?></p>
          </div>
        </div>
        <div class="header-right">
          <div class="search-container">
            <i data-lucide="search" width="14" height="14" class="search-icon"></i>
            <input type="text" placeholder="Cari..." class="search-input">
          </div>
          <button class="notification-btn">
            <i data-lucide="bell" color="var(--text-medium)" width="18" height="18"></i>
            <span class="notification-badge"></span>
          </button>
        </div>
      </header>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="flash-message flash-success">
          <i data-lucide="check-circle" width="16" height="16"></i>
          <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="flash-message flash-error">
          <i data-lucide="alert-circle" width="16" height="16"></i>
          <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <main class="main-area">
