<?php
$data = $_SESSION['form_reservasi'] ?? [];
?>

<link rel="stylesheet" href="<?= BASEURL ?>/assets/css/style.css">

<div class="reservation-container" id="reservasiWizard" data-save-url="<?= BASEURL ?>/pelanggan/buatreservasi"
	data-cek-jadwal-url="<?= BASEURL ?>/pelanggan/cekjadwal">

	<div class="step-card" id="stepIndicator">
		<?php
		$steps = [1 => 'Data Kendaraan', 2 => 'Jadwal & Layanan', 3 => 'Konfirmasi'];
		foreach ($steps as $n => $label): ?>
			<div class="step-item" data-step="<?= $n ?>">
				<span class="step-numbers"><?= $n ?></span>
				<span class="step-labels"><?= $label ?></span>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Step 1: Data Kendaraan -->
	<div class="reservation-form form-step" data-step="1">
		<div class="form-section">
			<h2 class="form-title">Data Kendaraan</h2>

			<div class="form-grid">
				<div class="form-group">
					<label class="form-label">Jenis Kendaraan <span class="required">*</span></label>
					<select name="jenisKendaraan" id="jenisKendaraan" class="form-input" required>
						<option value="">Pilih</option>
						<option value="Mobil" <?= ($data['jenisKendaraan'] ?? '') === 'Mobil' ? 'selected' : '' ?>>Mobil</option>
						<option value="Motor" <?= ($data['jenisKendaraan'] ?? '') === 'Motor' ? 'selected' : '' ?>>Motor</option>
					</select>
				</div>

				<div class="form-group">
					<label class="form-label">Merk & Tipe <span class="required">*</span></label>
					<input name="kendaraan" id="kendaraan" type="text" class="form-input" placeholder="Toyota Avanza / Honda Beat"
						required value="<?= htmlspecialchars($data['kendaraan'] ?? '') ?>">
				</div>
			</div>

			<div class="form-group">
				<label class="form-label">Plat Nomor <span class="required">*</span></label>
				<input name="plat" id="plat" type="text" class="form-input" placeholder="BE 1234 AB" required
					pattern="^[A-Za-z]{1,2}\s?\d{1,4}\s?[A-Za-z]{0,3}$"
					title="Format plat nomor Indonesia. Contoh: B 1234 ABC atau BE 1234"
					style="text-transform: uppercase;"
					value="<?= htmlspecialchars($data['plat'] ?? '') ?>">
			</div>
		</div>

		<div class="form-navigation">
			<a href="<?= BASEURL ?>/pelanggan/batalReservasi" class="btn-secondary">Batal</a>
			<button type="button" class="btn-primary" data-action="next">Lanjut ➜</button>
		</div>
	</div>

	<!-- Step 2: Jadwal & Layanan -->
	<div class="reservation-form form-step" data-step="2" hidden>
		<div class="form-section">
			<h2 class="form-title">Jadwal & Layanan</h2>

			<div class="form-grid" id="jadwalFields">
				<div class="form-group">
					<label class="form-label">Tanggal Service <span class="required">*</span></label>
					<input name="tanggal" id="tanggal" type="date" class="form-input" required min="<?= date('Y-m-d') ?>"
						value="<?= htmlspecialchars($data['tanggal'] ?? '') ?>">
				</div>

				<div class="form-group">
					<label class="form-label">Jam Service <span class="required">*</span></label>
					<select name="jam" id="jam" class="form-input" required>
						<option value="">Pilih</option>
						<?php foreach (["08:00", "09:00", "10:00", "11:00", "13:00", "14:00", "15:00", "16:00"] as $j): ?>
							<option value="<?= $j ?>" <?= ($data['jam'] ?? '') === $j ? 'selected' : '' ?>><?= $j ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div id="jadwalStatus" class="jadwal-status" aria-live="polite" hidden></div>

			<div class="form-group">
        <label class="form-label">Jenis Layanan <span class="required">*</span></label>

        <div class="service-search-box">
            <i class="ti ti-search"></i>
            <input type="text" id="serviceSearchInput"
                  placeholder="Cari layanan..."
                  oninput="filterLayanan(this.value)">
        </div>

        <div class="service-filter-tags">
            <span class="svc-ftag active" onclick="setKategori(this, 'all')">Semua</span>
            <?php
            $labelKategori = [
                'oli'       => 'Oli & Mesin',
                'transmisi' => 'Transmisi & Rantai',
                'rem'       => 'Rem & Suspensi',
                'ban'       => 'Ban & Roda',
                'ac'        => 'AC',
                'listrik'   => 'Kelistrikan',
                'body'      => 'Body & Kebersihan',
                'lain'      => 'Lainnya',
            ];
            foreach ($kategoris as $kat): ?>
                <span class="svc-ftag" onclick="setKategori(this, '<?= htmlspecialchars($kat) ?>')">
                    <?= $labelKategori[$kat] ?? ucfirst($kat) ?>
                </span>
            <?php endforeach; ?>
        </div>

        <div class="service-grid" id="serviceGrid">
            <?php foreach ($layanans as $layanan):
                $motorOk = $layanan['harga_motor_full'] !== null;
                $mobilOk = $layanan['harga_mobil_full'] !== null;
                $checked = in_array($layanan['layanan_id'], $data['layanan_id'] ?? [], false);
            ?>
                <label class="service-item <?= $checked ? 'selected' : '' ?>"
                      data-layanan-id="<?= (int) $layanan['layanan_id'] ?>"
                      data-motor="<?= $motorOk ? '1' : '0' ?>"
                      data-mobil="<?= $mobilOk ? '1' : '0' ?>"
                      data-kategori="<?= htmlspecialchars($layanan['kategori'] ?? 'lain') ?>"
                      data-nama="<?= strtolower(htmlspecialchars($layanan['nama_layanan'])) ?>">
                    <input type="checkbox"
                          name="layanan_id[]"
                          value="<?= (int) $layanan['layanan_id'] ?>"
                          <?= $checked ? 'checked' : '' ?>
                          onchange="toggleServiceItem(this)">
                    <span class="service-text"><?= htmlspecialchars($layanan['nama_layanan']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="service-selected-count hidden" id="serviceSelectedCount">
            <i class="ti ti-check"></i>
            <span id="serviceCountNum">0</span> layanan dipilih
        </div>
      </div>

			<div class="form-group">
				<label class="form-label">Catatan (opsional)</label>
				<textarea name="catatan" id="catatan" class="form-input form-textarea"
					placeholder="Keluhan atau informasi tambahan..."><?= htmlspecialchars($data['catatan'] ?? '') ?></textarea>
			</div>
		</div>

		<div class="form-navigation">
			<button type="button" class="btn-secondary" data-action="prev">← Kembali</button>
			<button type="button" class="btn-primary" data-action="next">Lanjut ➜</button>
		</div>
	</div>

	<!-- Step 3: Konfirmasi -->
	<div class="form-step" data-step="3" hidden>
		<div class="reservation-form">
			<div class="form-section">
				<h2 class="form-title">Konfirmasi Reservasi</h2>

				<div class="confirmation-box" id="confirmSummary">
					<div class="confirmation-item"><span>Kendaraan</span><span data-field="kendaraan">-</span></div>
					<div class="confirmation-item"><span>Plat Nomor</span><span data-field="plat">-</span></div>
					<div class="confirmation-item"><span>Jenis</span><span data-field="jenisKendaraan">-</span></div>
					<div class="confirmation-item"><span>Layanan</span><span data-field="layanan">-</span></div>
					<div class="confirmation-item"><span>Tanggal</span><span data-field="tanggal">-</span></div>
					<div class="confirmation-item"><span>Jam</span><span data-field="jam">-</span></div>
					<div class="confirmation-item"><span>Catatan</span><span data-field="catatan">-</span></div>
				</div>

				<div class="price-calculation">
					<h3 class="price-title">Perhitungan Biaya</h3>

					<table class="price-table">
						<thead>
							<tr class="price-table-head-row">
								<th class="price-table-header">Layanan</th>
								<th class="price-table-header-right">Harga DP</th>
							</tr>
						</thead>

						<tbody id="priceTableBody"></tbody>
						<tfoot id="priceTableFoot"></tfoot>
					</table>
				</div>
			</div>
		</div>

		<form id="formReservasi" action="<?= BASEURL ?>/pelanggan/simpanReservasi" method="POST"
			class="reservation-form reservation-form-no-style">

			<div id="hiddenFieldsContainer"></div>

			<input type="hidden" name="dp_paid" id="dpPaidInput" value="0">
			<input type="hidden" name="metode_pembayaran_dp" id="metodeDpInput" value="">
			<input type="hidden" name="midtrans_order_id" id="midtransOrderIdInput" value="">
			<input type="hidden" name="totalDP" id="totalDPInput" value="0">

			<div class="form-navigation">
				<button type="button" class="btn-secondary" data-action="prev">← Kembali</button>

				<button type="button" class="btn-primary" id="btnBayarDpSelesai">
					💳 Bayar DP dan Selesaikan — <span id="btnDpNominal">Rp 0</span>
				</button>
			</div>

			<p class="payment-note">
				Satu langkah: bayar DP via Midtrans, lalu reservasi dibuat otomatis setelah pembayaran terkonfirmasi.
			</p>
		</form>
	</div>

	<!-- Modal sukses DP + reservasi -->
	<div id="reservasiSuksesModal" class="popup-overlay hidden">

		<div class="popup-box popup-box-success">

			<div class="popup-check-icon">✓</div>

			<h3 class="popup-success-title">
				Pembayaran Berhasil!
			</h3>

			<p class="popup-success-description">
				Pembayaran DP Anda telah diterima dan reservasi berhasil dibuat.
			</p>

			<div class="popup-info-box">
				<p class="popup-info-text">
					<strong>No. Reservasi:</strong>
					<span id="suksesReservasiId">-</span>
				</p>

				<p class="popup-info-text">
					<strong>Nominal DP:</strong>
					<span id="suksesDpNominal">-</span>
				</p>

				<p class="popup-small-note">
					Tim kami akan segera mengkonfirmasi jadwal service Anda.
				</p>
			</div>

			<div class="popup-actions popup-actions-center">
				<button type="button" class="btn-primary" onclick="tutupSuksesDanKeRiwayat()">
					Selesai
				</button>
			</div>

		</div>
	</div>
</div>

<script>
	window.APP_BASEURL = <?= json_encode(BASEURL) ?>;
	window.MIDTRANS_CLIENT_KEY = <?= json_encode($midtrans_client_key ?? '') ?>;
	window.MIDTRANS_SNAP_SCRIPT = <?= json_encode($midtrans_snap_script ?? '') ?>;
	window.RESERVASI_LAYANAN_MAP = <?= json_encode($layananMap ?? []) ?>;
</script>

<script src="<?= BASEURL ?>/assets/js/midtrans-payment.js"></script>
<script src="<?= BASEURL ?>/assets/js/reservasi/common.js"></script>
<script src="<?= BASEURL ?>/assets/js/reservasi/jadwal.js"></script>
<script src="<?= BASEURL ?>/assets/js/reservasi/layanan.js"></script>
<script src="<?= BASEURL ?>/assets/js/reservasi/pembayaran.js"></script>
<script src="<?= BASEURL ?>/assets/js/reservasi/wizard.js"></script>