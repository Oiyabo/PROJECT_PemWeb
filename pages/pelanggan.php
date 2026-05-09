<?php
<link rel="stylesheet" href="public/assets/css/pelanggan.css">

if (!isset($pelanggan) || empty($pelanggan)) {
    $pelanggan = [
        [
            'id' => 1,
            'nama' => 'Budi Santoso',
            'email' => 'budi@gmail.com',
            'role' => 'Pelanggan'
        ],
        [
            'id' => 2,
            'nama' => 'Siti Rahayu',
            'email' => 'siti@gmail.com',
            'role' => 'Pelanggan'
        ],
        [
            'id' => 3,
            'nama' => 'Agus Pratama',
            'email' => 'agus@gmail.com',
            'role' => 'Pelanggan'
        ]
    ];
}
?>
<div class="container">

  <div class="flex-between" style="margin-bottom: 1rem;">
    <div class="input-search">
      <i data-lucide="search"></i>
      <input id="searchPelanggan" placeholder="Cari nama atau email..." />
    </div>
  </div>

  <div class="table-container">
    <div class="table-wrapper">
      <table id="pelangganTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pelanggan)): ?>
            <tr>
              <td colspan="4" style="text-align:center; color:var(--text-light); padding:2rem;">
                Belum ada data pelanggan.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($pelanggan as $p): ?>
            <tr>
              <td class="mono" style="color:var(--primary);">#<?= $p['id'] ?></td>
              <td>
                <div style="display:flex; align-items:center; gap:8px;">
                  <div class="admin-reservation-avatar" style="width:32px;height:32px;font-size:14px;">
                    <?= htmlspecialchars(substr($p['nama'], 0, 1)) ?>
                  </div>
                  <?= htmlspecialchars($p['nama']) ?>
                </div>
              </td>
              <td><?= htmlspecialchars($p['email']) ?></td>
              <td><span class="badge badge-info"><?= htmlspecialchars($p['role']) ?></span></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
document.getElementById('searchPelanggan').addEventListener('input', function() {
  const keyword = this.value.toLowerCase();
  document.querySelectorAll('#pelangganTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(keyword) ? '' : 'none';
  });
});
</script>