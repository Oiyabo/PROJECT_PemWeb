<?php
$reservasi = [
    ["id"=>"RSV-001","pelanggan"=>"Budi","tanggal"=>"2026-05-01","status"=>"Selesai"],
    ["id"=>"RSV-002","pelanggan"=>"Siti","tanggal"=>"2026-05-02","status"=>"Proses"]
];
?>

<div class="top-card">
    <h3>Data Reservasi</h3>
    <button class="btn-primary">+ Tambah Reservasi</button>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($reservasi as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= $r['pelanggan'] ?></td>
                <td><?= $r['tanggal'] ?></td>
                <td><span class="status"><?= $r['status'] ?></span></td>
                <td><button class="btn-delete">Hapus</button></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>