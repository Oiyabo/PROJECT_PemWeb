<?php

trait PesanTrait
{
    public function buatReservasi(): void
    {
        $ajax = $this->isAjaxRequest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $existingData = $_SESSION['form_reservasi'] ?? [];
            $newData = array_merge($existingData, $_POST);
            if (!isset($newData['layanan_id'])) {
                $newData['layanan_id'] = [];
            }
            $_SESSION['form_reservasi'] = $newData;

            if ($ajax) {
                $tanggal = trim($newData['tanggal'] ?? '');
                $jam = trim($newData['jam'] ?? '');

                if ($tanggal !== '' && $jam !== '') {
                    if ($this->reservasiModel->isJadwalTerisi($tanggal, $jam)) {
                        $this->jsonResponse([
                            'success' => false,
                            'message' => 'Jadwal pada tanggal dan jam tersebut sudah dipesan. Silakan pilih waktu lain.',
                            'jadwal_terisi' => true,
                        ], 409);
                    }
                }

                $layanans = $this->reservasiModel->getLayanan();
                $ringkasan = $this->buildRingkasanHarga($layanans, $newData);
                $this->jsonResponse([
                    'success' => true,
                    'data' => $newData,
                    'ringkasan' => $ringkasan,
                ]);
            }
        }

        $layanans = $this->reservasiModel->getLayanan();

        $layananMap = [];
        foreach ($layanans as $l) {
            $layananMap[$l['layanan_id']] = $l['nama_layanan'];
        }

        $kategoris = array_unique(array_column($layanans, 'kategori'));
        sort($kategoris);

        $midtrans = new MidtransService();
        $formData = $_SESSION['form_reservasi'] ?? [];
        $ringkasanHarga = $this->buildRingkasanHarga($layanans, $formData);

        $data = [
            'title' => 'Buat Reservasi Baru',
            'user' => $_SESSION['user'],
            'layanans' => $layanans,
            'layananMap' => $layananMap,
            'kategoris' => $kategoris,
            'ringkasanHarga' => $ringkasanHarga,
            'midtrans_client_key' => $midtrans->getClientKey(),
            'midtrans_snap_script' => $midtrans->getSnapScriptUrl(),
        ];

        $this->view('templates/header', $data);
        $this->view('pelanggan/buat-reservasi', $data);
        $this->view('templates/footer', $data);
    }

    public function simpanReservasi(): void
    {
        $ajax = $this->isAjaxRequest();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($ajax) {
                $this->jsonResponse(['success' => false, 'message' => 'Metode tidak valid'], 400);
            }
            header('Location: ' . BASEURL . '/pelanggan/buatreservasi');
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];
        $kendaraan = trim($_POST['kendaraan'] ?? '');
        $plat = trim($_POST['plat'] ?? '');
        $layananIds = $_POST['layanan_id'] ?? [];
        $tanggal = trim($_POST['tanggal'] ?? '');
        $jam = trim($_POST['jam'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');

        if (empty($kendaraan) || empty($plat) || empty($layananIds) || empty($tanggal) || empty($jam)) {
            $this->respondSimpan($ajax, false, 'Semua field wajib diisi!', BASEURL . '/pelanggan/buat-reservasi');
        }

        if (!preg_match('/^[A-Za-z]{1,2}\s?\d{1,4}\s?[A-Za-z]{0,3}$/', $plat)) {
            $this->respondSimpan($ajax, false, 'Format plat nomor tidak valid! (Contoh: B 1234 AB)', BASEURL . '/pelanggan/buatreservasi');
        }

        $jenisKendaraan = trim($_POST['jenisKendaraan'] ?? '');
        $dpSudahDibayar = ($_POST['dp_paid'] ?? '') === '1';
        $midtransOrderId = trim($_POST['midtrans_order_id'] ?? '');
        $totalDp = (int) ($_POST['totalDP'] ?? 0);

        if ($dpSudahDibayar && $midtransOrderId === '') {
            $this->respondSimpan($ajax, false, 'Data pembayaran DP tidak valid.', BASEURL . '/pelanggan/buatreservasi');
        }

        if ($dpSudahDibayar) {
            $PendingModel = $this->model('PendingModel');
            if (!$PendingModel->orderMilikiUser($midtransOrderId, $userId)) {
                $this->respondSimpan($ajax, false, 'Data pembayaran DP tidak valid.', BASEURL . '/pelanggan/buatreservasi');
            }
            if (!$PendingModel->isOrderPaid($midtransOrderId)) {
                $this->respondSimpan(
                    $ajax,
                    false,
                    'Pembayaran DP belum dikonfirmasi. Selesaikan pembayaran Midtrans terlebih dahulu.',
                    BASEURL . '/pelanggan/buatreservasi'
                );
            }
            if (!$PendingModel->dpPreBisaDihubungkan($midtransOrderId, $userId)) {
                $this->respondSimpan(
                    $ajax,
                    false,
                    'Pembayaran DP sudah digunakan atau tidak dapat dipakai lagi.',
                    BASEURL . '/pelanggan/buatreservasi'
                );
            }
        }

        if (!in_array($jenisKendaraan, ['Motor', 'Mobil'], true)) {
            $this->respondSimpan($ajax, false, 'Jenis kendaraan wajib dipilih (Motor/Mobil).', BASEURL . '/pelanggan/buatreservasi');
        }

        if ($this->reservasiModel->isJadwalTerisi($tanggal, $jam)) {
            $this->respondSimpan(
                $ajax,
                false,
                'Jadwal pada tanggal dan jam tersebut sudah dipesan. Silakan pilih waktu lain.',
                BASEURL . '/pelanggan/buatreservasi'
            );
        }

        $reservasiId = $this->reservasiModel->create(
            $userId,
            $kendaraan,
            $plat,
            $tanggal,
            $jam,
            $catatan,
            $jenisKendaraan
        );

        if (!$reservasiId) {
            $this->respondSimpan($ajax, false, 'Gagal membuat reservasi. Silakan coba lagi.', BASEURL . '/pelanggan/buatreservasi');
        }

        foreach ($layananIds as $layananId) {
            $this->reservasiModel->tambahLayanan($reservasiId, (int) $layananId);
        }

        if ($dpSudahDibayar && $midtransOrderId !== '') {
            $pembayaranModel = $this->model('PembayaranModel');
            try {
                $pembayaranModel->linkDpPreToReservasi($midtransOrderId, $reservasiId);
            } catch (Exception $e) {
                $msg = 'Reservasi dibuat, tetapi menghubungkan pembayaran DP gagal: ' . $e->getMessage();
                $this->respondSimpan($ajax, false, $msg, BASEURL . '/pelanggan/riwayat');
            }
        }

        require_once ROOT_PATH . '/app/models/NotifikasiModel.php';
        $notifModel = new NotifikasiModel();
        $notifModel->create(
            null,
            'Admin',
            'Reservasi Baru',
            'Pelanggan ' . $_SESSION['user']['nama'] . ' membuat reservasi baru untuk ' . $kendaraan . ' (' . $plat . ')',
            BASEURL . '/admin/reservasi?status=Menunggu'
        );

        unset($_SESSION['form_reservasi']);

        $message = 'Pembayaran DP berhasil dan reservasi telah dibuat. Kami akan segera mengkonfirmasi.';
        $_SESSION['success'] = $message;

        if ($ajax) {
            $this->jsonResponse([
                'success' => true,
                'message' => $message,
                'id_reservasi' => $reservasiId,
                'total_dp' => $totalDp,
                'redirect' => BASEURL . '/pelanggan/riwayat',
            ]);
        }

        header('Location: ' . BASEURL . '/pelanggan/riwayat');
        exit;
    }

    public function riwayat(): void
    {
        $userId = (int) $_SESSION['user']['id'];
        $reservasi = $this->reservasiModel->getByUserId($userId);

        $data = [
            'title' => 'Riwayat Reservasi',
            'user' => $_SESSION['user'],
            'reservasi' => $reservasi,
        ];

        $this->view('templates/header', $data);
        $this->view('pelanggan/riwayat', $data);
        $this->view('templates/footer', $data);
    }

    public function cekjadwal(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $tanggal = trim($_GET['tanggal'] ?? '');
        $jam = trim($_GET['jam'] ?? '');

        if ($tanggal === '' || $jam === '') {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Tanggal dan jam wajib diisi.',
            ], 400);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Format tanggal tidak valid.',
            ], 400);
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $jam)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Format jam tidak valid.',
            ], 400);
        }

        if ($tanggal < date('Y-m-d')) {
            $this->jsonResponse([
                'success' => false,
                'available' => false,
                'jadwal_terisi' => true,
                'message' => 'Tanggal tidak boleh di masa lalu.',
            ]);
        }

        $terisi = $this->reservasiModel->isJadwalTerisi($tanggal, $jam);

        $this->jsonResponse([
            'success' => true,
            'available' => !$terisi,
            'jadwal_terisi' => $terisi,
            'message' => $terisi
                ? 'Jadwal pada tanggal dan jam tersebut sudah dipesan. Silakan pilih waktu lain.'
                : 'Jadwal tersedia.',
        ]);
    }

    public function batalReservasi(): void
    {
        unset($_SESSION['form_reservasi']);
        header('Location: ' . BASEURL . '/pelanggan');
        exit;
    }
}
