<?php
if (!function_exists('badgeStatus')) {
    function badgeStatus(string $status): string {
        $map = [
            'Menunggu'   => ['bg' => '#fef3c7', 'color' => '#d97706'],
            'Konfirmasi' => ['bg' => '#e0f2fe', 'color' => '#0284c7'],
            'Proses'     => ['bg' => '#ccfbf1', 'color' => '#0f766e'],
            'Selesai'    => ['bg' => '#dcfce3', 'color' => '#166534'],
            'Batal'      => ['bg' => '#fee2e2', 'color' => '#b91c1c'],
        ];
        $style = $map[$status] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
        return '<span style="background-color: ' . $style['bg'] . '; color: ' . $style['color'] . '; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">' . htmlspecialchars($status) . '</span>';
    }
}
$reservasiList = $data['reservasi'] ?? [];
?>

<style>
    .reservasi-container {
        padding: 20px;
        background-color: transparent;
    }
    .search-wrapper {
        margin-bottom: 24px;
    }
    .search-box {
        display: inline-flex;
        align-items: center;
        border: 1px solid #38a3a5;
        border-radius: 8px;
        padding: 8px 16px;
        background: #fff;
        width: 300px;
    }
    .search-box input {
        border: none;
        outline: none;
        margin-left: 10px;
        width: 100%;
        color: #1e6091;
        font-size: 13px;
    }
    .search-box input::placeholder {
        color: #94a3b8;
    }
    
    .table-wrapper {
        background: #FFFFFF;
        border: 1px solid #38a3a5;
        border-radius: 12px;
        overflow: hidden;
        overflow-x: auto;
    }
    .table-reservasi {
        width: 100%;
        border-collapse: collapse;
        background-color: transparent;
    }
    .table-reservasi th {
        text-align: left;
        padding: 12px 15px;
        color: #38a3a5;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .table-reservasi td {
        padding: 15px;
        font-size: 13px;
        color: #1e6091;
        border-bottom: 1px solid #f1f5f9;
    }
    .id-text {
        color: #d97706;
        font-weight: 600;
        font-family: monospace;
        font-size: 13px;
    }
    .text-primary {
        font-weight: 600;
        color: #184e77;
    }
    .plat {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .form-input-sm {
        padding: 6px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        font-size: 12px;
        color: #1e6091;
        outline: none;
        background: #fff;
    }
    .btn-action {
        padding: 6px 8px;
        border: 1px solid #38a3a5;
        border-radius: 4px;
        background: #e6fffa;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="reservasi-container">
    <div class="search-wrapper">
        <div class="search-box">
            <i data-lucide="search" color="#38a3a5" width="18" height="18"></i>
            <input id="searchInput" type="text" placeholder="Cari nama pelanggan...">
        </div>
    </div>

    <div class="table-wrapper">
        <table class="table-reservasi" id="reservasiTable">
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
                <?php if (empty($reservasiList)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; color: #94a3b8; padding: 30px;">
                            Belum ada data reservasi.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservasiList as $r): ?>
                    <tr>
                        <td class="id-text">#<?= htmlspecialchars($r['id']) ?></td>
                        <td class="text-primary"><?= htmlspecialchars($r['nama'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($r['kendaraan'] ?? '') ?></td>
                        <td><span class="plat"><?= htmlspecialchars($r['plat'] ?? '') ?></span></td>
                        <td><?= htmlspecialchars($r['tanggal'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['jam'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['layanan'] ?? '') ?></td>
                        <td><?= badgeStatus($r['status'] ?? '') ?></td>
                        <td>
                            <form action="<?= BASEURL ?>/admin/updatestatus/<?= $r['id'] ?>" method="POST" style="display:flex; gap:6px; align-items:center; margin: 0;">
                                <select name="status" class="form-input-sm">
                                    <?php foreach (['Menunggu','Konfirmasi','Proses','Selesai','Batal'] as $s): ?>
                                        <option value="<?= $s ?>" <?= ($r['status'] ?? '') === $s ? 'selected' : '' ?>>
                                            <?= $s ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-action" title="Simpan">
                                    <i data-lucide="save" width="14" height="14" color="#0f766e"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('input', function() {
    const keyword = this.value.toLowerCase();
    document.querySelectorAll('#reservasiTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(keyword) ? '' : 'none';
    });
});
</script>
