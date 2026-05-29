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
        $stmt = $this->db->query(
            'SELECT * FROM v_reservasi_detail ORDER BY tanggal DESC, jam ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchAll(string $keyword): array
    {
        $keyword = '%' . $keyword . '%';

        $stmt = $this->db->prepare(
            'SELECT * FROM v_reservasi_detail
             WHERE nama LIKE ?
                OR email LIKE ?
                OR kendaraan LIKE ?
                OR plat LIKE ?
                OR layanan LIKE ?
                OR status LIKE ?
                OR tanggal LIKE ?
                OR jam LIKE ?
             ORDER BY tanggal DESC, jam ASC'
        );

        $stmt->execute([
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id_reservasi, user_id, kendaraan, plat, tanggal, jam, catatan,
                    status, created_at, nama, email, layanan
             FROM v_reservasi_detail
             WHERE user_id = ?
             ORDER BY tanggal DESC, jam ASC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLayanan(): array
    {
        $stmt = $this->db->query('SELECT * FROM layanan ORDER BY nama_layanan ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(
        int $userId,
        string $kendaraan,
        string $plat,
        string $tanggal,
        string $jam,
        string $catatan = '',
        string $jenisKendaraan = ''
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO reservasi (user_id, kendaraan, plat, jenis_kendaraan, tanggal, jam, catatan)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $jenis = in_array($jenisKendaraan, ['Motor', 'Mobil'], true) ? $jenisKendaraan : null;

        $stmt->execute([
            $userId,
            $kendaraan,
            $plat,
            $jenis,
            $tanggal,
            $jam,
            $catatan
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function tambahLayanan(int $reservasiId, int $layananId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO reservasi_layanan (id_reservasi, layanan_id) VALUES (?, ?)'
        );

        return $stmt->execute([$reservasiId, $layananId]);
    }

    public function updateStatus(int $reservasiId, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE reservasi SET status = ? WHERE id_reservasi = ?'
        );

        return $stmt->execute([$status, $reservasiId]);
    }

    public function isJadwalTerisi(string $tanggal, string $jam): bool
    {
        $jamNormalized = strlen($jam) === 5 ? $jam . ':00' : $jam;

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM reservasi
             WHERE tanggal = ?
               AND jam = ?
               AND status != ?
             LIMIT 1'
        );

        $stmt->execute([$tanggal, $jamNormalized, 'Batal']);

        return (int) $stmt->fetchColumn() > 0;
    }
}
