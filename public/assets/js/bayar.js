let currentReservID = null;
let currentSisaBayar = null;
let currentHargaFull = null;
let currentJenisKendaraan = null;
const baseUrl = window.APP_BASEURL;

function openPaymentModal(reservID, hargaFull, sisaBayar, kendaraan, jenisKendaraan) {
    currentReservID = reservID;
    currentHargaFull = hargaFull;
    currentSisaBayar = sisaBayar;
    currentJenisKendaraan = jenisKendaraan;

    document.getElementById('modalReservID').textContent = '#' + reservID;
    document.getElementById('modalKendaraan').textContent = kendaraan;
    document.getElementById('modalHargaFull').textContent = 'Rp ' + formatNumber(hargaFull);
    document.getElementById('modalFullAmount').textContent = 'Rp ' + formatNumber(sisaBayar);

    document.getElementById('paymentFullModal').style.display = 'flex';
}

function closePaymentModal() {
    document.getElementById('paymentFullModal').style.display = 'none';
}

function submitPaymentFull() {
    const btn = document.getElementById('btnSubmitFull');
    btn.disabled = true;

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
            hidePaymentLoading();
            showPaymentSuccess(nominal);
        },
        onTimeout: () => {
            hidePaymentLoading();
            alert('Pembayaran masih diproses. Jika sudah bayar, tunggu 1–2 menit lalu refresh halaman.');
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
        verifikasiPembayaranFull(orderId, null);
    }
});

function showPaymentSuccess(nominal) {
    document.getElementById('confirmAmount').textContent =
        'Rp ' + formatNumber(nominal || currentSisaBayar);
    document.getElementById('confirmPaymentModal').style.display = 'flex';
}

function closeConfirmAndRefresh() {
    document.getElementById('confirmPaymentModal').style.display = 'none';
    location.reload();
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}