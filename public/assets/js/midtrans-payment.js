function ensurePaymentLoadingOverlay() {
  let el = document.getElementById("payment-loading-overlay");
  if (el) return el;

  el = document.createElement("div");
  el.id = "payment-loading-overlay";
  el.setAttribute("role", "alertdialog");
  el.setAttribute("aria-live", "polite");
  el.innerHTML =
    '<div class="payment-loading-box">' +
    '<div class="payment-loading-spinner" aria-hidden="true"></div>' +
    '<p class="payment-loading-text" id="payment-loading-text">Memproses...</p>' +
    '<p class="payment-loading-hint">Mohon jangan tutup halaman ini</p>' +
    '<button type="button" class="btn-secondary" id="payment-loading-cancel" style="margin-top: 15px; font-size: 14px; padding: 6px 12px; display: none; width: 100%;">Tutup / Batal</button>' +
    "</div>";
  document.body.appendChild(el);
  
  const cancelBtn = el.querySelector("#payment-loading-cancel");
  if (cancelBtn) {
      cancelBtn.addEventListener("click", () => {
          window._cancelPaymentPolling = true;
          hidePaymentLoading();
          if (window._onPaymentPollingCancel) {
              window._onPaymentPollingCancel();
          }
      });
  }

  return el;
}

function showPaymentLoading(message) {
  const overlay = ensurePaymentLoadingOverlay();
  const text = overlay.querySelector("#payment-loading-text");
  if (text) text.textContent = message || "Memproses...";
  overlay.classList.add("is-active");
  document.body.style.overflow = "hidden";
}

function hidePaymentLoading() {
  const overlay = document.getElementById("payment-loading-overlay");
  if (overlay) overlay.classList.remove("is-active");
  document.body.style.overflow = "";
  const cancelBtn = document.getElementById("payment-loading-cancel");
  if (cancelBtn) cancelBtn.style.display = "none";
}

function loadMidtransSnap(scriptUrl, clientKey, callback) {
  if (window.snap) {
    callback();
    return;
  }
  const existing = document.querySelector('script[data-midtrans-snap="1"]');
  if (existing) {
    existing.addEventListener("load", callback, { once: true });
    return;
  }
  const script = document.createElement("script");
  script.src = scriptUrl;
  script.setAttribute("data-client-key", clientKey);
  script.setAttribute("data-midtrans-snap", "1");
  script.onload = callback;
  script.onerror = () => {
    hidePaymentLoading();
    alert("Gagal memuat Midtrans Snap. Periksa koneksi internet.");
  };
  document.head.appendChild(script);
}

function runAfterSnapUiCloses(fn) {
  requestAnimationFrame(() => requestAnimationFrame(fn));
}

function openMidtransSnap(snapToken, clientKey, onDone, onError, onCancel) {
  if (!window.snap) {
    hidePaymentLoading();
    alert("Midtrans belum siap. Coba lagi.");
    return;
  }
  hidePaymentLoading();

  let verificationStarted = false;

  function beginVerification() {
    if (verificationStarted) return;
    verificationStarted = true;
    showPaymentLoading("Memverifikasi pembayaran...");
    if (typeof onDone === "function") onDone();
  }

  window.snap.pay(snapToken, {
    onSuccess: () => runAfterSnapUiCloses(beginVerification),
    onPending: () => runAfterSnapUiCloses(beginVerification),
    onError: (result) => {
      hidePaymentLoading();
      if (typeof onError === "function") onError(result);
    },
    onClose: () => {
      if (!verificationStarted) {
        hidePaymentLoading();
        if (typeof onCancel === "function") onCancel();
      }
    },
  });
}

function pollPaymentStatus(baseUrl, orderId, opts) {
  const maxAttempts = opts.maxAttempts || 20;
  const onPaid = opts.onPaid || function () {};
  const onTimeout =
    opts.onTimeout ||
    function () {
      alert(
        "Pembayaran masih diproses. Jika sudah bayar, tunggu 1–2 menit lalu refresh halaman.",
      );
    };

  const message = opts.message || "Memverifikasi pembayaran...";
  if (opts.showLoading !== false) {
    showPaymentLoading(message);
  } else {
    const text = document.getElementById("payment-loading-text");
    if (text) text.textContent = message;
  }

  const cancelBtn = document.getElementById("payment-loading-cancel");
  if (cancelBtn) {
      cancelBtn.style.display = "block";
  }

  window._cancelPaymentPolling = false;
  window._onPaymentPollingCancel = () => {
      onTimeout(true);
  };

  let attempts = 0;

  function tick() {
    if (window._cancelPaymentPolling) return;
    attempts++;
    fetch(
      baseUrl +
        "/pelanggan/cekpembayaran?order_id=" +
        encodeURIComponent(orderId),
    )
      .then((r) => r.json())
      .then((data) => {
        if (window._cancelPaymentPolling) return;
        if (data.paid) {
          hidePaymentLoading();
          onPaid();
          return;
        }
        if (attempts >= maxAttempts) {
          hidePaymentLoading();
          onTimeout(false);
          return;
        }
        setTimeout(tick, 2000);
      })
      .catch(() => {
        if (window._cancelPaymentPolling) return;
        if (attempts >= maxAttempts) {
          hidePaymentLoading();
          onTimeout(false);
        } else {
          setTimeout(tick, 2000);
        }
      });
  }

  tick();
}

function requestMidtransSnap(baseUrl, formData, onTokenReady, onError) {
  fetch(baseUrl + "/pelanggan/midtranssnap", { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (!data.success) {
        hidePaymentLoading();
        const msg = data.message || "Gagal membuat pembayaran";
        if (typeof onError === "function") onError(msg);
        else alert(msg);
        return;
      }
      if (typeof onTokenReady === "function") onTokenReady(data);
    })
    .catch(() => {
      hidePaymentLoading();
      const msg = "Terjadi kesalahan saat menghubungi server pembayaran.";
      if (typeof onError === "function") onError(msg);
      else alert(msg);
    });
}

function afterSnapPaid(baseUrl, orderId, options) {
  pollPaymentStatus(baseUrl, orderId, {
    maxAttempts: options.maxAttempts || 20,
    message: options.verifyMessage,
    showLoading: options.showLoading,
    onPaid: options.onPaid,
    onTimeout: options.onTimeout,
  });
}
