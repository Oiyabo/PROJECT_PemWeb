<?php
if (!function_exists('formatRupiah')) {
    function formatRupiah(int|float $amount): string
    {
        return 'Rp ' . number_format((int) $amount, 0, ',', '.');
    }
}
?>

<div class="riwayat-pembayaran-page">
    <?php if (empty($transaksi)): ?>
        <div class="empty-state">
            <i data-lucide="receipt" width="48" height="48" class="empty-icon"></i>
            <p class="empty-text">Belum ada transaksi yang lunas (DP dan pelunasan selesai).</p>
            <a href="<?= BASEURL ?>/pelanggan/bayar" class="btn empty-btn">Lihat Pembayaran Tertunda</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <div class="table-wrapper">
                <table class="riwayat-pembayaran-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kendaraan</th>
                            <th>Plat</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th class="text-right">Harga DP</th>
                            <th class="text-right">Harga Full</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksi as $t): ?>
                            <?php
                            $id = (int) $t['id_reservasi'];
                            $jam = substr((string) $t['jam'], 0, 5);
                            ?>
                            <tr>
                                <td class="mono riwayat-id">#<?= $id ?></td>
                                <td><?= htmlspecialchars($t['kendaraan']) ?></td>
                                <td><span class="plat"><?= htmlspecialchars($t['plat']) ?></span></td>
                                <td><?= date('d/m/Y', strtotime($t['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($jam) ?></td>
                                <td class="text-right text-primary"><?= formatRupiah($t['harga_dp']) ?></td>
                                <td class="text-right text-primary"><?= formatRupiah($t['harga_full']) ?></td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-detail-pembayaran"
                                        data-id="<?= $id ?>"
                                        title="Lihat detail">
                                        <i data-lucide="eye" width="14" height="14"></i>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="detailPembayaranModal" class="popup-overlay" style="display: none;" aria-hidden="true">
    <div class="popup-box detail-pembayaran-box" role="dialog" aria-labelledby="detailPembayaranTitle">
        <div class="detail-pembayaran-header">
            <h3 id="detailPembayaranTitle">Detail Pembayaran</h3>
            <button type="button" class="btn-close-modal" id="btnCloseDetailPembayaran" aria-label="Tutup">
                <i data-lucide="x" width="18" height="18"></i>
            </button>
        </div>
        <div id="detailPembayaranLoading" class="detail-pembayaran-loading">Memuat data...</div>
        <div id="detailPembayaranContent" class="detail-pembayaran-content" style="display: none;"></div>
        <div id="strukPembayaranContent" class="struk-pembayaran-content" style="display: none;"></div>
        <div class="popup-actions detail-popup-actions">
            <button type="button" class="btn-struk-pembayaran" id="btnStrukPembayaran" style="display: none;">
                <i data-lucide="receipt" width="14" height="14"></i>
                Struk
            </button>
            <button type="button" class="btn-secondary" id="btnKembaliDetailPembayaran" style="display: none;">
                Kembali
            </button>
            <button type="button" class="btn-secondary" id="btnTutupDetailPembayaran">Tutup</button>
        </div>
    </div>
</div>

<script>
    window.APP_BASEURL = <?= json_encode(BASEURL) ?>;
</script>
<script src="<?= BASEURL ?>/assets/js/riwayat-pembayaran.js"></script>
