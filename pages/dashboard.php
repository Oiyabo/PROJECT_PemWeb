<?php
/**
 * app/views/admin/dashboard.php
 * VIEW: Dashboard Admin — Statistik & Reservasi Terbaru
 *
 * Variabel yang tersedia dari Admin::index():
 *   $stats            — Array berisi total, aktif, selesai, pelanggan
 *   $reservasiTerbaru — Array 5 reservasi terbaru
 *   $user             — Data user yang login
 */

// Fungsi helper untuk badge status (menggantikan renderBadge() dari data.php lama)
function badgeStatus(string $status): string
{
    $map = [
        'Menunggu'  => 'badge-warning',
        'Konfirmasi'=> 'badge-info',
        'Proses'    => 'badge-primary',
        'Selesai'   => 'badge-success',
        'Batal'     => 'badge-danger',
        'Lunas'     => 'badge-success',
        'Pending'   => 'badge-warning',
    ];
    $class = $map[$status] ?? 'badge-warning';
    return '<span class="badge ' . $class . '">' . htmlspecialchars($status) . '</span>';
}
?>

<div id="admin-dashboard" class="admin-dashboard-container">

  <!-- Kartu Statistik -->
  <div id="admin-stats" class="admin-stats-grid">

    <div class="admin-stat-card">
      <div class="admin-stat-header">
        <div class="admin-stat-label">Total Reservasi</div>
        <div class="admin-stat-icon-wrapper" style="background: var(--primary)22;">
          <i data-lucide="calendar" color="var(--primary)" width="18" height="18"></i>
        </div>
      </div>
      <div class="admin-stat-body">
        <div class="admin-stat-value"><?= $stats['total'] ?></div>
        <div class="admin-stat-trend-container">
          <i data-lucide="arrow-up-right" color="var(--secondary)" width="13" height="13"></i>
          <span class="admin-stat-trend-text trend-up">Semua waktu</span>
        </div>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-header">
        <div class="admin-stat-label">Service Aktif</div>
        <div class="admin-stat-icon-wrapper" style="background: var(--primary-light)22;">
          <i data-lucide="wrench" color="var(--primary-light)" width="18" height="18"></i>
        </div>
      </div>
      <div class="admin-stat-body">
        <div class="admin-stat-value"><?= $stats['aktif'] ?></div>
        <div class="admin-stat-trend-container">
          <span class="admin-stat-trend-subtext">Sedang berjalan</span>
        </div>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-header">
        <div class="admin-stat-label">Total Pelanggan</div>
        <div class="admin-stat-icon-wrapper" style="background: var(--secondary)22;">
          <i data-lucide="users" color="var(--secondary)" width="18" height="18"></i>
        </div>
      </div>
      <div class="admin-stat-body">
        <div class="admin-stat-value"><?= $stats['pelanggan'] ?></div>
        <div class="admin-stat-trend-container">
          <span class="admin-stat-trend-subtext">Terdaftar</span>
        </div>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-header">
        <div class="admin-stat-label">Selesai</div>
        <div class="admin-stat-icon-wrapper" style="background: var(--primary-dark)22;">
          <i data-lucide="check-circle" color="var(--primary-dark)" width="18" height="18"></i>
        </div>
      </div>
      <div class="admin-stat-body">
        <div class="admin-stat-value"><?= $stats['selesai'] ?></div>
        <div class="admin-stat-trend-container">
          <span class="admin-stat-trend-subtext">Reservasi selesai</span>
        </div>
      </div>
    </div>

  </div><!-- /.admin-stats-grid -->

  <!-- Konten Utama: Reservasi Terbaru -->
  <div id="admin-main-content" class="admin-main-grid">
    <div id="admin-reservations" class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">Reservasi Terbaru</h2>
        <a href="<?= BASEURL ?>/admin/reservasi" class="admin-card-action">Lihat semua</a>
      </div>
      <div class="admin-reservation-list">
        <?php if (empty($reservasiTerbaru)): ?>
          <p style="color: var(--text-light); padding: 1rem 0; text-align:center;">
            Belum ada reservasi.
          </p>
        <?php else: ?>
          <?php foreach ($reservasiTerbaru as $r): ?>
            <div class="admin-reservation-item">
              <div class="admin-reservation-info">
                <div class="admin-reservation-avatar">
                  <?= htmlspecialchars(substr($r['nama'], 0, 1)) ?>
                </div>
                <div class="admin-reservation-text">
                  <div class="admin-reservation-name"><?= htmlspecialchars($r['nama']) ?></div>
                  <div class="admin-reservation-details">
                    <?= htmlspecialchars($r['layanan']) ?> · <?= htmlspecialchars($r['jam']) ?>
                  </div>
                </div>
              </div>
              <?= badgeStatus($r['status']) ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Status Bengkel Hari Ini -->
    <div id="admin-status" class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">Status Bengkel Hari Ini</h2>
        <span class="admin-card-date"><?= date('d M Y') ?></span>
      </div>
      <?php
      $statuses = [
          ['label' => 'Slot Tersedia',    'value' => max(0, 10 - $stats['aktif']), 'total' => 10, 'color' => 'var(--secondary)'],
          ['label' => 'Sedang Diproses',  'value' => $stats['aktif'],              'total' => 10, 'color' => 'var(--primary)'],
          ['label' => 'Selesai Hari Ini', 'value' => $stats['selesai'],            'total' => max(1, $stats['total']), 'color' => 'var(--primary-light)'],
      ];
      foreach ($statuses as $item): ?>
        <div class="admin-status-item">
          <div class="admin-status-header">
            <span class="admin-status-label"><?= $item['label'] ?></span>
            <span class="admin-status-value"><?= $item['value'] ?>/<?= $item['total'] ?></span>
          </div>
          <div class="admin-status-bar-container">
            <div class="admin-status-bar-progress"
                 style="width: <?= min(100, ($item['value'] / $item['total']) * 100) ?>%;
                        background: <?= $item['color'] ?>;"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div><!-- /.admin-main-grid -->

</div><!-- /#admin-dashboard -->
