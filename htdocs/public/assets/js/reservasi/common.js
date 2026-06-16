let midtransOrderId = "";
let currentStep = 1;
let verifikasiDpBerjalan = false;

const midtransClientKey = window.MIDTRANS_CLIENT_KEY || "";
const midtransSnapScript = window.MIDTRANS_SNAP_SCRIPT || "";
const baseUrl = window.APP_BASEURL || "";
const layananMap = window.RESERVASI_LAYANAN_MAP || {};

const GAGAL_PAYMENT = ["deny", "cancel", "expire", "failure"];
const OK_PAYMENT_RETURN = ["settlement", "capture", "pending"];

function fieldVal(id) {
  return document.getElementById(id)?.value || "";
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

function formatNumber(num) {
  return new Intl.NumberFormat("id-ID").format(num);
}
