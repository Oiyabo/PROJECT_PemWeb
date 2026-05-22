<?php

class ReservasiModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.nama, u.email, GROUP_CONCAT(l.nama_layanan SEPARATOR ", ") AS layanan
             FROM reservasi r
             JOIN users u ON r.user_id = u.id
             LEFT JOIN reservasi_layanan rl ON r.id_reservasi = rl.id_reservasi
             LEFT JOIN layanan l ON rl.layanan_id = l.layanan_id
             GROUP BY r.id_reservasi
             ORDER BY r.tanggal DESC, r.jam ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT 
                r.*,
                GROUP_CONCAT(l.nama_layanan SEPARATOR ", ") AS layanan
            FROM reservasi r

            LEFT JOIN reservasi_layanan rl
                ON r.id_reservasi = rl.id_reservasi

            LEFT JOIN layanan l
                ON rl.layanan_id = l.layanan_id

            WHERE r.user_id = ?

            GROUP BY r.id_reservasi

            ORDER BY r.tanggal DESC, r.jam ASC'
        );

        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLayanan(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM layanan ORDER BY nama_layanan ASC"
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(
        int    $userId,
        string $kendaraan,
        string $plat,
        string $tanggal,
        string $jam,
        string $catatan = ''
    ): int {

        $stmt = $this->db->prepare(
            'INSERT INTO reservasi
            (user_id, kendaraan, plat, tanggal, jam, catatan)
            VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $userId,
            $kendaraan,
            $plat,
            $tanggal,
            $jam,
            $catatan
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function tambahLayanan(
        int $reservasiId,
        int $layananId
    ): bool {

        $stmt = $this->db->prepare(
            'INSERT INTO reservasi_layanan
            (id_reservasi, layanan_id)
            VALUES (?, ?)'
        );

        return $stmt->execute([
            $reservasiId,
            $layananId
        ]);
    }

    public function updateStatus(int $reservasiId, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE reservasi SET status = ? WHERE id_reservasi = ?'
        );

        return $stmt->execute([$status, $reservasiId]);
    }
}