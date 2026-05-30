<?php
$title = 'Dashboard Pelanggan';

$statusOrder = [
	'Menunggu' => 1,
	'Konfirmasi' => 2,
	'Proses' => 3,
	'Selesai' => 4
];

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

	<h2>Status Reservasi</h2>
	<?php if (!empty($reservasiAktif)): ?>

		<?php foreach ($reservasiAktif as $reservasi): ?>

			<?php
			$currentStep = $statusOrder[$reservasi['status']] ?? 1;
			?>

			<div class="reservation-progress-card">
				<div class="progress-header">
					<div>
						<h3>
							<?= htmlspecialchars($reservasi['kendaraan']) ?>
						</h3>

						<p>
							<?= htmlspecialchars($reservasi['plat']) ?>
						</p>

						<small class="reservation-date">
							<?= date('d M Y', strtotime($reservasi['tanggal'])) ?>
							•
							<?= substr($reservasi['jam'], 0, 5) ?>
						</small>
					</div>

					<span class="status-badge status-<?= strtolower($reservasi['status']) ?>">
						<?= $reservasi['status'] ?>
					</span>

				</div>

				<div class="timeline-wrapper">

					<div class="timeline">

						<div class="step <?= $currentStep >= 1 ? 'active' : '' ?>">
							<div class="circle">
								<?= $currentStep >= 1 ? '✓' : '○' ?>
							</div>
							<span>Menunggu</span>
						</div>

						<div class="line <?= $currentStep >= 2 ? 'active' : '' ?>"></div>

						<div class="step <?= $currentStep >= 2 ? 'active' : '' ?>">
							<div class="circle">
								<?= $currentStep >= 2 ? '✓' : '○' ?>
							</div>
							<span>Konfirmasi</span>
						</div>

						<div class="line <?= $currentStep >= 3 ? 'active' : '' ?>"></div>

						<div class="step <?= $currentStep >= 3 ? 'active' : '' ?>">
							<div class="circle">
								<?= $currentStep >= 3 ? '✓' : '○' ?>
							</div>
							<span>Proses</span>
						</div>

						<div class="line <?= $currentStep >= 4 ? 'active' : '' ?>"></div>

						<div class="step <?= $currentStep >= 4 ? 'active' : '' ?>">
							<div class="circle">
								<?= $currentStep >= 4 ? '✓' : '○' ?>
							</div>
							<span>Selesai</span>
						</div>

					</div>
				</div>
			</div>

		<?php endforeach; ?>

	<?php else: ?>

		<div class="reservation-empty-card">
			<div class="empty-icon">🚗</div>

			<h3>Tidak Ada Reservasi Aktif</h3>

			<p>
				Saat ini tidak ada reservasi yang sedang berjalan.
				Buat reservasi baru untuk melakukan service kendaraan.
			</p>
		</div>

	<?php endif; ?>
</div>