let midtransOrderId = "";
let currentStep = 1;

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

    const action = btn.dataset.action;
    if (action === "next") {
      e.preventDefault();
      goNext();
    } else if (action === "prev") {
      e.preventDefault();
      goPrev();
    }
  });

  const jenisEl = document.getElementById("jenisKendaraan");
  if (jenisEl) {
    jenisEl.addEventListener("change", filterLayananByJenis);
    filterLayananByJenis();
  }

  const btnBayar = document.getElementById("btnBayarDpSelesai");
  if (btnBayar) {
    btnBayar.addEventListener("click", bayarDpDanSelesaikan);
  }

  if (!new URLSearchParams(window.location.search).get("order_id")) {
    hidePaymentLoading();
  }

  handleMidtransReturn();
}

function showStep(step, scroll = true) {
  currentStep = step;

  wizard.panels.forEach((panel) => {
    const panelStep = parseInt(panel.dataset.step, 10);
    const active = panelStep === step;
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

function validateStep(step) {
  const panel = getStepPanel(step);
  if (!panel) return false;

  const fields = panel.querySelectorAll("input, select, textarea");
  for (const field of fields) {
    if (field.closest("[hidden]") || field.offsetParent === null) continue;
    if (!field.checkValidity()) {
      field.reportValidity();
      return false;
    }
  }

  if (step === 2) {
    const checked = panel.querySelectorAll(
      'input[name="layanan_id[]"]:checked',
    );
    if (checked.length === 0) {
      alert("Pilih minimal satu jenis layanan.");
      return false;
    }

    const tanggal = document.getElementById("tanggal")?.value || "";
    const jam = document.getElementById("jam")?.value || "";

    if (jadwalState.verifying) {
      alert("Mohon tunggu, jadwal sedang diverifikasi...");
      return false;
    }

    if (!tanggal || !jam) {
      return false;
    }

    const key = tanggal + "|" + jam;
    if (jadwalState.available !== true || jadwalState.lastKey !== key) {
      alert(
        jadwalState.available === false
          ? "Jadwal pada tanggal dan jam tersebut sudah dipesan. Silakan pilih waktu lain."
          : "Verifikasi jadwal belum selesai. Tunggu sebentar atau ubah tanggal/jam.",
      );
      if (jadwalState.lastKey !== key) {
        verifyJadwal();
      }
      return false;
    }
  }

  return true;
}

function initJadwalVerification() {
  const tanggalEl = document.getElementById("tanggal");
  const jamEl = document.getElementById("jam");

  const onJadwalChange = () => {
    resetJadwalState();
    const tanggal = tanggalEl?.value || "";
    const jam = jamEl?.value || "";
    if (tanggal && jam) {
      verifyJadwal();
    } else {
      hideJadwalStatus();
    }
  };

  if (tanggalEl) tanggalEl.addEventListener("change", onJadwalChange);
  if (jamEl) jamEl.addEventListener("change", onJadwalChange);

  if (tanggalEl?.value && jamEl?.value) {
    verifyJadwal();
  }
}

function resetJadwalState() {
  jadwalState.available = null;
  jadwalState.lastKey = "";
  if (jadwalState.abortController) {
    jadwalState.abortController.abort();
    jadwalState.abortController = null;
  }
}

function setStep2NavDisabled(disabled) {
  const panel = getStepPanel(2);
  if (!panel) return;
  panel.querySelectorAll("[data-action]").forEach((btn) => {
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
  } else if (type === "available") {
    el.classList.add("is-available");
    el.textContent = message;
  } else {
    el.classList.add("is-unavailable");
    el.textContent = message;
  }

  if (fields) {
    fields.classList.toggle("is-verifying", type === "loading");
  }
}

function hideJadwalStatus() {
  const el = document.getElementById("jadwalStatus");
  const fields = document.getElementById("jadwalFields");
  if (el) {
    el.hidden = true;
    el.className = "jadwal-status";
    el.textContent = "";
  }
  if (fields) fields.classList.remove("is-verifying");
}

function verifyJadwal() {
  const tanggal = document.getElementById("tanggal")?.value || "";
  const jam = document.getElementById("jam")?.value || "";

  if (!tanggal || !jam) {
    hideJadwalStatus();
    return Promise.resolve(false);
  }

  const key = tanggal + "|" + jam;

  if (jadwalState.abortController) {
    jadwalState.abortController.abort();
  }
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

      if (!ok && !data.success) {
        jadwalState.available = false;
        showJadwalStatus(
          "unavailable",
          data.message || "Gagal memverifikasi jadwal.",
        );
        return false;
      }

      const available = data.available === true;
      jadwalState.available = available;

      if (available) {
        showJadwalStatus("available", data.message || "Jadwal tersedia.");
      } else {
        showJadwalStatus(
          "unavailable",
          data.message ||
            "Jadwal pada tanggal dan jam tersebut sudah dipesan. Silakan pilih waktu lain.",
        );
      }
      return available;
    })
    .catch((err) => {
      if (err.name === "AbortError") return false;
      if (jadwalState.lastKey !== key) return false;

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
  const panel = getStepPanel(step);
  const data = new FormData();

  if (step === 1) {
    data.append(
      "jenisKendaraan",
      document.getElementById("jenisKendaraan")?.value || "",
    );
    data.append("kendaraan", document.getElementById("kendaraan")?.value || "");
    data.append("plat", document.getElementById("plat")?.value || "");
  }

  if (step === 2) {
    data.append("tanggal", document.getElementById("tanggal")?.value || "");
    data.append("jam", document.getElementById("jam")?.value || "");
    data.append("catatan", document.getElementById("catatan")?.value || "");
    document
      .querySelectorAll('#serviceGrid input[name="layanan_id[]"]:checked')
      .forEach((cb) => {
        data.append("layanan_id[]", cb.value);
      });
  }

  if (step >= 2) {
    data.append(
      "jenisKendaraan",
      document.getElementById("jenisKendaraan")?.value || "",
    );
    data.append("kendaraan", document.getElementById("kendaraan")?.value || "");
    data.append("plat", document.getElementById("plat")?.value || "");
  }

  if (step >= 3) {
    data.append("tanggal", document.getElementById("tanggal")?.value || "");
    data.append("jam", document.getElementById("jam")?.value || "");
    data.append("catatan", document.getElementById("catatan")?.value || "");
    document
      .querySelectorAll('#serviceGrid input[name="layanan_id[]"]:checked')
      .forEach((cb) => {
        data.append("layanan_id[]", cb.value);
      });
  }

  data.append("ajax", "1");
  return data;
}

function saveSessionToServer(step) {
  const formData = collectStepData(step);

  return fetch(wizard.saveUrl, {
    method: "POST",
    body: formData,
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

  const btn = wizard.el.querySelector(
    `.form-step[data-step="${currentStep}"] [data-action="next"]`,
  );
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
      .catch((err) =>
        alert(
          err.message ||
            "Terjadi kesalahan saat menyimpan data. Silakan coba lagi.",
        ),
      )
      .finally(() => {
        if (btn) btn.disabled = false;
      });
  };

  if (currentStep === 2) {
    const tanggal = document.getElementById("tanggal")?.value || "";
    const jam = document.getElementById("jam")?.value || "";
    const key = tanggal + "|" + jam;

    if (jadwalState.available === true && jadwalState.lastKey === key) {
      proceed();
      return;
    }

    verifyJadwal().then((available) => {
      if (!available) {
        if (btn) btn.disabled = false;
        return;
      }
      if (!validateStep(2)) {
        if (btn) btn.disabled = false;
        return;
      }
      proceed();
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

  if (currentStep === 3) {
    showStep(2);
  } else if (currentStep === 2) {
    showStep(1);
  }
}

function filterLayananByJenis() {
  const jenis = document.getElementById("jenisKendaraan")?.value || "";
  const items = document.querySelectorAll("#serviceGrid .service-item");

  items.forEach((label) => {
    const motorOk = label.dataset.motor === "1";
    const mobilOk = label.dataset.mobil === "1";
    let visible = true;

    if (jenis === "Motor") visible = motorOk;
    else if (jenis === "Mobil") visible = mobilOk;

    label.style.display = visible ? "" : "none";
    if (!visible) {
      const cb = label.querySelector('input[type="checkbox"]');
      if (cb) cb.checked = false;
    }
  });
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

    const layananNames = (data.layanan_id || [])
      .map((id) => layananMap[id] || layananMap[String(id)])
      .filter(Boolean);
    set("layanan", layananNames.length ? layananNames.join(", ") : "-");
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
  const layananInputs = document.querySelectorAll(
    '#hiddenFieldsContainer input[name="layanan_id[]"]',
  );
  const layananIds = Array.from(layananInputs).map((el) => el.value);
  const jenis =
    document.querySelector(
      '#hiddenFieldsContainer input[name="jenisKendaraan"]',
    )?.value || "";
  const nominal = parseInt(
    document.getElementById("totalDPInput")?.value || "0",
    10,
  );

  if (!jenis || layananIds.length === 0) {
    alert("Data layanan tidak lengkap.");
    return;
  }

  const btn = document.getElementById("btnBayarDpSelesai");
  if (btn) btn.disabled = true;

  const formData = new FormData();
  formData.append("tipe", "DP_PRE");
  formData.append("jenis_kendaraan", jenis);
  formData.append("nominal", nominal);
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
        () => {
          openMidtransSnap(
            data.snap_token,
            data.client_key,
            () => startVerifikasiDanSimpan(),
            () => startVerifikasiDanSimpan(),
            () => {
              if (btn) btn.disabled = false;
            },
          );
        },
      );
    },
    (msg) => {
      alert(msg);
      if (btn) btn.disabled = false;
    },
  );
}

let verifikasiDpBerjalan = false;

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

  const form = document.getElementById("formReservasi");
  const formData = new FormData(form);
  formData.append("ajax", "1");

  fetch(form.action, {
    method: "POST",
    body: formData,
    headers: { "X-Requested-With": "XMLHttpRequest" },
  })
    .then((r) => r.json())
    .then((data) => {
      hidePaymentLoading();
      if (data.success) {
        tampilkanModalSuksesReservasi(data);
        return;
      }
      alert(data.message || "Gagal menyimpan reservasi");
      resetBtnBayar();
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
    const nominal = data.total_dp || totalDp;
    nominalEl.textContent = "Rp " + formatNumber(nominal);
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

  const gagal = ["deny", "cancel", "expire", "failure"];
  if (gagal.includes(payment)) {
    sessionStorage.removeItem("midtrans_dp_order");
    midtransOrderId = "";
    alert(
      "Pembayaran dibatalkan atau gagal. Silakan coba lagi dari halaman konfirmasi.",
    );
    return;
  }

  // Hanya lanjutkan untuk status sukses / menunggu / kosong (redirect finish Midtrans)
  const bolehVerify =
    !payment || ["settlement", "capture", "pending"].includes(payment);
  if (!bolehVerify) {
    sessionStorage.removeItem("midtrans_dp_order");
    return;
  }

  saveSessionToServer(3)
    .then((res) => {
      if (res.success) {
        renderKonfirmasi(res.data, res.ringkasan);
      }
      showStep(3, false);
      const btn = document.getElementById("btnBayarDpSelesai");
      if (btn) btn.disabled = true;
      startVerifikasiDanSimpan();
    })
    .catch(() => {
      showStep(3, false);
      const btn = document.getElementById("btnBayarDpSelesai");
      if (btn) btn.disabled = true;
      startVerifikasiDanSimpan();
    });
}

document.addEventListener("DOMContentLoaded", initReservasiWizard);
