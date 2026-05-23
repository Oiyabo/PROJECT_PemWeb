let midtransOrderId = '';

const midtransClientKey = window.MIDTRANS_CLIENT_KEY || '';
const midtransSnapScript = window.MIDTRANS_SNAP_SCRIPT || '';
const baseUrl = window.APP_BASEURL || '';

function bayarDpDanSelesaikan() {
    const layananInputs = document.querySelectorAll('input[name="layanan_id[]"]');
    const layananIds = Array.from(layananInputs).map((el) => el.value);
    const jenis =
        document.querySelector('input[name="jenisKendaraan"]')?.value || '';
    const nominal = parseInt(
        document.getElementById('totalDPInput')?.value || '0',
        10
    );

    if (!jenis || layananIds.length === 0) {
        alert('Data layanan tidak lengkap.');
        return;
    }

    const btn = document.getElementById('btnBayarDpSelesai');
    if (btn) {
        btn.disabled = true;
    }

    const formData = new FormData();
    formData.append('tipe', 'DP_PRE');
    formData.append('jenis_kendaraan', jenis);
    formData.append('nominal', nominal);
    layananIds.forEach((id) => formData.append('layanan_id[]', id));

    requestMidtransSnap(
        baseUrl,
        formData,
        (data) => {
            midtransOrderId = data.order_id;
            sessionStorage.setItem('midtrans_dp_order', data.order_id);

            loadMidtransSnap(
                data.snap_script || midtransSnapScript,
                data.client_key || midtransClientKey,
                () => {
                    openMidtransSnap(
                        data.snap_token,
                        data.client_key,
                        () => startVerifikasiDanSimpan(),
                        () => startVerifikasiDanSimpan(),
                        () => {
                            if (btn) btn.disabled = false;
                        }
                    );
                }
            );
        },
        (msg) => {
            alert(msg);
            if (btn) btn.disabled = false;
        }
    );
}

function startVerifikasiDanSimpan() {
    if (!midtransOrderId) {
        return;
    }

    afterSnapPaid(baseUrl, midtransOrderId, {
        maxAttempts: 25,
        verifyMessage: 'Memverifikasi pembayaran DP...',
        onPaid: simpanReservasiOtomatis,
        onTimeout: () => {
            const btn = document.getElementById('btnBayarDpSelesai');
            if (btn) btn.disabled = false;
            alert(
                'Pembayaran masih diproses. Jika sudah bayar, tunggu sebentar lalu klik tombol lagi atau refresh halaman.'
            );
        },
    });
}

function simpanReservasiOtomatis() {
    document.getElementById('dpPaidInput').value = '1';
    document.getElementById('metodeDpInput').value = 'midtrans';
    document.getElementById('midtransOrderIdInput').value = midtransOrderId;
    sessionStorage.removeItem('midtrans_dp_order');

    showPaymentLoading('Menyimpan reservasi...');

    const btn = document.getElementById('btnBayarDpSelesai');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Menyimpan reservasi...';
    }

    const form = document.getElementById('formReservasi');
    const formData = new FormData(form);
    formData.append('ajax', '1');

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
        .then((r) => r.json())
        .then((data) => {
            hidePaymentLoading();
            if (data.success) {
                tampilkanModalSuksesReservasi(data);
                return;
            }
            alert(data.message || 'Gagal menyimpan reservasi');
            if (btn) {
                btn.disabled = false;
                btn.textContent =
                    '💳 Bayar DP dan Selesaikan — Rp ' +
                    formatNumber(parseInt(document.getElementById('totalDPInput')?.value || '0', 10));
            }
        })
        .catch(() => {
            hidePaymentLoading();
            alert('Terjadi kesalahan saat menyimpan reservasi.');
            if (btn) {
                btn.disabled = false;
                btn.textContent =
                    '💳 Bayar DP dan Selesaikan — Rp ' +
                    formatNumber(parseInt(document.getElementById('totalDPInput')?.value || '0', 10));
            }
        });
}

function tampilkanModalSuksesReservasi(data) {
    const idEl = document.getElementById('suksesReservasiId');
    const nominalEl = document.getElementById('suksesDpNominal');
    const totalDp = parseInt(document.getElementById('totalDPInput')?.value || '0', 10);

    if (idEl) {
        idEl.textContent = data.id_reservasi ? '#' + data.id_reservasi : '-';
    }
    if (nominalEl) {
        const nominal = data.total_dp || totalDp;
        nominalEl.textContent = 'Rp ' + formatNumber(nominal);
    }

    const modal = document.getElementById('reservasiSuksesModal');
    if (modal) {
        modal.style.display = 'flex';
    }

    const btn = document.getElementById('btnBayarDpSelesai');
    if (btn) {
        btn.textContent = '✓ Reservasi Berhasil';
    }
}

function tutupSuksesDanKeRiwayat() {
    window.location.href = baseUrl + '/pelanggan/riwayat';
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const payment = params.get('payment');
    const orderFromUrl = params.get('order_id');

    if (params.get('step') !== '3') {
        return;
    }

    if (orderFromUrl) {
        midtransOrderId = orderFromUrl;
        sessionStorage.setItem('midtrans_dp_order', orderFromUrl);
    } else {
        const savedOrder = sessionStorage.getItem('midtrans_dp_order');
        if (savedOrder) {
            midtransOrderId = savedOrder;
        }
    }

    if (
        midtransOrderId &&
        (payment === 'settlement' ||
            payment === 'capture' ||
            payment === 'pending' ||
            payment === '')
    ) {
        const btn = document.getElementById('btnBayarDpSelesai');
        if (btn) btn.disabled = true;
        startVerifikasiDanSimpan();
    }
});
