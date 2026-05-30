<?php
$title = 'Dashboard Pelanggan';
?>

<div class="dashboard-container">

	<div class="welcome-card">
		<div class="avatar">
			<?= strtoupper(substr($user['nama'], 0, 1)) ?>
		</div>

		<h2 class="welcome-title">
			Selamat Datang, <?= htmlspecialchars($user['nama']) ?>!
		</h2>

		<p class="welcome-text">
			Silakan buat reservasi baru atau cek riwayat service kendaraan Anda.
		</p>

		<div class="card-actions">
			<a href="<?= BASEURL ?>/pelanggan/buatreservasi" class="dashboard-btn">
				➕ Buat Reservasi
			</a>

			<a href="<?= BASEURL ?>/pelanggan/riwayat" class="dashboard-btn">
				📋 Riwayat Service
			</a>
		</div>
	</div>

	<div class="pelanggan-stats-grid dashboard-stats">

		<div class="pelanggan-stat-card">
			<div class="pelanggan-stat-header">

				<div class="pelanggan-stat-label">
					Total Reservasi
				</div>

				<div class="pelanggan-stat-icon-wrapper stat-icon-blue">
					<i data-lucide="calendar-days" color="#168aad" width="18" height="18"></i>
				</div>

			</div>

			<div class="pelanggan-stat-body">
				<div class="pelanggan-stat-value">
					<?= $stats['total'] ?>
				</div>
			</div>
		</div>

		<div class="pelanggan-stat-card">
			<div class="pelanggan-stat-header">

				<div class="pelanggan-stat-label">
					Sedang Aktif
				</div>

				<div class="pelanggan-stat-icon-wrapper stat-icon-green">
					<i data-lucide="loader-circle" color="#34a0a4" width="18" height="18"></i>
				</div>

			</div>

			<div class="pelanggan-stat-body">
				<div class="pelanggan-stat-value">
					<?= $stats['aktif'] ?>
				</div>
			</div>
		</div>

		<div class="pelanggan-stat-card">
			<div class="pelanggan-stat-header">

				<div class="pelanggan-stat-label">
					Selesai
				</div>

				<div class="pelanggan-stat-icon-wrapper stat-icon-success">
					<i data-lucide="check-circle-2" color="#76c893" width="18" height="18"></i>
				</div>

			</div>

			<div class="pelanggan-stat-body">
				<div class="pelanggan-stat-value">
					<?= $stats['selesai'] ?>
				</div>
			</div>
		</div>

		<div class="pelanggan-stat-card">
			<div class="pelanggan-stat-header">

				<div class="pelanggan-stat-label">
					Terbayar
				</div>

				<div class="pelanggan-stat-icon-wrapper stat-icon-success">
					<i data-lucide="circle-dollar-sign" color="#76c8a0" width="18" height="18"></i>
				</div>

			</div>

			<div class="pelanggan-stat-body">
				<div class="pelanggan-stat-value">
					<?= $stats['terbayar'] ?>
				</div>
			</div>
		</div>
	</div>

	<div class="dashboard-history-card">

    <div class="dashboard-history-header">
        <h3>Reservasi Terbaru</h3>
    </div>

    <?php if (!empty($reservasiAktif)): ?>

        <table class="dashboard-history-table">
            <thead>
                <tr>
                    <th>Kendaraan</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($reservasiAktif as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['kendaraan']) ?></td>
                        <td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                        <td><?= substr($r['jam'], 0, 5) ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower($r['status']) ?>">
                                <?= $r['status'] ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <div class="dashboard-empty">
            Belum ada reservasi yang pernah dibuat.
        </div>

    <?php endif; ?>

</div>
</div>