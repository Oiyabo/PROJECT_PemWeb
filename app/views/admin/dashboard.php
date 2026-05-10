<?php
$title = 'Dashboard Admin';
?>

<link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/dashboard.css">

<div class="admin-dashboard-container">

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-header">
                <div class="admin-stat-label">Total Reservasi</div>
                <div class="admin-stat-icon-wrapper">
                    <i data-lucide="calendar-days" color="#168aad"></i>
                </div>
            </div>
            <div class="admin-stat-value"><?= $stats['total'] ?></div>
            <div class="admin-stat-description">Semua reservasi tercatat</div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-header">
                <div class="admin-stat-label">Sedang Aktif</div>
                <div class="admin-stat-icon-wrapper">
                    <i data-lucide="loader-circle" color="#34a0a4"></i>
                </div>
            </div>
            <div class="admin-stat-value"><?= $stats['aktif'] ?></div>
            <div class="admin-stat-description">Dalam proses service</div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-header">
                <div class="admin-stat-label">Pelanggan</div>
                <div class="admin-stat-icon-wrapper">
                    <i data-lucide="users" color="#52b69a"></i>
                </div>
            </div>
            <div class="admin-stat-value"><?= $stats['pelanggan'] ?></div>
            <div class="admin-stat-description">Pengguna terdaftar</div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-header">
                <div class="admin-stat-label">Selesai</div>
                <div class="admin-stat-icon-wrapper">
                    <i data-lucide="check-circle-2" color="#76c893"></i>
                </div>
            </div>
            <div class="admin-stat-value"><?= $stats['selesai'] ?></div>
            <div class="admin-stat-description">Service telah selesai</div>
        </div>
    </div>

    <div class="admin-dashboard-grid">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Reservasi Terbaru</h3>
                <a href="<?= BASEURL; ?>/admin/dataservice" class="admin-card-link">Lihat Semua</a>
            </div>

            <?php foreach ($reservasiTerbaru as $r): ?>
                <div class="reservation-item">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="admin-reservation-avatar">
                            <?= strtoupper(substr($r['nama'], 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:700; color:var(--text-dark);">
                                <?= htmlspecialchars($r['nama']) ?>
                            </div>
                            <div style="font-size:12px; color:var(--text-light);">
                                <?= htmlspecialchars($r['layanan']) ?>
                            </div>
                        </div>
                    </div>

                    <div style="text-align:right;">
                        <div style="font-size:12px; color:var(--text-light);">
                            <?= htmlspecialchars($r['jam']) ?>
                        </div>
                        <span class="badge <?=
                            $r['status'] === 'Selesai'
                                ? 'badge-success'
                                : ($r['status'] === 'Konfirmasi'
                                    ? 'badge-info'
                                    : 'badge-warning')
                        ?>">
                            <?= htmlspecialchars($r['status']) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Ringkasan Progress</h3>
            </div>

            <div style="margin-bottom:18px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span style="font-size:13px; color:var(--text-medium);">Reservasi Aktif</span>
                    <span style="font-size:13px; font-weight:600; color:var(--text-dark);">
                        <?= $stats['aktif'] ?>/<?= $stats['total'] ?>
                    </span>
                </div>
                <div class="progress">
                    <div
                        class="progress-bar"
                        style="width: <?= $stats['total'] > 0 ? ($stats['aktif'] / $stats['total']) * 100 : 0 ?>%; background:#34a0a4;">
                    </div>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span style="font-size:13px; color:var(--text-medium);">Reservasi Selesai</span>
                    <span style="font-size:13px; font-weight:600; color:var(--text-dark);">
                        <?= $stats['selesai'] ?>/<?= $stats['total'] ?>
                    </span>
                </div>
                <div class="progress">
                    <div
                        class="progress-bar"
                        style="width: <?= $stats['total'] > 0 ? ($stats['selesai'] / $stats['total']) * 100 : 0 ?>%; background:#76c893;">
                    </div>
                </div>
            </div>

            <div>
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span style="font-size:13px; color:var(--text-medium);">Total Pelanggan</span>
                    <span style="font-size:13px; font-weight:600; color:var(--text-dark);">
                        <?= $stats['pelanggan'] ?>
                    </span>
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width:100%; background:#52b69a;"></div>
                </div>
            </div>
        </div>
    </div>

</div>