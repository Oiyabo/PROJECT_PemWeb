<?php
if (!function_exists('badgeStatus')) {
    function badgeStatus(string $status): string {
        $map = [
            'Menunggu'   => 'badge-warning',
            'Konfirmasi' => 'badge-info',
            'Proses'     => 'badge-primary',
            'Selesai'    => 'badge-success',
            'Batal'      => 'badge-danger',
        ];

        $class = $map[$status] ?? 'badge-warning';

        return '<span class="badge ' . $class . '">' . htmlspecialchars($status) . '</span>';
    }
}
?>

<link rel="stylesheet" href="<?= BASEURL ?>/assets/css/riwayat.css">

<div class="container">

    <div style="margin-bottom:1rem; text-align:right;">
        <a href="<?= BASEURL ?>/pelanggan/buatreservasi" class="btn btn-primary">
            <i data-lucide="plus-circle" width="14" height="14"></i>
            Buat Reservasi Baru
        </a>
    </div>

    <?php if (empty($reservasi)): ?>
        <div style="text-align:center; padding:3rem; color:var(--text-light);">
            <i data-lucide="clipboard-list"
               width="48"
               height="48"
               style="opacity:0.3;">
            </i>

            <p style="margin-top:1rem;">
                Anda belum memiliki riwayat reservasi.
            </p>

            <a href="<?= BASEURL ?>/pelanggan/buatreservasi"
               class="btn"
               style="margin-top:1rem;">
                Buat Reservasi Pertama Anda
            </a>
        </div>

    <?php else: ?>
        <div class="table-container">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kendaraan</th>
                            <th>Plat</th>
                            <th>Layanan</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Catatan</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($reservasi as $r): ?>
                        <tr>
                            <td class="mono" style="color:var(--primary);">
                                #<?= $r['id'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($r['kendaraan']) ?>
                            </td>

                            <td>
                                <span class="plat">
                                    <?= htmlspecialchars($r['plat']) ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars($r['layanan']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($r['tanggal']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($r['jam']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($r['catatan'] ?: '-') ?>
                            </td>

                            <td>
                                <?= badgeStatus($r['status']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>