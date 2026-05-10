<style>
    .transaksi-container {
        padding: 20px;
        background-color: transparent;
    }
    
    .table-transaksi-wrapper {
        background: #fff;
        border: 1px solid #38a3a5; /* Warna border hijau tosca */
        border-radius: 12px;
        overflow: hidden;
        overflow-x: auto;
    }
    
    .table-transaksi {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-transaksi th {
        text-align: left;
        padding: 16px 20px;
        color: #38a3a5;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .table-transaksi td {
        padding: 16px 20px;
        font-size: 14px;
        color: #1e6091; /* Warna teks biru gelap */
        border-bottom: 1px solid #e2e8f0;
    }
    
    .table-transaksi tr:last-child td {
        border-bottom: none; /* Hilangkan garis di baris paling bawah */
    }
    
    .id-text {
        color: #38a3a5;
        font-weight: 600;
        font-size: 13px;
    }
    
    .badge-plat {
        background: #b5e48c; /* Hijau muda */
        color: #ffffff;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 6px;
        letter-spacing: 0.5px;
    }
    
    .badge-selesai {
        background-color: #dcfce3;
        color: #166534;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #bbf7d0;
    }
</style>

<div class="transaksi-container">
    <div class="table-transaksi-wrapper">
        <table class="table-transaksi">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>PELANGGAN</th>
                    <th>LAYANAN</th>
                    <th>KENDARAAN</th>
                    <th>TANGGAL</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['transaksi'])): ?>
                    <?php foreach ($data['transaksi'] as $index => $t): ?>
                        <tr>
                            <td class="id-text">
                                #<?= htmlspecialchars($t['id'] ?? ($index + 1)) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($t['nama'] ?? 'Tidak diketahui') ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($t['layanan'] ?? '-') ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($t['kendaraan'] ?? '-') ?> 
                                <?php if (!empty($t['plat'])): ?>
                                    <span class="badge-plat"><?= htmlspecialchars($t['plat']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($t['tanggal'] ?? '-') ?>
                            </td>
                            <td>
                                <span class="badge-selesai">
                                    <?= htmlspecialchars($t['status'] ?? 'Selesai') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td class="id-text">#1</td>
                        <td>Budi Santoso</td>
                        <td>Ganti Oli</td>
                        <td>
                            Honda Beat <span class="badge-plat">BE 1234 AB</span>
                        </td>
                        <td>2026-05-10</td>
                        <td>
                            <span class="badge-selesai">Selesai</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
