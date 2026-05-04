<?php 
$transaksi = [
    ["id"=>"TRX-001","pelanggan"=>"Budi","total"=>"150000","status"=>"Lunas"],
    ["id"=>"TRX-002","pelanggan"=>"Siti","total"=>"200000","status"=>"Belum"]
]; 
?>

<link rel="stylesheet" href="../style/transaksi.css">

<div class="container-transaksi">

    <div class="header-transaksi">
        <h3>Data Transaksi</h3>
        <button class="btn-transaksi">+ Tambah</button>
    </div>

    <div class="box-transaksi">
        <table class="table-transaksi">
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

                    <td>
                        <span class="status-transaksi <?= strtolower($t['status']) ?>">
                            <?= $t['status'] ?>
                        </span>
                    </td>

                    <td>
                        <button class="btn-delete-transaksi">x</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
