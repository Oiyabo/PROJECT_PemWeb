<div style="padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <button style="display: flex; align-items: center; gap: 6px; padding: 7px 12px; border: 1px solid #34a0a4; border-radius: 8px; background: #FFFFFF; font-size: 12px; color: #1e6091; cursor: pointer;">
                <i data-lucide="filter" width="13" height="13"></i> Filter
            </button>
            <div style="display: flex; align-items: center; gap: 6px; padding: 7px 12px; border: 1px solid #34a0a4; border-radius: 8px; background: #FFFFFF; font-size: 12px; color: #1e6091;">
                <i data-lucide="search" width="13" height="13"></i>
                <input placeholder="Cari..." style="border: none; outline: none; font-size: 12px; color: #1e6091; width: 120px; background: transparent;" />
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-primary">
                <i data-lucide="plus-circle" width="15" height="15"></i> Tambah Data Service
            </button>
        </div>
    </div>
    
    <div style="background: #FFFFFF; border: 1px solid #34a0a4; border-radius: 12px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Pelanggan</th><th>Kendaraan</th><th>Mekanik</th>
                        <th>Layanan</th><th>Mulai</th><th>Selesai</th><th>Status</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mockDataService as $s): ?>
                    <tr>
                        <td><span style="font-family: monospace; font-size: 12px; color: <?= $COLORS['amber'] ?>; font-weight: 600;"><?= $s['id'] ?></span></td>
                        <td><div style="font-weight: 600; color: #184e77;"><?= $s['pelanggan'] ?></div></td>
                        <td><?= $s['kendaraan'] ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="width: 24px; height: 24px; border-radius: 50%; background: #b5e48c; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: <?= $COLORS['amber'] ?>;">
                                    <?php
                                        $m_parts = explode(' ', $s['mekanik']);
                                        echo substr(end($m_parts), 0, 1);
                                    ?>
                                </div>
                                <?= $s['mekanik'] ?>
                            </div>
                        </td>
                        <td><?= $s['layanan'] ?></td>
                        <td style="font-size: 12px;"><?= $s['mulai'] ?></td>
                        <td style="font-size: 12px;"><?= $s['selesai'] ?></td>
                        <td><?= renderBadge($s['status']) ?></td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <button class="btn-action"><i data-lucide="eye" width="13" height="13"></i></button>
                                <button class="btn-action"><i data-lucide="edit-2" width="13" height="13"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="padding: 12px 16px; border-top: 1px solid #eee; background: #f6f5f7;">
            <p style="font-size: 12px; color: #52b69a; margin: 0;">Menampilkan <?= count($mockDataService) ?> data service</p>
        </div>
    </div>
</div>


