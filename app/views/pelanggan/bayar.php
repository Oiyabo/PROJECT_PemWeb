<?php
$userId = $_SESSION['user']['id'];
?>

<div class="page-header">
  <h1>Pembayaran Sisanya</h1>
  <p class="page-subtitle">Bayar sisa pembayaran untuk service yang sudah selesai</p>
</div>

<div>
  <?php if (!empty($reservasi)): ?>
    <div class="reservasi-table-container">
      <table class="reservasi-table">
        <thead>
          <tr>
            <th>No. Reservasi</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th>Kendaraan</th>
            <th>Layanan</th>
            <th>Harga Full</th>
            <th>DP Terbayar</th>
            <th>Sisa Pembayaran</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reservasi as $r): ?>
            <?php
            $hargaFull = $r['total_full'];
            $dpTerbayar = $r['total_dp'];
            $sisaBayar = $hargaFull - $dpTerbayar;
            $idReservasi = $r['id_reservasi'];
            ?>
            <tr>
              <td><strong>#<?= $idReservasi ?></strong></td>
              <td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
              <td><?= substr($r['jam'], 0, 5) ?></td>
              <td><?= htmlspecialchars($r['kendaraan']) ?> (<?= htmlspecialchars($r['plat']) ?>)</td>
              <td class="layanan-cell"><?= htmlspecialchars($r['layanan'] ?? '-') ?></td>
              <td style="text-align: right;">Rp <?= number_format($hargaFull, 0, ',', '.') ?></td>
              <td style="text-align: right;">
                <span style="color: #28a745;">Rp <?= number_format($dpTerbayar, 0, ',', '.') ?></span>
              </td>
              <td style="text-align: right;">
                <span style="color: #dc3545; font-weight: 600;">Rp <?= number_format($sisaBayar, 0, ',', '.') ?></span>
              </td>
              <td style="text-align: center;">
                <button class="btn-small btn-primary" onclick="openPaymentModal(<?= $idReservasi ?>, <?= $sisaBayar ?>, '<?= htmlspecialchars($r['kendaraan']) ?>')">
                  💳 Bayar
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon">📋</div>
      <h2>Tidak ada pembayaran yang tertunda</h2>
      <p>Semua pembayaran Anda sudah lengkap atau belum ada service yang selesai.</p>
      <a href="<?= BASEURL ?>/pelanggan" class="btn-primary" style="margin-top: 16px;">Kembali ke Dashboard</a>
    </div>
  <?php endif; ?>
</div>

<!-- Modal Pembayaran FULL -->
<div id="paymentFullModal" class="popup-overlay" style="display: none;">
  <div class="popup-box" style="width: 90%; max-width: 450px;">
    <h3>Pembayaran Sisa Service</h3>
    
    <div style="margin: 20px 0; padding: 12px; background-color: #f8f9fa; border-radius: 6px;">
      <p style="margin: 8px 0;"><strong>No. Reservasi:</strong> <span id="modalReservID">#-</span></p>
      <p style="margin: 8px 0;"><strong>Kendaraan:</strong> <span id="modalKendaraan">-</span></p>
      <p style="margin: 8px 0;"><strong>Sisa Pembayaran:</strong> <span id="modalFullAmount" style="font-size: 18px; color: #dc3545; font-weight: 700;">Rp 0</span></p>
    </div>

    <div style="margin: 16px 0;">
      <label style="display: block; margin-bottom: 8px; font-weight: 600;">Metode Pembayaran</label>
      <select id="metodePembayaran" class="form-input" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        <option value="">Pilih Metode Pembayaran</option>
        <option value="transfer_bank">Transfer Bank</option>
        <option value="e_wallet">E-Wallet</option>
        <option value="cash">Tunai</option>
      </select>
    </div>

    <div class="popup-actions">
      <button class="btn-secondary" onclick="closePaymentModal()">Batal</button>
      <button class="btn-primary" onclick="submitPaymentFull()">Lanjut Pembayaran</button>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Pembayaran -->
<div id="confirmPaymentModal" class="popup-overlay" style="display: none;">
  <div class="popup-box" style="width: 90%; max-width: 450px;">
    <h3>Konfirmasi Pembayaran</h3>
    
    <div style="margin: 20px 0; padding: 12px; background-color: #d4edda; border-radius: 6px; text-align: center;">
      <p style="margin: 0; color: #155724; font-size: 18px; font-weight: 700;">✓ Pembayaran Berhasil!</p>
    </div>

    <div style="margin: 16px 0;">
      <p><strong>Nominal:</strong> <span id="confirmAmount">Rp 0</span></p>
      <p><strong>Metode:</strong> <span id="confirmMetode">-</span></p>
      <p><strong>Status:</strong> <span style="color: #28a745; font-weight: 600;">Selesai</span></p>
    </div>

    <div style="padding: 12px; background-color: #e7f3ff; border-radius: 6px; border-left: 4px solid #2196F3;">
      <p style="margin: 0; color: #1565c0; font-size: 14px;">
        Terima kasih telah melakukan pembayaran. Administrasi akan memverifikasi pembayaran Anda.
      </p>
    </div>

    <div class="popup-actions">
      <button class="btn-primary" onclick="closeConfirmAndRefresh()">Selesai</button>
    </div>
  </div>
</div>

<style>
.reservasi-table-container {
  overflow-x: auto;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.reservasi-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.reservasi-table thead {
  background: linear-gradient(135deg, #38a3a5 0%, #2d8a8c 100%);
  color: white;
}

.reservasi-table th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
}

.reservasi-table tbody tr {
  border-bottom: 1px solid #e2e8f0;
  transition: background-color 0.2s;
}

.reservasi-table tbody tr:hover {
  background-color: #f8f9fa;
}

.reservasi-table td {
  padding: 12px;
}

.layanan-cell {
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: normal;
  line-height: 1.4;
}

.btn-small {
  padding: 6px 12px;
  font-size: 12px;
  white-space: nowrap;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.empty-state h2 {
  color: #333;
  margin-bottom: 8px;
}

.empty-state p {
  color: #666;
  margin-bottom: 0;
}

@media (max-width: 768px) {
  .reservasi-table {
    font-size: 12px;
  }

  .reservasi-table th,
  .reservasi-table td {
    padding: 8px;
  }

  .layanan-cell {
    max-width: 150px;
  }
}
</style>

<script>
let currentReservID = null;
let currentSisaBayar = null;

function openPaymentModal(reservID, sisaBayar, kendaraan) {
  currentReservID = reservID;
  currentSisaBayar = sisaBayar;
  
  document.getElementById('modalReservID').textContent = '#' + reservID;
  document.getElementById('modalKendaraan').textContent = kendaraan;
  document.getElementById('modalFullAmount').textContent = 'Rp ' + formatNumber(sisaBayar);
  document.getElementById('metodePembayaran').value = '';
  
  document.getElementById('paymentFullModal').style.display = 'flex';
}

function closePaymentModal() {
  document.getElementById('paymentFullModal').style.display = 'none';
}

function submitPaymentFull() {
  const metode = document.getElementById('metodePembayaran').value;
  
  if (!metode) {
    alert('Pilih metode pembayaran terlebih dahulu!');
    return;
  }
  
  // Submit pembayaran
  const formData = new FormData();
  formData.append('id_reservasi', currentReservID);
  formData.append('tipe_pembayaran', 'FULL');
  formData.append('nominal', currentSisaBayar);
  formData.append('metode_pembayaran', metode);
  
  fetch('<?= BASEURL ?>/pelanggan/prosesPembayaran', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      closePaymentModal();
      
      document.getElementById('confirmAmount').textContent = 'Rp ' + formatNumber(currentSisaBayar);
      document.getElementById('confirmMetode').textContent = metode.replace('_', ' ').toUpperCase();
      document.getElementById('confirmPaymentModal').style.display = 'flex';
    } else {
      alert('Terjadi kesalahan: ' + (data.message || 'Gagal memproses pembayaran'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat memproses pembayaran');
  });
}

function closeConfirmAndRefresh() {
  document.getElementById('confirmPaymentModal').style.display = 'none';
  location.reload();
}

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num);
}
</script>
