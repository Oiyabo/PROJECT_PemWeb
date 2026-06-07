<?php

class RiwayatModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function getPembayaranPendingByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM v_pembayaran_pending WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservasiSelesaiUnpaidFull(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM v_reservasi_unpaid_full
             WHERE user_id = ?
             ORDER BY tanggal DESC, jam DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRiwayatLunasByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id_reservasi, kendaraan, plat, tanggal, jam, jenis_kendaraan,
                    harga_dp, harga_full, nominal_dp_dibayar, nominal_full_dibayar, tanggal_lunas
             FROM v_riwayat_pembayaran_lunas
             WHERE user_id = ?
             ORDER BY tanggal_lunas DESC, id_reservasi DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRiwayatDetailByReservasi(int $reservasiId, int $userId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM v_riwayat_pembayaran_detail
             WHERE id_reservasi = ? AND user_id = ?
             LIMIT 1'
        );
        $stmt->execute([$reservasiId, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: false;
    }

    public function getStrukByReservasi(int $reservasiId, int $userId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id_reservasi, user_id, nama_pelanggan, kendaraan, plat, jenis_kendaraan,
                    tanggal, jam, layanan_id, nama_layanan, kategori,
                    harga_dp, harga_full, harga_sisa,
                    no_transaksi_dp, no_transaksi_full, tanggal_struk
             FROM v_struk_pembayaran
             WHERE id_reservasi = ? AND user_id = ?
             ORDER BY nama_layanan ASC'
        );
        $stmt->execute([$reservasiId, $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return false;
        }

        $first = $rows[0];
        $items = [];
        $totalDp = 0;
        $totalFull = 0;
        $totalSisa = 0;

        foreach ($rows as $row) {
            $dp = (int) $row['harga_dp'];
            $full = (int) $row['harga_full'];
            $sisa = (int) $row['harga_sisa'];
            $totalDp += $dp;
            $totalFull += $full;
            $totalSisa += $sisa;
            $items[] = [
                'layanan_id' => (int) $row['layanan_id'],
                'nama_layanan' => $row['nama_layanan'],
                'kategori' => $row['kategori'],
                'harga_dp' => $dp,
                'harga_full' => $full,
                'harga_sisa' => $sisa,
            ];
        }

        return [
            'header' => [
                'id_reservasi' => (int) $first['id_reservasi'],
                'nama_pelanggan' => $first['nama_pelanggan'],
                'kendaraan' => $first['kendaraan'],
                'plat' => $first['plat'],
                'jenis_kendaraan' => $first['jenis_kendaraan'],
                'tanggal' => $first['tanggal'],
                'jam' => $first['jam'],
                'no_transaksi_dp' => $first['no_transaksi_dp'],
                'no_transaksi_full' => $first['no_transaksi_full'],
                'tanggal_struk' => $first['tanggal_struk'],
            ],
            'items' => $items,
            'totals' => [
                'harga_dp' => $totalDp,
                'harga_full' => $totalFull,
                'harga_sisa' => $totalSisa,
            ],
        ];
    }
}
