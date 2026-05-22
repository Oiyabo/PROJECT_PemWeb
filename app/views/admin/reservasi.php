<?php
/**
 * Admin Reservasi View
 * Menampilkan daftar reservasi dengan status dan aksi
 */

if (!function_exists('badgeStatus')) {
    /**
     * Generate status badge dengan warna sesuai status
     * 
     * @param string $status
     * @return string HTML badge
     */
    function badgeStatus(string $status): string {
        $statusMap = [
            'Menunggu'   => 'status-waiting',
            'Konfirmasi' => 'status-confirmed',
            'Proses'     => 'status-waiting',
            'Selesai'    => 'status-done',
            'Batal'      => 'status-danger',
        ];
        
        $class = $statusMap[$status] ?? 'status-waiting';
        return '<span class="status-badge ' . $class . '">' . htmlspecialchars($status) . '</span>';
    }
}

$reservasiList = $data['reservasi'] ?? [];
?>

<div class="reservasi-container">
    <!-- Search Bar -->
    <div class="search-wrapper">
        <div class="input-search">
            <i data-lucide="search" width="16" height="16"></i>
            <input id="searchInput" type="text" placeholder="Cari nama pelanggan...">
        </div>
    </div>

    <!-- Reservasi Table -->
    <div class="table-container">
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
                        <tr class="table-empty">
                            <td colspan="9">
                                <div class="empty-state">
                                    <p class="empty-text">Belum ada data reservasi</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservasiList as $r): ?>
                            <tr>
                                <td class="riwayat-id">#<?= htmlspecialchars($r['id_reservasi']) ?></td>
                                <td class="text-primary"><?= htmlspecialchars($r['nama'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($r['kendaraan'] ?? '') ?></td>
                                <td><span class="plat"><?= htmlspecialchars($r['plat'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($r['tanggal'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['jam'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['layanan'] ?? '') ?></td>
                                <td><?= badgeStatus($r['status'] ?? '') ?></td>
                                <td>
                                    <form action="<?= BASEURL ?>/admin/updatestatus/<?= $r['id_reservasi'] ?>" method="POST" class="status-form">
                                        <select name="status" class="form-input form-input-sm" onchange="this.form.submit()">
                                            <?php foreach (['Menunggu', 'Konfirmasi', 'Proses', 'Selesai', 'Batal'] as $statusOption): ?>
                                                <option value="<?= $statusOption ?>" <?= ($r['status'] ?? '') === $statusOption ? 'selected' : '' ?>>
                                                    <?= $statusOption ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchInput').addEventListener('input', function() {
        const keyword = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#reservasiTable tbody tr');
        
        tableRows.forEach(row => {
            const isMatch = row.textContent.toLowerCase().includes(keyword);
            row.style.display = isMatch ? '' : 'none';
        });
    });
</script>

<style>
    .reservasi-container {
        padding: 0;
    }

    .search-wrapper {
        margin-bottom: 24px;
    }

    .table-reservasi {
        width: 100%;
        border-collapse: collapse;
    }

    .table-reservasi td:first-child {
        width: 80px;
    }

    .table-empty td {
        padding: 0 !important;
        border-bottom: none !important;
    }

    .status-form {
        display: flex;
        gap: 6px;
        align-items: center;
        margin: 0;
    }

    .form-input-sm {
        padding: 6px 8px;
        font-size: 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: var(--bg-surface);
        color: var(--text-dark);
        min-width: 80px;
    }
</style>
