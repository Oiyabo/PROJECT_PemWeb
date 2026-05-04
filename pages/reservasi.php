<?php

$COLORS = [
    'amber' => '#f59e0b'
];

$mockReservasi = [
    [
        'id' => 'RSV001',
        'nama' => 'Andi',
        'kendaraan' => 'Honda Beat',
        'plat' => 'BE 1234 AA',
        'tanggal' => '2024-05-10',
        'jam' => '10:00',
        'layanan' => 'Servis Ringan',
        'status' => 'Pending'
    ],
    [
        'id' => 'RSV002',
        'nama' => 'Budi',
        'kendaraan' => 'Yamaha NMAX',
        'plat' => 'BE 5678 BB',
        'tanggal' => '2024-05-11',
        'jam' => '13:00',
        'layanan' => 'Ganti Oli',
        'status' => 'Selesai'
    ]
];


?>

<!DOCTYPE html>
<html>
<head>
    <title>Reservasi</title>
    <link rel="stylesheet" href="reservasi.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

<div class="container">

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

        <div class="flex">
            <a href="?page=buat-reservasi" class="btn btn-primary">
                <i data-lucide="plus-circle"></i> Buat Reservasi
            </a>
        </div>
    </div>


    <div class="table-container">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Kendaraan</th>
                        <th>Plat</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($mockReservasi as $r): ?>
                    <tr>

                        <td class="mono" style="color: <?= $COLORS['amber'] ?>">
                            <?= $r['id'] ?>
                        </td>

                        <td class="text-primary">
                            <?= $r['nama'] ?>
                        </td>

                        <td><?= $r['kendaraan'] ?></td>

                        <td>
                            <span class="plat"><?= $r['plat'] ?></span>
                        </td>

                        <td><?= $r['tanggal'] ?></td>
                        <td><?= $r['jam'] ?></td>
                        <td><?= $r['layanan'] ?></td>

                        <td><?= renderBadge($r['status']) ?></td>

                        <td>
                            <div class="action-group">
                                <button class="btn-action"><i data-lucide="eye"></i></button>
                                <button class="btn-action"><i data-lucide="edit-2"></i></button>
                                <button class="btn-action-danger"><i data-lucide="trash-2"></i></button>
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