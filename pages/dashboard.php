<link rel="stylesheet" href="style/dashboard.css">

<?php
if ($role === 'Admin') {
    // Admin Dashboard
    ?>
    <div id="admin-dashboard" class="admin-dashboard-container">
        <div id="admin-stats" class="admin-stats-grid">
            <?php foreach ($mockStats as $s): ?>
                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <div class="admin-stat-label"><?= $s['label'] ?></div>
                        <div class="admin-stat-icon-wrapper" style="background: <?= $s['color'] ?>15;">
                            <i data-lucide="<?= $s['icon'] ?>" color="<?= $s['color'] ?>" width="18" height="18"></i>
                        </div>
                    </div>
                    <div class="admin-stat-body">
                        <div class="admin-stat-value"><?= $s['value'] ?></div>
                        <div class="admin-stat-trend-container">
                            <?php if ($s['up']): ?>
                                <i data-lucide="arrow-up-right" color="var(--secondary)" width="13" height="13"></i>
                                <span class="admin-stat-trend-text trend-up"><?= $s['trend'] ?></span>
                            <?php else: ?>
                                <i data-lucide="arrow-down-right" color="var(--error)" width="13" height="13"></i>
                                <span class="admin-stat-trend-text trend-down"><?= $s['trend'] ?></span>
                            <?php endif; ?>
                            <span class="admin-stat-trend-subtext">vs bulan lalu</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="admin-main-content" class="admin-main-grid">
            <div id="admin-reservations" class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Reservasi Terbaru</h2>
                    <span class="admin-card-action">Lihat semua</span>
                </div>
                <div class="admin-reservation-list">
                    <?php foreach (array_slice($mockReservasi, 0, 4) as $r): ?>
                        <div class="admin-reservation-item">
                            <div class="admin-reservation-info">
                                <div class="admin-reservation-avatar">
                                    <?= substr($r['nama'], 0, 1) ?>
                                </div>
                                <div class="admin-reservation-text">
                                    <div class="admin-reservation-name">
                                        <?= $r['nama'] ?>
                                    </div>
                                    <div class="admin-reservation-details"><?= $r['layanan'] ?> · <?= $r['jam'] ?></div>
                                </div>
                            </div>
                            <?= renderBadge($r['status']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="admin-status" class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Status Bengkel Hari Ini</h2>
                    <span class="admin-card-date">30 Apr 2026</span>
                </div>
                <?php
                $statuses = [
                    ["label" => "Slot Tersedia", "value" => 6, "total" => 10, "color" => "var(--secondary)"],
                    ["label" => "Sedang Diproses", "value" => 4, "total" => 10, "color" => "var(--primary)"],
                    ["label" => "Selesai Hari Ini", "value" => 8, "total" => 15, "color" => "var(--primary-light)"],
                ];
                foreach ($statuses as $item):
                    ?>
                    <div class="admin-status-item">
                        <div class="admin-status-header">
                            <span class="admin-status-label"><?= $item['label'] ?></span>
                            <span class="admin-status-value"><?= $item['value'] ?>/<?= $item['total'] ?></span>
                        </div>
                        <div class="admin-status-bar-container">
                            <div class="admin-status-bar-progress"
                                style="width: <?= ($item['value'] / $item['total']) * 100 ?>%; background: <?= $item['color'] ?>;">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div id="admin-metrics" class="admin-metrics-grid">
                    <?php
                    $metrics = [
                        ["label" => "Mekanik Aktif", "value" => "4/5", "icon" => "users"],
                        ["label" => "Kendaraan Masuk", "value" => "12", "icon" => "car"],
                        ["label" => "Antrian", "value" => "3", "icon" => "clock"],
                        ["label" => "Rating Hari Ini", "value" => "4.8★", "icon" => "star"],
                    ];
                    foreach ($metrics as $m):
                        ?>
                        <div class="admin-metric-card">
                            <div class="admin-metric-label">
                                <?= $m['label'] ?>
                            </div>
                            <div class="admin-metric-value"><?= $m['value'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
} else {
    ?>

    <div class="dashboard-container">
        <div class="welcome-card">
            <div class="avatar">
                <?= substr($user['nama'], 0, 1) ?>
            </div>

            <h2 class="welcome-title">Selamat Datang, <?= $user['nama'] ?></h2>
            <p class="welcome-text">Silahkan buat reservasi baru atau cek riwayat service kendaraan Anda</p>

            <div class="card-actions">
                <a href="?page=buat-reservasi" class="btn">➕ Buat Reservasi</a>
                <a href="?page=riwayat" class="btn">📋 Riwayat Service</a>
            </div>
        </div>
    </div>
<?php } ?>