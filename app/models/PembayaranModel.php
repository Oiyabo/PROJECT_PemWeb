<?php

class PembayaranModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function hitungTotalHarga(int $reservasiId, string $jenisKendaraan = ''): array
    {
        $stmt = $this->db->prepare(
            'SELECT jenis_kendaraan, total_dp, total_full, total_sisa FROM v_reservasi_harga WHERE id_reservasi = ?'
        );
        $stmt->execute([$reservasiId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['total_dp' => 0, 'total_full' => 0, 'total_sisa' => 0, 'jenis_kendaraan' => $jenisKendaraan];
        }

        return [
            'total_dp' => (int) $row['total_dp'],
            'total_full' => (int) $row['total_full'],
            'total_sisa' => (int) $row['total_sisa'],
            'jenis_kendaraan' => $row['jenis_kendaraan'] ?: $jenisKendaraan,
        ];
    }

    public function ringkasanHargaLayanan(array $semuaLayanan, array $layananIds, string $jenisKendaraan): array
    {
        $ids = array_map('intval', $layananIds);
        $motor = $jenisKendaraan === 'Motor';
        $items = [];
        $totalDP = 0;
        $totalFull = 0;
        foreach ($semuaLayanan as $layanan) {
            if (!in_array((int) $layanan['layanan_id'], $ids, true)) {
                continue;
            }
            $dp = (int) ($motor ? ($layanan['dp_motor'] ?? 0) : ($layanan['dp_mobil'] ?? 0));
            $full = (int) ($motor ? ($layanan['harga_motor_full'] ?? 0) : ($layanan['harga_mobil_full'] ?? 0));
            $items[] = [
                'nama_layanan' => $layanan['nama_layanan'],
                'dp' => $dp,
                'full' => $full,
            ];
            $totalDP += $dp;
            $totalFull += $full;
        }
        return [
            'items' => $items,
            'total_dp' => $totalDP,
            'total_full' => $totalFull,
            'total_sisa' => $totalFull - $totalDP,
        ];
    }

    public function hitungHargaDariLayanan(array $layananIds, string $jenisKendaraan): array
    {
        if (empty($layananIds)) {
            return ['total_dp' => 0, 'total_full' => 0, 'total_sisa' => 0];
        }
        $ids = implode(',', array_map('intval', $layananIds));
        $stmt = $this->db->prepare('CALL sp_hitung_harga_layanan(?, ?, @dp, @full)');
        $stmt->execute([$ids, $jenisKendaraan]);
        $stmt->closeCursor();
        $row = $this->db->query('SELECT @dp AS total_dp, @full AS total_full')->fetch(PDO::FETCH_ASSOC);
        $dp = (int) ($row['total_dp'] ?? 0);
        $full = (int) ($row['total_full'] ?? 0);
        return ['total_dp' => $dp, 'total_full' => $full, 'total_sisa' => $full - $dp];
    }

    public function deteksiJenisKendaraan(int $reservasiId): string
    {
        $stmt = $this->db->prepare('SELECT fn_deteksi_jenis_kendaraan(?) AS jenis');
        $stmt->execute([$reservasiId]);
        return (string) $stmt->fetchColumn();
    }

    public function sudahBayarDP(int $reservasiId): bool
    {
        $stmt = $this->db->prepare('SELECT fn_sudah_bayar_dp(?) AS sudah');
        $stmt->execute([$reservasiId]);
        return (bool) $stmt->fetchColumn();
    }

    public function sudahBayarFull(int $reservasiId): bool
    {
        $stmt = $this->db->prepare('SELECT fn_sudah_bayar_full(?) AS sudah');
        $stmt->execute([$reservasiId]);
        return (bool) $stmt->fetchColumn();
    }

    public function prosesPembayaranDP(int $reservasiId, string $jenisKendaraan, ?string $metodePembayaran = 'midtrans'): array
    {
        $stmt = $this->db->prepare('CALL sp_proses_pembayaran_dp(?, ?, ?, @id_dp, @nominal, @no_trx)');
        $stmt->execute([$reservasiId, $jenisKendaraan, $metodePembayaran ?? 'midtrans']);
        $stmt->closeCursor();
        $out = $this->db->query('SELECT @id_dp AS id_pembayaran_dp, @nominal AS nominal, @no_trx AS no_transaksi')->fetch(PDO::FETCH_ASSOC);
        return [
            'id_pembayaran_dp' => (int) $out['id_pembayaran_dp'],
            'nominal' => (int) $out['nominal'],
            'no_transaksi' => $out['no_transaksi'],
        ];
    }

    public function prosesPembayaranFull(int $reservasiId, string $jenisKendaraan, ?string $metodePembayaran = 'midtrans'): array
    {
        $stmt = $this->db->prepare('CALL sp_proses_pembayaran_full(?, ?, ?, @id_full, @nominal, @sisa, @no_trx)');
        $stmt->execute([$reservasiId, $jenisKendaraan, $metodePembayaran ?? 'midtrans']);
        $stmt->closeCursor();
        $out = $this->db->query('SELECT @id_full AS id_pembayaran_full, @nominal AS nominal, @sisa AS nominal_sisa, @no_trx AS no_transaksi')->fetch(PDO::FETCH_ASSOC);
        return [
            'id_pembayaran_full' => (int) $out['id_pembayaran_full'],
            'nominal' => (int) $out['nominal'],
            'nominal_sisa' => (int) $out['nominal_sisa'],
            'no_transaksi' => $out['no_transaksi'],
        ];
    }

    public function linkDpPreToReservasi(string $orderId, int $reservasiId): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('UPDATE pembayaran_dp SET id_reservasi = ? WHERE order_id = ?');
            $stmt->execute([$reservasiId, $orderId]);

            $stmtDp = $this->db->prepare('SELECT * FROM pembayaran_dp WHERE order_id = ? LIMIT 1');
            $stmtDp->execute([$orderId]);
            $dp = $stmtDp->fetch(PDO::FETCH_ASSOC);

            if ($dp) {
                $stmtUpsert = $this->db->prepare('CALL sp_upsert_hubung(?, ?, ?)');
                $stmtUpsert->execute([$reservasiId, (int) $dp['id_pembayaran_dp'], null]);
                $stmtUpsert->closeCursor();
            }

            $stmtPending = $this->db->prepare('UPDATE pembayaran_midtrans_pending SET id_reservasi = ? WHERE order_id = ?');
            $stmtPending->execute([$reservasiId, $orderId]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
