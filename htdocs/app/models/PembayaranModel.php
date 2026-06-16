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
            'SELECT jenis_kendaraan, total_dp, total_full, total_sisa FROM ' . DbView::v_reservasi_harga() . ' AS v_reservasi_harga WHERE id_reservasi = ?'
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
        $res = DbProcedure::hitung_harga_layanan($this->db, $layananIds, $jenisKendaraan);
        $dp = $res['total_dp'];
        $full = $res['total_full'];
        return ['total_dp' => $dp, 'total_full' => $full, 'total_sisa' => $full - $dp];
    }

    public function deteksiJenisKendaraan(int $reservasiId): string
    {
        return DbFunction::deteksi_jenis_kendaraan($this->db, $reservasiId);
    }

    public function sudahBayarDP(int $reservasiId): bool
    {
        return DbFunction::sudah_bayar_dp($this->db, $reservasiId);
    }

    public function sudahBayarFull(int $reservasiId): bool
    {
        return DbFunction::sudah_bayar_full($this->db, $reservasiId);
    }

    public function prosesPembayaranDP(int $reservasiId, string $jenisKendaraan, ?string $metodePembayaran = 'midtrans'): array
    {
        $res = DbProcedure::proses_pembayaran_dp($this->db, $reservasiId, $jenisKendaraan, $metodePembayaran ?? 'midtrans');
        return [
            'id_pembayaran_dp' => $res['id_dp'],
            'nominal' => $res['nominal'],
            'no_transaksi' => $res['no_trx'],
        ];
    }

    public function prosesPembayaranFull(int $reservasiId, string $jenisKendaraan, ?string $metodePembayaran = 'midtrans'): array
    {
        $res = DbProcedure::proses_pembayaran_full($this->db, $reservasiId, $jenisKendaraan, $metodePembayaran ?? 'midtrans');
        return [
            'id_pembayaran_full' => $res['id_full'],
            'nominal' => $res['nominal'],
            'nominal_sisa' => $res['nominal_sisa'],
            'no_transaksi' => $res['no_trx'],
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
                DbProcedure::upsert_hubung($this->db, $reservasiId, (int) $dp['id_pembayaran_dp'], null);
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
