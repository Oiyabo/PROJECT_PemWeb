<link rel="stylesheet" href="style/riwayat.css">

<div class="history-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-left">
                <div class="avatar">
                    <?= substr($user['nama'], 0, 1) ?>
                </div>

                <div class="user-info">
                    <div class="user-name">
                        <?= $user['nama'] ?>
                    </div>

                    <div class="user-detail-wrapper">
                        <span class="user-detail">🏍️ Yamaha NMAX · BE 9012 EF</span>
                        <span class="user-detail">📞 0814-5678-9012</span>
                    </div>
                </div>
            </div>

            <div class="total-service">
                <div class="total-number"><?= count($mockRiwayat) ?></div>
                <div class="total-label">Total Service</div>
            </div>
        </div>
    </div>

    <div class="history-list">
        <?php foreach ($mockRiwayat as $i => $r): ?>

        <div class="history-card">
            <div class="timeline-section">
                <div class="timeline-icon <?= $i === 0 ? 'active' : '' ?>">
                    🛠️
                </div>

                <?php if ($i < count($mockRiwayat) - 1): ?>
                    <div class="timeline-line"></div>
                <?php endif; ?>
            </div>

            <div class="service-content">
                <div class="service-header">
                    <div>
                        <div class="service-title">
                            <?= $r['layanan'] ?>
                        </div>

                        <div class="service-meta">
                            <?= $r['tanggal'] ?> · <?= $r['mekanik'] ?>
                        </div>
                    </div>

                    <div class="service-price-section">
                        <div class="service-price">
                            Rp <?= number_format($r['biaya'], 0, ',', '.') ?>
                        </div>

                        <div class="rating-stars">
                            <?php for ($si = 0; $si < 5; $si++): ?>
                                <?php if ($si < $r['rating']): ?>
                                    <span class="star-filled">★</span>
                                <?php else: ?>
                                    <span class="star-empty">☆</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="service-note">
                    <?= $r['catatan'] ?>
                </div>

                <div class="service-footer">
                    <span class="status-badge">
                        Selesai
                    </span>

                    <span class="service-id">
                        <?= $r['id'] ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>