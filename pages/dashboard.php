<?php
if ($role === 'Pelanggan') {
?>

<link rel="stylesheet" href="style/dashboard-content.css">

<div class= "dashboard-container">
    <div class = "welcome-card">
        <div class= "avatar">
            <?= substr($user['nama'], 0, 1) ?>
        </div>

        <h2 class="welcome-title">Selamat Datang, <?= $user['nama'] ?></h2>
        <p class="welcome-text">Silahkan buat reservasi baru atau cek riwayat service kendaraan Anda</p>

        <div class="card-actions">
            <a href="?page=buat-reservasi" class="btn">➕ Buat Reservasi</a>
            <a href="?page=riwayat" class="btn">📋 Riwayat Service</a>
        </div>
    </div>
</div>
<?php } ?>