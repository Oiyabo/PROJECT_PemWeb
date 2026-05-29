<div class="transaksi-container">
	<div class="table-transaksi-wrapper">
		<table class="table-transaksi">
			<thead>
				<tr>
					<th>ID</th>
					<th>PELANGGAN</th>
					<th>LAYANAN</th>
					<th>KENDARAAN</th>
					<th>TANGGAL</th>
					<th>STATUS</th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($data['transaksi'])): ?>
					<?php foreach ($data['transaksi'] as $index => $t): ?>
						<tr>
							<td class="id-text">
								#<?= htmlspecialchars($t['id'] ?? ($index + 1)) ?>
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
									<span class="badge-plat"><?= htmlspecialchars($t['plat']) ?></span>
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
						<td class="id-text">#1</td>
						<td>Budi Santoso</td>
						<td>Ganti Oli</td>
						<td>
							Honda Beat <span class="badge-plat">BE 1234 AB</span>
						</td>
						<td>2026-05-10</td>
						<td>
							<span class="badge-selesai">Selesai</span>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>