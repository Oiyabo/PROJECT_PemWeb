<?php
$navItems = [];

if (isset($user)) {
  if ($user['role'] === 'Admin') {
    $navItems = [
      [
        'url' => BASEURL . '/admin',
        'label' => 'Dashboard',
        'icon' => 'layout-dashboard',
        'key' => 'dashboard'
      ],
      [
        'url' => BASEURL . '/admin/reservasi',
        'label' => 'Reservasi',
        'icon' => 'clipboard-list',
        'key' => 'reservasi'
      ],
      [
        'url' => BASEURL . '/admin/jadwal',
        'label' => 'Jadwal',
        'icon' => 'calendar-days',
        'key' => 'jadwal'
      ],
      [
        'url' => BASEURL . '/admin/pelanggan',
        'label' => 'Pelanggan',
        'icon' => 'users',
        'key' => 'pelanggan'
      ],
    ];
  } else {
    $navItems = [
      [
        'url' => BASEURL . '/pelanggan',
        'label' => 'Dashboard',
        'icon' => 'layout-dashboard',
        'key' => 'dashboard'
      ],
      [
        'url' => BASEURL . '/pelanggan/buatreservasi',
        'label' => 'Buat Reservasi',
        'icon' => 'plus-circle',
        'key' => 'buatreservasi'
      ],
      [
        'url' => BASEURL . '/pelanggan/riwayat',
        'label' => 'Riwayat Service',
        'icon' => 'clipboard-list',
        'key' => 'riwayat'
      ],
      [
        'url' => BASEURL . '/pelanggan/bayar',
        'label' => 'Bayar',
        'icon' => 'credit-card',
        'key' => 'bayar'
      ],
      [
        'url' => BASEURL . '/pelanggan/riwayatpembayaran',
        'label' => 'Riwayat Pembayaran',
        'icon' => 'receipt',
        'key' => 'riwayatpembayaran'
      ],
    ];
  }
}

$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

function isNavActive(array $nav, string $currentPath): bool
{
  $navPath = trim(parse_url($nav['url'], PHP_URL_PATH), '/');
  $key = $nav['key'] ?? '';

  if ($key === 'dashboard') {
    return $currentPath === $navPath;
  }

  if ($key === 'reservasi') {
    return str_contains($currentPath, 'admin/reservasi')
      || str_contains($currentPath, 'admin/dataservice')
      || str_contains($currentPath, 'admin/transaksi');
  }

  if ($key === 'jadwal') {
    return str_contains($currentPath, 'admin/jadwal');
  }

  if ($key === 'pelanggan') {
    return str_contains($currentPath, 'admin/pelanggan');
  }

  if ($key === 'buatreservasi') {
    return str_contains($currentPath, 'pelanggan/buatreservasi')
      || str_contains($currentPath, 'pelanggan/buat-reservasi');
  }

  if ($key === 'riwayat') {
    return str_contains($currentPath, 'pelanggan/riwayat')
      && !str_contains($currentPath, 'pelanggan/riwayatpembayaran');
  }

  if ($key === 'bayar') {
    return str_contains($currentPath, 'pelanggan/bayar');
  }

  if ($key === 'riwayatpembayaran') {
    return str_contains($currentPath, 'pelanggan/riwayatpembayaran');
  }

  return $currentPath === $navPath;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AutoFix - <?= htmlspecialchars($title ?? 'Dashboard') ?></title>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/style.css">
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
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-title">MENU</div>

        <?php foreach ($navItems as $nav): ?>
          <?php $isActive = isNavActive($nav, $currentPath); ?>

          <a href="<?= $nav['url'] ?>" class="nav-item <?= $isActive ? 'active' : '' ?>">
            <div class="nav-icon-wrapper">
              <i data-lucide="<?= $nav['icon'] ?>" width="17" height="17"></i>
            </div>

            <span class="nav-label"><?= htmlspecialchars($nav['label']) ?></span>

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

          <div class="page-heading">
            <h1 class="page-title"><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
            <p class="page-date"><?= date('l, d M Y') ?></p>
          </div>
        </div>

        <div class="header-right">
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

      <?php if (isset($user)): ?>
        <div
          id="sessionTimeoutModal"
          class="popup-overlay session-timeout-overlay"
          aria-hidden="true"
          role="dialog"
          aria-labelledby="sessionTimeoutTitle"
        >
          <div class="popup-box session-timeout-box">
            <div class="session-timeout-icon">
              <i data-lucide="clock" width="28" height="28"></i>
            </div>

            <h3 id="sessionTimeoutTitle">Session akan habis</h3>

            <p id="sessionTimeoutMessage">
              Sesi Anda akan berakhir. Perpanjang untuk tetap masuk atau keluar dari akun.
            </p>

            <p id="sessionTimeoutCountdown" class="session-timeout-countdown" aria-live="polite"></p>

            <div class="popup-actions">
              <button type="button" id="sessionTimeoutLogout" class="btn-cancel">Keluar</button>
              <button type="button" id="sessionTimeoutExtend" class="btn-confirm">Perpanjang</button>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <main class="main-area">