<div class="pelanggan-container">

    <form class="search-wrapper" method="GET" action="<?= BASEURL ?>/admin/pelanggan">
        <div class="search-box">
            <i data-lucide="search" color="#38a3a5" width="18" height="18"></i>
            <input
                type="text"
                name="q"
                placeholder="Cari nama, email, atau role"
                value="<?= htmlspecialchars($keyword ?? '') ?>"
            >
        </div>

        <?php if (!empty($keyword)): ?>
            <a href="<?= BASEURL ?>/admin/pelanggan" class="btn-reset-search">
                Reset
            </a>
        <?php endif; ?>
    </form>

    <table class="table-pelanggan">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($pelanggan)): ?>
                <?php foreach ($pelanggan as $index => $p): ?>
                    <tr>
                        <td class="id-text">
                            #<?= htmlspecialchars($p['id'] ?? ($index + 1)) ?>
                        </td>

                        <td>
                            <div class="avatar-wrapper">
                                <div class="avatar-circle">
                                    <?php
                                    $nama = $p['nama'] ?? '?';
                                    echo htmlspecialchars(strtoupper(substr($nama, 0, 1)));
                                    ?>
                                </div>

                                <span>
                                    <?= htmlspecialchars($p['nama'] ?? 'Tidak ada nama') ?>
                                </span>
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
                    <td colspan="4" class="empty-table">
                        <?php if (!empty($keyword)): ?>
                            Data pelanggan dengan kata kunci "<?= htmlspecialchars($keyword) ?>" tidak ditemukan.
                        <?php else: ?>
                            Belum ada data pelanggan.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>
