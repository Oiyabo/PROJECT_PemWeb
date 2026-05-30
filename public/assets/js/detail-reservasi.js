(function () {
  const modal = document.getElementById("detailReservasiModal");
  const contentEl = document.getElementById("detailReservasiContent");
  const adminActionsEl = document.getElementById("detailReservasiAdminActions");
  const titleEl = document.getElementById("detailReservasiTitle");
  const btnClose = document.getElementById("btnCloseDetailReservasi");
  const btnTutup = document.getElementById("btnTutupDetailReservasi");

  if (!modal || !contentEl) return;

  const baseUrl = window.APP_BASEURL || "";
  const isAdmin = Boolean(window.DETAIL_RESERVASI_ADMIN);
  const adminBack = window.DETAIL_RESERVASI_BACK || baseUrl + "/admin/reservasi";

  function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
  }

  function formatJam(jam) {
    if (!jam) return "-";
    return String(jam).substring(0, 5);
  }

  function refreshIcons() {
    if (typeof lucide !== "undefined") {
      lucide.createIcons();
    }
  }

  function statusBadgeClass(status) {
    const map = {
      Menunggu: "badge-warning",
      Konfirmasi: "badge-info",
      Proses: "badge-primary",
      Selesai: "badge-success",
      Batal: "badge-danger",
    };
    return map[status] || "badge-warning";
  }

  function renderDetail(data) {
    const id = data.id_reservasi ?? "";
    const status = data.status || "Menunggu";
    const jam = formatJam(data.jam);
    const catatan = (data.catatan || "").trim() !== "" ? data.catatan : "-";

    titleEl.textContent = "Detail Reservasi #" + id;

    let html = `
      <section class="detail-section">
        <h4>Informasi Reservasi</h4>
        <dl class="detail-dl">
          <dt>ID</dt><dd class="mono">#${escapeHtml(String(id))}</dd>
          <dt>Nama Pelanggan</dt><dd>${escapeHtml(data.nama || "-")}</dd>
    `;

    if (isAdmin && data.email) {
      html += `<dt>Email</dt><dd>${escapeHtml(data.email)}</dd>`;
    }

    html += `
          <dt>Kendaraan</dt><dd>${escapeHtml(data.kendaraan || "-")}</dd>
          <dt>Plat Nomor</dt><dd>${escapeHtml(data.plat || "-")}</dd>
          <dt>Tanggal</dt><dd>${escapeHtml(data.tanggal || "-")}</dd>
          <dt>Jam</dt><dd>${escapeHtml(jam)}</dd>
          <dt>Layanan</dt><dd>${escapeHtml(data.layanan || "-")}</dd>
          <dt>Catatan</dt><dd>${escapeHtml(catatan)}</dd>
          <dt>Status</dt><dd><span class="badge ${statusBadgeClass(status)}">${escapeHtml(status)}</span></dd>
        </dl>
      </section>
    `;

    contentEl.innerHTML = html;

    if (isAdmin && adminActionsEl) {
      const statusOptions = ["Menunggu", "Konfirmasi", "Proses", "Selesai", "Batal"];
      const optionsHtml = statusOptions
        .map(
          (opt) =>
            `<option value="${escapeHtml(opt)}"${status === opt ? " selected" : ""}>${escapeHtml(opt)}</option>`
        )
        .join("");

      adminActionsEl.innerHTML = `
        <section class="detail-section detail-section-admin">
          <h4>Ubah Status</h4>
          <form action="${escapeHtml(baseUrl)}/admin/updatestatus/${escapeHtml(String(id))}" method="POST" class="status-form status-form-modal">
            <input type="hidden" name="back" value="${escapeHtml(adminBack)}">
            <label for="detailReservasiStatusSelect" class="detail-status-label">Status reservasi</label>
            <select id="detailReservasiStatusSelect" name="status" class="form-input form-input-sm">
              ${optionsHtml}
            </select>
            <button type="submit" class="btn-primary btn-simpan-status">Simpan Status</button>
          </form>
        </section>
      `;
      adminActionsEl.style.display = "block";
    } else if (adminActionsEl) {
      adminActionsEl.innerHTML = "";
      adminActionsEl.style.display = "none";
    }

    refreshIcons();
  }

  function openModal(data) {
    renderDetail(data);
    modal.style.display = "flex";
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    refreshIcons();
  }

  function closeModal() {
    modal.style.display = "none";
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  document.querySelectorAll(".btn-detail-reservasi").forEach((btn) => {
    btn.addEventListener("click", () => {
      const raw = btn.getAttribute("data-reservasi");
      if (!raw) return;

      try {
        const data = JSON.parse(raw);
        openModal(data);
      } catch (e) {
        console.error("Data reservasi tidak valid", e);
      }
    });
  });

  btnClose?.addEventListener("click", closeModal);
  btnTutup?.addEventListener("click", closeModal);

  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modal.style.display === "flex") {
      closeModal();
    }
  });
})();
