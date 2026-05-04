<?php
$transaksi = [
    ["id"=>"TRX-001","pelanggan"=>"Budi","total"=>"150000","status"=>"Lunas"],
    ["id"=>"TRX-002","pelanggan"=>"Siti","total"=>"200000","status"=>"Belum"]
];
?>

<div class="top-card">
    <h3>Data Transaksi</h3>
    <button class="btn-primary">+ Tambah Transaksi</button>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($transaksi as $t): ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <td><?= $t['pelanggan'] ?></td>
                <td>Rp <?= $t['total'] ?></td>
                <td><span class="status"><?= $t['status'] ?></span></td>
                <td><button class="btn-delete">Hapus</button></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>