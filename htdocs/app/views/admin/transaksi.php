<div class="transaksi-container">

	<form class="search-wrapper" method="GET" action="<?= BASEURL ?>/admin/transaksi">
		<div class="search-box">
			<i data-lucide="search" color="#38a3a5" width="18" height="18"></i>
			<input type="text" name="q" placeholder="Cari pelanggan, layanan, kendaraan, plat..."
				value="<?= htmlspecialchars($keyword ?? '') ?>">
		</div>

		<?php if (!empty($keyword)): ?>
			<a href="<?= BASEURL ?>/admin/transaksi" class="btn-reset-search">
				Reset
			</a>
		<?php endif; ?>
	</form>

	<div class="table-transaksi-wrapper">
		<table class="table-transaksi">
			<thead>
				<tr>
					<th>ID</th>
					<th>Pelanggan</th>
					<th>Layanan</th>
					<th>Kendaraan</th>
					<th>Tanggal</th>
					<th>Status</th>
				</tr>
			</thead>

			<tbody>
				<?php if (!empty($transaksi)): ?>
					<?php foreach ($transaksi as $index => $t): ?>
						<tr>
							<td class="id-text">
								#<?= htmlspecialchars($t['id_reservasi'] ?? ($index + 1)) ?>
							</td>

							<td>
								<?= htmlspecialchars($t['nama'] ?? 'Tidak diketahui') ?>
							</td>

							<td>
								<?= htmlspecialchars($t['layanan'] ?? '-') ?>
							</td>

							<td>
								<?= htmlspecialchars($t['kendaraan'] ?? '-') ?>

								<?php if (!empty($t['plat'])): ?>
									<span class="badge-plat">
										<?= htmlspecialchars($t['plat']) ?>
									</span>
								<?php endif; ?>
							</td>

							<td>
								<?= htmlspecialchars($t['tanggal'] ?? '-') ?>
							</td>

							<td>
								<span class="badge-selesai">
									<?= htmlspecialchars($t['status'] ?? 'Selesai') ?>
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="6" class="empty-table">
							<?php if (!empty($keyword)): ?>
								Data transaksi dengan kata kunci "<?= htmlspecialchars($keyword) ?>" tidak ditemukan.
							<?php else: ?>
								Belum ada data transaksi.
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

</div>