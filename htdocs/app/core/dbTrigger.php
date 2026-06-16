<?php

class DbTrigger
{
    /**
     * Simulates `trg_pembayaran_dp_selesai`
     */
    public static function trigger_pembayaran_dp_selesai(PDO $db, int $id_pembayaran_dp): void
    {
        $stmt = $db->prepare('SELECT status, tanggal_pembayaran, no_transaksi FROM pembayaran_dp WHERE id_pembayaran_dp = ?');
        $stmt->execute([$id_pembayaran_dp]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['status'] === 'Selesai') {
            $updates = [];
            $params = [];

            if (empty($row['tanggal_pembayaran'])) {
                $updates[] = 'tanggal_pembayaran = NOW()';
            }

            if (empty($row['no_transaksi'])) {
                $no_trx = DbFunction::generate_no_transaksi('TRX-DP', $id_pembayaran_dp);
                $updates[] = 'no_transaksi = ?';
                $params[] = $no_trx;
            }

            if (!empty($updates)) {
                $params[] = $id_pembayaran_dp;
                $sql = 'UPDATE pembayaran_dp SET ' . implode(', ', $updates) . ' WHERE id_pembayaran_dp = ?';
                $stmtUpdate = $db->prepare($sql);
                $stmtUpdate->execute($params);
            }
        }
    }

    /**
     * Simulates `trg_pembayaran_full_selesai`
     */
    public static function trigger_pembayaran_full_selesai(PDO $db, int $id_pembayaran_full): void
    {
        $stmt = $db->prepare('SELECT status, tanggal_pembayaran, no_transaksi FROM pembayaran_full WHERE id_pembayaran_full = ?');
        $stmt->execute([$id_pembayaran_full]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['status'] === 'Selesai') {
            $updates = [];
            $params = [];

            if (empty($row['tanggal_pembayaran'])) {
                $updates[] = 'tanggal_pembayaran = NOW()';
            }

            if (empty($row['no_transaksi'])) {
                $no_trx = DbFunction::generate_no_transaksi('TRX-FULL', $id_pembayaran_full);
                $updates[] = 'no_transaksi = ?';
                $params[] = $no_trx;
            }

            if (!empty($updates)) {
                $params[] = $id_pembayaran_full;
                $sql = 'UPDATE pembayaran_full SET ' . implode(', ', $updates) . ' WHERE id_pembayaran_full = ?';
                $stmtUpdate = $db->prepare($sql);
                $stmtUpdate->execute($params);
            }
        }
    }
}
