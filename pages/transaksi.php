<?php

$COLORS = [
    'green' => '#16a34a',
    'blue' => '#2563eb',
    'amber' => '#f59e0b'
];

$mockTransaksi = [
    [
        'id' => 'TRX001',
        'pelanggan' => 'Andi',
        'layanan' => 'Ganti Oli',
        'tanggal' => '2024-05-01',
        'metode' => 'Cash',
        'total' => 150000,
        'status' => 'Lunas'
    ],
    [
        'id' => 'TRX002',
        'pelanggan' => 'Budi',
        'layanan' => 'Servis Mesin',
        'tanggal' => '2024-05-02',
        'metode' => 'Transfer',
        'total' => 300000,
        'status' => 'Pending'
    ]
];

$total = array_reduce($mockTransaksi, fn($c,$i)=>$c+$i['total'], 0);
$lunasCount = count(array_filter($mockTransaksi, fn($t)=>$t['status']=='Lunas'));
$pendingCount = count(array_filter($mockTransaksi, fn($t)=>$t['status']=='Pending'));

?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaksi</title>
    <link rel="stylesheet" href="transaksi.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

<div class="container">

    <div class="card-grid">
        <div class="card">
            <div class="card-title">Total Pendapatan</div>
            <div class="card-value" style="color: <?= $COLORS['green'] ?>">
                Rp <?= number_format($total, 0, ',', '.') ?>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Transaksi Lunas</div>
            <div class="card-value" style="color: <?= $COLORS['blue'] ?>">
                <?= $lunasCount ?>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Pending</div>
            <div class="card-value" style="color: <?= $COLORS['amber'] ?>">
                <?= $pendingCount ?>
            </div>
        </div>
    </div>

    <div class="flex-between">
        <div class="flex">
            <button class="btn">
                <i data-lucide="filter"></i> Filter
            </button>

            <div class="input-search">
                <i data-lucide="search"></i>
                <input placeholder="Cari..." />
            </div>
        </div>

        <button class="btn btn-primary">
            <i data-lucide="plus-circle"></i> Tambah Transaksi
        </button>
    </div>


    <div class="table-container">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Tanggal</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($mockTransaksi as $t): ?>
                    <tr>
                        <td class="mono"><?= $t['id'] ?></td>
                        <td class="text-primary"><?= $t['pelanggan'] ?></td>
                        <td class="text-secondary"><?= $t['layanan'] ?></td>
                        <td class="text-secondary"><?= $t['tanggal'] ?></td>

                        <td>
                            <?php
                            $bg = $t['metode']=='Transfer' ? '#EFF6FF' : '#F0FDF4';
                            $color = $t['metode']=='Transfer' ? '#1D4ED8' : '#15803D';
                            ?>
                            <span class="badge" style="background:<?= $bg ?>;color:<?= $color ?>">
                                <?= $t['metode'] ?>
                            </span>
                        </td>

                        <td class="text-bold">
                            Rp <?= number_format($t['total'],0,',','.') ?>
                        </td>

                        <td><?= renderBadge($t['status']) ?></td>

                        <td>
                            <div class="action-group">
                                <button class="btn-action"><i data-lucide="eye"></i></button>
                                <button class="btn-action"><i data-lucide="edit-2"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

<script>
lucide.createIcons();
</script>

</body>
</html>