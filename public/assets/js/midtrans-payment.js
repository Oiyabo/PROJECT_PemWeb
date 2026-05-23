(function () {
    const style = document.createElement('style');
    style.textContent = `
#payment-loading-overlay {
  position: fixed;
  inset: 0;
  z-index: 10000;
  background: rgba(15, 23, 42, 0.65);
  display: none;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
#payment-loading-overlay.is-active {
  display: flex;
}
.payment-loading-box {
  background: #fff;
  padding: 32px 40px;
  border-radius: 12px;
  text-align: center;
  max-width: 360px;
  width: 100%;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
}
.payment-loading-spinner {
  width: 48px;
  height: 48px;
  margin: 0 auto 20px;
  border: 4px solid #e2e8f0;
  border-top-color: #38a3a5;
  border-radius: 50%;
  animation: payment-spin 0.85s linear infinite;
}
.payment-loading-text {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  line-height: 1.5;
}
.payment-loading-hint {
  margin: 10px 0 0;
  font-size: 13px;
  color: #64748b;
}
@keyframes payment-spin {
  to { transform: rotate(360deg); }
}
`;
    document.head.appendChild(style);
})();

function ensurePaymentLoadingOverlay() {
    let el = document.getElementById('payment-loading-overlay');
    if (el) {
        return el;
    }
    el = document.createElement('div');
    el.id = 'payment-loading-overlay';
    el.setAttribute('role', 'alertdialog');
    el.setAttribute('aria-live', 'polite');
    el.innerHTML =
        '<div class="payment-loading-box">' +
        '<div class="payment-loading-spinner" aria-hidden="true"></div>' +
        '<p class="payment-loading-text" id="payment-loading-text">Memproses...</p>' +
        '<p class="payment-loading-hint">Mohon jangan tutup halaman ini</p>' +
        '</div>';
    document.body.appendChild(el);
    return el;
}

function showPaymentLoading(message) {
    const overlay = ensurePaymentLoadingOverlay();
    const text = document.getElementById('payment-loading-text');
    if (text) {
        text.textContent = message || 'Memproses...';
    }
    overlay.classList.add('is-active');
    document.body.style.overflow = 'hidden';
}

function hidePaymentLoading() {
    const overlay = document.getElementById('payment-loading-overlay');
    if (overlay) {
        overlay.classList.remove('is-active');
    }
    document.body.style.overflow = '';
}

function loadMidtransSnap(scriptUrl, clientKey, callback) {
    if (window.snap && typeof callback === 'function') {
        callback();
        return;
    }
    const existing = document.querySelector('script[data-midtrans-snap="1"]');
    if (existing) {
        existing.addEventListener('load', callback);
        return;
    }
    const script = document.createElement('script');
    script.src = scriptUrl;
    script.setAttribute('data-client-key', clientKey);
    script.setAttribute('data-midtrans-snap', '1');
    script.onload = callback;
    script.onerror = () => {
        hidePaymentLoading();
        alert('Gagal memuat Midtrans Snap. Periksa koneksi internet.');
    };
    document.head.appendChild(script);
}

function openMidtransSnap(snapToken, clientKey, onSuccess, onPending, onError) {
    if (!window.snap) {
        hidePaymentLoading();
        alert('Midtrans belum siap. Coba lagi.');
        return;
    }
    hidePaymentLoading();
    window.snap.pay(snapToken, {
        onSuccess: onSuccess || function () {},
        onPending: onPending || function () {},
        onError: function (result) {
            hidePaymentLoading();
            if (typeof onError === 'function') {
                onError(result);
            }
        },
        onClose: function () {
            hidePaymentLoading();
        },
    });
}

function pollPaymentStatus(baseUrl, orderId, maxAttempts, onPaid, onTimeout, loadingMessage) {
    showPaymentLoading(loadingMessage || 'Memverifikasi pembayaran...');

    let attempts = 0;

    function tick() {
        attempts++;
        fetch(baseUrl + '/pelanggan/cekpembayaran?order_id=' + encodeURIComponent(orderId))
            .then((r) => r.json())
            .then((data) => {
                if (data.paid) {
                    hidePaymentLoading();
                    onPaid();
                    return;
                }
                if (attempts >= maxAttempts) {
                    hidePaymentLoading();
                    onTimeout();
                    return;
                }
                setTimeout(tick, 2000);
            })
            .catch(() => {
                if (attempts >= maxAttempts) {
                    hidePaymentLoading();
                    onTimeout();
                } else {
                    setTimeout(tick, 2000);
                }
            });
    }

    tick();
}

function requestMidtransSnap(baseUrl, formData, onTokenReady, onError) {
    showPaymentLoading('Menyiapkan pembayaran Midtrans...');

    fetch(baseUrl + '/pelanggan/midtranssnap', {
        method: 'POST',
        body: formData,
    })
        .then((r) => r.json())
        .then((data) => {
            if (!data.success) {
                hidePaymentLoading();
                if (typeof onError === 'function') {
                    onError(data.message || 'Gagal membuat pembayaran');
                } else {
                    alert(data.message || 'Gagal membuat pembayaran');
                }
                return;
            }
            if (typeof onTokenReady === 'function') {
                onTokenReady(data);
            }
        })
        .catch(() => {
            hidePaymentLoading();
            if (typeof onError === 'function') {
                onError('Terjadi kesalahan saat menghubungi server pembayaran.');
            } else {
                alert('Terjadi kesalahan saat menghubungi server pembayaran.');
            }
        });
}

function afterSnapPaid(baseUrl, orderId, options) {
    const maxAttempts = options.maxAttempts || 20;
    const verifyMsg = options.verifyMessage || 'Memverifikasi pembayaran...';
    const onPaid = options.onPaid || function () {};
    const onTimeout = options.onTimeout || function () {
        alert(
            'Pembayaran masih diproses. Jika sudah bayar, tunggu 1–2 menit lalu refresh halaman.'
        );
    };

    pollPaymentStatus(baseUrl, orderId, maxAttempts, onPaid, onTimeout, verifyMsg);
}
