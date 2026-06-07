<?php
$userId = $_SESSION['user']['id'];
?>

<link rel="stylesheet" href="<?= BASEURL ?>/assets/css/style.css">

<div class="page-header">
	<h1>Pembayaran Sisanya</h1>
	<p class="page-subtitle">Bayar sisa pembayaran via Midtrans untuk service yang sudah selesai</p>
</div>

<?php if (!empty($_GET['payment'])): ?>
	<div class="payment-alert">
		Status pembayaran Midtrans: <strong><?= htmlspecialchars($_GET['payment']) ?></strong>
		<?php if (in_array($_GET['payment'], ['settlement', 'capture'], true)): ?>
			— terima kasih, pembayaran sedang diverifikasi.
		<?php endif; ?>
	</div>
<?php endif; ?>

<div>
	<?php if (!empty($reservasi)): ?>
		<div class="reservasi-table-container">
			<table class="reservasi-table">
				<thead>
					<tr>
						<th>No. Reservasi</th>
						<th>Tanggal</th>
						<th>Jam</th>
						<th>Kendaraan</th>
						<th>Layanan</th>
						<th>Jenis</th>
						<th>Harga Full</th>
						<th>DP Terbayar</th>
						<th>Sisa Pembayaran</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($reservasi as $r): ?>
						<?php
						$hargaFull = (int) ($r['total_full'] ?? 0);
						$dpTerbayar = (int) ($r['total_dp'] ?? 0);
						$sisaBayar = (int) ($r['total_sisa'] ?? ($hargaFull - $dpTerbayar));
						$idReservasi = (int) $r['id_reservasi'];
						$jenisKendaraan = $r['jenis_kendaraan'] ?? 'Motor';
						?>
						<tr>
							<td><strong>#<?= $idReservasi ?></strong></td>
							<td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
							<td><?= substr($r['jam'], 0, 5) ?></td>
							<td><?= htmlspecialchars($r['kendaraan']) ?> (<?= htmlspecialchars($r['plat']) ?>)</td>
							<td class="layanan-cell"><?= htmlspecialchars($r['layanan'] ?? '-') ?></td>
							<td><?= htmlspecialchars($jenisKendaraan) ?></td>

							<td class="text-right">
								Rp <?= number_format($hargaFull, 0, ',', '.') ?>
							</td>

							<td class="text-right">
								<span class="text-success">
									Rp <?= number_format($dpTerbayar, 0, ',', '.') ?>
								</span>
							</td>

							<td class="text-right">
								<span class="text-danger-bold">
									Rp <?= number_format($sisaBayar, 0, ',', '.') ?>
								</span>
							</td>

							<td class="text-center">
								<button
									type="button"
									class="btn-small btn-primary"
									onclick="openPaymentModal(
										<?= $idReservasi ?>,
										<?= $hargaFull ?>,
										<?= $sisaBayar ?>,
										'<?= htmlspecialchars($r['kendaraan'], ENT_QUOTES) ?>',
										'<?= htmlspecialchars($jenisKendaraan, ENT_QUOTES) ?>'
									)"
								>
									💳 Bayar (Midtrans)
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
		<div class="empty-state">
			<div class="empty-icon">📋</div>

			<h2>Tidak ada pembayaran yang tertunda</h2>

			<p>
				Semua pembayaran Anda sudah lengkap atau belum ada service yang selesai.
			</p>

			<a href="<?= BASEURL ?>/pelanggan" class="btn-primary btn-dashboard">
				Kembali ke Dashboard
			</a>
		</div>
	<?php endif; ?>
</div>

<!-- Modal Pembayaran -->
<div id="paymentFullModal" class="popup-overlay hidden">
	<div class="popup-box popup-payment-box">

		<h3>Pelunasan via Midtrans</h3>

		<div class="payment-detail-box">

			<p class="payment-info-row">
				<strong>No. Reservasi:</strong>
				<span id="modalReservID">#-</span>
			</p>

			<p class="payment-info-row">
				<strong>Kendaraan:</strong>
				<span id="modalKendaraan">-</span>
			</p>

			<p class="payment-info-row">
				<strong>Harga Full (layanan):</strong>

				<span id="modalHargaFull" class="font-semibold">
					Rp 0
				</span>
			</p>

			<p class="payment-info-row">
				<strong>Sisa Pembayaran:</strong>

				<span id="modalFullAmount" class="payment-total">
					Rp 0
				</span>
			</p>

		</div>

		<p class="payment-description">
			Pembayaran diproses oleh Midtrans.
			Pilih metode (VA, e-wallet, QRIS, dll) di halaman Snap.
		</p>

		<div class="popup-actions">
			<button type="button" class="btn-secondary" onclick="closePaymentModal()">
				Batal
			</button>

			<button
				type="button"
				class="btn-primary"
				id="btnSubmitFull"
				onclick="submitPaymentFull()"
			>
				Bayar dengan Midtrans
			</button>
		</div>

	</div>
</div>

<!-- Modal Konfirmasi -->
<div id="confirmPaymentModal" class="popup-overlay hidden">
	<div class="popup-box popup-payment-box">

		<h3>Pembayaran Berhasil</h3>

		<div class="payment-success-box">
			<p class="payment-success-text">
				✓ Pembayaran Midtrans diterima
			</p>
		</div>

		<div class="payment-confirm-info">
			<p>
				<strong>Nominal:</strong>
				<span id="confirmAmount">Rp 0</span>
			</p>

			<p>
				<strong>Metode:</strong>
				Midtrans
			</p>

			<p>
				<strong>Status:</strong>
				<span class="status-success">Selesai</span>
			</p>
		</div>

		<div class="reload-info-box">
			<p class="reload-info-text">
				Riwayat pembayaran full telah diperbarui.
				Halaman akan dimuat ulang.
			</p>
		</div>

		<div class="popup-actions">
			<button
				type="button"
				class="btn-primary"
				onclick="closeConfirmAndRefresh()"
			>
				Selesai
			</button>
		</div>

	</div>
</div>

<script>
	window.APP_BASEURL = <?= json_encode(BASEURL) ?>;
	window.MIDTRANS_CLIENT_KEY = <?= json_encode($midtrans_client_key ?? '') ?>;
	window.MIDTRANS_SNAP_SCRIPT = <?= json_encode($midtrans_snap_script ?? '') ?>;
</script>

<script src="<?= BASEURL ?>/assets/js/midtrans-payment.js"></script>
<script src="<?= BASEURL ?>/assets/js/bayar.js"></script>