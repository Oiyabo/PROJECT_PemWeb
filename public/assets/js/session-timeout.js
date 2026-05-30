(function () {
  "use strict";

  const config = window.APP_SESSION;
  if (!config) {
    return;
  }

  const modal = document.getElementById("sessionTimeoutModal");
  const titleEl = document.getElementById("sessionTimeoutTitle");
  const messageEl = document.getElementById("sessionTimeoutMessage");
  const btnExtend = document.getElementById("sessionTimeoutExtend");
  const btnLogout = document.getElementById("sessionTimeoutLogout");

  if (!modal || !btnExtend || !btnLogout) {
    return;
  }

  let expiresAt = Number(config.expiresAt) || 0;
  const serverOffset =
    (Number(config.serverNow) || Math.floor(Date.now() / 1000)) -
    Math.floor(Date.now() / 1000);
  const warningSeconds = Math.max(0, Number(config.warning) || 60);
  let modalVisible = false;
  let isExpiredState = false;
  let tickTimer = null;

  function nowServer() {
    return Math.floor(Date.now() / 1000) + serverOffset;
  }

  function secondsLeft() {
    return Math.max(0, expiresAt - nowServer());
  }

  function syncExpiresAt(value) {
    const next = Number(value);
    if (!next) {
      return;
    }

    expiresAt = next;

    if (modalVisible && !isExpiredState && secondsLeft() > warningSeconds) {
      hideModal();
    }
  }

  function syncExpiresFromResponse(response) {
    const raw = response.headers.get("X-Session-Expires-At");
    if (raw) {
      syncExpiresAt(raw);
    }
  }

  function showModal(expired) {
    isExpiredState = expired;
    modalVisible = true;
    modal.classList.add("show");
    modal.setAttribute("aria-hidden", "false");

    if (expired) {
      titleEl.textContent = "Session habis";
      messageEl.textContent =
        "Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan keluar dan login kembali.";
      btnExtend.hidden = true;
    } else {
      titleEl.textContent = "Session akan habis";
      messageEl.textContent =
        "Sesi Anda akan segera berakhir. Perpanjang untuk tetap masuk atau keluar dari akun.";
      btnExtend.hidden = false;
      btnExtend.textContent = "Perpanjang";
    }

    if (typeof lucide !== "undefined") {
      lucide.createIcons();
    }
  }

  function hideModal() {
    modalVisible = false;
    isExpiredState = false;
    btnExtend.hidden = false;
    modal.classList.remove("show");
    modal.setAttribute("aria-hidden", "true");
  }

  function onTick() {
    const left = secondsLeft();

    if (left <= 0) {
      if (!modalVisible || !isExpiredState) {
        showModal(true);
      }
      return;
    }

    if (left <= warningSeconds && !modalVisible) {
      showModal(false);
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
    if (isExpiredState) {
      return;
    }

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

      syncExpiresFromResponse(res);

      const data = await res.json().catch(() => ({}));

      if (!res.ok || !data.ok) {
        showModal(true);
        return;
      }

      syncExpiresAt(
        data.expires_at ||
          Math.floor(Date.now() / 1000) +
            serverOffset +
            Number(config.timeout)
      );
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

    syncExpiresFromResponse(response);

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
