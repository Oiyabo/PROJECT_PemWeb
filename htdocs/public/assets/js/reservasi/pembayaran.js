function bayarDpDanSelesaikan() {
  const layananIds = Array.from(
    document.querySelectorAll(
      '#hiddenFieldsContainer input[name="layanan_id[]"]',
    ),
  ).map((el) => el.value);
  
  const jenis =
    document.querySelector(
      '#hiddenFieldsContainer input[name="jenisKendaraan"]',
    )?.value || "";

  if (!jenis || !layananIds.length) {
    alert("Data layanan tidak lengkap.");
    return;
  }

  const btn = document.getElementById("btnBayarDpSelesai");
  if (btn) btn.disabled = true;

  showPaymentLoading("Menyiapkan pembayaran Midtrans...");

  const formData = new FormData();
  formData.append("tipe", "DP_PRE");
  formData.append("jenis_kendaraan", jenis);
  layananIds.forEach((id) => formData.append("layanan_id[]", id));

  requestMidtransSnap(
    baseUrl,
    formData,
    (data) => {
      midtransOrderId = data.order_id;
      sessionStorage.setItem("midtrans_dp_order", data.order_id);
      loadMidtransSnap(
        data.snap_script || midtransSnapScript,
        data.client_key || midtransClientKey,
        () =>
          openMidtransSnap(
            data.snap_token,
            data.client_key,
            startVerifikasiDanSimpan,
            () => {
              if (btn) btn.disabled = false;
            },
            () => {
              if (btn) btn.disabled = false;
            },
          ),
      );
    },
    (msg) => {
      alert(msg);
      if (btn) btn.disabled = false;
    },
  );
}

function startVerifikasiDanSimpan() {
  if (!midtransOrderId || verifikasiDpBerjalan) return;
  verifikasiDpBerjalan = true;

  afterSnapPaid(baseUrl, midtransOrderId, {
    maxAttempts: 25,
    verifyMessage: "Memverifikasi pembayaran DP...",
    onPaid: simpanReservasiOtomatis,
    onTimeout: (isCanceled) => {
      verifikasiDpBerjalan = false;
      const btn = document.getElementById("btnBayarDpSelesai");
      if (btn) btn.disabled = false;
      if (!isCanceled) {
          alert(
            "Pembayaran masih diproses. Jika sudah bayar, tunggu sebentar lalu klik tombol lagi.",
          );
      }
    },
  });
}

function simpanReservasiOtomatis() {
  verifikasiDpBerjalan = false;
  document.getElementById("dpPaidInput").value = "1";
  document.getElementById("metodeDpInput").value = "midtrans";
  document.getElementById("midtransOrderIdInput").value = midtransOrderId;
  sessionStorage.removeItem("midtrans_dp_order");

  showPaymentLoading("Menyimpan reservasi...");
  const btn = document.getElementById("btnBayarDpSelesai");
  if (btn) {
    btn.disabled = true;
    btn.textContent = "Menyimpan reservasi...";
  }

  const formData = new FormData(document.getElementById("formReservasi"));
  formData.append("ajax", "1");

  fetch(document.getElementById("formReservasi").action, {
    method: "POST",
    body: formData,
    headers: { "X-Requested-With": "XMLHttpRequest" },
  })
    .then((r) => r.json())
    .then((data) => {
      hidePaymentLoading();
      if (data.success) tampilkanModalSuksesReservasi(data);
      else {
        alert(data.message || "Gagal menyimpan reservasi");
        resetBtnBayar();
      }
    })
    .catch(() => {
      hidePaymentLoading();
      alert("Terjadi kesalahan saat menyimpan reservasi.");
      resetBtnBayar();
    });
}

function resetBtnBayar() {
  const btn = document.getElementById("btnBayarDpSelesai");
  const totalDp = parseInt(
    document.getElementById("totalDPInput")?.value || "0",
    10,
  );
  if (btn) {
    btn.disabled = false;
    btn.innerHTML =
      '💳 Bayar DP dan Selesaikan — <span id="btnDpNominal">Rp ' +
      formatNumber(totalDp) +
      "</span>";
  }
}

function handleMidtransReturn() {
  const params = new URLSearchParams(window.location.search);
  const orderFromUrl = params.get("order_id");
  const payment = (params.get("payment") || "").toLowerCase();

  const savedOrderId = sessionStorage.getItem("midtrans_dp_order");

  if (!orderFromUrl) {
    if (savedOrderId) {
      midtransOrderId = savedOrderId;
      fetch(baseUrl + "/pelanggan/cekpembayaran?order_id=" + encodeURIComponent(savedOrderId))
        .then((r) => r.json())
        .then((data) => {
          if (data.paid) {
            const lanjut = () => {
              showStep(3, false);
              const btn = document.getElementById("btnBayarDpSelesai");
              if (btn) btn.disabled = true;
              startVerifikasiDanSimpan();
            };
            saveSessionToServer(3)
              .then((res) => {
                if (res.success) renderKonfirmasi(res.data, res.ringkasan);
                lanjut();
              })
              .catch(lanjut);
          }
        })
        .catch(() => {});
    }
    return;
  }

  midtransOrderId = orderFromUrl;
  sessionStorage.setItem("midtrans_dp_order", orderFromUrl);

  if (GAGAL_PAYMENT.includes(payment)) {
    sessionStorage.removeItem("midtrans_dp_order");
    midtransOrderId = "";
    alert(
      "Pembayaran dibatalkan atau gagal. Silakan coba lagi dari halaman konfirmasi.",
    );
    return;
  }

  if (payment && !OK_PAYMENT_RETURN.includes(payment)) {
    sessionStorage.removeItem("midtrans_dp_order");
    return;
  }

  const lanjut = () => {
    showStep(3, false);
    document.getElementById("btnBayarDpSelesai").disabled = true;
    startVerifikasiDanSimpan();
  };

  saveSessionToServer(3)
    .then((res) => {
      if (res.success) renderKonfirmasi(res.data, res.ringkasan);
      lanjut();
    })
    .catch(lanjut);
}
