<?php
$userId = $_SESSION['user']['id'];
?>

<div class="page-header">
	<h1>Pembayaran Sisanya</h1>
	<p class="page-subtitle">Bayar sisa pembayaran via Midtrans untuk service yang sudah selesai</p>
</div>

<?php if (!empty($_GET['payment'])): ?>
	<div style="margin-bottom: 16px; padding: 12px; border-radius: 6px; background: #e7f3ff; color: #1565c0;">
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
							<td style="text-align: right;">Rp <?= number_format($hargaFull, 0, ',', '.') ?></td>
							<td style="text-align: right;">
								<span style="color: #28a745;">Rp <?= number_format($dpTerbayar, 0, ',', '.') ?></span>
							</td>
							<td style="text-align: right;">
								<span style="color: #dc3545; font-weight: 600;">Rp <?= number_format($sisaBayar, 0, ',', '.') ?></span>
							</td>
							<td style="text-align: center;">
								<button type="button" class="btn-small btn-primary"
									onclick="openPaymentModal(<?= $idReservasi ?>, <?= $hargaFull ?>, <?= $sisaBayar ?>, '<?= htmlspecialchars($r['kendaraan'], ENT_QUOTES) ?>', '<?= htmlspecialchars($jenisKendaraan, ENT_QUOTES) ?>')">
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
			<p>Semua pembayaran Anda sudah lengkap atau belum ada service yang selesai.</p>
			<a href="<?= BASEURL ?>/pelanggan" class="btn-primary" style="margin-top: 16px;">Kembali ke Dashboard</a>
		</div>
	<?php endif; ?>
</div>

<!-- Modal Pembayaran FULL (Midtrans) -->
<div id="paymentFullModal" class="popup-overlay" style="display: none;">
	<div class="popup-box" style="width: 90%; max-width: 450px;">
		<h3>Pelunasan via Midtrans</h3>

		<div style="margin: 20px 0; padding: 12px; background-color: #f8f9fa; border-radius: 6px;">
			<p style="margin: 8px 0;"><strong>No. Reservasi:</strong> <span id="modalReservID">#-</span></p>
			<p style="margin: 8px 0;"><strong>Kendaraan:</strong> <span id="modalKendaraan">-</span></p>
			<p style="margin: 8px 0;"><strong>Harga Full (layanan):</strong> <span id="modalHargaFull"
					style="font-weight: 600;">Rp 0</span></p>
			<p style="margin: 8px 0;"><strong>Sisa Pembayaran:</strong> <span id="modalFullAmount"
					style="font-size: 18px; color: #dc3545; font-weight: 700;">Rp 0</span></p>
		</div>

		<p style="font-size: 14px; color: #555; margin-bottom: 16px;">
			Pembayaran diproses oleh Midtrans. Pilih metode (VA, e-wallet, QRIS, dll) di halaman Snap.
		</p>

		<div class="popup-actions">
			<button type="button" class="btn-secondary" onclick="closePaymentModal()">Batal</button>
			<button type="button" class="btn-primary" id="btnSubmitFull" onclick="submitPaymentFull()">Bayar dengan
				Midtrans</button>
		</div>
	</div>
</div>

<!-- Modal Konfirmasi Pembayaran -->
<div id="confirmPaymentModal" class="popup-overlay" style="display: none;">
	<div class="popup-box" style="width: 90%; max-width: 450px;">
		<h3>Pembayaran Berhasil</h3>

		<div style="margin: 20px 0; padding: 12px; background-color: #d4edda; border-radius: 6px; text-align: center;">
			<p style="margin: 0; color: #155724; font-size: 18px; font-weight: 700;">✓ Pembayaran Midtrans diterima</p>
		</div>

		<div style="margin: 16px 0;">
			<p><strong>Nominal:</strong> <span id="confirmAmount">Rp 0</span></p>
			<p><strong>Metode:</strong> Midtrans</p>
			<p><strong>Status:</strong> <span style="color: #28a745; font-weight: 600;">Selesai</span></p>
		</div>

		<div style="padding: 12px; background-color: #e7f3ff; border-radius: 6px; border-left: 4px solid #2196F3;">
			<p style="margin: 0; color: #1565c0; font-size: 14px;">
				Riwayat pembayaran full telah diperbarui. Halaman akan dimuat ulang.
			</p>
		</div>

		<div class="popup-actions">
			<button type="button" class="btn-primary" onclick="closeConfirmAndRefresh()">Selesai</button>
		</div>
	</div>
</div>

<style>
	.reservasi-table-container {
		overflow-x: auto;
		background: white;
		border-radius: 8px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	}

	.reservasi-table {
		width: 100%;
		border-collapse: collapse;
		font-size: 14px;
	}

	.reservasi-table thead {
		background: linear-gradient(135deg, #38a3a5 0%, #2d8a8c 100%);
		color: white;
	}

	.reservasi-table th {
		padding: 12px;
		text-align: left;
		font-weight: 600;
	}

	.reservasi-table tbody tr {
		border-bottom: 1px solid #e2e8f0;
		transition: background-color 0.2s;
	}

	.reservasi-table tbody tr:hover {
		background-color: #f8f9fa;
	}

	.reservasi-table td {
		padding: 12px;
	}

	.layanan-cell {
		max-width: 300px;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: normal;
		line-height: 1.4;
	}

	.btn-small {
		padding: 6px 12px;
		font-size: 12px;
		white-space: nowrap;
	}

	.empty-state {
		text-align: center;
		padding: 60px 20px;
		background: white;
		border-radius: 8px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	}

	.empty-icon {
		font-size: 48px;
		margin-bottom: 16px;
	}

	.empty-state h2 {
		color: #333;
		margin-bottom: 8px;
	}

	.empty-state p {
		color: #666;
		margin-bottom: 0;
	}

	@media (max-width: 768px) {
		.reservasi-table {
			font-size: 12px;
		}

		.reservasi-table th,
		.reservasi-table td {
			padding: 8px;
		}

		.layanan-cell {
			max-width: 150px;
		}
	}
</style>

<script>
	window.APP_BASEURL = <?= json_encode(BASEURL) ?>;
	window.MIDTRANS_CLIENT_KEY = <?= json_encode($midtrans_client_key ?? '') ?>;
	window.MIDTRANS_SNAP_SCRIPT = <?= json_encode($midtrans_snap_script ?? '') ?>;
</script>
<script src="<?= BASEURL ?>/assets/js/midtrans-payment.js"></script>
<script>
	let currentReservID = null;
	let currentSisaBayar = null;
	let currentHargaFull = null;
	let currentJenisKendaraan = null;
	const baseUrl = window.APP_BASEURL;

	function openPaymentModal(reservID, hargaFull, sisaBayar, kendaraan, jenisKendaraan) {
		currentReservID = reservID;
		currentHargaFull = hargaFull;
		currentSisaBayar = sisaBayar;
		currentJenisKendaraan = jenisKendaraan;

		document.getElementById('modalReservID').textContent = '#' + reservID;
		document.getElementById('modalKendaraan').textContent = kendaraan;
		document.getElementById('modalHargaFull').textContent = 'Rp ' + formatNumber(hargaFull);
		document.getElementById('modalFullAmount').textContent = 'Rp ' + formatNumber(sisaBayar);

		document.getElementById('paymentFullModal').style.display = 'flex';
	}

	function closePaymentModal() {
		document.getElementById('paymentFullModal').style.display = 'none';
	}

	function submitPaymentFull() {
		const btn = document.getElementById('btnSubmitFull');
		btn.disabled = true;

		const formData = new FormData();
		formData.append('tipe', 'FULL');
		formData.append('id_reservasi', currentReservID);
		formData.append('jenis_kendaraan', currentJenisKendaraan);

		requestMidtransSnap(
			baseUrl,
			formData,
			(data) => {
				closePaymentModal();
				btn.disabled = false;
				btn.textContent = 'Bayar dengan Midtrans';

				loadMidtransSnap(
					data.snap_script || window.MIDTRANS_SNAP_SCRIPT,
					data.client_key || window.MIDTRANS_CLIENT_KEY,
					() => {
						openMidtransSnap(
							data.snap_token,
							data.client_key,
							() => verifikasiPembayaranFull(data.order_id, data.nominal),
							() => verifikasiPembayaranFull(data.order_id, data.nominal),
							() => alert('Pembayaran gagal atau dibatalkan.')
						);
					}
				);
			},
			(msg) => {
				alert(msg);
				btn.disabled = false;
				btn.textContent = 'Bayar dengan Midtrans';
			}
		);
	}

	function verifikasiPembayaranFull(orderId, nominal) {
		afterSnapPaid(baseUrl, orderId, {
			maxAttempts: 25,
			verifyMessage: 'Memverifikasi pelunasan pembayaran...',
			onPaid: () => showPaymentSuccess(nominal),
			onTimeout: () => {
				alert('Pembayaran masih diproses. Jika sudah bayar, tunggu 1–2 menit lalu refresh halaman.');
			},
		});
	}

	document.addEventListener('DOMContentLoaded', () => {
		const params = new URLSearchParams(window.location.search);
		const payment = params.get('payment');
		const orderId = params.get('order_id');

		if (
			orderId &&
			(payment === 'settlement' || payment === 'capture' || payment === 'pending')
		) {
			verifikasiPembayaranFull(orderId, null);
		}
	});

	function showPaymentSuccess(nominal) {
		document.getElementById('confirmAmount').textContent =
			'Rp ' + formatNumber(nominal || currentSisaBayar);
		document.getElementById('confirmPaymentModal').style.display = 'flex';
	}

	function closeConfirmAndRefresh() {
		document.getElementById('confirmPaymentModal').style.display = 'none';
		location.reload();
	}

	function formatNumber(num) {
		return new Intl.NumberFormat('id-ID').format(num);
	}
</script>