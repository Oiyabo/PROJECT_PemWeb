<?php


$COLORS = [
    'amber' => '#f59e0b',
    'amberDark' => '#b45309'
];


$mockPelanggan = [
    [
        'id' => 'PLG001',
        'nama' => 'Andi Saputra',
        'telepon' => '081234567890',
        'email' => 'andi@gmail.com',
        'kendaraan' => 'Honda Beat',
        'totalService' => 5,
        'bergabung' => '2023-01-10'
    ],
    [
        'id' => 'PLG002',
        'nama' => 'Budi Santoso',
        'telepon' => '082233445566',
        'email' => 'budi@gmail.com',
        'kendaraan' => 'Yamaha NMAX',
        'totalService' => 3,
        'bergabung' => '2023-03-21'
    ],
    [
        'id' => 'PLG003',
        'nama' => 'Citra Dewi',
        'telepon' => '085678912345',
        'email' => 'citra@gmail.com',
        'kendaraan' => 'Suzuki Satria',
        'totalService' => 7,
        'bergabung' => '2022-11-05'
    ]
];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pelanggan</title>

   
    <link rel="stylesheet" href="pelanggan.css">

   
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
            <button class="btn-primary">
                <i data-lucide="plus-circle"></i> Tambah Pelanggan
            </button>
        </div>
    </div>

    
    <div class="table-container">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Kendaraan</th>
                        <th>Total Service</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($mockPelanggan as $p): ?>
                    <tr>

                    
                        <td>
                            <div class="pelanggan-info">
                                <div class="avatar" style="color: <?= $COLORS['amber'] ?>">
                                    <?= substr($p['nama'], 0, 1) ?>
                                </div>

                                <div>
                                    <div class="nama"><?= $p['nama'] ?></div>
                                    <div class="id"><?= $p['id'] ?></div>
                                </div>
                            </div>
                        </td>

                
                        <td>
                            <div class="icon-text">
                                <i data-lucide="phone"></i>
                                <?= $p['telepon'] ?>
                            </div>
                        </td>

                
                        <td>
                            <div class="icon-text">
                                <i data-lucide="mail"></i>
                                <?= $p['email'] ?>
                            </div>
                        </td>

                       
                        <td>
                            <div class="icon-text">
                                <i data-lucide="car"></i>
                                <?= $p['kendaraan'] ?>
                            </div>
                        </td>


                        <td>
                            <span class="badge"
                                  style="background: <?= $COLORS['amber'] ?>20; color: <?= $COLORS['amberDark'] ?>">
                                <?= $p['totalService'] ?>x
                            </span>
                        </td>

                    
                        <td style="color:#52b69a;">
                            <?= $p['bergabung'] ?>
                        </td>

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