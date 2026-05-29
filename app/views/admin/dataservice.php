<?php
$title = 'Data Service';
?>

<div class="dataservice-container">
  <div class="page-header">
    <h2>Data Service</h2>
    <a href="<?= BASEURL; ?>/admin" class="btn btn-secondary">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>

  <div class="card">
    <div class="card-header">
      <h3>Daftar Service Kendaraan</h3>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Pelanggan</th>
            <th>Kendaraan</th>
            <th>Plat Nomor</th>
            <th>Layanan</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th>Catatan</th>
          </tr>
        </thead>

        <tbody>
          <?php if (!empty($dataService)): ?>
            <?php foreach ($dataService as $index => $service): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($service['nama'] ?? '') ?></td>
                <td><?= htmlspecialchars($service['kendaraan'] ?? '') ?></td>
                <td><?= htmlspecialchars($service['plat'] ?? '') ?></td>
                <td><?= htmlspecialchars($service['layanan'] ?? '-') ?></td>
                <td><?= htmlspecialchars($service['tanggal'] ?? '') ?></td>
                <td><?= htmlspecialchars($service['jam'] ?? '') ?></td>
                <td><?= htmlspecialchars($service['catatan'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center">
                <?php if (!empty($keyword)): ?>
                  Data service dengan kata kunci "<?= htmlspecialchars($keyword) ?>" tidak ditemukan.
                <?php else: ?>
                  Belum ada data service.
                <?php endif; ?>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>