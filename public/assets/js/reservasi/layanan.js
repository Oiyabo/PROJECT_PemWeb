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
