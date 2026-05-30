<?php
$title = 'Dashboard Admin';
?>

<div class="admin-dashboard-container">

	<!-- Statistics Cards -->
	<div class="admin-stats-grid">
		<!-- Total Reservasi -->
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

		<!-- Reservasi Aktif -->
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

		<!-- Total Pelanggan -->
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

		<!-- Reservasi Selesai -->
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

		<!-- Reservasi Terbayar -->
		<div class="admin-stat-card">
			<div class="admin-stat-header">
				<div class="admin-stat-label">Terbayar</div>
				<div class="admin-stat-icon-wrapper">
					<i data-lucide="circle-dollar-sign" color="#76c8a0"></i>
				</div>
			</div>
			<div class="admin-stat-value"><?= $stats['terbayar'] ?></div>
			<div class="admin-stat-description">Service telah dibayar</div>
		</div>
	</div>

	<!-- Main Grid: Reservasi Terbaru & Progress -->
	<div class="admin-dashboard-grid">

		<!-- Reservasi Terbaru -->
		<div class="admin-card">
			<div class="admin-card-header">
				<h3 class="admin-card-title">Reservasi Terbaru</h3>
				<a href="<?= BASEURL; ?>/admin/dataservice" class="admin-card-link">Lihat Semua</a>
			</div>

			<div class="admin-card-content">
				<?php foreach ($reservasiTerbaru as $r): ?>
					<div class="reservation-item">
						<div class="reservation-info">
							<div class="admin-reservation-avatar">
								<?= strtoupper(substr($r['nama'], 0, 1)) ?>
							</div>
							<div>
								<div class="reservation-name">
									<?= htmlspecialchars($r['nama'] ?? '') ?>
								</div>
								<div class="reservation-service">
									<?= htmlspecialchars($r['layanan'] ?? '-') ?>
								</div>
							</div>
						</div>

						<div>
							<div class="reservation-time">
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
		</div>

		<!-- Progress Summary -->
		<div class="admin-card">
			<div class="admin-card-header">
				<h3 class="admin-card-title">Ringkasan Progress</h3>
			</div>

			<div class="admin-card-content">
				<!-- Reservasi Aktif -->
				<div class="progress-item">
					<div class="progress-header">
						<span class="progress-label">Reservasi Aktif</span>
						<span class="progress-value">
							<?= $stats['aktif'] ?>/<?= $stats['total'] ?>
						</span>
					</div>
					<div class="progress">
						<div class="progress-bar bg-secondary"
							style="width: <?= $stats['total'] > 0 ? ($stats['aktif'] / $stats['total']) * 100 : 0 ?>%;">
						</div>
					</div>
				</div>

				<!-- Reservasi Selesai -->
				<div class="progress-item">
					<div class="progress-header">
						<span class="progress-label">Reservasi Selesai</span>
						<span class="progress-value">
							<?= $stats['selesai'] ?>/<?= $stats['total'] ?>
						</span>
					</div>
					<div class="progress">
						<div class="progress-bar bg-primary"
							style="width: <?= $stats['total'] > 0 ? ($stats['selesai'] / $stats['total']) * 100 : 0 ?>%;">
						</div>
					</div>
				</div>

				<!-- Reservasi Terbayar -->
				<div class="progress-item">
					<div class="progress-header">
						<span class="progress-label">Reservasi Terbayar</span>
						<span class="progress-value">
							<?= $stats['terbayar'] ?>/<?= $stats['total'] ?>
						</span>
					</div>
					<div class="progress">
						<div class="progress-bar bg-primary"
							style="width: <?= $stats['total'] > 0 ? ($stats['terbayar'] / $stats['total']) * 100 : 0 ?>%;">
						</div>
					</div>
				</div>

				<!-- Total Pelanggan -->
				<div class="progress-item">
					<div class="progress-header">
						<span class="progress-label">Total Pelanggan</span>
						<span class="progress-value">
							<?= $stats['pelanggan'] ?>
						</span>
					</div>
					<div class="progress">
						<div class="progress-bar bg-neutral-teal" style="width: 100%;"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>