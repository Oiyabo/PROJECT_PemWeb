<?php
if (!function_exists('badgeStatus')) {
<<<<<<< HEAD
    function badgeStatus(string $status): string {
        $map = [
            'Menunggu'   => 'badge-warning',
            'Konfirmasi' => 'badge-info',
            'Proses'     => 'badge-primary',
            'Selesai'    => 'badge-success',
            'Batal'      => 'badge-danger',
        ];
=======

	function badgeStatus(string $status): string
	{
		$map = [
			'Menunggu' => 'badge-warning',
			'Konfirmasi' => 'badge-info',
			'Proses' => 'badge-primary',
			'Selesai' => 'badge-success',
			'Batal' => 'badge-danger',
		];
>>>>>>> 5050aa1868e7e474ce356535120cac92e664db99

		$class = $map[$status] ?? 'badge-warning';

		return '<span class="badge ' . $class . '">'
			. htmlspecialchars($status) .
			'</span>';
	}
}

$statusTabs = [
    'Semua',
    'Menunggu',
    'Konfirmasi',
    'Proses',
    'Selesai',
    'Batal'
];

$activeTab = $_GET['status'] ?? 'Semua';

if (!in_array($activeTab, $statusTabs, true)) {
    $activeTab = 'Semua';
}

$jumlahStatus = [];

foreach ($statusTabs as $status) {
    if ($status === 'Semua') {
        $jumlahStatus[$status] = count($reservasi ?? []);
    } else {
        $jumlahStatus[$status] = count(array_filter($reservasi ?? [], fn($r) => ($r['status'] ?? '') === $status));
    }
}

$reservasiTampil = $activeTab === 'Semua'
    ? ($reservasi ?? [])
    : array_values(array_filter($reservasi ?? [], fn($r) => ($r['status'] ?? '') === $activeTab));
?>

<<<<<<< HEAD
<div class="riwayat-page">
    <div class="riwayat-header">
        <a href="<?= BASEURL ?>/pelanggan/buat-reservasi" class="btn btn-primary">
            <i data-lucide="plus-circle" width="14" height="14"></i>
            Buat Reservasi Baru
        </a>
    </div>

    <?php if (empty($reservasi)): ?>
        <div class="empty-state">
            <i data-lucide="clipboard-list" width="48" height="48" class="empty-icon"></i>
=======
<div>
	<div class="riwayat-header">
		<a href="<?= BASEURL ?>/pelanggan/buat-reservasi" class="btn btn-primary">

			<i data-lucide="plus-circle" width="14" height="14">
			</i>
			Buat Reservasi Baru
		</a>
	</div>

	<?php if (empty($reservasi)): ?>
		<div class="empty-state">
			<i data-lucide="clipboard-list" width="48" height="48" class="empty-icon">
			</i>
>>>>>>> 5050aa1868e7e474ce356535120cac92e664db99

			<p class="empty-text">
				Anda belum memiliki riwayat reservasi.
			</p>

<<<<<<< HEAD
            <a href="<?= BASEURL ?>/pelanggan/buatreservasi" class="btn empty-btn">
                Buat Reservasi Pertama Anda
            </a>
        </div>
    <?php else: ?>

        <div class="status-tabs">
            <?php foreach ($statusTabs as $status): ?>
                <a
                    href="<?= BASEURL ?>/pelanggan/riwayat?status=<?= urlencode($status) ?>"
                    class="status-tab <?= $activeTab === $status ? 'active' : '' ?>"
                >
                    <?= htmlspecialchars($status) ?>
                    <span><?= $jumlahStatus[$status] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($reservasiTampil)): ?>
            <div class="empty-state">
                <i data-lucide="clipboard-list" width="48" height="48" class="empty-icon"></i>
                <p class="empty-text">
                    Tidak ada riwayat dengan status <?= htmlspecialchars($activeTab) ?>.
                </p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kendaraan</th>
                                <th>Plat</th>
                                <th>Layanan</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Catatan</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($reservasiTampil as $r): ?>
                                <tr>
                                    <td class="mono riwayat-id">
                                        #<?= htmlspecialchars($r['id_reservasi'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($r['kendaraan'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <span class="plat">
                                            <?= htmlspecialchars($r['plat'] ?? '-') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($r['layanan'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($r['tanggal'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($r['jam'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(($r['catatan'] ?? '') !== '' ? $r['catatan'] : '-') ?>
                                    </td>

                                    <td>
                                        <?= badgeStatus($r['status'] ?? 'Menunggu') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
=======
			<a href="<?= BASEURL ?>/pelanggan/buatreservasi" class="btn empty-btn">
				Buat Reservasi Pertama Anda
			</a>
		</div>

	<?php else: ?>
		<div class="table-container">
			<div class="table-wrapper">
				<table>
					<thead>
						<tr>
							<th>ID</th>
							<th>Kendaraan</th>
							<th>Plat</th>
							<th>Layanan</th>
							<th>Tanggal</th>
							<th>Jam</th>
							<th>Catatan</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($reservasi as $r): ?>
							<tr>
								<td class="mono riwayat-id">
									#<?= $r['id_reservasi'] ?>
								</td>

								<td>
									<?= htmlspecialchars($r['kendaraan']) ?>
								</td>

								<td>
									<span class="plat">
										<?= htmlspecialchars($r['plat']) ?>
									</span>
								</td>

								<td>
									<?= htmlspecialchars($r['layanan'] ?? '-') ?>
								</td>

								<td>
									<?= htmlspecialchars($r['tanggal']) ?>
								</td>

								<td>
									<?= htmlspecialchars($r['jam']) ?>
								</td>

								<td>
									<?= htmlspecialchars($r['catatan'] ?: '-') ?>
								</td>

								<td>
									<?= badgeStatus($r['status']) ?>
								</td>

							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>
>>>>>>> 5050aa1868e7e474ce356535120cac92e664db99
</div>