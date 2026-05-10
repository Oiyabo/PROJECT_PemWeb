<style>
    .pelanggan-container {
        padding: 20px;
        background-color: transparent;
    }
    .search-wrapper {
        margin-bottom: 24px;
    }
    .search-box {
        display: inline-flex;
        align-items: center;
        border: 1px solid #38a3a5; /* Warna hijau tosca border */
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
        color: #1e6091; /* Warna teks biru gelap */
        font-size: 13px;
    }
    .search-box input::placeholder {
        color: #94a3b8;
    }
    
    .table-pelanggan {
        width: 100%;
        border-collapse: collapse;
        background-color: transparent;
    }
    .table-pelanggan th {
        text-align: left;
        padding: 12px 15px;
        color: #38a3a5; /* Warna teks header hijau tosca */
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 2px solid #38a3a5; /* Garis bawah header */
    }
    .table-pelanggan td {
        padding: 15px;
        font-size: 14px;
        color: #1e6091; /* Warna teks biru gelap */
        border-bottom: 1px solid #e2e8f0;
    }
    .id-text {
        color: #38a3a5;
        font-weight: 600;
        font-size: 13px;
    }
    .avatar-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #b5e48c; /* Hijau muda */
        display: flex;
        align-items: center;
        justify-content: center;
        color: #38a3a5;
        font-weight: 700;
        font-size: 14px;
    }
    .role-badge {
        background-color: #1e6091; /* Biru gelap */
        color: #ffffff;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
</style>

<div class="pelanggan-container">
    
    <div class="search-wrapper">
        <div class="search-box">
            <i data-lucide="search" color="#38a3a5" width="18" height="18"></i>
            <input type="text" placeholder="Cari nama atau email">
        </div>
    </div>

    <table class="table-pelanggan">
        <thead>
            <tr>
                <th>ID</th>
                <th>NAMA</th>
                <th>EMAIL</th>
                <th>ROLE</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data['pelanggan'])): ?>
                <?php foreach ($data['pelanggan'] as $index => $p): ?>
                    <tr>
                        <td class="id-text">
                            #<?= htmlspecialchars($p['id'] ?? ($index + 1)) ?>
                        </td>
                        <td>
                            <div class="avatar-wrapper">
                                <div class="avatar-circle">
                                    <?php 
                                        // Mengambil huruf pertama dari nama untuk avatar
                                        $nama = $p['nama'] ?? '?';
                                        echo strtoupper(substr($nama, 0, 1)); 
                                    ?>
                                </div>
                                <span><?= htmlspecialchars($p['nama'] ?? 'Tidak ada nama') ?></span>
                            </div>
                        </td>
                        <td>
                            <?= htmlspecialchars($p['email'] ?? '-') ?>
                        </td>
                        <td>
                            <span class="role-badge">
                                <?= htmlspecialchars($p['role'] ?? 'Pelanggan') ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td class="id-text">#2</td>
                    <td>
                        <div class="avatar-wrapper">
                            <div class="avatar-circle">B</div>
                            <span>Budi Santoso</span>
                        </div>
                    </td>
                    <td>pelanggan@email.com</td>
                    <td>
                        <span class="role-badge">Pelanggan</span>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>