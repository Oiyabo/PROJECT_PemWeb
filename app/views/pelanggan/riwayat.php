<?php
if (!function_exists('badgeStatus')) {
    function badgeStatus(string $status): string
    {
        $map = [
            'Menunggu'   => 'badge-warning',
            'Konfirmasi' => 'badge-info',
            'Proses'     => 'badge-primary',
            'Selesai'    => 'badge-success',
            'Batal'      => 'badge-danger',
        ];

        $class = $map[$status] ?? 'badge-warning';

        return '<span class="badge ' . $class . '">'
            . htmlspecialchars($status) .
            '</span>';
    }
}

$statusTabs = [
    'Semua',
    'Menunggu',
    'Konfirmasi',
    'Proses',
    'Selesai',
    'Batal'
];

$activeTab = $_GET['status'] ?? 'Semua';
$keyword = trim($_GET['q'] ?? '');

if (!in_array($activeTab, $statusTabs, true)) {
    $activeTab = 'Semua';
}

$semuaReservasi = $reservasi ?? [];

if ($keyword !== '') {
    $semuaReservasi = array_values(array_filter($semuaReservasi, function ($r) use ($keyword) {
        $text = strtolower(
            ($r['id_reservasi'] ?? '') . ' ' .
            ($r['nama'] ?? '') . ' ' .
            ($r['kendaraan'] ?? '') . ' ' .
            ($r['plat'] ?? '') . ' ' .
            ($r['layanan'] ?? '') . ' ' .
            ($r['tanggal'] ?? '') . ' ' .
            ($r['jam'] ?? '') . ' ' .
            ($r['catatan'] ?? '') . ' ' .
            ($r['status'] ?? '')
        );

        return str_contains($text, strtolower($keyword));
    }));
}

$jumlahStatus = [];

foreach ($statusTabs as $status) {
    if ($status === 'Semua') {
        $jumlahStatus[$status] = count($reservasi ?? []);
    } else {
        $jumlahStatus[$status] = count(array_filter($reservasi ?? [], fn($r) => ($r['status'] ?? '') === $status));
    }
}

$reservasiTampil = $activeTab === 'Semua'
    ? $semuaReservasi
    : array_values(array_filter($semuaReservasi, fn($r) => ($r['status'] ?? '') === $activeTab));
?>

<div class="riwayat-page">
    <div class="riwayat-header">
        <a href="<?= BASEURL ?>/pelanggan/buatreservasi" class="btn btn-primary">
            <i data-lucide="plus-circle" width="14" height="14"></i>
            Buat Reservasi Baru
        </a>
    </div>

    <?php if (empty($reservasi)): ?>
        <div class="empty-state">
            <i data-lucide="clipboard-list" width="48" height="48" class="empty-icon"></i>

            <p class="empty-text">
                Anda belum memiliki riwayat reservasi.
            </p>

            <a href="<?= BASEURL ?>/pelanggan/buatreservasi" class="btn empty-btn">
                Buat Reservasi Pertama Anda
            </a>
        </div>
    <?php else: ?>

        <div class="status-tabs">
            <?php foreach ($statusTabs as $status): ?>
                <a
                    href="<?= BASEURL ?>/pelanggan/riwayat?status=<?= urlencode($status) ?><?= $keyword !== '' ? '&q=' . urlencode($keyword) : '' ?>"
                    class="status-tab <?= $activeTab === $status ? 'active' : '' ?>"
                >
                    <?= htmlspecialchars($status) ?>
                    <span><?= $jumlahStatus[$status] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <form class="search-wrapper" method="GET" action="<?= BASEURL ?>/pelanggan/riwayat">
            <input type="hidden" name="status" value="<?= htmlspecialchars($activeTab) ?>">

            <div class="search-box">
                <i data-lucide="search" color="#38a3a5" width="18" height="18"></i>
                <input
                    type="text"
                    name="q"
                    placeholder="Cari pelanggan, kendaraan, plat, layanan..."
                    value="<?= htmlspecialchars($keyword) ?>"
                >
            </div>

            <?php if (!empty($keyword)): ?>
                <a href="<?= BASEURL ?>/pelanggan/riwayat?status=<?= urlencode($activeTab) ?>" class="btn-reset-search">
                    Reset
                </a>
            <?php endif; ?>
        </form>

        <?php if (empty($reservasiTampil)): ?>
            <div class="empty-state">
                <i data-lucide="clipboard-list" width="48" height="48" class="empty-icon"></i>

                <p class="empty-text">
                    <?php if (!empty($keyword)): ?>
                        Data riwayat dengan kata kunci "<?= htmlspecialchars($keyword) ?>" tidak ditemukan.
                    <?php else: ?>
                        Tidak ada riwayat dengan status <?= htmlspecialchars($activeTab) ?>.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="table-riwayat-pelanggan">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pelanggan</th>
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
                            <?php foreach ($reservasiTampil as $r): ?>
                                <tr>
                                    <td class="mono riwayat-id">
                                        #<?= htmlspecialchars($r['id_reservasi'] ?? '') ?>
                                    </td>

                                    <td class="text-primary">
                                        <?= htmlspecialchars($r['nama'] ?? ($user['nama'] ?? '-')) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($r['kendaraan'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <span class="plat">
                                            <?= htmlspecialchars($r['plat'] ?? '-') ?>
                                        </span>
                                    </td>

                                    <td class="layanan-cell">
                                        <?php $layananText = $r['layanan'] ?? '-'; ?>

                                        <span class="truncate-layanan" title="<?= htmlspecialchars($layananText) ?>">
                                            <?= htmlspecialchars($layananText) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($r['tanggal'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($r['jam'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(($r['catatan'] ?? '') !== '' ? $r['catatan'] : '-') ?>
                                    </td>

                                    <td>
                                        <?= badgeStatus($r['status'] ?? 'Menunggu') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
