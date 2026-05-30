let midtransOrderId = "";
let currentStep = 1;
let verifikasiDpBerjalan = false;

const midtransClientKey = window.MIDTRANS_CLIENT_KEY || "";
const midtransSnapScript = window.MIDTRANS_SNAP_SCRIPT || "";
const baseUrl = window.APP_BASEURL || "";
const layananMap = window.RESERVASI_LAYANAN_MAP || {};

const jadwalState = {
  verifying: false,
  available: null,
  lastKey: "",
  abortController: null,
};

const wizard = {
  el: null,
  saveUrl: "",
  cekJadwalUrl: "",
  panels: [],
  indicators: [],
};

const GAGAL_PAYMENT = ["deny", "cancel", "expire", "failure"];
const OK_PAYMENT_RETURN = ["settlement", "capture", "pending"];

function initReservasiWizard() {
  wizard.el = document.getElementById("reservasiWizard");
  if (!wizard.el) return;

  wizard.saveUrl =
    wizard.el.dataset.saveUrl || baseUrl + "/pelanggan/buatreservasi";
  wizard.cekJadwalUrl =
    wizard.el.dataset.cekJadwalUrl || baseUrl + "/pelanggan/cekjadwal";
  wizard.panels = Array.from(wizard.el.querySelectorAll(".form-step"));
  wizard.indicators = Array.from(
    wizard.el.querySelectorAll("#stepIndicator .step-item"),
  );

  initJadwalVerification();
  showStep(1, false);

  wizard.el.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-action]");
    if (!btn) return;
    e.preventDefault();
    if (btn.dataset.action === "next") goNext();
    else if (btn.dataset.action === "prev") goPrev();
  });

  const jenisEl = document.getElementById("jenisKendaraan");
  if (jenisEl) {
    jenisEl.addEventListener("change", filterLayananByJenis);
    filterLayananByJenis();
  }

  document
    .getElementById("btnBayarDpSelesai")
    ?.addEventListener("click", bayarDpDanSelesaikan);

  if (!new URLSearchParams(window.location.search).get("order_id")) {
    hidePaymentLoading();
  }

  handleMidtransReturn();
}

function showStep(step, scroll = true) {
  currentStep = step;

  wizard.panels.forEach((panel) => {
    const n = parseInt(panel.dataset.step, 10);
    const active = n === step;
    panel.hidden = !active;
    panel.classList.toggle("active", active);
  });

  wizard.indicators.forEach((item) => {
    const n = parseInt(item.dataset.step, 10);
    const numEl = item.querySelector(".step-numbers");
    item.classList.remove("active", "completed");
    if (n === step) {
      item.classList.add("active");
      if (numEl) numEl.textContent = String(n);
    } else if (n < step) {
      item.classList.add("completed");
      if (numEl) numEl.textContent = "✓";
    } else if (numEl) {
      numEl.textContent = String(n);
    }
  });

  if (scroll) {
    wizard.el.scrollIntoView({ behavior: "smooth", block: "start" });
  }
}

function getStepPanel(step) {
  return wizard.el.querySelector(`.form-step[data-step="${step}"]`);
}

function fieldVal(id) {
  return document.getElementById(id)?.value || "";
}

function jadwalKey() {
  return fieldVal("tanggal") + "|" + fieldVal("jam");
}

function validateStep(step) {
  const panel = getStepPanel(step);
  if (!panel) return false;

  for (const field of panel.querySelectorAll("input, select, textarea")) {
    if (field.closest("[hidden]") || field.offsetParent === null) continue;
    if (!field.checkValidity()) {
      field.reportValidity();
      return false;
    }
  }

  if (step !== 2) return true;

  if (!panel.querySelectorAll('input[name="layanan_id[]"]:checked').length) {
    alert("Pilih minimal satu jenis layanan.");
    return false;
  }

  if (jadwalState.verifying) {
    alert("Mohon tunggu, jadwal sedang diverifikasi...");
    return false;
  }

  const key = jadwalKey();
  if (!fieldVal("tanggal") || !fieldVal("jam")) return false;

  if (jadwalState.available === true && jadwalState.lastKey === key) {
    return true;
  }

  alert(
    jadwalState.available === false
      ? "Jadwal pada tanggal dan jam tersebut sudah dipesan. Silakan pilih waktu lain."
      : "Verifikasi jadwal belum selesai. Tunggu sebentar atau ubah tanggal/jam.",
  );
  if (jadwalState.lastKey !== key) verifyJadwal();
  return false;
}

function initJadwalVerification() {
  const onChange = () => {
    resetJadwalState();
    if (fieldVal("tanggal") && fieldVal("jam")) verifyJadwal();
    else hideJadwalStatus();
  };
  document.getElementById("tanggal")?.addEventListener("change", onChange);
  document.getElementById("jam")?.addEventListener("change", onChange);
  if (fieldVal("tanggal") && fieldVal("jam")) verifyJadwal();
}

function resetJadwalState() {
  jadwalState.available = null;
  jadwalState.lastKey = "";
  jadwalState.abortController?.abort();
  jadwalState.abortController = null;
}

function setStep2NavDisabled(disabled) {
  getStepPanel(2)
    ?.querySelectorAll("[data-action]")
    .forEach((btn) => {
      btn.disabled = disabled;
    });
}

function showJadwalStatus(type, message) {
  const el = document.getElementById("jadwalStatus");
  const fields = document.getElementById("jadwalFields");
  if (!el) return;

  el.hidden = false;
  el.className = "jadwal-status";
  if (type === "loading") {
    el.classList.add("is-loading");
    el.innerHTML =
      '<span class="jadwal-status-spinner" aria-hidden="true"></span><span>' +
      escapeHtml(message) +
      "</span>";
  } else {
    el.classList.add(type === "available" ? "is-available" : "is-unavailable");
    el.textContent = message;
  }
  fields?.classList.toggle("is-verifying", type === "loading");
}

function hideJadwalStatus() {
  const el = document.getElementById("jadwalStatus");
  if (el) {
    el.hidden = true;
    el.className = "jadwal-status";
    el.textContent = "";
  }
  document.getElementById("jadwalFields")?.classList.remove("is-verifying");
}

function verifyJadwal() {
  const tanggal = fieldVal("tanggal");
  const jam = fieldVal("jam");
  if (!tanggal || !jam) {
    hideJadwalStatus();
    return Promise.resolve(false);
  }

  const key = tanggal + "|" + jam;
  jadwalState.abortController?.abort();
  jadwalState.abortController = new AbortController();
  jadwalState.verifying = true;
  jadwalState.available = null;
  jadwalState.lastKey = key;
  setStep2NavDisabled(true);
  showJadwalStatus("loading", "Memverifikasi ketersediaan jadwal...");

  const url =
    wizard.cekJadwalUrl +
    "?tanggal=" +
    encodeURIComponent(tanggal) +
    "&jam=" +
    encodeURIComponent(jam);

  return fetch(url, {
    signal: jadwalState.abortController.signal,
    headers: { "X-Requested-With": "XMLHttpRequest" },
  })
    .then((r) => r.json().then((data) => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
      if (jadwalState.lastKey !== key) return false;
      const available = ok && data.available === true;
      jadwalState.available = available;
      showJadwalStatus(
        available ? "available" : "unavailable",
        data.message ||
          (available
            ? "Jadwal tersedia."
            : "Jadwal pada tanggal dan jam tersebut sudah dipesan. Silakan pilih waktu lain."),
      );
      return available;
    })
    .catch((err) => {
      if (err.name === "AbortError" || jadwalState.lastKey !== key) return false;
      jadwalState.available = false;
      showJadwalStatus(
        "unavailable",
        "Gagal memverifikasi jadwal. Periksa koneksi lalu coba lagi.",
      );
      return false;
    })
    .finally(() => {
      if (jadwalState.lastKey === key) {
        jadwalState.verifying = false;
        setStep2NavDisabled(false);
      }
      jadwalState.abortController = null;
    });
}

function collectStepData(step) {
  const data = new FormData();
  if (step >= 1) {
    data.append("jenisKendaraan", fieldVal("jenisKendaraan"));
    data.append("kendaraan", fieldVal("kendaraan"));
    data.append("plat", fieldVal("plat"));
  }
  if (step >= 2) {
    data.append("tanggal", fieldVal("tanggal"));
    data.append("jam", fieldVal("jam"));
    data.append("catatan", fieldVal("catatan"));
    document
      .querySelectorAll('#serviceGrid input[name="layanan_id[]"]:checked')
      .forEach((cb) => data.append("layanan_id[]", cb.value));
  }
  data.append("ajax", "1");
  return data;
}

function saveSessionToServer(step) {
  return fetch(wizard.saveUrl, {
    method: "POST",
    body: collectStepData(step),
    headers: { "X-Requested-With": "XMLHttpRequest" },
  }).then(async (r) => {
    const data = await r.json().catch(() => ({}));
    if (!r.ok) {
      if (data.jadwal_terisi) {
        jadwalState.available = false;
        showJadwalStatus(
          "unavailable",
          data.message ||
            "Jadwal pada tanggal dan jam tersebut sudah dipesan. Silakan pilih waktu lain.",
        );
      }
      throw new Error(data.message || "Gagal menyimpan data");
    }
    return data;
  });
}

function goNext() {
  if (!validateStep(currentStep)) return;
  if (currentStep === 2 && jadwalState.verifying) {
    alert("Mohon tunggu, jadwal sedang diverifikasi...");
    return;
  }

  const btn = getStepPanel(currentStep)?.querySelector('[data-action="next"]');
  if (btn) btn.disabled = true;

  const proceed = () => {
    saveSessionToServer(currentStep)
      .then((res) => {
        if (!res.success) {
          alert(res.message || "Gagal menyimpan data");
          return;
        }
        if (currentStep === 1) {
          filterLayananByJenis();
          showStep(2);
        } else if (currentStep === 2) {
          renderKonfirmasi(res.data, res.ringkasan);
          showStep(3);
        }
      })
      .catch((err) => alert(err.message || "Terjadi kesalahan saat menyimpan data."))
      .finally(() => {
        if (btn) btn.disabled = false;
      });
  };

  if (currentStep === 2 && jadwalState.available !== true) {
    verifyJadwal().then((ok) => {
      if (ok && validateStep(2)) proceed();
      else if (btn) btn.disabled = false;
    });
    return;
  }

  proceed();
}

function goPrev() {
  if (jadwalState.verifying) {
    alert("Mohon tunggu, jadwal sedang diverifikasi...");
    return;
  }
  if (currentStep === 3) showStep(2);
  else if (currentStep === 2) showStep(1);
}

function filterLayananByJenis() {
  const jenis = fieldVal("jenisKendaraan");
  document.querySelectorAll("#serviceGrid .service-item").forEach((label) => {
    const visible =
      jenis === "Motor"
        ? label.dataset.motor === "1"
        : jenis === "Mobil"
          ? label.dataset.mobil === "1"
          : true;
    label.style.display = visible ? "" : "none";
    if (!visible) {
      const cb = label.querySelector('input[type="checkbox"]');
      if (cb) cb.checked = false;
      label.classList.remove("selected");
    }
  });

  // Reset search dan filter tag saat jenis kendaraan berubah
  const searchInput = document.getElementById("serviceSearchInput");
  if (searchInput) searchInput.value = "";
  activeKategori = "all";
  document.querySelectorAll(".svc-ftag").forEach((t, i) =>
    t.classList.toggle("active", i === 0)
  );

  updateServiceCount();
}

function renderKonfirmasi(data, ringkasan) {
  const summary = document.getElementById("confirmSummary");
  if (summary) {
    const set = (field, val) => {
      const el = summary.querySelector(`[data-field="${field}"]`);
      if (el) el.textContent = val || "-";
    };
    set("kendaraan", data.kendaraan);
    set("plat", data.plat);
    set("jenisKendaraan", data.jenisKendaraan);
    set("tanggal", data.tanggal);
    set("jam", data.jam);
    set("catatan", data.catatan || "-");
    const names = (data.layanan_id || [])
      .map((id) => layananMap[id] || layananMap[String(id)])
      .filter(Boolean);
    set("layanan", names.length ? names.join(", ") : "-");
  }

  const ring = ringkasan || {
    items: [],
    total_dp: 0,
    total_full: 0,
    total_sisa: 0,
  };
  const totalDP = parseInt(ring.total_dp || 0, 10);
  const totalFull = parseInt(ring.total_full || 0, 10);
  const totalSisa = parseInt(ring.total_sisa || 0, 10);
  const jenisLabel = data.jenisKendaraan || "-";

  const tbody = document.getElementById("priceTableBody");
  if (tbody) {
    tbody.innerHTML = (ring.items || [])
      .map(
        (item) =>
          `<tr style="border-bottom: 1px solid #e2e8f0;">
              <td style="padding: 12px 8px;">${escapeHtml(item.nama_layanan)}</td>
              <td style="text-align: right; padding: 12px 8px; font-weight: 600;">Rp ${formatNumber(item.dp)}</td>
            </tr>`,
      )
      .join("");
  }

  const tfoot = document.getElementById("priceTableFoot");
  if (tfoot) {
    tfoot.innerHTML = `
      <tr style="border-top: 2px solid #38a3a5; background-color: #f8fafc;">
        <td style="padding: 12px 8px; font-weight: 700;">Total DP (${escapeHtml(jenisLabel)})</td>
        <td style="text-align: right; padding: 12px 8px; font-weight: 700; font-size: 16px; color: #38a3a5;">Rp ${formatNumber(totalDP)}</td>
      </tr>
      <tr style="background-color: #f8fafc;">
        <td style="padding: 8px; font-weight: 600; color: #64748b;">Estimasi Harga Full (${escapeHtml(jenisLabel)})</td>
        <td style="text-align: right; padding: 8px; font-weight: 600; color: #64748b;">Rp ${formatNumber(totalFull)}</td>
      </tr>
      <tr style="background-color: #fff7ed;">
        <td style="padding: 8px; font-weight: 600; color: #9a3412;">Sisa setelah DP</td>
        <td style="text-align: right; padding: 8px; font-weight: 600; color: #9a3412;">Rp ${formatNumber(totalSisa)}</td>
      </tr>`;
  }

  document.getElementById("totalDPInput").value = String(totalDP);
  const btnNominal = document.getElementById("btnDpNominal");
  if (btnNominal) btnNominal.textContent = "Rp " + formatNumber(totalDP);
  syncHiddenFormFields(data, totalDP);
}

function syncHiddenFormFields(data, totalDP) {
  const container = document.getElementById("hiddenFieldsContainer");
  if (!container) return;

  const fields = {
    kendaraan: data.kendaraan || "",
    plat: data.plat || "",
    jenisKendaraan: data.jenisKendaraan || "",
    tanggal: data.tanggal || "",
    jam: data.jam || "",
    catatan: data.catatan || "",
  };

  let html = "";
  Object.entries(fields).forEach(([name, value]) => {
    html += `<input type="hidden" name="${name}" value="${escapeAttr(value)}">`;
  });
  (data.layanan_id || []).forEach((id) => {
    html += `<input type="hidden" name="layanan_id[]" value="${escapeAttr(String(id))}">`;
  });

  container.innerHTML = html;
  document.getElementById("totalDPInput").value = String(totalDP);
}

function escapeHtml(str) {
  const div = document.createElement("div");
  div.textContent = str;
  return div.innerHTML;
}

function escapeAttr(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/"/g, "&quot;")
    .replace(/</g, "&lt;");
}

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
    onTimeout: () => {
      verifikasiDpBerjalan = false;
      const btn = document.getElementById("btnBayarDpSelesai");
      if (btn) btn.disabled = false;
      alert(
        "Pembayaran masih diproses. Jika sudah bayar, tunggu sebentar lalu klik tombol lagi.",
      );
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

function tampilkanModalSuksesReservasi(data) {
  const idEl = document.getElementById("suksesReservasiId");
  const nominalEl = document.getElementById("suksesDpNominal");
  const totalDp = parseInt(
    document.getElementById("totalDPInput")?.value || "0",
    10,
  );

  if (idEl)
    idEl.textContent = data.id_reservasi ? "#" + data.id_reservasi : "-";
  if (nominalEl) {
    nominalEl.textContent =
      "Rp " + formatNumber(data.total_dp || totalDp);
  }

  const modal = document.getElementById("reservasiSuksesModal");
  if (modal) modal.style.display = "flex";

  const btn = document.getElementById("btnBayarDpSelesai");
  if (btn) btn.textContent = "✓ Reservasi Berhasil";
}

function tutupSuksesDanKeRiwayat() {
  window.location.href = baseUrl + "/pelanggan/riwayat";
}

function formatNumber(num) {
  return new Intl.NumberFormat("id-ID").format(num);
}

function handleMidtransReturn() {
  const params = new URLSearchParams(window.location.search);
  const orderFromUrl = params.get("order_id");
  const payment = (params.get("payment") || "").toLowerCase();

  if (!orderFromUrl) {
    sessionStorage.removeItem("midtrans_dp_order");
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

let activeKategori = 'all';

function filterLayanan(query) {
  const items = document.querySelectorAll("#serviceGrid .service-item");
  items.forEach((item) => {
    const hiddenByJenis = item.style.display === "none";
    if (hiddenByJenis) return;

    const namaMatch = item.dataset.nama.includes(query.toLowerCase());
    const katMatch =
      activeKategori === "all" || item.dataset.kategori === activeKategori;
    item.classList.toggle("hidden", !(namaMatch && katMatch));
  });
}

function setKategori(el, kat) {
    document.querySelectorAll('.svc-ftag').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    activeKategori = kat;
    filterLayanan(document.getElementById('serviceSearchInput')?.value || '');
}

function toggleServiceItem(checkbox) {
    checkbox.closest('.service-item').classList.toggle('selected', checkbox.checked);
    updateServiceCount();
}

function updateServiceCount() {
    const total = document.querySelectorAll('#serviceGrid input[name="layanan_id[]"]:checked').length;
    const countEl = document.getElementById('serviceSelectedCount');
    const numEl = document.getElementById('serviceCountNum');
    if (numEl) numEl.textContent = total;
    if (countEl) countEl.style.display = total > 0 ? 'flex' : 'none';
}

document.addEventListener("DOMContentLoaded", initReservasiWizard);
