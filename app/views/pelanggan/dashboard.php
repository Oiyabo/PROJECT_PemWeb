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
	</div>
</div>