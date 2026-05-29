(function () {
  "use strict";

  const config = window.APP_SESSION;
  if (!config) {
    return;
  }

  const modal = document.getElementById("sessionTimeoutModal");
  const titleEl = document.getElementById("sessionTimeoutTitle");
  const messageEl = document.getElementById("sessionTimeoutMessage");
  const countdownEl = document.getElementById("sessionTimeoutCountdown");
  const btnExtend = document.getElementById("sessionTimeoutExtend");
  const btnLogout = document.getElementById("sessionTimeoutLogout");

  if (!modal || !btnExtend || !btnLogout) {
    return;
  }

  let expiresAt = Number(config.expiresAt) || 0;
  const warningSeconds = Math.max(0, Number(config.warning) || 60);
  let modalVisible = false;
  let isExpiredState = false;
  let tickTimer = null;

  function secondsLeft() {
    return Math.max(0, expiresAt - Math.floor(Date.now() / 1000));
  }

  function formatCountdown(totalSeconds) {
    const m = Math.floor(totalSeconds / 60);
    const s = totalSeconds % 60;
    return `${m}:${String(s).padStart(2, "0")}`;
  }

  function showModal(expired) {
    isExpiredState = expired;
    modalVisible = true;
    modal.classList.add("show");
    modal.setAttribute("aria-hidden", "false");

    if (expired) {
      titleEl.textContent = "Session habis";
      messageEl.textContent =
        "Sesi Anda telah berakhir karena tidak ada aktivitas. Perpanjang untuk tetap masuk atau keluar dari akun.";
      countdownEl.textContent = "";
      btnExtend.textContent = "Perpanjang";
    } else {
      titleEl.textContent = "Session akan habis";
      messageEl.textContent =
        "Sesi Anda akan segera berakhir. Perpanjang untuk tetap masuk atau keluar dari akun.";
      btnExtend.textContent = "Perpanjang";
    }

    if (typeof lucide !== "undefined") {
      lucide.createIcons();
    }
  }

  function hideModal() {
    modalVisible = false;
    isExpiredState = false;
    modal.classList.remove("show");
    modal.setAttribute("aria-hidden", "true");
  }

  function updateCountdownLabel() {
    if (!modalVisible || isExpiredState) {
      return;
    }

    const left = secondsLeft();
    if (left > 0) {
      countdownEl.textContent = `Sisa waktu: ${formatCountdown(left)}`;
    } else {
      countdownEl.textContent = "";
    }
  }

  function onTick() {
    const left = secondsLeft();

    if (left <= 0) {
      if (!modalVisible || !isExpiredState) {
        showModal(true);
      }
      return;
    }

    if (left <= warningSeconds) {
      if (!modalVisible) {
        showModal(false);
      }
      updateCountdownLabel();
    }
  }

  function startTicker() {
    if (tickTimer) {
      clearInterval(tickTimer);
    }
    onTick();
    tickTimer = setInterval(onTick, 1000);
  }

  async function extendSession() {
    btnExtend.disabled = true;

    try {
      const res = await fetch(config.extendUrl, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
        credentials: "same-origin",
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok || !data.ok) {
        showModal(true);
        return;
      }

      expiresAt =
        Number(data.expires_at) ||
        Math.floor(Date.now() / 1000) + Number(config.timeout);
      hideModal();
      startTicker();
    } catch {
      showModal(true);
    } finally {
      btnExtend.disabled = false;
    }
  }

  function logout() {
    window.location.href = config.logoutUrl;
  }

  btnExtend.addEventListener("click", extendSession);
  btnLogout.addEventListener("click", logout);

  /** Tangkap respons 401 dari fetch API lain di aplikasi */
  const nativeFetch = window.fetch.bind(window);
  window.fetch = async function (...args) {
    const response = await nativeFetch(...args);

    if (response.status === 401) {
      const cloned = response.clone();
      try {
        const body = await cloned.json();
        if (body && body.session_expired) {
          showModal(true);
        }
      } catch {
        /* bukan JSON */
      }
    }

    return response;
  };

  startTicker();
})();
