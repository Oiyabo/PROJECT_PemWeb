<?php

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min(3, $step));

$data = $_SESSION['form_reservasi'] ?? [];
$data['nama'] = $data['nama'] ?? $user['nama'];
?>

<div class="reservation-container">
  <div class="step-card">
    <?php
    $steps = [1 => 'Data Kendaraan', 2 => 'Jadwal & Layanan', 3 => 'Konfirmasi'];
    foreach ($steps as $n => $label): ?>
      <div class="step-item <?= $step === $n ? 'active' : '' ?>">
        <span class="step-numbers"><?= $step > $n ? '✓' : $n ?></span>
        <span class="step-labels"><?= $label ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($step < 3): ?>
  <form class="reservation-form"
        action="<?= BASEURL ?>/pelanggan/buatreservasi?step=<?= $step + 1 ?>"
        method="POST">

    <?php if ($step === 1): ?>

    <div class="form-section">
      <h2 class="form-title">Data Kendaraan</h2>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Jenis Kendaraan <span class="required">*</span></label>
          <select name="jenisKendaraan" class="form-input" required>
            <option value="">Pilih</option>
            <option <?= ($data['jenisKendaraan'] ?? '') == 'Mobil' ? 'selected' : '' ?>>Mobil</option>
            <option <?= ($data['jenisKendaraan'] ?? '') == 'Motor' ? 'selected' : '' ?>>Motor</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Merk & Tipe <span class="required">*</span></label>
          <input name="kendaraan" type="text" class="form-input"
                 placeholder="Toyota Avanza / Honda Beat"
                 required value="<?= htmlspecialchars($data['kendaraan'] ?? '') ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Plat Nomor <span class="required">*</span></label>
        <input name="plat" type="text" class="form-input"
               placeholder="BE 1234 AB"
               required value="<?= htmlspecialchars($data['plat'] ?? '') ?>">
      </div>
    </div>

    <?php elseif ($step === 2): ?>

    <div class="form-section">
      <h2 class="form-title">Jadwal & Layanan</h2>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Tanggal Service <span class="required">*</span></label>
          <input name="tanggal" type="date" class="form-input"
                 required min="<?= date('Y-m-d') ?>"
                 value="<?= htmlspecialchars($data['tanggal'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Jam Service <span class="required">*</span></label>
          <select name="jam" class="form-input" required>
            <option value="">Pilih</option>
            <?php foreach (["08:00","09:00","10:00","11:00","13:00","14:00","15:00","16:00"] as $j): ?>
              <option <?= ($data['jam'] ?? '') == $j ? 'selected' : '' ?>><?= $j ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">

          <label class="form-label">
              Jenis Layanan <span class="required">*</span>
          </label>

          <div class="service-grid">

              <?php foreach ($layanans as $layanan): ?>

                  <?php
                  $jenis = $data['jenisKendaraan'] ?? '';
                  if ($jenis === 'Motor' && $layanan['harga_motor_full'] === null) {
                      continue;
                  }
                  if ($jenis === 'Mobil' && $layanan['harga_mobil_full'] === null) {
                      continue;
                  }
                  ?>

                  <label class="service-item">

                      <input
                          type="checkbox"
                          name="layanan_id[]"
                          value="<?= $layanan['layanan_id']; ?>"

                          <?= in_array(
                                $layanan['layanan_id'],
                                $data['layanan_id'] ?? []
                            ) ? 'checked' : '' ?>
                      >

                      <span class="service-text">
                          <?= htmlspecialchars($layanan['nama_layanan']); ?>
                      </span>

                  </label>

              <?php endforeach; ?>

          </div>

      </div>

      <div class="form-group">
        <label class="form-label">Catatan (opsional)</label>
        <textarea name="catatan" class="form-input form-textarea"
                  placeholder="Keluhan atau informasi tambahan..."><?= htmlspecialchars($data['catatan'] ?? '') ?></textarea>
      </div>
    </div>

    <?php endif; ?>

    <div class="form-navigation">
      <?php if ($step > 1): ?>
        <a href="<?= BASEURL ?>/pelanggan/buatreservasi?step=<?= $step - 1 ?>" class="btn-secondary">← Kembali</a>
      <?php else: ?>
        <a href="<?= BASEURL ?>/pelanggan" class="btn-secondary">Batal</a>
      <?php endif; ?>

      <button type="submit" class="btn-primary">Lanjut ➜</button>
    </div>

  </form>

  <?php else: ?>

  <div class="form-section">
    <h2 class="form-title">Konfirmasi Reservasi</h2>

    <div class="confirmation-box">
      <div class="confirmation-item"><span>Kendaraan</span><span><?= htmlspecialchars($data['kendaraan'] ?? '-') ?></span></div>
      <div class="confirmation-item"><span>Plat Nomor</span><span><?= htmlspecialchars($data['plat'] ?? '-') ?></span></div>
      <div class="confirmation-item">
          <span>Layanan</span>
          <span>
              <?php
              if (!empty($data['layanan_id'])) {
                  $selected = [];
                  foreach ($layanans as $layanan) {
                      if (in_array($layanan['layanan_id'], $data['layanan_id'])) {
                          $selected[] = $layanan['nama_layanan'];
                      }
                  }
                  echo htmlspecialchars(implode(', ', $selected));
              } else {
                  echo '-';
              }
              ?>
          </span>
      </div>
      <div class="confirmation-item"><span>Tanggal</span><span><?= htmlspecialchars($data['tanggal'] ?? '-') ?></span></div>
      <div class="confirmation-item"><span>Jam</span><span><?= htmlspecialchars($data['jam'] ?? '-') ?></span></div>
      <div class="confirmation-item"><span>Catatan</span><span><?= htmlspecialchars($data['catatan'] ?? '-') ?></span></div>
    </div>

    <div class="price-calculation">
      <h3 style="margin-bottom: 12px; font-size: 16px; font-weight: 700;">Perhitungan Biaya</h3>
      <table class="price-table" style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr style="border-bottom: 2px solid #38a3a5;">
            <th style="text-align: left; padding: 8px; font-weight: 700;">Layanan</th>
            <th style="text-align: right; padding: 8px; font-weight: 700;">Harga DP</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $ringkasan = $ringkasanHarga ?? ['items' => [], 'total_dp' => 0, 'total_full' => 0, 'total_sisa' => 0];
          $totalDP = (int) $ringkasan['total_dp'];
          $totalFull = (int) $ringkasan['total_full'];
          $jenisLabel = htmlspecialchars($data['jenisKendaraan'] ?? '-');

          foreach ($ringkasan['items'] as $item):
          ?>
                      <tr style="border-bottom: 1px solid #e2e8f0;">
                          <td style="padding: 12px 8px;"><?= htmlspecialchars($item['nama_layanan']) ?></td>
                          <td style="text-align: right; padding: 12px 8px; font-weight: 600;">Rp <?= number_format((int) $item['dp'], 0, ',', '.') ?></td>
                      </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="border-top: 2px solid #38a3a5; background-color: #f8fafc;">
            <td style="padding: 12px 8px; font-weight: 700;">Total DP (<?= $jenisLabel ?>)</td>
            <td style="text-align: right; padding: 12px 8px; font-weight: 700; font-size: 16px; color: #38a3a5;">Rp <?= number_format($totalDP, 0, ',', '.') ?></td>
          </tr>
          <tr style="background-color: #f8fafc;">
            <td style="padding: 8px; font-weight: 600; color: #64748b;">Estimasi Harga Full (<?= $jenisLabel ?>)</td>
            <td style="text-align: right; padding: 8px; font-weight: 600; color: #64748b;">Rp <?= number_format($totalFull, 0, ',', '.') ?></td>
          </tr>
          <tr style="background-color: #fff7ed;">
            <td style="padding: 8px; font-weight: 600; color: #9a3412;">Sisa setelah DP</td>
            <td style="text-align: right; padding: 8px; font-weight: 600; color: #9a3412;">Rp <?= number_format((int) $ringkasan['total_sisa'], 0, ',', '.') ?></td>
          </tr>
        </tfoot>
      </table>
    </div>

  </div>

  <form id="formReservasi" action="<?= BASEURL ?>/pelanggan/simpanReservasi" method="POST">

    <input type="hidden" name="kendaraan" value="<?= htmlspecialchars($data['kendaraan'] ?? '') ?>">
    <input type="hidden" name="plat" value="<?= htmlspecialchars($data['plat'] ?? '') ?>">
    <input type="hidden" name="jenisKendaraan" value="<?= htmlspecialchars($data['jenisKendaraan'] ?? '') ?>">
    <input type="hidden" name="dp_paid" id="dpPaidInput" value="0">
    <input type="hidden" name="metode_pembayaran_dp" id="metodeDpInput" value="">
    <input type="hidden" name="midtrans_order_id" id="midtransOrderIdInput" value="">
    <?php if (!empty($data['layanan_id'])): ?>
      <?php foreach ($data['layanan_id'] as $id): ?>
          <input
              type="hidden"
              name="layanan_id[]"
              value="<?= htmlspecialchars($id) ?>"
          >

      <?php endforeach; ?>

  <?php endif; ?>
    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($data['tanggal'] ?? '') ?>">
    <input type="hidden" name="jam" value="<?= htmlspecialchars($data['jam'] ?? '') ?>">
    <input type="hidden" name="catatan" value="<?= htmlspecialchars($data['catatan'] ?? '') ?>">
    <input type="hidden" name="totalDP" id="totalDPInput" value="<?= $totalDP ?>">

    <div class="form-navigation">
      <a href="<?= BASEURL ?>/pelanggan/buatReservasi?step=2" class="btn-secondary">← Kembali</a>

      <button type="button" class="btn-primary" id="btnBayarDpSelesai" onclick="bayarDpDanSelesaikan()">
        💳 Bayar DP dan Selesaikan — Rp <?= number_format($totalDP, 0, ',', '.') ?>
      </button>
    </div>

    <p style="margin-top: 12px; font-size: 13px; color: #64748b; max-width: 520px;">
      Satu langkah: bayar DP via Midtrans, lalu reservasi dibuat otomatis setelah pembayaran terkonfirmasi.
    </p>
  </form>

  <!-- Modal sukses DP + reservasi -->
  <div id="reservasiSuksesModal" class="popup-overlay" style="display: none;">
    <div class="popup-box" style="max-width: 420px; text-align: center;">
      <div style="font-size: 48px; margin-bottom: 8px;">✓</div>
      <h3 style="margin: 0 0 12px; color: #155724;">Pembayaran Berhasil!</h3>
      <p style="margin: 0 0 16px; color: #333; line-height: 1.5;">
        Pembayaran DP Anda telah diterima dan reservasi berhasil dibuat.
      </p>
      <div style="margin: 16px 0; padding: 14px; background: #f8fafc; border-radius: 8px; text-align: left; font-size: 14px;">
        <p style="margin: 6px 0;"><strong>No. Reservasi:</strong> <span id="suksesReservasiId">-</span></p>
        <p style="margin: 6px 0;"><strong>Nominal DP:</strong> <span id="suksesDpNominal">-</span></p>
        <p style="margin: 6px 0; color: #64748b; font-size: 13px;">Tim kami akan segera mengkonfirmasi jadwal service Anda.</p>
      </div>
      <div class="popup-actions" style="justify-content: center;">
        <button type="button" class="btn-primary" onclick="tutupSuksesDanKeRiwayat()">Selesai</button>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>

<script>
  window.APP_BASEURL = <?= json_encode(BASEURL) ?>;
  window.MIDTRANS_CLIENT_KEY = <?= json_encode($midtrans_client_key ?? '') ?>;
  window.MIDTRANS_SNAP_SCRIPT = <?= json_encode($midtrans_snap_script ?? '') ?>;
</script>
<script src="<?= BASEURL ?>/assets/js/midtrans-payment.js"></script>
<script src="<?= BASEURL ?>/assets/js/buat-reservasi.js"></script>