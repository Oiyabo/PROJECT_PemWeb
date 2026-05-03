<?php
$mockStats = [
  ["label" => "Total Reservasi", "value" => "128", "icon" => "calendar", "trend" => "+12%", "up" => true, "color" => "var(--primary)"],
  ["label" => "Service Aktif", "value" => "24", "icon" => "wrench", "trend" => "+5%", "up" => true, "color" => "var(--primary-light)"],
  ["label" => "Total Pelanggan", "value" => "385", "icon" => "users", "trend" => "+18%", "up" => true, "color" => "var(--secondary)"],
  ["label" => "Pendapatan Bulan Ini", "value" => "Rp 48,5jt", "icon" => "credit-card", "trend" => "-3%", "up" => false, "color" => "var(--primary-dark)"],
];

$mockReservasi = [
  ["id" => "RES-001", "nama" => "Budi Santoso", "kendaraan" => "Toyota Avanza", "plat" => "BE 1234 AB", "tanggal" => "2026-05-01", "jam" => "09:00", "layanan" => "Ganti Oli", "status" => "Menunggu"],
  ["id" => "RES-002", "nama" => "Siti Rahayu", "kendaraan" => "Honda Beat", "plat" => "BE 5678 CD", "tanggal" => "2026-05-01", "jam" => "10:30", "layanan" => "Tune Up", "status" => "Konfirmasi"],
  ["id" => "RES-003", "nama" => "Agus Pratama", "kendaraan" => "Yamaha NMAX", "plat" => "BE 9012 EF", "tanggal" => "2026-05-02", "jam" => "08:00", "layanan" => "Servis Berkala", "status" => "Selesai"],
  ["id" => "RES-004", "nama" => "Dewi Lestari", "kendaraan" => "Suzuki Ertiga", "plat" => "BE 3456 GH", "tanggal" => "2026-05-02", "jam" => "13:00", "layanan" => "Ganti Ban", "status" => "Proses"],
  ["id" => "RES-005", "nama" => "Rudi Hartono", "kendaraan" => "Honda Jazz", "plat" => "BE 7890 IJ", "tanggal" => "2026-05-03", "jam" => "11:00", "layanan" => "AC Service", "status" => "Menunggu"],
  ["id" => "RES-006", "nama" => "Maya Sari", "kendaraan" => "Mitsubishi Xpander", "plat" => "BE 2468 KL", "tanggal" => "2026-05-03", "jam" => "14:00", "layanan" => "Rem Brake", "status" => "Konfirmasi"],
];

$mockPelanggan = [
  ["id" => "PLG-001", "nama" => "Budi Santoso", "telepon" => "0812-3456-7890", "email" => "budi@email.com", "kendaraan" => "Toyota Avanza", "totalService" => 8, "bergabung" => "2024-03-15"],
  ["id" => "PLG-002", "nama" => "Siti Rahayu", "telepon" => "0813-4567-8901", "email" => "siti@email.com", "kendaraan" => "Honda Beat", "totalService" => 5, "bergabung" => "2024-05-20"],
  ["id" => "PLG-003", "nama" => "Agus Pratama", "telepon" => "0814-5678-9012", "email" => "agus@email.com", "kendaraan" => "Yamaha NMAX", "totalService" => 12, "bergabung" => "2023-11-08"],
  ["id" => "PLG-004", "nama" => "Dewi Lestari", "telepon" => "0815-6789-0123", "email" => "dewi@email.com", "kendaraan" => "Suzuki Ertiga", "totalService" => 3, "bergabung" => "2025-01-12"],
  ["id" => "PLG-005", "nama" => "Rudi Hartono", "telepon" => "0816-7890-1234", "email" => "rudi@email.com", "kendaraan" => "Honda Jazz", "totalService" => 7, "bergabung" => "2024-07-30"],
];

$mockTransaksi = [
  ["id" => "TRX-001", "pelanggan" => "Budi Santoso", "layanan" => "Ganti Oli + Filter", "tanggal" => "2026-04-28", "total" => 285000, "metode" => "Transfer", "status" => "Lunas"],
  ["id" => "TRX-002", "pelanggan" => "Siti Rahayu", "layanan" => "Tune Up Motor", "tanggal" => "2026-04-27", "total" => 450000, "metode" => "Cash", "status" => "Lunas"],
  ["id" => "TRX-003", "pelanggan" => "Agus Pratama", "layanan" => "Servis Berkala", "tanggal" => "2026-04-26", "total" => 650000, "metode" => "Transfer", "status" => "Lunas"],
  ["id" => "TRX-004", "pelanggan" => "Dewi Lestari", "layanan" => "Ganti Ban 2pcs", "tanggal" => "2026-04-25", "total" => 1200000, "metode" => "Cash", "status" => "Pending"],
  ["id" => "TRX-005", "pelanggan" => "Rudi Hartono", "layanan" => "AC Service Full", "tanggal" => "2026-04-24", "total" => 850000, "metode" => "Transfer", "status" => "Lunas"],
];

$mockDataService = [
  ["id" => "SVC-001", "pelanggan" => "Agus Pratama", "kendaraan" => "Yamaha NMAX", "mekanik" => "Pak Hendra", "layanan" => "Servis Berkala", "mulai" => "2026-04-26 08:00", "selesai" => "2026-04-26 10:30", "status" => "Selesai", "catatan" => "Ganti oli, filter udara, periksa rem"],
  ["id" => "SVC-002", "pelanggan" => "Dewi Lestari", "kendaraan" => "Suzuki Ertiga", "mekanik" => "Pak Doni", "layanan" => "Ganti Ban", "mulai" => "2026-05-02 13:00", "selesai" => "-", "status" => "Proses", "catatan" => "Ban depan kanan kiri"],
  ["id" => "SVC-003", "pelanggan" => "Rudi Hartono", "kendaraan" => "Honda Jazz", "mekanik" => "Pak Hendra", "layanan" => "AC Service", "mulai" => "2026-04-24 09:00", "selesai" => "2026-04-24 12:00", "status" => "Selesai", "catatan" => "Cuci evaporator, isi freon R134a"],
];

$mockRiwayat = [
  ["id" => "SVC-001", "tanggal" => "2026-04-26", "layanan" => "Servis Berkala", "mekanik" => "Pak Hendra", "biaya" => 650000, "kendaraan" => "Yamaha NMAX", "catatan" => "Ganti oli, filter udara, periksa rem", "rating" => 5],
  ["id" => "SVC-B02", "tanggal" => "2026-03-10", "layanan" => "Ganti Oli", "mekanik" => "Pak Doni", "biaya" => 285000, "kendaraan" => "Yamaha NMAX", "catatan" => "Oli diganti Shell Helix", "rating" => 4],
  ["id" => "SVC-C03", "tanggal" => "2026-01-18", "layanan" => "Tune Up", "mekanik" => "Pak Hendra", "biaya" => 400000, "kendaraan" => "Yamaha NMAX", "catatan" => "Karburator dibersihkan, busi diganti", "rating" => 5],
  ["id" => "SVC-D04", "tanggal" => "2025-11-05", "layanan" => "Ganti Kampas Rem", "mekanik" => "Pak Beni", "biaya" => 180000, "kendaraan" => "Yamaha NMAX", "catatan" => "Kampas rem depan belakang", "rating" => 4],
];

function getStatusStyle($status)
{
  $map = [
    "Menunggu" => ["bg" => "var(--accent)", "color" => "var(--text-dark)", "border" => "var(--primary)"],
    "Konfirmasi" => ["bg" => "var(--secondary-lighter)", "color" => "var(--text-dark)", "border" => "var(--primary-light)"],
    "Proses" => ["bg" => "var(--secondary-light)", "color" => "var(--text-dark)", "border" => "var(--primary-dark)"],
    "Selesai" => ["bg" => "var(--secondary-lighter)", "color" => "var(--text-dark)", "border" => "var(--secondary)"],
    "Lunas" => ["bg" => "var(--secondary-lighter)", "color" => "var(--text-dark)", "border" => "var(--secondary)"],
    "Pending" => ["bg" => "var(--accent)", "color" => "var(--text-dark)", "border" => "var(--primary)"],
    "Batal" => ["bg" => "var(--error-bg)", "color" => "var(--error-text)", "border" => "var(--error)"],
  ];
  return $map[$status] ?? $map["Menunggu"];
}

function renderBadge($status)
{
  $s = getStatusStyle($status);
  return '<span style="background: ' . $s['bg'] . '; color: ' . $s['color'] . '; border: 1px solid ' . $s['border'] . '; padding: 2px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap;">' . htmlspecialchars($status) . '</span>';
}
?>