<?php
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min(4, $step));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['reservasi'] = array_merge(
        $_SESSION['reservasi'] ?? [],
        $_POST
    );
}

$data = $_SESSION['reservasi'] ?? [];
?>

<link rel="stylesheet" href="style/buat-reservasi.css">

<div class="reservation-container">

    <div class="step-card">
        <?php
        $steps = [
            1 => 'Data Pelanggan',
            2 => 'Data Kendaraan',
            3 => 'Jadwal & Layanan',
            4 => 'Konfirmasi Reservasi'
        ];
        foreach ($steps as $n => $label):
        ?>
        <div class="step-item <?= $step === $n ? 'active' : '' ?>">
            <span class="step-numbers">
                <?= $step > $n ? '✓' : $n ?>
            </span>
            <span class="step-labels"><?= $label ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <form class="reservation-form"
          action="?page=buat-reservasi&step=<?= $step < 4 ? $step + 1 : 4 ?>"
          method="POST">

        <?php if ($step === 1): ?>
        <div class="form-section">
            <h2 class="form-title">Data Pelanggan</h2>

            <div class="form-group">
                <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                <input name="nama" type="text" class="form-input"
                       placeholder="Masukkan nama lengkap" 
                       required
                       value="<?= $data['nama'] ?? '' ?>">
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nomor Telepon <span class="required">*</span></label>
                    <input name="telepon" type="text" class="form-input"
                           placeholder="08xx-xxxx-xxxx"
                           required
                           value="<?= $data['telepon'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" class="form-input"
                           placeholder="nama@email.com"
                           value="<?= $data['email'] ?? '' ?>">
                </div>
            </div>
        </div>

        <?php elseif ($step === 2): ?>
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
                    <input name="merk" type="text" class="form-input"
                           placeholder="Toyota Avanza / Honda Beat"
                           required
                           value="<?= $data['merk'] ?? '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Plat Nomor <span class="required">*</span></label>
                <input name="plat" type="text" class="form-input"
                       placeholder="BE 1234 AB"
                       required
                       value="<?= $data['plat'] ?? '' ?>">
            </div>
        </div>

        <?php elseif ($step === 3): ?>
        <div class="form-section">
            <h2 class="form-title">Jadwal & Layanan</h2>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tanggal Service <span class="required">*</span></label>
                    <input name="tanggal" type="date" class="form-input"
                           required
                           value="<?= $data['tanggal'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Jam Service <span class="required">*</span></label>
                    <select name="jam" class="form-input" required>
                        <option value="">Pilih</option>
                        <?php foreach(["08:00","09:00","10:00","11:00","13:00","14:00","15:00","16:00"] as $j): ?>
                            <option <?= ($data['jam'] ?? '') == $j ? 'selected' : '' ?>><?= $j ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Jenis Layanan <span class="required">*</span></label>
                <select name="layanan" class="form-input" required>
                    <option value="">Pilih</option>
                    <?php foreach(["Ganti Oli", "Tune Up", "Servis Berkala", "Ganti Ban", "AC Service", "Rem/Brake", "Kelistrikan", "Body Repair", "Lainnya"] as $l): ?>
                        <option <?= ($data['layanan'] ?? '') == $l ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan</label>
                <textarea name="catatan" class="form-input form-textarea" placeholder="Keluhan atau informasi tambahan..."></textarea><?= $data['catatan'] ?? '' ?></textarea>
            </div>
        </div>

        <?php elseif ($step === 4): ?>
        <div class="form-section">
            <h2 class="form-title">Konfirmasi Reservasi</h2>

            <div class="confirmation-box">
                <div class="confirmation-item">
                    <span>Nama</span>
                    <span><?= $data['nama'] ?? '-' ?></span>
                </div>

                <div class="confirmation-item">
                    <span>Kendaraan</span>
                    <span><?= ($data['jenisKendaraan'] ?? '-') . ' - ' . ($data['merk'] ?? '-') ?></span>
                </div>

                <div class="confirmation-item">
                    <span>Jadwal</span>
                    <span><?= ($data['tanggal'] ?? '-') . ' ' . ($data['jam'] ?? '-') ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-navigation">
            <?php if ($step > 1): ?>
                <a href="?page=buat-reservasi&step=<?= $step - 1 ?>" class="btn-secondary">← Kembali</a>
            <?php else: ?>
                <a href="?page=dashboard" class="btn-secondary">Batal</a>
            <?php endif; ?>

            <button type="submit" class="btn-primary">
                <?= $step === 4 ? '✔ Kirim Reservasi' : 'Lanjut ➜' ?>
            </button>
        </div>

    </form>
</div>