<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title'] ?? 'AutoFix') ?></title>

    <link rel="stylesheet" href="<?= BASEURL ?>/public/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/public/assets/css/dataservice.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/public/assets/css/pelanggan.css">
    <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/lucide.min.css">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<div id="main">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-icon">
                    <i data-lucide="wrench"></i>
                </div>
                <div class="brand-text">
                    <h2>AutoFix</h2>
                    <p>Manajemen Bengkel</p>
                </div>
            </div>

            <button id="sidebarClose" class="sidebar-close" type="button">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="sidebar-menu-title">Menu</div>

        <nav class="sidebar-nav">
            <a href="<?= BASEURL ?>/admin"
               class="nav-item <?= ($_SERVER['REQUEST_URI'] === '/PROJECT_PemWeb-main/admin') ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>

            <a href="<?= BASEURL ?>/admin/reservasi" class="nav-item">
                <i data-lucide="calendar"></i>
                <span>Reservasi</span>
            </a>

            <a href="<?= BASEURL ?>/admin/dataservice" class="nav-item">
                <i data-lucide="wrench"></i>
                <span>Data Service</span>
            </a>

            <a href="<?= BASEURL ?>/admin/pelanggan" class="nav-item">
                <i data-lucide="users"></i>
                <span>Pelanggan</span>
            </a>

            <a href="<?= BASEURL ?>/admin/transaksi" class="nav-item">
                <i data-lucide="credit-card"></i>
                <span>Transaksi</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-avatar">
                <?= htmlspecialchars(substr($data['user']['nama'] ?? 'A', 0, 1)) ?>
            </div>

            <div class="user-info">
                <div class="user-name">
                    <?= htmlspecialchars($data['user']['nama'] ?? 'Admin Bengkel') ?>
                </div>
                <div class="user-email">
                    <?= htmlspecialchars($data['user']['email'] ?? 'admin@email.com') ?>
                </div>
            </div>

            <a href="<?= BASEURL ?>/auth/logout" class="logout-btn">
                <i data-lucide="log-out"></i>
            </a>
        </div>
    </aside>

    <div class="main-content-wrapper">
        <header class="topbar">
            <button id="sidebarToggle" class="menu-toggle" type="button">
                <i data-lucide="menu"></i>
            </button>

            <div class="topbar-title">
                <h1><?= htmlspecialchars($data['title'] ?? 'Dashboard Admin') ?></h1>
                <p><?= date('l, d F Y') ?></p>
            </div>

            <div class="topbar-actions">
                <div class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" placeholder="Cari...">
                </div>

                <button class="notification-btn" type="button">
                    <i data-lucide="bell"></i>
                </button>
            </div>
        </header>

        <main class="main-area">