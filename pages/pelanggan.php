<?php
$pelanggan = [
    ["id"=>"PLG-001","nama"=>"Budi Santoso","telepon"=>"0812","email"=>"budi@email.com","kendaraan"=>"Toyota"],
    ["id"=>"PLG-002","nama"=>"Siti Rahayu","telepon"=>"0813","email"=>"siti@email.com","kendaraan"=>"Honda"]
];
?>

<link rel="stylesheet" href="style/pelanggan.css">

<div class="container">
    <h2>Data Pelanggan</h2>

    <button class="btn-primary">Tambah Pelanggan</button>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Email</th>
                <th>Kendaraan</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($pelanggan as $p): ?>
            <tr>
                <td><?= $p['nama'] ?></td>
                <td><?= $p['telepon'] ?></td>
                <td><?= $p['email'] ?></td>
                <td><?= $p['kendaraan'] ?></td>
                <td>
                    <button class="btn-delete">Hapus</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>