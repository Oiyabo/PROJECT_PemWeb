<?php

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min(3, $step));

$data = $_SESSION['form_reservasi'] ?? [];
$data['nama'] = $data['nama'] ?? $user['nama'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['form_reservasi'] = array_merge($data, $_POST);
}
?>

<link rel="stylesheet" href="<?= BASEURL ?>/assets/css/buat-reservasi.css">

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
        <label class="form-label">Jenis Layanan <span class="required">*</span></label>
        <select name="layanan" class="form-input" required>
          <option value="">Pilih</option>
          <?php foreach (["Ganti Oli","Tune Up","Servis Berkala","Ganti Ban","AC Service","Rem/Brake","Kelistrikan","Body Repair","Lainnya"] as $l): ?>
            <option <?= ($data['layanan'] ?? '') == $l ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
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
      <div class="confirmation-item"><span>Layanan</span><span><?= htmlspecialchars($data['layanan'] ?? '-') ?></span></div>
      <div class="confirmation-item"><span>Tanggal</span><span><?= htmlspecialchars($data['tanggal'] ?? '-') ?></span></div>
      <div class="confirmation-item"><span>Jam</span><span><?= htmlspecialchars($data['jam'] ?? '-') ?></span></div>
      <div class="confirmation-item"><span>Catatan</span><span><?= htmlspecialchars($data['catatan'] ?? '-') ?></span></div>
    </div>
  </div>

  <form id="formReservasi" action="<?= BASEURL ?>/pelanggan/simpanReservasi" method="POST">

    <input type="hidden" name="kendaraan" value="<?= htmlspecialchars($data['kendaraan'] ?? '') ?>">
    <input type="hidden" name="plat" value="<?= htmlspecialchars($data['plat'] ?? '') ?>">
    <input type="hidden" name="layanan" value="<?= htmlspecialchars($data['layanan'] ?? '') ?>">
    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($data['tanggal'] ?? '') ?>">
    <input type="hidden" name="jam" value="<?= htmlspecialchars($data['jam'] ?? '') ?>">
    <input type="hidden" name="catatan" value="<?= htmlspecialchars($data['catatan'] ?? '') ?>">

    <div class="form-navigation">
      <a href="<?= BASEURL ?>/pelanggan/buatReservasi?step=2" class="btn-secondary">← Kembali</a>
      <button type="button" class="btn-primary"  onclick="openConfirm()">✔ Kirim Reservasi</button>
    </div>
  </form>

  <div id="confirmPopup" class="popup-overlay">
    <div class="popup-box">
        <h3>Konfirmasi Reservasi</h3>
        <p>Yakin ingin mengirim reservasi ini?</p>

        <div class="popup-actions">
        <button class="btn-secondary" onclick="closeConfirm()">Batal</button>
        <button class="btn-primary" onclick="submitReservasi()">Ya, Kirim</button>
        </div>
    </div>
    </div>
  <?php endif; ?>

</div>

<script src="<?= BASEURL ?>/assets/js/buat-reservasi.js"></script>