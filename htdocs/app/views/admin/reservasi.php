<?php
if (!function_exists('badgeStatus')) {
	function badgeStatus(string $status): string
	{
		$statusMap = [
			'Menunggu' => 'status-waiting',
			'Konfirmasi' => 'status-confirmed',
			'Proses' => 'status-process',
			'Selesai' => 'status-done',
			'Batal' => 'status-danger',
		];

		$class = $statusMap[$status] ?? 'status-waiting';

		return '<span class="status-badge ' . $class . '">'
			. htmlspecialchars($status) .
			'</span>';
	}
}

$statusTabs = $statusTabs ?? ['Semua', 'Menunggu', 'Konfirmasi', 'Proses', 'Selesai', 'Terbayar', 'Batal'];
$statusAktif = $statusAktif ?? 'Semua';
$jumlahStatus = $jumlahStatus ?? [];
$keyword = $keyword ?? '';
$reservasiList = $reservasi ?? [];
?>

<div class="reservasi-container">

	<div class="status-tabs">
		<?php foreach ($statusTabs as $status): ?>
			<a href="<?= BASEURL ?>/admin/reservasi?status=<?= urlencode($status) ?>"
				class="status-tab <?= $statusAktif === $status ? 'active' : '' ?>">
				<?= htmlspecialchars($status) ?>
				<span><?= $jumlahStatus[$status] ?? 0 ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<form class="search-wrapper" method="GET" action="<?= BASEURL ?>/admin/reservasi">
		<input type="hidden" name="status" value="<?= htmlspecialchars($statusAktif) ?>">

		<div class="search-box">
			<i data-lucide="search" color="#38a3a5" width="18" height="18"></i>
			<input type="text" name="q" placeholder="Cari nama, kendaraan, tanggal, status..."
				value="<?= htmlspecialchars($keyword) ?>">
		</div>

		<?php if (!empty($keyword)): ?>
			<a href="<?= BASEURL ?>/admin/reservasi?status=<?= urlencode($statusAktif) ?>" class="btn-reset-search">
				Reset
			</a>
		<?php endif; ?>
	</form>

	<div class="table-container table-scroll-x">
		<div class="table-wrapper">
			<table class="table-reservasi">
				<thead>
					<tr>
						<th>Nama Pelanggan</th>
						<th>Kendaraan</th>
						<th>Tanggal</th>
						<th>Status</th>
						<th>Detail</th>
					</tr>
				</thead>

				<tbody>
					<?php if (empty($reservasiList)): ?>
						<tr>
							<td colspan="5" class="empty-table">
								<?php if (!empty($keyword)): ?>
									Data reservasi status <?= htmlspecialchars($statusAktif) ?> dengan kata kunci
									"<?= htmlspecialchars($keyword) ?>" tidak ditemukan.
								<?php else: ?>
									Belum ada data reservasi dengan status <?= htmlspecialchars($statusAktif) ?>.
								<?php endif; ?>
							</td>
						</tr>
					<?php else: ?>
						<?php foreach ($reservasiList as $r): ?>
							<?php
							$reservasiJson = htmlspecialchars(
								json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
								ENT_QUOTES,
								'UTF-8'
							);
							?>
							<tr>
								<td class="text-primary">
									<?= htmlspecialchars($r['nama'] ?? 'Unknown') ?>
								</td>

								<td>
									<?= htmlspecialchars($r['kendaraan'] ?? '-') ?>
								</td>

								<td>
									<?= htmlspecialchars($r['tanggal'] ?? '-') ?>
								</td>

								<td>
									<?= badgeStatus($r['status'] ?? 'Menunggu') ?>
								</td>

								<td>
									<button type="button" class="btn btn-detail-reservasi" data-reservasi="<?= $reservasiJson ?>" title="Lihat detail">
										<i data-lucide="eye" width="14" height="14"></i>
										Detail
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

</div>

<?php
$adminBackUrl = BASEURL . '/admin/reservasi?status=' . urlencode($statusAktif)
	. (!empty($keyword) ? '&q=' . urlencode($keyword) : '');
?>
<?php require __DIR__ . '/../partials/detail-reservasi-modal.php'; ?>

<script>
	window.APP_BASEURL = <?= json_encode(BASEURL) ?>;
	window.DETAIL_RESERVASI_ADMIN = true;
	window.DETAIL_RESERVASI_BACK = <?= json_encode($adminBackUrl) ?>;
</script>
<script src="<?= BASEURL ?>/assets/js/detail-reservasi.js"></script>