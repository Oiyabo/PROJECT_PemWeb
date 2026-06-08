(function () {
  const baseUrl = window.APP_BASEURL || "";
  const modal = document.getElementById("detailPembayaranModal");
  const loadingEl = document.getElementById("detailPembayaranLoading");
  const contentEl = document.getElementById("detailPembayaranContent");
  const strukEl = document.getElementById("strukPembayaranContent");
  const titleEl = document.getElementById("detailPembayaranTitle");
  const btnStruk = document.getElementById("btnStrukPembayaran");
  const btnKembali = document.getElementById("btnKembaliDetailPembayaran");
  const btnTutup = document.getElementById("btnTutupDetailPembayaran");

  if (!modal) return;

  let currentReservasiId = null;

  function formatRupiah(num) {
    return "Rp " + new Intl.NumberFormat("id-ID").format(Number(num) || 0);
  }

  function formatTanggal(dateStr) {
    if (!dateStr) return "-";
    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "short",
      year: "numeric",
    });
  }

  function formatDateTime(dateStr) {
    if (!dateStr) return "-";
    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return dateStr;
    return d.toLocaleString("id-ID", {
      day: "2-digit",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
  }

  function refreshIcons() {
    if (typeof lucide !== "undefined") {
      lucide.createIcons();
    }
  }

  function setFooterMode(mode) {
    if (mode === "detail") {
      if (currentReservasiId) {
        btnStruk.classList.remove("hidden");
        btnStruk.style.display = "inline-flex";
      } else {
        btnStruk.classList.add("hidden");
        btnStruk.style.display = "none";
      }
      btnKembali.classList.add("hidden");
      btnKembali.style.display = "none";
    } else if (mode === "struk") {
      btnStruk.classList.add("hidden");
      btnStruk.style.display = "none";
      btnKembali.classList.remove("hidden");
      btnKembali.style.display = "inline-flex";
    } else {
      btnStruk.classList.add("hidden");
      btnStruk.style.display = "none";
      btnKembali.classList.add("hidden");
      btnKembali.style.display = "none";
    }
  }

  function renderDetail(data) {
    const jam = (data.jam || "").toString().substring(0, 5);
    titleEl.textContent = "Detail Pembayaran #" + data.id_reservasi;

    contentEl.innerHTML = `
      <section class="detail-section">
        <h4>Reservasi</h4>
        <dl class="detail-dl">
          <dt>Kendaraan</dt><dd>${escapeHtml(data.kendaraan)} (${escapeHtml(data.jenis_kendaraan || "-")})</dd>
          <dt>Plat</dt><dd>${escapeHtml(data.plat)}</dd>
          <dt>Tanggal & Jam</dt><dd>${formatTanggal(data.tanggal)} · ${escapeHtml(jam)}</dd>
          <dt>Status Service</dt><dd>${escapeHtml(data.status_reservasi)}</dd>
          <dt>Layanan</dt><dd>${escapeHtml(data.layanan || "-")}</dd>
          <dt>Catatan</dt><dd>${escapeHtml(data.catatan || "-")}</dd>
        </dl>
      </section>
      <section class="detail-section">
        <h4>Ringkasan Harga</h4>
        <dl class="detail-dl">
          <dt>Harga DP (layanan)</dt><dd>${formatRupiah(data.harga_dp)}</dd>
          <dt>Harga Full (layanan)</dt><dd>${formatRupiah(data.harga_full)}</dd>
          <dt>Sisa (sebelum lunas)</dt><dd>${formatRupiah(data.harga_sisa)}</dd>
        </dl>
      </section>
      <section class="detail-section">
        <h4>Pembayaran DP</h4>
        <dl class="detail-dl">
          <dt>Nominal dibayar</dt><dd>${formatRupiah(data.nominal_dp)}</dd>
          <dt>No. Transaksi</dt><dd class="mono">${escapeHtml(data.no_transaksi_dp || "-")}</dd>
          <dt>Order ID</dt><dd class="mono">${escapeHtml(data.order_id_dp || "-")}</dd>
          <dt>Metode</dt><dd>${escapeHtml(data.metode_dp || "-")} ${data.channel_dp ? "(" + escapeHtml(data.channel_dp) + ")" : ""}</dd>
          <dt>Tanggal bayar</dt><dd>${formatDateTime(data.tanggal_bayar_dp)}</dd>
        </dl>
      </section>
      <section class="detail-section">
        <h4>Pelunasan (Full)</h4>
        <dl class="detail-dl">
          <dt>Nominal dibayar</dt><dd>${formatRupiah(data.nominal_full)}</dd>
          <dt>No. Transaksi</dt><dd class="mono">${escapeHtml(data.no_transaksi_full || "-")}</dd>
          <dt>Order ID</dt><dd class="mono">${escapeHtml(data.order_id_full || "-")}</dd>
          <dt>Metode</dt><dd>${escapeHtml(data.metode_full || "-")} ${data.channel_full ? "(" + escapeHtml(data.channel_full) + ")" : ""}</dd>
          <dt>Tanggal bayar</dt><dd>${formatDateTime(data.tanggal_bayar_full)}</dd>
        </dl>
      </section>
    `;

    refreshIcons();
  }

  function renderStruk(data) {
    const h = data.header;
    const jam = (h.jam || "").toString().substring(0, 5);
    const t = data.totals;

    const rows = (data.items || [])
      .map(
        (item) => `
        <tr>
          <td>
            <span class="struk-layanan-nama">${escapeHtml(item.nama_layanan)}</span>
            ${item.kategori ? `<span class="struk-layanan-kat">${escapeHtml(item.kategori)}</span>` : ""}
          </td>
          <td class="text-right">${formatRupiah(item.harga_dp)}</td>
          <td class="text-right">${formatRupiah(item.harga_full)}</td>
          <td class="text-right">${formatRupiah(item.harga_sisa)}</td>
        </tr>`,
      )
      .join("");

    titleEl.textContent = "Struk Pembayaran #" + h.id_reservasi;

    strukEl.innerHTML = `
      <article class="struk-card" id="strukPrintArea">
        <header class="struk-card-header">
          <div class="struk-brand">AutoFix</div>
          <p class="struk-subtitle">Struk Pembayaran Resmi</p>
          <p class="struk-meta">No. Reservasi <strong>#${h.id_reservasi}</strong></p>
          <p class="struk-meta">${formatDateTime(h.tanggal_struk)}</p>
        </header>
        <section class="struk-info-block">
          <p><span>Pelanggan</span><strong>${escapeHtml(h.nama_pelanggan)}</strong></p>
          <p><span>Kendaraan</span><strong>${escapeHtml(h.kendaraan)} (${escapeHtml(h.jenis_kendaraan || "-")})</strong></p>
          <p><span>Plat</span><strong>${escapeHtml(h.plat)}</strong></p>
          <p><span>Jadwal</span><strong>${formatTanggal(h.tanggal)} · ${escapeHtml(jam)}</strong></p>
        </section>
        <p class="struk-harga-note">Harga resmi per layanan (${escapeHtml(h.jenis_kendaraan || "kendaraan")})</p>
        <div class="struk-table-wrap">
          <table class="struk-table">
            <thead>
              <tr>
                <th>Layanan</th>
                <th class="text-right">DP</th>
                <th class="text-right">Full</th>
                <th class="text-right">Sisa</th>
              </tr>
            </thead>
            <tbody>
              ${rows}
            </tbody>
            <tfoot>
              <tr>
                <th>Total</th>
                <th class="text-right">${formatRupiah(t.harga_dp)}</th>
                <th class="text-right">${formatRupiah(t.harga_full)}</th>
                <th class="text-right">${formatRupiah(t.harga_sisa)}</th>
              </tr>
            </tfoot>
          </table>
        </div>
        <footer class="struk-footer">
          <p><span>Trx DP</span><span class="mono">${escapeHtml(h.no_transaksi_dp || "-")}</span></p>
          <p><span>Trx Full</span><span class="mono">${escapeHtml(h.no_transaksi_full || "-")}</span></p>
          <p class="struk-thanks">Terima kasih telah menggunakan layanan kami.</p>
        </footer>
      </article>
    `;

    refreshIcons();
  }

  function showDetailView() {
    contentEl.classList.remove("hidden");
    contentEl.style.display = "block";
    strukEl.classList.add("hidden");
    strukEl.style.display = "none";
    setFooterMode("detail");
  }

  function showStrukView() {
    contentEl.classList.add("hidden");
    contentEl.style.display = "none";
    strukEl.classList.remove("hidden");
    strukEl.style.display = "block";
    setFooterMode("struk");
  }

  function openModal() {
    modal.classList.remove("hidden");
    modal.style.display = "flex";
    modal.setAttribute("aria-hidden", "false");
  }

  function closeModal() {
    modal.classList.add("hidden");
    modal.style.display = "none";
    modal.setAttribute("aria-hidden", "true");
    contentEl.classList.add("hidden");
    contentEl.style.display = "none";
    contentEl.innerHTML = "";
    strukEl.classList.add("hidden");
    strukEl.style.display = "none";
    strukEl.innerHTML = "";
    loadingEl.classList.remove("hidden");
    loadingEl.style.display = "block";
    loadingEl.textContent = "Memuat data...";
    currentReservasiId = null;
    setFooterMode("loading");
  }

  async function loadDetail(idReservasi) {
    currentReservasiId = idReservasi;
    openModal();
    loadingEl.classList.remove("hidden");
    loadingEl.style.display = "block";
    contentEl.classList.add("hidden");
    contentEl.style.display = "none";
    strukEl.classList.add("hidden");
    strukEl.style.display = "none";
    setFooterMode("loading");

    try {
      const res = await fetch(
        `${baseUrl}/pelanggan/detailpembayaran/${idReservasi}`,
        { headers: { Accept: "application/json" } },
      );
      const json = await res.json();

      if (!json.success || !json.data) {
        throw new Error(json.message || "Gagal memuat detail");
      }

      renderDetail(json.data);
      loadingEl.classList.add("hidden");
      loadingEl.style.display = "none";
      showDetailView();
    } catch (err) {
      loadingEl.textContent = err.message || "Terjadi kesalahan";
      setFooterMode("loading");
    }
  }

  async function loadStruk() {
    if (!currentReservasiId) return;

    loadingEl.classList.remove("hidden");
    loadingEl.style.display = "block";
    loadingEl.textContent = "Memuat struk...";
    contentEl.classList.add("hidden");
    contentEl.style.display = "none";
    strukEl.classList.add("hidden");
    strukEl.style.display = "none";

    try {
      const res = await fetch(
        `${baseUrl}/pelanggan/strukpembayaran/${currentReservasiId}`,
        { headers: { Accept: "application/json" } },
      );
      const json = await res.json();

      if (!json.success || !json.data) {
        throw new Error(json.message || "Gagal memuat struk");
      }

      renderStruk(json.data);
      loadingEl.classList.add("hidden");
      loadingEl.style.display = "none";
      showStrukView();
    } catch (err) {
      loadingEl.textContent = err.message || "Terjadi kesalahan";
      loadingEl.classList.remove("hidden");
      loadingEl.style.display = "block";
    }
  }

  document.querySelectorAll(".btn-detail-pembayaran").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.getAttribute("data-id");
      if (id) loadDetail(id);
    });
  });

  document
    .getElementById("btnCloseDetailPembayaran")
    ?.addEventListener("click", closeModal);
  btnTutup?.addEventListener("click", closeModal);
  btnStruk?.addEventListener("click", loadStruk);
  btnKembali?.addEventListener("click", () => {
    loadingEl.classList.add("hidden");
    loadingEl.style.display = "none";
    showDetailView();
    titleEl.textContent = "Detail Pembayaran #" + currentReservasiId;
  });

  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });
})();
