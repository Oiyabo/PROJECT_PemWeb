<?php
$dataService = [
    ["id"=>"SVC-001","pelanggan"=>"Budi","kendaraan"=>"Toyota","layanan"=>"Ganti Oli","status"=>"Selesai"],
    ["id"=>"SVC-002","pelanggan"=>"Siti","kendaraan"=>"Honda","layanan"=>"Tune Up","status"=>"Proses"]
];
?>

<div class="top-card">
    <h3>Data Service</h3>
    <button class="btn-primary">+ Tambah Service</button>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Kendaraan</th>
                <th>Layanan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($dataService as $d): ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= $d['pelanggan'] ?></td>
                <td><?= $d['kendaraan'] ?></td>
                <td><?= $d['layanan'] ?></td>
                <td>
                    <span class="status"><?= $d['status'] ?></span>
                </td>
                <td>
                    <button class="btn-delete">Hapus</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>