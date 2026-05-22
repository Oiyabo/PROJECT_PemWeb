<?php

class PembayaranModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Hitung total harga (DP + Full) untuk reservasi berdasarkan layanan
     */
    public function hitungTotalHarga(int $reservasiId, string $jenisKendaraan): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, l.*, rl.layanan_id
             FROM reservasi r
             JOIN reservasi_layanan rl ON r.id_reservasi = rl.id_reservasi
             JOIN layanan l ON rl.layanan_id = l.layanan_id
             WHERE r.id_reservasi = ?'
        );
        $stmt->execute([$reservasiId]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dpField = ($jenisKendaraan === 'Motor') ? 'dp_motor' : 'dp_mobil';
        $fullField = ($jenisKendaraan === 'Motor') ? 'harga_motor_full' : 'harga_mobil_full';

        $totalDP = 0;
        $totalFull = 0;

        foreach ($details as $item) {
            $totalDP += (int)($item[$dpField] ?? 0);
            $totalFull += (int)($item[$fullField] ?? 0);
        }

        return [
            'total_dp' => $totalDP,
            'total_full' => $totalFull,
            'total_sisa' => $totalFull - $totalDP
        ];
    }

    /**
     * Buat record pembayaran
     */
    public function buatPembayaran(
        int $reservasiId,
        string $tipePembayaran,
        int $nominal
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO pembayaran (id_reservasi, tipe_pembayaran, nominal, status)
             VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $reservasiId,
            $tipePembayaran,
            $nominal,
            'Pending'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Ambil pembayaran berdasarkan ID
     */
    public function getPembayaranById(int $idPembayaran): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM pembayaran WHERE id_pembayaran = ?'
        );
        $stmt->execute([$idPembayaran]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Ambil semua pembayaran untuk reservasi
     */
    public function getPembayaranByReservasi(int $reservasiId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM pembayaran 
             WHERE id_reservasi = ? 
             ORDER BY created_at DESC'
        );
        $stmt->execute([$reservasiId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cek apakah sudah ada pembayaran DP untuk reservasi
     */
    public function sudahBayarDP(int $reservasiId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as count FROM pembayaran 
             WHERE id_reservasi = ? AND tipe_pembayaran = ? AND status = ?'
        );
        $stmt->execute([$reservasiId, 'DP', 'Selesai']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Cek apakah sudah ada pembayaran FULL untuk reservasi
     */
    public function sudahBayarFull(int $reservasiId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as count FROM pembayaran 
             WHERE id_reservasi = ? AND tipe_pembayaran = ? AND status = ?'
        );
        $stmt->execute([$reservasiId, 'FULL', 'Selesai']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Update status pembayaran
     */
    public function updateStatusPembayaran(
        int $idPembayaran,
        string $status,
        ?string $noTransaksi = null,
        ?string $metodePembayaran = null
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE pembayaran 
             SET status = ?, 
                 no_transaksi = COALESCE(?, no_transaksi),
                 metode_pembayaran = COALESCE(?, metode_pembayaran),
                 tanggal_pembayaran = CASE WHEN ? = ? THEN NOW() ELSE tanggal_pembayaran END
             WHERE id_pembayaran = ?'
        );

        return $stmt->execute([
            $status,
            $noTransaksi,
            $metodePembayaran,
            $status,
            'Selesai',
            $idPembayaran
        ]);
    }

    /**
     * Ambil pembayaran pending untuk user
     */
    public function getPembayaranPendingByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, r.kendaraan, r.plat, r.tanggal, r.jam, r.status as reservasi_status
             FROM pembayaran p
             JOIN reservasi r ON p.id_reservasi = r.id_reservasi
             WHERE r.user_id = ? AND p.status = ? AND r.status IN (?, ?)
             ORDER BY p.created_at DESC'
        );
        $stmt->execute([$userId, 'Pending', 'Menunggu', 'Selesai']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil reservasi selesai yang belum dibayar full
     */
    public function getReservasiSelesaiUnpaidFull(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, 
                    GROUP_CONCAT(l.nama_layanan SEPARATOR ", ") AS layanan
             FROM reservasi r
             LEFT JOIN reservasi_layanan rl ON r.id_reservasi = rl.id_reservasi
             LEFT JOIN layanan l ON rl.layanan_id = l.layanan_id
             WHERE r.user_id = ? AND r.status = ?
             AND r.id_reservasi NOT IN (
                 SELECT id_reservasi FROM pembayaran 
                 WHERE tipe_pembayaran = ? AND status = ?
             )
             GROUP BY r.id_reservasi
             ORDER BY r.tanggal DESC, r.jam DESC'
        );
        $stmt->execute([$userId, 'Selesai', 'FULL', 'Selesai']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
