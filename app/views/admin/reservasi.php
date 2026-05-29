<?php
if (!function_exists('badgeStatus')) {
<<<<<<< HEAD
    function badgeStatus(string $status): string {
        $statusMap = [
            'Menunggu'   => 'status-waiting',
            'Konfirmasi' => 'status-confirmed',
            'Proses'     => 'status-process',
            'Selesai'    => 'status-done',
            'Batal'      => 'status-danger',
        ];

        $class = $statusMap[$status] ?? 'status-waiting';

        return '<span class="status-badge ' . $class . '">'
            . htmlspecialchars($status) .
            '</span>';
    }
=======
	function badgeStatus(string $status): string
	{
		$statusMap = [
			'Menunggu' => 'status-waiting',
			'Konfirmasi' => 'status-confirmed',
			'Proses' => 'status-waiting',
			'Selesai' => 'status-done',
			'Batal' => 'status-danger',
		];

		$class = $statusMap[$status] ?? 'status-waiting';
		return '<span class="status-badge ' . $class . '">' . htmlspecialchars($status) . '</span>';
	}
>>>>>>> 5050aa1868e7e474ce356535120cac92e664db99
}

$statusTabs = $statusTabs ?? ['Menunggu', 'Konfirmasi', 'Proses', 'Selesai', 'Batal'];
$statusAktif = $statusAktif ?? 'Menunggu';
$jumlahStatus = $jumlahStatus ?? [];
$keyword = $keyword ?? '';
$reservasiList = $reservasi ?? [];
?>

<div class="reservasi-container">
<<<<<<< HEAD

    <div class="status-tabs">
        <?php foreach ($statusTabs as $status): ?>
            <a
                href="<?= BASEURL ?>/admin/reservasi?status=<?= urlencode($status) ?>"
                class="status-tab <?= $statusAktif === $status ? 'active' : '' ?>"
            >
                <?= htmlspecialchars($status) ?>
                <span><?= $jumlahStatus[$status] ?? 0 ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <form class="search-wrapper" method="GET" action="<?= BASEURL ?>/admin/reservasi">
        <input type="hidden" name="status" value="<?= htmlspecialchars($statusAktif) ?>">

        <div class="search-box">
            <i data-lucide="search" color="#38a3a5" width="18" height="18"></i>
            <input
                type="text"
                name="q"
                placeholder="Cari pelanggan, kendaraan, plat, layanan..."
                value="<?= htmlspecialchars($keyword) ?>"
            >
        </div>

        <?php if (!empty($keyword)): ?>
            <a
                href="<?= BASEURL ?>/admin/reservasi?status=<?= urlencode($statusAktif) ?>"
                class="btn-reset-search"
            >
                Reset
            </a>
        <?php endif; ?>
    </form>

    <div class="table-container">
        <div class="table-wrapper">
            <table class="table-reservasi">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Kendaraan</th>
                        <th>Plat</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($reservasiList)): ?>
                        <tr>
                            <td colspan="9" class="empty-table">
                                <?php if (!empty($keyword)): ?>
                                    Data reservasi status <?= htmlspecialchars($statusAktif) ?> dengan kata kunci "<?= htmlspecialchars($keyword) ?>" tidak ditemukan.
                                <?php else: ?>
                                    Belum ada data reservasi dengan status <?= htmlspecialchars($statusAktif) ?>.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservasiList as $r): ?>
                            <tr>
                                <td class="riwayat-id">
                                    #<?= htmlspecialchars($r['id_reservasi'] ?? '') ?>
                                </td>

                                <td class="text-primary">
                                    <?= htmlspecialchars($r['nama'] ?? 'Unknown') ?>
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
                                    <?= htmlspecialchars($r['tanggal'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r['jam'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r['layanan'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= badgeStatus($r['status'] ?? 'Menunggu') ?>
                                </td>

                                <td>
                                    <form
                                        action="<?= BASEURL ?>/admin/updatestatus/<?= htmlspecialchars($r['id_reservasi'] ?? '') ?>"
                                        method="POST"
                                        class="status-form"
                                    >
                                        <input
                                            type="hidden"
                                            name="back"
                                            value="<?= htmlspecialchars(BASEURL . '/admin/reservasi?status=' . urlencode($statusAktif) . (!empty($keyword) ? '&q=' . urlencode($keyword) : '')) ?>"
                                        >

                                        <select name="status" class="form-input form-input-sm" onchange="this.form.submit()">
                                            <?php foreach (['Menunggu', 'Konfirmasi', 'Proses', 'Selesai', 'Batal'] as $statusOption): ?>
                                                <option
                                                    value="<?= $statusOption ?>"
                                                    <?= ($r['status'] ?? '') === $statusOption ? 'selected' : '' ?>
                                                >
                                                    <?= $statusOption ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
=======
	<!-- Search Bar -->
	<div class="search-wrapper">
		<div class="input-search">
			<i data-lucide="search" width="16" height="16"></i>
			<input id="searchInput" type="text" placeholder="Cari nama pelanggan...">
		</div>
	</div>

	<!-- Reservasi Table -->
	<div class="table-container">
		<div class="table-wrapper">
			<table class="table-reservasi" id="reservasiTable">
				<thead>
					<tr>
						<th>ID</th>
						<th>Pelanggan</th>
						<th>Kendaraan</th>
						<th>Plat</th>
						<th>Tanggal</th>
						<th>Jam</th>
						<th>Layanan</th>
						<th>Status</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($reservasiList)): ?>
						<tr class="table-empty">
							<td colspan="9">
								<div class="empty-state">
									<p class="empty-text">Belum ada data reservasi</p>
								</div>
							</td>
						</tr>
					<?php else: ?>
						<?php foreach ($reservasiList as $r): ?>
							<tr>
								<td class="riwayat-id">#<?= htmlspecialchars($r['id_reservasi']) ?></td>
								<td class="text-primary"><?= htmlspecialchars($r['nama'] ?? 'Unknown') ?></td>
								<td><?= htmlspecialchars($r['kendaraan'] ?? '') ?></td>
								<td><span class="plat"><?= htmlspecialchars($r['plat'] ?? '') ?></span></td>
								<td><?= htmlspecialchars($r['tanggal'] ?? '') ?></td>
								<td><?= htmlspecialchars($r['jam'] ?? '') ?></td>
								<td><?= htmlspecialchars($r['layanan'] ?? '') ?></td>
								<td><?= badgeStatus($r['status'] ?? '') ?></td>
								<td>
									<form action="<?= BASEURL ?>/admin/updatestatus/<?= $r['id_reservasi'] ?>" method="POST"
										class="status-form">
										<select name="status" class="form-input form-input-sm" onchange="this.form.submit()">
											<?php foreach (['Menunggu', 'Konfirmasi', 'Proses', 'Selesai', 'Batal'] as $statusOption): ?>
												<option value="<?= $statusOption ?>" <?= ($r['status'] ?? '') === $statusOption ? 'selected' : '' ?>>
													<?= $statusOption ?>
												</option>
											<?php endforeach; ?>
										</select>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
	document.getElementById('searchInput').addEventListener('input', function () {
		const keyword = this.value.toLowerCase();
		const tableRows = document.querySelectorAll('#reservasiTable tbody tr');

		tableRows.forEach(row => {
			const isMatch = row.textContent.toLowerCase().includes(keyword);
			row.style.display = isMatch ? '' : 'none';
		});
	});
</script>
>>>>>>> 5050aa1868e7e474ce356535120cac92e664db99
