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

function setStep2NavDisabled(disabled) {
  getStepPanel(2)
    ?.querySelectorAll("[data-action]")
    .forEach((btn) => {
      btn.disabled = disabled;
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
  if (modal) {
    modal.classList.remove("hidden");
    modal.style.display = 'flex';
  }

  const btn = document.getElementById("btnBayarDpSelesai");
  if (btn) btn.textContent = "✓ Reservasi Berhasil";

  // Auto-redirect ke halaman riwayat setelah 2 detik
  setTimeout(() => {
    tutupSuksesDanKeRiwayat();
  }, 2000);
}

function tutupSuksesDanKeRiwayat() {
  const modal = document.getElementById("reservasiSuksesModal");
  if (modal) {
    modal.classList.add("hidden");
    modal.style.display = 'none';
  }
  window.location.href = baseUrl + "/pelanggan/riwayat";
}

document.addEventListener("DOMContentLoaded", initReservasiWizard);
