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
            'SELECT r.*, u.nama, u.email
             FROM reservasi r
             JOIN users u ON r.user_id = u.id
             ORDER BY r.tanggal DESC, r.jam ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM reservasi WHERE user_id = ? ORDER BY tanggal DESC, jam ASC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create(
        int    $userId,
        string $kendaraan,
        string $plat,
        string $layanan,
        string $tanggal,
        string $jam,
        string $catatan = ''
    ): bool {
        $stmt = $this->db->prepare(
            'INSERT INTO reservasi (user_id, kendaraan, plat, layanan, tanggal, jam, catatan)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        return $stmt->execute([$userId, $kendaraan, $plat, $layanan, $tanggal, $jam, $catatan]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE reservasi SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }
}