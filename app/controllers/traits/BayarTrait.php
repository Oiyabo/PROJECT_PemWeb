<?php

trait BayarTrait
{
    public function midtranssnap(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 400);
        }

        $userId = (int) $_SESSION['user']['id'];
        $tipe = strtoupper(trim($_POST['tipe'] ?? ''));
        $pembayaranModel = $this->model('PembayaranModel');
        $PendingModel = $this->model('PendingModel');
        $midtrans = new MidtransService();

        try {
            if ($tipe === 'DP_PRE') {
                $jenisKendaraan = trim($_POST['jenis_kendaraan'] ?? '');
                $layananIds = $_POST['layanan_id'] ?? [];

                if (!in_array($jenisKendaraan, ['Motor', 'Mobil'], true) || empty($layananIds)) {
                    throw new InvalidArgumentException('Data layanan tidak lengkap');
                }

                $nominal = (int) $pembayaranModel->hitungHargaDariLayanan($layananIds, $jenisKendaraan)['total_dp'];
                if ($nominal <= 0) {
                    throw new InvalidArgumentException('Nominal DP tidak valid');
                }

                $orderId = $PendingModel->generateOrderId('BKP-DP-PRE', $userId);
                $idDp = $PendingModel->buatPembayaranDpPending(null, $nominal, $orderId);

                $PendingModel->simpanPendingMidtrans([
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'tipe' => 'DP_PRE',
                    'layanan_ids' => implode(',', array_map('intval', $layananIds)),
                    'jenis_kendaraan' => $jenisKendaraan,
                    'nominal' => $nominal,
                ]);
                $PendingModel->linkPendingToPembayaran($orderId, 'DP_PRE', $idDp);

                $this->kirimSnapResponse(
                    $midtrans,
                    $PendingModel,
                    $orderId,
                    $nominal,
                    $midtrans->buildSnapPayload($orderId, $nominal, $_SESSION['user'], 'DP Reservasi Bengkel'),
                    'pembayaran_dp'
                );
            }

            if ($tipe === 'FULL') {
                $idReservasi = (int) ($_POST['id_reservasi'] ?? 0);
                if (!$idReservasi) {
                    throw new InvalidArgumentException('Reservasi tidak ditemukan');
                }

                $db = getDB();
                $stmt = $db->prepare('SELECT user_id FROM reservasi WHERE id_reservasi = ?');
                $stmt->execute([$idReservasi]);
                $reservasi = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$reservasi || (int) $reservasi['user_id'] !== $userId) {
                    $this->jsonResponse(['success' => false, 'message' => 'Akses ditolak'], 403);
                }

                if ($pembayaranModel->sudahBayarFull($idReservasi)) {
                    $this->jsonResponse(['success' => false, 'message' => 'Pembayaran full sudah selesai']);
                }

                $harga = $pembayaranModel->hitungTotalHarga($idReservasi);
                $jenisKendaraan = $harga['jenis_kendaraan'] ?: $pembayaranModel->deteksiJenisKendaraan($idReservasi);
                $nominal = (int) $harga['total_sisa'];
                if ($nominal <= 0) {
                    $nominal = max(0, (int) $harga['total_full'] - (int) $harga['total_dp']);
                }

                $orderId = $PendingModel->generateOrderId('BKP-FULL', $userId);
                $idFull = $PendingModel->buatPembayaranFullPending($idReservasi, $nominal, $orderId);

                $PendingModel->simpanPendingMidtrans([
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'tipe' => 'FULL',
                    'id_reservasi' => $idReservasi,
                    'jenis_kendaraan' => $jenisKendaraan,
                    'nominal' => $nominal,
                ]);
                $PendingModel->linkPendingToPembayaran($orderId, 'FULL', $idFull);

                $this->kirimSnapResponse(
                    $midtrans,
                    $PendingModel,
                    $orderId,
                    $nominal,
                    $midtrans->buildSnapPayload(
                        $orderId,
                        $nominal,
                        $_SESSION['user'],
                        'Pelunasan Service #' . $idReservasi
                    ),
                    'pembayaran_full'
                );
            }
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cekpembayaran(): void
    {
        $orderId = trim($_GET['order_id'] ?? '');
        $userId = (int) $_SESSION['user']['id'];
        $PendingModel = $this->model('PendingModel');

        $paid = false;
        if (
            $orderId !== ''
            && $PendingModel->orderMilikiUser($orderId, $userId)
        ) {
            $paid = $PendingModel->isOrderPaid($orderId)
                || $PendingModel->sinkronkanDariMidtrans($orderId);
        }

        $this->jsonResponse(['paid' => $paid]);
    }

    public function riwayatpembayaran(): void
    {
        $userId = (int) $_SESSION['user']['id'];
        $RiwayatModel = $this->model('RiwayatModel');
        $transaksi = $RiwayatModel->getRiwayatLunasByUser($userId);

        $data = [
            'title' => 'Riwayat Pembayaran',
            'user' => $_SESSION['user'],
            'transaksi' => $transaksi,
        ];

        $this->view('templates/header', $data);
        $this->view('pelanggan/riwayat-pembayaran', $data);
        $this->view('templates/footer', $data);
    }

    public function detailpembayaran(int $id = 0): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $userId = (int) $_SESSION['user']['id'];
        $idReservasi = $id > 0 ? $id : (int) ($_GET['id'] ?? 0);

        if ($idReservasi <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID reservasi tidak valid'], 400);
        }

        $RiwayatModel = $this->model('RiwayatModel');
        $detail = $RiwayatModel->getRiwayatDetailByReservasi($idReservasi, $userId);

        if (!$detail) {
            $this->jsonResponse(['success' => false, 'message' => 'Transaksi tidak ditemukan'], 404);
        }

        $this->jsonResponse(['success' => true, 'data' => $detail]);
    }

    public function strukpembayaran(int $id = 0): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $userId = (int) $_SESSION['user']['id'];
        $idReservasi = $id > 0 ? $id : (int) ($_GET['id'] ?? 0);

        if ($idReservasi <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID reservasi tidak valid'], 400);
        }

        $RiwayatModel = $this->model('RiwayatModel');
        $struk = $RiwayatModel->getStrukByReservasi($idReservasi, $userId);

        if (!$struk) {
            $this->jsonResponse(['success' => false, 'message' => 'Struk tidak ditemukan'], 404);
        }

        $this->jsonResponse(['success' => true, 'data' => $struk]);
    }

    public function bayar(): void
    {
        $userId = (int) $_SESSION['user']['id'];
        $RiwayatModel = $this->model('RiwayatModel');
        $midtrans = new MidtransService();

        $reservasiSelesai = $RiwayatModel->getReservasiSelesaiUnpaidFull($userId);

        $data = [
            'title' => 'Pembayaran Sisanya',
            'user' => $_SESSION['user'],
            'reservasi' => $reservasiSelesai,
            'midtrans_client_key' => $midtrans->getClientKey(),
            'midtrans_snap_script' => $midtrans->getSnapScriptUrl(),
        ];

        $this->view('templates/header', $data);
        $this->view('pelanggan/bayar', $data);
        $this->view('templates/footer', $data);
    }

    public function prosesPembayaran(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];
        $idReservasi = (int) ($_POST['id_reservasi'] ?? 0);
        $tipePembayaran = trim($_POST['tipe_pembayaran'] ?? '');
        $metodePembayaran = trim($_POST['metode_pembayaran'] ?? 'e_wallet');
        $jenisKendaraan = trim($_POST['jenis_kendaraan'] ?? '');

        if (!$idReservasi || !$tipePembayaran) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Data pembayaran tidak lengkap']);
            exit;
        }

        if (!in_array($tipePembayaran, ['DP', 'FULL'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tipe pembayaran tidak valid']);
            exit;
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT user_id FROM reservasi WHERE id_reservasi = ?');
        $stmt->execute([$idReservasi]);
        $reservasi = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservasi || (int) $reservasi['user_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
            exit;
        }

        $pembayaranModel = $this->model('PembayaranModel');

        if ($jenisKendaraan === '') {
            $jenisKendaraan = $pembayaranModel->deteksiJenisKendaraan($idReservasi);
        }

        try {
            if ($tipePembayaran === 'DP') {
                if ($pembayaranModel->sudahBayarDP($idReservasi)) {
                    echo json_encode(['success' => false, 'message' => 'DP sudah dibayar']);
                    exit;
                }
                $hasil = $pembayaranModel->prosesPembayaranDP(
                    $idReservasi,
                    $jenisKendaraan,
                    $metodePembayaran ?: 'e_wallet'
                );
                echo json_encode([
                    'success' => true,
                    'message' => 'Pembayaran DP berhasil diproses',
                    'id_pembayaran' => $hasil['id_pembayaran_dp'],
                    'nominal' => $hasil['nominal'],
                    'no_transaksi' => $hasil['no_transaksi'],
                ]);
            } else {
                if ($pembayaranModel->sudahBayarFull($idReservasi)) {
                    echo json_encode(['success' => false, 'message' => 'Pembayaran full sudah selesai']);
                    exit;
                }
                $hasil = $pembayaranModel->prosesPembayaranFull(
                    $idReservasi,
                    $jenisKendaraan,
                    $metodePembayaran ?: 'e_wallet'
                );
                echo json_encode([
                    'success' => true,
                    'message' => 'Pembayaran full berhasil diproses',
                    'id_pembayaran' => $hasil['id_pembayaran_full'],
                    'nominal' => $hasil['nominal'],
                    'nominal_sisa' => $hasil['nominal_sisa'],
                    'no_transaksi' => $hasil['no_transaksi'],
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }

        exit;
    }
}
