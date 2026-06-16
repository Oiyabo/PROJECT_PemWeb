<?php

class DbView
{
    public static function v_pelanggan(): string
    {
        return "(SELECT id, nama, email, role, created_at FROM users WHERE role = 'Pelanggan')";
    }

    public static function v_reservasi_detail(): string
    {
        return "(SELECT
            r.id_reservasi, r.user_id, r.kendaraan, r.plat, r.tanggal, r.jam, r.catatan, r.status, r.created_at,
            u.nama, u.email,
            GROUP_CONCAT(l.nama_layanan ORDER BY l.nama_layanan ASC SEPARATOR ', ') AS layanan
        FROM reservasi r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN reservasi_layanan rl ON r.id_reservasi = rl.id_reservasi
        LEFT JOIN layanan l ON rl.layanan_id = l.layanan_id
        GROUP BY r.id_reservasi, r.user_id, r.kendaraan, r.plat, r.tanggal, r.jam, r.catatan, r.status, r.created_at, u.nama, u.email)";
    }

    public static function v_jenis_kendaraan(): string
    {
        return "(SELECT 
            r.id_reservasi,
            CASE 
                WHEN r.jenis_kendaraan IN ('Motor', 'Mobil') THEN r.jenis_kendaraan
                ELSE (
                    SELECT 
                        CASE 
                            WHEN COALESCE(SUM(l2.harga_mobil_full), 0) > COALESCE(SUM(l2.harga_motor_full), 0) THEN 'Mobil'
                            WHEN COALESCE(SUM(l2.harga_motor_full), 0) > 0 THEN 'Motor'
                            ELSE 'Mobil'
                        END
                    FROM reservasi_layanan rl2
                    JOIN layanan l2 ON rl2.layanan_id = l2.layanan_id
                    WHERE rl2.id_reservasi = r.id_reservasi
                )
            END AS jenis_kendaraan
        FROM reservasi r)";
    }

    public static function v_status_lunas(): string
    {
        return "(SELECT
            r.id_reservasi,
            CASE WHEN EXISTS (
                SELECT 1 FROM pembayaran_dp pd WHERE pd.id_reservasi = r.id_reservasi AND pd.status = 'Selesai'
            ) THEN 1 ELSE 0 END AS sudah_bayar_dp,
            CASE WHEN EXISTS (
                SELECT 1 FROM pembayaran_full pf WHERE pf.id_reservasi = r.id_reservasi AND pf.status = 'Selesai'
            ) THEN 1 ELSE 0 END AS sudah_bayar_full
        FROM reservasi r)";
    }

    public static function v_reservasi_harga(): string
    {
        return "(SELECT
            r.id_reservasi,
            jk.jenis_kendaraan,
            COALESCE(SUM(l.dp_motor), 0) AS total_dp_motor,
            COALESCE(SUM(l.harga_motor_full), 0) AS total_full_motor,
            COALESCE(SUM(l.dp_mobil), 0) AS total_dp_mobil,
            COALESCE(SUM(l.harga_mobil_full), 0) AS total_full_mobil,
            CASE jk.jenis_kendaraan WHEN 'Motor' THEN COALESCE(SUM(l.dp_motor), 0) ELSE COALESCE(SUM(l.dp_mobil), 0) END AS total_dp,
            CASE jk.jenis_kendaraan WHEN 'Motor' THEN COALESCE(SUM(l.harga_motor_full), 0) ELSE COALESCE(SUM(l.harga_mobil_full), 0) END AS total_full,
            CASE jk.jenis_kendaraan WHEN 'Motor' THEN COALESCE(SUM(l.harga_motor_full), 0) - COALESCE(SUM(l.dp_motor), 0) ELSE COALESCE(SUM(l.harga_mobil_full), 0) - COALESCE(SUM(l.dp_mobil), 0) END AS total_sisa
        FROM reservasi r
        JOIN " . self::v_jenis_kendaraan() . " jk ON r.id_reservasi = jk.id_reservasi
        LEFT JOIN reservasi_layanan rl ON r.id_reservasi = rl.id_reservasi
        LEFT JOIN layanan l ON rl.layanan_id = l.layanan_id
        GROUP BY r.id_reservasi, jk.jenis_kendaraan)";
    }

    public static function v_reservasi_unpaid_full(): string
    {
        return "(SELECT
            r.id_reservasi, r.user_id, r.kendaraan, r.plat, r.tanggal, r.jam, r.catatan, r.status, r.created_at,
            GROUP_CONCAT(l.nama_layanan ORDER BY l.nama_layanan ASC SEPARATOR ', ') AS layanan,
            h.jenis_kendaraan, h.total_dp, h.total_full, h.total_sisa
        FROM reservasi r
        JOIN " . self::v_reservasi_harga() . " h ON r.id_reservasi = h.id_reservasi
        JOIN " . self::v_status_lunas() . " sl ON r.id_reservasi = sl.id_reservasi
        LEFT JOIN reservasi_layanan rl ON r.id_reservasi = rl.id_reservasi
        LEFT JOIN layanan l ON rl.layanan_id = l.layanan_id
        WHERE r.status = 'Selesai' AND sl.sudah_bayar_full = 0
        GROUP BY r.id_reservasi, r.user_id, r.kendaraan, r.plat, r.tanggal, r.jam, r.catatan, r.status, r.created_at, h.jenis_kendaraan, h.total_dp, h.total_full, h.total_sisa)";
    }

    public static function v_pembayaran_pending(): string
    {
        return "(SELECT
            pd.id_pembayaran_dp AS id_pembayaran, pd.id_reservasi, pd.nominal, pd.status, pd.metode_pembayaran, pd.no_transaksi, pd.tanggal_pembayaran, pd.created_at, pd.updated_at,
            r.kendaraan, r.plat, r.tanggal AS tanggal_reservasi, r.jam, r.status AS reservasi_status, r.user_id, 'DP' AS tipe
        FROM pembayaran_dp pd
        JOIN reservasi r ON pd.id_reservasi = r.id_reservasi
        WHERE pd.status = 'Pending' AND r.status IN ('Menunggu', 'Selesai')
        UNION ALL
        SELECT
            pf.id_pembayaran_full AS id_pembayaran, pf.id_reservasi, pf.nominal, pf.status, pf.metode_pembayaran, pf.no_transaksi, pf.tanggal_pembayaran, pf.created_at, pf.updated_at,
            r.kendaraan, r.plat, r.tanggal AS tanggal_reservasi, r.jam, r.status AS reservasi_status, r.user_id, 'FULL' AS tipe
        FROM pembayaran_full pf
        JOIN reservasi r ON pf.id_reservasi = r.id_reservasi
        WHERE pf.status = 'Pending' AND r.status IN ('Menunggu', 'Selesai'))";
    }

    public static function v_riwayat_pembayaran_lunas(): string
    {
        return "(SELECT
            r.id_reservasi, r.user_id, r.kendaraan, r.plat, r.tanggal, r.jam, r.status AS status_reservasi,
            h.jenis_kendaraan, h.total_dp AS harga_dp, h.total_full AS harga_full,
            pd.nominal AS nominal_dp_dibayar, pf.nominal AS nominal_full_dibayar, pf.tanggal_pembayaran AS tanggal_lunas
        FROM reservasi r
        INNER JOIN " . self::v_reservasi_harga() . " h ON h.id_reservasi = r.id_reservasi
        JOIN " . self::v_status_lunas() . " sl ON sl.id_reservasi = r.id_reservasi
        LEFT JOIN pembayaran_dp pd ON pd.id_reservasi = r.id_reservasi AND pd.status = 'Selesai'
        LEFT JOIN pembayaran_full pf ON pf.id_reservasi = r.id_reservasi AND pf.status = 'Selesai'
        WHERE sl.sudah_bayar_dp = 1 AND sl.sudah_bayar_full = 1)";
    }

    public static function v_riwayat_pembayaran_detail(): string
    {
        return "(SELECT
            rd.id_reservasi, rd.user_id, rd.kendaraan, rd.plat, rd.tanggal, rd.jam, rd.catatan, rd.status AS status_reservasi, rd.created_at, rd.nama AS nama_pelanggan, rd.email AS email_pelanggan, rd.layanan,
            h.jenis_kendaraan, h.total_dp AS harga_dp, h.total_full AS harga_full, h.total_sisa AS harga_sisa,
            pd.id_pembayaran_dp, pd.nominal AS nominal_dp, pd.no_transaksi AS no_transaksi_dp, pd.order_id AS order_id_dp, pd.metode_pembayaran AS metode_dp, pd.payment_channel AS channel_dp, pd.tanggal_pembayaran AS tanggal_bayar_dp,
            pf.id_pembayaran_full, pf.nominal AS nominal_full, pf.no_transaksi AS no_transaksi_full, pf.order_id AS order_id_full, pf.metode_pembayaran AS metode_full, pf.payment_channel AS channel_full, pf.tanggal_pembayaran AS tanggal_bayar_full
        FROM " . self::v_reservasi_detail() . " rd
        INNER JOIN " . self::v_reservasi_harga() . " h ON h.id_reservasi = rd.id_reservasi
        JOIN " . self::v_status_lunas() . " sl ON sl.id_reservasi = rd.id_reservasi
        LEFT JOIN pembayaran_dp pd ON pd.id_reservasi = rd.id_reservasi AND pd.status = 'Selesai'
        LEFT JOIN pembayaran_full pf ON pf.id_reservasi = rd.id_reservasi AND pf.status = 'Selesai'
        WHERE sl.sudah_bayar_dp = 1 AND sl.sudah_bayar_full = 1)";
    }

    public static function v_struk_pembayaran(): string
    {
        return "(SELECT
            r.id_reservasi, r.user_id, u.nama AS nama_pelanggan, r.kendaraan, r.plat, jk.jenis_kendaraan, r.tanggal, r.jam,
            l.layanan_id, l.nama_layanan, l.kategori,
            (CASE jk.jenis_kendaraan WHEN 'Motor' THEN COALESCE(l.dp_motor, 0) ELSE COALESCE(l.dp_mobil, 0) END) AS harga_dp,
            (CASE jk.jenis_kendaraan WHEN 'Motor' THEN COALESCE(l.harga_motor_full, 0) ELSE COALESCE(l.harga_mobil_full, 0) END) AS harga_full,
            (CASE jk.jenis_kendaraan WHEN 'Motor' THEN COALESCE(l.harga_motor_full, 0) - COALESCE(l.dp_motor, 0) ELSE COALESCE(l.harga_mobil_full, 0) - COALESCE(l.dp_mobil, 0) END) AS harga_sisa,
            pd.no_transaksi AS no_transaksi_dp, pf.no_transaksi AS no_transaksi_full, pf.tanggal_pembayaran AS tanggal_struk
        FROM reservasi r
        INNER JOIN users u ON u.id = r.user_id
        JOIN " . self::v_jenis_kendaraan() . " jk ON jk.id_reservasi = r.id_reservasi
        JOIN " . self::v_status_lunas() . " sl ON sl.id_reservasi = r.id_reservasi
        INNER JOIN reservasi_layanan rl ON rl.id_reservasi = r.id_reservasi
        INNER JOIN layanan l ON l.layanan_id = rl.layanan_id
        LEFT JOIN pembayaran_dp pd ON pd.id_reservasi = r.id_reservasi AND pd.status = 'Selesai'
        LEFT JOIN pembayaran_full pf ON pf.id_reservasi = r.id_reservasi AND pf.status = 'Selesai'
        WHERE sl.sudah_bayar_dp = 1 AND sl.sudah_bayar_full = 1)";
    }
}
