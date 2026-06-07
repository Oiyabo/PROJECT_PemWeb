const jadwalState = {
  verifying: false,
  available: null,
  lastKey: "",
  abortController: null,
};

function jadwalKey() {
  return fieldVal("tanggal") + "|" + fieldVal("jam");
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
