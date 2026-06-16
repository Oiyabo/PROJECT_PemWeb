let currentReservID = null;
let currentSisaBayar = null;
let currentHargaFull = null;
let currentJenisKendaraan = null;
const baseUrl = window.APP_BASEURL || "";

function openPaymentModal(reservID, hargaFull, sisaBayar, kendaraan, jenisKendaraan, tanggal, jam, layanan, dpTerbayar) {
    currentReservID = reservID;
    currentHargaFull = hargaFull;
    currentSisaBayar = sisaBayar;
    currentJenisKendaraan = jenisKendaraan;

    document.getElementById('modalReservID').textContent = '#' + reservID;
    document.getElementById('modalTanggal').textContent = tanggal || '-';
    document.getElementById('modalJam').textContent = jam || '-';
    document.getElementById('modalKendaraan').textContent = kendaraan;
    document.getElementById('modalLayanan').textContent = layanan || '-';
    document.getElementById('modalHargaFull').textContent = 'Rp ' + formatNumber(hargaFull);
    document.getElementById('modalDpTerbayar').textContent = 'Rp ' + formatNumber(dpTerbayar || 0);
    document.getElementById('modalFullAmount').textContent = 'Rp ' + formatNumber(sisaBayar);

    const modal = document.getElementById('paymentFullModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closePaymentModal() {
    const modal = document.getElementById('paymentFullModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
}

function submitPaymentFull() {
    const btn = document.getElementById('btnSubmitFull');
    if (!btn) {
        alert('Tombol pembayaran tidak ditemukan. Silakan refresh halaman.');
        return;
    }
    btn.disabled = true;

    if (!baseUrl) {
        alert('Konfigurasi aplikasi tidak lengkap. Silakan refresh halaman.');
        btn.disabled = false;
        return;
    }

    if (typeof showPaymentLoading !== 'function') {
        alert('Fungsi loading tidak ditemukan. Cek console.');
        btn.disabled = false;
        return;
    }

    showPaymentLoading('Menyiapkan pembayaran Midtrans...');

    const formData = new FormData();
    formData.append('tipe', 'FULL');
    formData.append('id_reservasi', currentReservID);
    formData.append('jenis_kendaraan', currentJenisKendaraan);

    requestMidtransSnap(
        baseUrl,
        formData,
        (data) => {
            closePaymentModal();
            sessionStorage.setItem('midtrans_full_order', data.order_id);

            loadMidtransSnap(
                data.snap_script || window.MIDTRANS_SNAP_SCRIPT,
                data.client_key || window.MIDTRANS_CLIENT_KEY,
                () => {
                    openMidtransSnap(
                        data.snap_token,
                        data.client_key,
                        () => verifikasiPembayaranFull(data.order_id, data.nominal),
                        () => {
                            alert('Pembayaran gagal atau dibatalkan.');
                            resetSubmitFullBtn(btn);
                        },
                        () => resetSubmitFullBtn(btn)
                    );
                }
            );
        },
        (msg) => {
            alert(msg);
            resetSubmitFullBtn(btn);
        }
    );
}

function resetSubmitFullBtn(btn) {
    const el = btn || document.getElementById('btnSubmitFull');
    if (!el) return;
    el.disabled = false;
    el.textContent = 'Bayar dengan Midtrans';
}

function verifikasiPembayaranFull(orderId, nominal) {
    afterSnapPaid(baseUrl, orderId, {
        maxAttempts: 25,
        verifyMessage: 'Memverifikasi pelunasan pembayaran...',
        onPaid: () => {
            sessionStorage.removeItem('midtrans_full_order');
            hidePaymentLoading();
            showPaymentSuccess(nominal);
        },
        onTimeout: (isCanceled) => {
            hidePaymentLoading();
            if (!isCanceled) {
                alert('Pembayaran masih diproses. Jika sudah bayar, tunggu 1–2 menit lalu refresh halaman.');
            }
            resetSubmitFullBtn();
        },
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const payment = params.get('payment');
    const orderId = params.get('order_id');

    if (
        orderId &&
        (payment === 'settlement' || payment === 'capture' || payment === 'pending')
    ) {
        sessionStorage.removeItem('midtrans_full_order');
        verifikasiPembayaranFull(orderId, null);
    } else if (!orderId) {
        const savedOrderId = sessionStorage.getItem('midtrans_full_order');
        if (savedOrderId) {
            fetch(baseUrl + "/pelanggan/cekpembayaran?order_id=" + encodeURIComponent(savedOrderId))
                .then((r) => r.json())
                .then((data) => {
                    if (data.paid) {
                        sessionStorage.removeItem('midtrans_full_order');
                        verifikasiPembayaranFull(savedOrderId, null);
                    }
                })
                .catch(() => {});
        }
    }
});

function showPaymentSuccess(nominal) {
    const amount = nominal || currentSisaBayar;
    const confirmAmountEl = document.getElementById('confirmAmount');
    if (amount) {
        confirmAmountEl.textContent = 'Rp ' + formatNumber(amount);
        confirmAmountEl.parentElement.style.display = 'block';
    } else {
        confirmAmountEl.parentElement.style.display = 'none';
    }
    
    const modal = document.getElementById('confirmPaymentModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closeConfirmAndRefresh() {
    const modal = document.getElementById('confirmPaymentModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    location.reload();
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}