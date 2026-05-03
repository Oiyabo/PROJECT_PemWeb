<?php
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

require_once 'data.php';

$user = $_SESSION['user'];
$role = $user['role'];

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

if ($role === 'Admin') {
  $navItems = [
    ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
    ['id' => 'reservasi', 'label' => 'Reservasi', 'icon' => 'calendar'],
    ['id' => 'dataservice', 'label' => 'Data Service', 'icon' => 'wrench'],
    ['id' => 'pelanggan', 'label' => 'Pelanggan', 'icon' => 'users'],
    ['id' => 'transaksi', 'label' => 'Transaksi', 'icon' => 'credit-card'],
  ];
  $allowedPages = array_column($navItems, 'id');
} else {
  $navItems = [
    ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
    ['id' => 'buat-reservasi', 'label' => 'Buat Reservasi', 'icon' => 'plus-circle'],
    ['id' => 'riwayat', 'label' => 'Riwayat Service', 'icon' => 'clipboard-list'],
  ];
  $allowedPages = array_column($navItems, 'id');
}

if (!in_array($page, $allowedPages)) {
  $page = 'dashboard';
}

$pageTitles = [
  'dashboard' => 'Dashboard',
  'reservasi' => 'Manajemen Reservasi',
  'dataservice' => 'Data Service',
  'pelanggan' => 'Data Pelanggan',
  'transaksi' => 'Transaksi & Pembayaran',
  'buat-reservasi' => 'Buat Reservasi Baru',
  'riwayat' => 'Riwayat Service',
];
$title = $pageTitles[$page] ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AutoFix -
    <?= htmlspecialchars($title) ?>
  </title>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="style/pelanggan.css">
  <link rel="stylesheet" href="style/dataservice.css">
  <link rel="stylesheet" href="style/dashboard.css">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div id="main">
    <aside class="sidebar">

      <div class="sidebar-header">
        <div class="brand-container">
          <div class="brand-icon-wrapper">
            <i data-lucide="wrench" color="var(--text-dark)" width="18" height="18"></i>
          </div>
          <div>
            <div class="brand-title">AutoFix</div>
            <div class="brand-subtitle">Manajemen Bengkel</div>
          </div>
        </div>
        <button id="sidebarClose" class="close-btn">
          <i data-lucide="x" color="var(--text-light)" width="20" height="20"></i>
        </button>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-title">Menu</div>
        <?php foreach ($navItems as $nav): ?>
          <?php $isActive = $page === $nav['id']; ?>
          <a href="?page=<?= $nav['id'] ?>" class="nav-item <?= $isActive ? 'active' : '' ?>">
            <div class="nav-icon-wrapper">
              <i data-lucide="<?= $nav['icon'] ?>" width="17" height="17"></i>
            </div>
            <span class="nav-label">
              <?= $nav['label'] ?>
            </span>
            <?php if ($isActive): ?>
              <div class="active-indicator"></div>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </nav>

      <div class="user-profile-section">
        <div class="user-profile-container">
          <div class="user-avatar">
            <?= substr($user['nama'], 0, 1) ?>
          </div>
          <div class="user-info">
            <div class="user-name">
              <?= htmlspecialchars($user['nama']) ?>
            </div>
            <div class="user-email">
              <?= htmlspecialchars($user['email']) ?>
            </div>
          </div>
          <a href="proses.php?logout=1" class="logout-btn">
            <i data-lucide="log-out" color="var(--text-light)" width="16" height="16"></i>
          </a>
        </div>
      </div>

    </aside>

    <div class="main-content-wrapper">
      <header class="top-header">
        <div class="header-left">
          <button id="sidebarToggle" class="menu-btn">
            <i data-lucide="menu" width="20" height="20"></i>
          </button>
          <div>
            <h1 class="page-title">
              <?= $title ?>
            </h1>
            <p class="page-date">Kamis, 30 April 2026</p>
          </div>
        </div>
        <div class="header-right">
          <button class="search-btn">
            <i data-lucide="search" width="14" height="14"></i> <span class="search-text">Cari...</span>
          </button>
          <button class="notification-btn">
            <i data-lucide="bell" color="var(--text-medium)" width="16" height="16"></i>
            <span class="notification-badge"></span>
          </button>
        </div>
      </header>

      <main class="main-area">
        <?php include "pages/{$page}.php"; ?>
      </main>
    </div>
  </div>

  <script>
    lucide.createIcons();
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');

    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('toggled');
    });

    sidebarClose.addEventListener('click', () => {
      sidebar.classList.remove('toggled');
    });

    document.addEventListener('click', (e) => {
      if (window.matchMedia('(orientation: portrait)').matches) {
        if (sidebar.classList.contains('toggled') && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
          sidebar.classList.remove('toggled');
        }
      }
    });
  </script>
</body>

</html>