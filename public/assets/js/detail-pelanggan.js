(function () {
    const modal = document.getElementById('detailPelangganModal');
    const contentEl = document.getElementById('detailPelangganContent');
    const titleEl = document.getElementById('detailPelangganTitle');
    const btnClose = document.getElementById('btnClosePelangganModal');

    if (!modal || !contentEl) return;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function renderDetail(data) {
        titleEl.textContent = 'Detail Pelanggan #' + (data.id ?? '');

        const reservasi = data.reservasi ?? [];

        let reservasiHtml = '';
        if (reservasi.length === 0) {
            reservasiHtml = '<p style="color:#888;font-size:0.875rem;">Belum ada riwayat reservasi.</p>';
        } else {
            const rows = reservasi.map(r => `
                <tr>
                    <td>${escapeHtml(r.tanggal ?? '-')}</td>
                    <td>${escapeHtml(r.jam ? String(r.jam).substring(0, 5) : '-')}</td>
                    <td>${escapeHtml(r.kendaraan ?? '-')} — ${escapeHtml(r.plat ?? '-')}</td>
                    <td>${escapeHtml(r.layanan ?? '-')}</td>
                    <td><span class="badge ${statusBadgeClass(r.status)}">${escapeHtml(r.status ?? '-')}</span></td>
                </tr>
            `).join('');

            reservasiHtml = `
                <table class="table-reservasi-mini">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Kendaraan</th>
                            <th>Layanan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            `;
        }

        contentEl.innerHTML = `
            <section class="detail-section">
                <h4>Informasi Pelanggan</h4>
                <dl class="detail-dl">
                    <dt>ID</dt><dd class="mono">#${escapeHtml(String(data.id ?? '-'))}</dd>
                    <dt>Nama</dt><dd>${escapeHtml(data.nama ?? '-')}</dd>
                    <dt>Email</dt><dd>${escapeHtml(data.email ?? '-')}</dd>
                    <dt>Role</dt><dd>${escapeHtml(data.role ?? '-')}</dd>
                    <dt>Dibuat tanggal</dt><dd>${escapeHtml(data.created_at ?? '-')}</dd>
                </dl>
            </section>
            <section class="detail-section">
                <h4>Riwayat Reservasi (${reservasi.length})</h4>
                ${reservasiHtml}
            </section>
        `;

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function statusBadgeClass(status) {
        const map = {
            Menunggu: 'badge-warning',
            Konfirmasi: 'badge-info',
            Proses: 'badge-primary',
            Selesai: 'badge-success',
            Batal: 'badge-danger',
            Terbayar: 'badge-success',
        };
        return map[status] || 'badge-warning';
    }

    function openModal(data) {
        renderDetail(data);
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-detail-pelanggan');
        if (!btn) return;
        try {
            openModal(JSON.parse(btn.getAttribute('data-pelanggan')));
        } catch (err) {
            console.error('Data pelanggan tidak valid', err);
        }
    });

    btnClose?.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
    });
})();