<?php

class DbProcedure
{
    /**
     * Replaces `sp_hitung_harga_layanan`
     */
    public static function hitung_harga_layanan(PDO $db, array $layanan_ids, string $jenis): array
    {
        if (empty($layanan_ids)) {
            return ['total_dp' => 0, 'total_full' => 0];
        }

        $placeholders = implode(',', array_fill(0, count($layanan_ids), '?'));

        if ($jenis === 'Motor') {
            $sql = "SELECT
                        COALESCE(SUM(dp_motor), 0) as total_dp,
                        COALESCE(SUM(harga_motor_full), 0) as total_full
                    FROM layanan
                    WHERE layanan_id IN ($placeholders)";
        } else {
            $sql = "SELECT
                        COALESCE(SUM(dp_mobil), 0) as total_dp,
                        COALESCE(SUM(harga_mobil_full), 0) as total_full
                    FROM layanan
                    WHERE layanan_id IN ($placeholders)";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($layanan_ids);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_dp' => (int) ($row['total_dp'] ?? 0),
            'total_full' => (int) ($row['total_full'] ?? 0)
        ];
    }

    /**
     * Replaces `sp_upsert_hubung`
     */
    public static function upsert_hubung(PDO $db, int $id_reservasi, ?int $id_dp, ?int $id_full): void
    {
        $stmtCheck = $db->prepare('SELECT 1 FROM pembayaran_hubung WHERE id_reservasi = ? LIMIT 1');
        $stmtCheck->execute([$id_reservasi]);
        $exists = $stmtCheck->fetchColumn();

        if ($exists) {
            $updates = [];
            $params = [];
            if ($id_dp !== null) {
                $updates[] = 'id_pembayaran_dp = ?';
                $params[] = $id_dp;
            }
            if ($id_full !== null) {
                $updates[] = 'id_pembayaran_full = ?';
                $params[] = $id_full;
            }

            if (empty($updates)) {
                return;
            }

            $params[] = $id_reservasi;
            $sql = 'UPDATE pembayaran_hubung SET ' . implode(', ', $updates) . ' WHERE id_reservasi = ?';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $db->prepare('INSERT INTO pembayaran_hubung (id_reservasi, id_pembayaran_dp, id_pembayaran_full) VALUES (?, ?, ?)');
            $stmt->execute([$id_reservasi, $id_dp, $id_full]);
        }
    }

    /**
     * Replaces `sp_proses_pembayaran_dp`
     */
    public static function proses_pembayaran_dp(PDO $db, int $id_reservasi, string $jenis, string $metode): array
    {
        $nominal = DbFunction::hitung_dp_reservasi($db, $id_reservasi, $jenis);

        if ($nominal <= 0) {
            throw new Exception('Nominal DP tidak valid');
        }

        $metode = $metode ?: 'midtrans';

        $stmt = $db->prepare('INSERT INTO pembayaran_dp (id_reservasi, nominal, status, metode_pembayaran) VALUES (?, ?, ?, ?)');
        $stmt->execute([$id_reservasi, $nominal, 'Pending', $metode]);

        $id_dp = (int) $db->lastInsertId();

        self::upsert_hubung($db, $id_reservasi, $id_dp, null);

        return [
            'id_dp' => $id_dp,
            'nominal' => $nominal,
            'no_trx' => null
        ];
    }

    /**
     * Replaces `sp_proses_pembayaran_full`
     */
    public static function proses_pembayaran_full(PDO $db, int $id_reservasi, string $jenis, string $metode): array
    {
        $nominal = DbFunction::hitung_full_reservasi($db, $id_reservasi, $jenis);
        $dp_sudah = DbFunction::hitung_dp_reservasi($db, $id_reservasi, $jenis);
        $nominal_sisa = $nominal - $dp_sudah;

        if ($nominal <= 0) {
            throw new Exception('Nominal Full tidak valid');
        }

        $stmtCekHubung = $db->prepare('SELECT id_pembayaran_dp FROM pembayaran_hubung WHERE id_reservasi = ? LIMIT 1');
        $stmtCekHubung->execute([$id_reservasi]);
        $v_id_dp = $stmtCekHubung->fetchColumn() ?: null;

        $metode = $metode ?: 'midtrans';

        $stmt = $db->prepare('INSERT INTO pembayaran_full (id_reservasi, nominal, status, metode_pembayaran) VALUES (?, ?, ?, ?)');
        $stmt->execute([$id_reservasi, $nominal, 'Pending', $metode]);

        $id_full = (int) $db->lastInsertId();

        self::upsert_hubung($db, $id_reservasi, $v_id_dp, $id_full);

        return [
            'id_full' => $id_full,
            'nominal' => $nominal,
            'nominal_sisa' => $nominal_sisa,
            'no_trx' => null
        ];
    }
}
