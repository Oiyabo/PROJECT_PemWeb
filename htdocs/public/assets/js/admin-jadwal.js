(function () {
  "use strict";

  const eventsByDate = window.JADWAL_EVENTS || {};
  const dayButtons = document.querySelectorAll(".jadwal-day[data-date]");
  const detailPanel = document.getElementById("jadwalDayDetail");
  const detailTitle = document.getElementById("jadwalDayDetailTitle");
  const detailContent = document.getElementById("jadwalDayDetailContent");
  const closeBtn = document.getElementById("btnCloseDayDetail");

  const bulanSingkat = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "Mei",
    "Jun",
    "Jul",
    "Agu",
    "Sep",
    "Okt",
    "Nov",
    "Des",
  ];

  const statusClassMap = {
    Menunggu: "status-waiting",
    Konfirmasi: "status-confirmed",
    Proses: "status-process",
    Selesai: "status-done",
    Batal: "status-danger",
  };

  function formatTanggalIndo(dateStr) {
    const parts = dateStr.split("-");
    if (parts.length !== 3) return dateStr;
    const day = parseInt(parts[2], 10);
    const month = parseInt(parts[1], 10) - 1;
    const year = parts[0];
    return day + " " + (bulanSingkat[month] || parts[1]) + " " + year;
  }

  function formatJam(jam) {
    return (jam || "").substring(0, 5);
  }

  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text ?? "";
    return div.innerHTML;
  }

  function badgeStatus(status) {
    const cls = statusClassMap[status] || "status-waiting";
    return (
      '<span class="status-badge ' +
      cls +
      '">' +
      escapeHtml(status) +
      "</span>"
    );
  }

  function renderDayDetail(dateKey) {
    const events = eventsByDate[dateKey] || [];

    if (!detailPanel || !detailTitle || !detailContent) return;

    detailTitle.textContent = "Jadwal " + formatTanggalIndo(dateKey);

    if (events.length === 0) {
      detailContent.innerHTML =
        '<p class="jadwal-empty">Tidak ada jadwal pada tanggal ini.</p>';
    } else {
      detailContent.innerHTML = events
        .map(function (ev) {
          const json = JSON.stringify(ev)
            .replace(/&/g, "&amp;")
            .replace(/'/g, "&#39;")
            .replace(/"/g, "&quot;")
            .replace(/</g, "&lt;");

          return (
            '<div class="jadwal-detail-item">' +
            '<div class="jadwal-detail-top">' +
            '<span class="jadwal-detail-time">' +
            escapeHtml(formatJam(ev.jam)) +
            "</span>" +
            badgeStatus(ev.status || "Menunggu") +
            "</div>" +
            '<div class="jadwal-detail-name">' +
            escapeHtml(ev.nama || "-") +
            "</div>" +
            '<div class="jadwal-detail-meta">' +
            escapeHtml(ev.kendaraan || "-") +
            (ev.plat ? " &middot; " + escapeHtml(ev.plat) : "") +
            "</div>" +
            (ev.layanan
              ? '<div class="jadwal-detail-service">' +
                escapeHtml(ev.layanan) +
                "</div>"
              : "") +
            '<button type="button" class="btn btn-detail-reservasi btn-sm" data-reservasi="' +
            json +
            '">' +
            '<i data-lucide="eye" width="14" height="14"></i> Detail' +
            "</button>" +
            "</div>"
          );
        })
        .join("");
    }

    detailPanel.hidden = false;

    if (window.lucide) {
      window.lucide.createIcons();
    }
  }

  function clearSelection() {
    dayButtons.forEach(function (btn) {
      btn.classList.remove("is-selected");
    });
  }

  dayButtons.forEach(function (btn) {
    btn.addEventListener("click", function () {
      const dateKey = btn.dataset.date;
      if (!dateKey) return;

      clearSelection();
      btn.classList.add("is-selected");
      renderDayDetail(dateKey);
    });
  });

  if (closeBtn && detailPanel) {
    closeBtn.addEventListener("click", function () {
      detailPanel.hidden = true;
      clearSelection();
    });
  }

  const todayBtn = document.querySelector(
    '.jadwal-day.is-today[data-date]'
  );
  if (todayBtn) {
    todayBtn.click();
  }
})();
