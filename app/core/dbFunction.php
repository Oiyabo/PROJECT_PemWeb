<?php

class DbFunction
{
    /**
     * Replaces `fn_generate_no_transaksi`
     */
    public static function generate_no_transaksi(string $prefix, int $id): string
    {
        return $prefix . date('YmdHis') . $id;
    }

    /**
     * Replaces `fn_hitung_dp_reservasi`
     */
    public static function hitung_dp_reservasi(PDO $db, int $id_reservasi, string $jenis): int
    {
        if ($jenis === 'Motor') {
            $sql = 'SELECT COALESCE(SUM(l.dp_motor), 0) as total
                    FROM reservasi_layanan rl
                    JOIN layanan l ON rl.layanan_id = l.layanan_id
                    WHERE rl.id_reservasi = ?';
        } else {
            $sql = 'SELECT COALESCE(SUM(l.dp_mobil), 0) as total
                    FROM reservasi_layanan rl
                    JOIN layanan l ON rl.layanan_id = l.layanan_id
                    WHERE rl.id_reservasi = ?';
        }

        $stmt = $db->prepare($sql);
        $stmt->execute([$id_reservasi]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Replaces `fn_hitung_full_reservasi`
     */
    public static function hitung_full_reservasi(PDO $db, int $id_reservasi, string $jenis): int
    {
        if ($jenis === 'Motor') {
            $sql = 'SELECT COALESCE(SUM(l.harga_motor_full), 0) as total
                    FROM reservasi_layanan rl
                    JOIN layanan l ON rl.layanan_id = l.layanan_id
                    WHERE rl.id_reservasi = ?';
        } else {
            $sql = 'SELECT COALESCE(SUM(l.harga_mobil_full), 0) as total
                    FROM reservasi_layanan rl
                    JOIN layanan l ON rl.layanan_id = l.layanan_id
                    WHERE rl.id_reservasi = ?';
        }

        $stmt = $db->prepare($sql);
        $stmt->execute([$id_reservasi]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Replaces `fn_deteksi_jenis_kendaraan`
     */
    public static function deteksi_jenis_kendaraan(PDO $db, int $id_reservasi): string
    {
        // Get jenis_kendaraan from reservasi table first
        $stmt = $db->prepare('SELECT jenis_kendaraan FROM reservasi WHERE id_reservasi = ? LIMIT 1');
        $stmt->execute([$id_reservasi]);
        $jenis = $stmt->fetchColumn();

        if (in_array($jenis, ['Motor', 'Mobil'], true)) {
            return $jenis;
        }

        // If null, determine from layanan prices
        $sql = 'SELECT
                    COALESCE(SUM(l.harga_motor_full), 0) as total_motor,
                    COALESCE(SUM(l.harga_mobil_full), 0) as total_mobil
                FROM reservasi_layanan rl
                JOIN layanan l ON rl.layanan_id = l.layanan_id
                WHERE rl.id_reservasi = ?';
        $stmt = $db->prepare($sql);
        $stmt->execute([$id_reservasi]);
        $totals = $stmt->fetch(PDO::FETCH_ASSOC);

        $v_motor = (int) ($totals['total_motor'] ?? 0);
        $v_mobil = (int) ($totals['total_mobil'] ?? 0);

        if ($v_mobil > $v_motor) {
            return 'Mobil';
        }
        if ($v_motor > 0) {
            return 'Motor';
        }
        return 'Mobil';
    }

    /**
     * Replaces `fn_sudah_bayar_dp`
     */
    public static function sudah_bayar_dp(PDO $db, int $id_reservasi): bool
    {
        $stmt = $db->prepare('SELECT 1 FROM pembayaran_dp WHERE id_reservasi = ? AND status = ? LIMIT 1');
        $stmt->execute([$id_reservasi, 'Selesai']);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Replaces `fn_sudah_bayar_full`
     */
    public static function sudah_bayar_full(PDO $db, int $id_reservasi): bool
    {
        $stmt = $db->prepare('SELECT 1 FROM pembayaran_full WHERE id_reservasi = ? AND status = ? LIMIT 1');
        $stmt->execute([$id_reservasi, 'Selesai']);
        return (bool) $stmt->fetchColumn();
    }
}
