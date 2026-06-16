<?php

class PendingModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function generateOrderId(string $prefix, int $userId): string
    {
        return sprintf('%s-%d-%s', $prefix, $userId, bin2hex(random_bytes(6)));
    }

    public function simpanPendingMidtrans(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pembayaran_midtrans_pending (order_id, user_id, tipe, id_reservasi, layanan_ids, jenis_kendaraan, nominal, snap_token, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $data['order_id'],
            $data['user_id'],
            $data['tipe'],
            $data['id_reservasi'] ?? null,
            $data['layanan_ids'] ?? null,
            $data['jenis_kendaraan'] ?? null,
            $data['nominal'],
            $data['snap_token'] ?? null,
            'pending',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getPendingByOrderId(string $orderId)
    {
        $stmt = $this->db->prepare('SELECT * FROM pembayaran_midtrans_pending WHERE order_id = ? LIMIT 1');
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function orderMilikiUser(string $orderId, int $userId): bool
    {
        $pending = $this->getPendingByOrderId($orderId);
        return $pending && (int) $pending['user_id'] === $userId;
    }

    public function dpPreBisaDihubungkan(string $orderId, int $userId): bool
    {
        if (!$this->orderMilikiUser($orderId, $userId)) {
            return false;
        }
        $pending = $this->getPendingByOrderId($orderId);
        if (!$pending || $pending['tipe'] !== 'DP_PRE') {
            return false;
        }
        $dp = $this->getPembayaranDpByOrderId($orderId);
        if (!$dp || !empty($dp['id_reservasi'])) {
            return false;
        }
        return $this->isOrderPaid($orderId);
    }

    public function updatePendingSnapToken(string $orderId, string $snapToken): void
    {
        $stmt = $this->db->prepare(
            'UPDATE pembayaran_midtrans_pending SET snap_token = ? WHERE order_id = ?'
        );
        $stmt->execute([$snapToken, $orderId]);
    }

    public function updatePendingStatus(string $orderId, string $status): void
    {
        $stmt = $this->db->prepare(
            'UPDATE pembayaran_midtrans_pending SET status = ? WHERE order_id = ?'
        );
        $stmt->execute([$status, $orderId]);
    }

    public function buatPembayaranDpPending(
        ?int $reservasiId,
        int $nominal,
        string $orderId,
        ?string $snapToken = null
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO pembayaran_dp (id_reservasi, nominal, status, metode_pembayaran, order_id, snap_token) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$reservasiId, $nominal, 'Pending', 'midtrans', $orderId, $snapToken]);
        return (int) $this->db->lastInsertId();
    }

    public function buatPembayaranFullPending(
        int $reservasiId,
        int $nominal,
        string $orderId,
        ?string $snapToken = null
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO pembayaran_full (id_reservasi, nominal, status, metode_pembayaran, order_id, snap_token) VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([$reservasiId, $nominal, 'Pending', 'midtrans', $orderId, $snapToken]);
        return (int) $this->db->lastInsertId();
    }

    public function linkPendingToPembayaran(string $orderId, string $tipe, int $idPembayaran): void
    {
        if ($tipe === 'DP_PRE' || $tipe === 'DP') {
            $stmt = $this->db->prepare(
                'UPDATE pembayaran_midtrans_pending SET id_pembayaran_dp = ? WHERE order_id = ?'
            );

            $stmt->execute([$idPembayaran, $orderId]);
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE pembayaran_midtrans_pending SET id_pembayaran_full = ? WHERE order_id = ?'
        );

        $stmt->execute([$idPembayaran, $orderId]);
    }

    public function konfirmasiPembayaranDp(
        string $orderId,
        string $transactionId,
        string $paymentType,
        string $midtransStatus
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE pembayaran_dp SET status = ?, no_transaksi = ?, metode_pembayaran = ?, payment_channel = ?, midtrans_status = ?, tanggal_pembayaran = NOW() WHERE order_id = ? AND status = ?'
        );

        $localStatus = $this->mapMidtransToPembayaranStatus($midtransStatus);

        $ok = $stmt->execute([
            $localStatus,
            $transactionId,
            'midtrans',
            $paymentType,
            $midtransStatus,
            $orderId,
            'Pending',
        ]) && $stmt->rowCount() > 0;

        if ($ok && $localStatus === 'Selesai') {
            $row = $this->getPembayaranDpByOrderId($orderId);
            if ($row) {
                DbTrigger::trigger_pembayaran_dp_selesai($this->db, (int)$row['id_pembayaran_dp']);
            }
        }

        return $ok;
    }

    public function konfirmasiPembayaranFull(
        string $orderId,
        string $transactionId,
        string $paymentType,
        string $midtransStatus
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE pembayaran_full SET status = ?, no_transaksi = ?, metode_pembayaran = ?, payment_channel = ?, midtrans_status = ?, tanggal_pembayaran = NOW() WHERE order_id = ? AND status = ?'
        );

        $localStatus = $this->mapMidtransToPembayaranStatus($midtransStatus);

        $ok = $stmt->execute([
            $localStatus,
            $transactionId,
            'midtrans',
            $paymentType,
            $midtransStatus,
            $orderId,
            'Pending',
        ]) && $stmt->rowCount() > 0;

        if ($ok && $localStatus === 'Selesai') {
            $row = $this->getPembayaranFullByOrderId($orderId);

            if ($row) {
                DbTrigger::trigger_pembayaran_full_selesai($this->db, (int) $row['id_pembayaran_full']);
                
                DbProcedure::upsert_hubung($this->db, (int) $row['id_reservasi'], null, (int) $row['id_pembayaran_full']);

                $stmtUpdate = $this->db->prepare(
                    "UPDATE reservasi SET status = 'Terbayar' WHERE id_reservasi = ?"
                );
                $stmtUpdate->execute([ $row['id_reservasi'] ]);

                $stmtDetails = $this->db->prepare("
                    SELECT r.kendaraan, r.plat, u.nama 
                    FROM reservasi r 
                    JOIN users u ON r.user_id = u.id 
                    WHERE r.id_reservasi = ?
                ");
                $stmtDetails->execute([(int) $row['id_reservasi']]);
                $resDetails = $stmtDetails->fetch(PDO::FETCH_ASSOC);

                if ($resDetails) {
                    require_once ROOT_PATH . '/app/models/NotifikasiModel.php';
                    $notifModel = new NotifikasiModel();
                    $notifModel->create(
                        null,
                        'Admin',
                        'Pembayaran Lunas',
                        'Pelanggan ' . $resDetails['nama'] . ' telah membayar lunas untuk ' . $resDetails['kendaraan'] . ' (' . $resDetails['plat'] . ')',
                        BASEURL . '/admin/reservasi?status=Terbayar'
                    );
                }
            }
        }
        return $ok;
    }

    public function getPembayaranDpByOrderId(string $orderId)
    {
        $stmt = $this->db->prepare('SELECT * FROM pembayaran_dp WHERE order_id = ? LIMIT 1');
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPembayaranFullByOrderId(string $orderId)
    {
        $stmt = $this->db->prepare('SELECT * FROM pembayaran_full WHERE order_id = ? LIMIT 1');
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isOrderPaid(string $orderId): bool
    {
        $pending = $this->getPendingByOrderId($orderId);
        if ($pending && in_array($pending['status'], ['settlement', 'capture'], true)) {
            return true;
        }

        $dp = $this->getPembayaranDpByOrderId($orderId);
        if ($dp && $dp['status'] === 'Selesai') {
            return true;
        }

        $full = $this->getPembayaranFullByOrderId($orderId);
        return $full && $full['status'] === 'Selesai';
    }

    public function sinkronkanDariMidtrans(string $orderId): bool
    {
        if ($this->isOrderPaid($orderId)) {
            return true;
        }

        $midtrans = new MidtransService();

        try {
            $status = $midtrans->getTransactionStatus($orderId);
        } catch (Throwable) {
            return false;
        }

        $transactionStatus = $status['transaction_status'] ?? '';

        if (!$midtrans->isSettlementStatus($transactionStatus)) {
            return false;
        }

        $this->prosesNotifikasiMidtrans([
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'transaction_id' => $status['transaction_id'] ?? $orderId,
            'payment_type' => $status['payment_type'] ?? 'midtrans',
            'status_code' => (string) ($status['status_code'] ?? '200'),
            'gross_amount' => (string) ($status['gross_amount'] ?? '0'),
            'signature_key' => $status['signature_key'] ?? '',
            'fraud_status' => $status['fraud_status'] ?? 'accept',
        ]);

        return $this->isOrderPaid($orderId);
    }

    private function mapMidtransToPembayaranStatus(string $midtransStatus): string
    {
        if (in_array($midtransStatus, ['capture', 'settlement'], true)) {
            return 'Selesai';
        }
        if (in_array($midtransStatus, ['expire', 'expired'], true)) {
            return 'Expire';
        }
        if (in_array($midtransStatus, ['cancel', 'deny', 'failure'], true)) {
            return 'Gagal';
        }
        return 'Pending';
    }

    public function prosesNotifikasiMidtrans(array $notif): array
    {
        $orderId = $notif['order_id'] ?? '';
        $transactionStatus = $notif['transaction_status'] ?? '';
        $transactionId = $notif['transaction_id'] ?? $orderId;
        $paymentType = $notif['payment_type'] ?? 'midtrans';
        $fraudStatus = $notif['fraud_status'] ?? 'accept';

        if ($orderId === '') {
            return ['handled' => false, 'message' => 'order_id kosong'];
        }

        $pending = $this->getPendingByOrderId($orderId);
        if (!$pending) {
            return ['handled' => false, 'message' => 'Order tidak ditemukan'];
        }

        $localPending = (new MidtransService())->mapTransactionToLocalStatus($transactionStatus);
        $this->updatePendingStatus($orderId, $localPending);

        if ($fraudStatus === 'challenge') {
            return ['handled' => true, 'message' => 'Menunggu verifikasi fraud'];
        }

        if (!in_array($transactionStatus, ['capture', 'settlement'], true)) {
            if (in_array($transactionStatus, ['expire', 'deny', 'cancel', 'failure'], true)) {
                $this->tandaiPembayaranGagal($orderId, $transactionStatus);
            }
            return ['handled' => true, 'message' => 'Status: ' . $transactionStatus];
        }

        $tipe = $pending['tipe'];
        if ($tipe === 'FULL') {
            $this->konfirmasiPembayaranFull($orderId, $transactionId, $paymentType, $transactionStatus);
        } else {
            $this->konfirmasiPembayaranDp($orderId, $transactionId, $paymentType, $transactionStatus);
            $dp = $this->getPembayaranDpByOrderId($orderId);
            if ($dp && !empty($dp['id_reservasi'])) {
                DbProcedure::upsert_hubung($this->db, (int) $dp['id_reservasi'], (int) $dp['id_pembayaran_dp'], null);
            }
        }

        return ['handled' => true, 'message' => 'Pembayaran dikonfirmasi'];
    }

    private function tandaiPembayaranGagal(string $orderId, string $midtransStatus): void
    {
        $status = $this->mapMidtransToPembayaranStatus($midtransStatus);

        $stmt = $this->db->prepare(
            'UPDATE pembayaran_dp SET status = ?, midtrans_status = ? WHERE order_id = ? AND status = ?'
        );
        $stmt->execute([$status, $midtransStatus, $orderId, 'Pending']);

        $stmt = $this->db->prepare(
            'UPDATE pembayaran_full SET status = ?, midtrans_status = ? WHERE order_id = ? AND status = ?'
        );
        $stmt->execute([$status, $midtransStatus, $orderId, 'Pending']);
    }
}
