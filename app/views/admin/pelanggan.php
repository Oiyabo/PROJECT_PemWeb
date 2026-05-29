<div class="pelanggan-container">

	<div class="search-wrapper">
		<div class="search-box">
			<i data-lucide="search" color="#38a3a5" width="18" height="18"></i>
			<input type="text" placeholder="Cari nama atau email">
		</div>
	</div>

	<table class="table-pelanggan">
		<thead>
			<tr>
				<th>ID</th>
				<th>NAMA</th>
				<th>EMAIL</th>
				<th>ROLE</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($data['pelanggan'])): ?>
				<?php foreach ($data['pelanggan'] as $index => $p): ?>
					<tr>
						<td class="id-text">
							#<?= htmlspecialchars($p['id'] ?? ($index + 1)) ?>
						</td>
						<td>
							<div class="avatar-wrapper">
								<div class="avatar-circle">
									<?php
									// Mengambil huruf pertama dari nama untuk avatar
									$nama = $p['nama'] ?? '?';
									echo strtoupper(substr($nama, 0, 1));
									?>
								</div>
								<span><?= htmlspecialchars($p['nama'] ?? 'Tidak ada nama') ?></span>
							</div>
						</td>
						<td>
							<?= htmlspecialchars($p['email'] ?? '-') ?>
						</td>
						<td>
							<span class="role-badge">
								<?= htmlspecialchars($p['role'] ?? 'Pelanggan') ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr>
					<td class="id-text">#2</td>
					<td>
						<div class="avatar-wrapper">
							<div class="avatar-circle">B</div>
							<span>Budi Santoso</span>
						</div>
					</td>
					<td>pelanggan@email.com</td>
					<td>
						<span class="role-badge">Pelanggan</span>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

</div>