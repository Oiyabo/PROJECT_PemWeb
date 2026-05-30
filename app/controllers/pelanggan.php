<?php
class Pelanggan extends Controller
{

	private object $reservasiModel;
	private object $pembayaranModel;

	public function __construct()
	{
		$this->requireRole('Pelanggan');
		$this->reservasiModel = $this->model('ReservasiModel');
		$this->pembayaranModel = $this->model('PembayaranModel');
	}

	// Dashboard pelanggan
	public function index(): void
	{
		$userId = $_SESSION['user']['id'];
		$reservasi = $this->reservasiModel->getByUserId($userId);

		$data = [
			'title' => 'Dashboard',
			'user' => $_SESSION['user'],
			'reservasi' => $reservasi,
			'stats' => [
				'total' => count($reservasi),
				'aktif' => count(array_filter($reservasi, fn($r) => in_array($r['status'], ['Menunggu', 'Konfirmasi', 'Proses']))),
				'selesai' => count(array_filter($reservasi, fn($r) => $r['status'] === 'Selesai')),
			],
		];

		$this->view('templates/header', $data);
		$this->view('pelanggan/dashboard', $data);
		$this->view('templates/footer', $data);
	}

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

	// Proses pengiriman form (POST)
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

		$jenisKendaraan = trim($_POST['jenisKendaraan'] ?? '');
		$dpSudahDibayar = ($_POST['dp_paid'] ?? '') === '1';
		$midtransOrderId = trim($_POST['midtrans_order_id'] ?? '');
		$totalDp = (int) ($_POST['totalDP'] ?? 0);

		if ($dpSudahDibayar && $midtransOrderId === '') {
			$this->respondSimpan($ajax, false, 'Data pembayaran DP tidak valid.', BASEURL . '/pelanggan/buatreservasi');
		}

		if ($dpSudahDibayar) {
			$pembayaranModel = $this->model('PembayaranModel');
			if (!$pembayaranModel->orderMilikiUser($midtransOrderId, $userId)) {
				$this->respondSimpan($ajax, false, 'Data pembayaran DP tidak valid.', BASEURL . '/pelanggan/buatreservasi');
			}
			if (!$pembayaranModel->isOrderPaid($midtransOrderId)) {
				$this->respondSimpan(
					$ajax,
					false,
					'Pembayaran DP belum dikonfirmasi. Selesaikan pembayaran Midtrans terlebih dahulu.',
					BASEURL . '/pelanggan/buatreservasi'
				);
			}
			if (!$pembayaranModel->dpPreBisaDihubungkan($midtransOrderId, $userId)) {
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

	// Riwayat reservasi
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

	// Buat token Snap Midtrans (DP pre-reservasi atau FULL)
	public function midtranssnap(): void
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 400);
		}

		$userId = (int) $_SESSION['user']['id'];
		$tipe = strtoupper(trim($_POST['tipe'] ?? ''));
		$pembayaranModel = $this->model('PembayaranModel');
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

				$orderId = $pembayaranModel->generateOrderId('BKP-DP-PRE', $userId);
				$idDp = $pembayaranModel->buatPembayaranDpPending(null, $nominal, $orderId);

				$pembayaranModel->simpanPendingMidtrans([
					'order_id' => $orderId,
					'user_id' => $userId,
					'tipe' => 'DP_PRE',
					'layanan_ids' => implode(',', array_map('intval', $layananIds)),
					'jenis_kendaraan' => $jenisKendaraan,
					'nominal' => $nominal,
				]);
				$pembayaranModel->linkPendingToPembayaran($orderId, 'DP_PRE', $idDp);

				$this->kirimSnapResponse(
					$midtrans,
					$pembayaranModel,
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

				$orderId = $pembayaranModel->generateOrderId('BKP-FULL', $userId);
				$idFull = $pembayaranModel->buatPembayaranFullPending($idReservasi, $nominal, $orderId);

				$pembayaranModel->simpanPendingMidtrans([
					'order_id' => $orderId,
					'user_id' => $userId,
					'tipe' => 'FULL',
					'id_reservasi' => $idReservasi,
					'jenis_kendaraan' => $jenisKendaraan,
					'nominal' => $nominal,
				]);
				$pembayaranModel->linkPendingToPembayaran($orderId, 'FULL', $idFull);

				$this->kirimSnapResponse(
					$midtrans,
					$pembayaranModel,
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

			throw new InvalidArgumentException('Tipe pembayaran tidak valid');
		} catch (Throwable $e) {
			$this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
		}
	}

	/** Cek ketersediaan slot tanggal + jam (AJAX GET). */
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

	public function cekpembayaran(): void
	{
		$orderId = trim($_GET['order_id'] ?? '');
		$userId = (int) $_SESSION['user']['id'];
		$pembayaranModel = $this->model('PembayaranModel');

		$paid = false;
		if (
			$orderId !== ''
			&& $pembayaranModel->orderMilikiUser($orderId, $userId)
		) {
			$paid = $pembayaranModel->isOrderPaid($orderId)
				|| $pembayaranModel->sinkronkanDariMidtrans($orderId);
		}

		$this->jsonResponse(['paid' => $paid]);
	}

	// Riwayat pembayaran yang sudah lunas (DP + full)
	public function riwayatpembayaran(): void
	{
		$userId = (int) $_SESSION['user']['id'];
		$transaksi = $this->pembayaranModel->getRiwayatLunasByUser($userId);

		$data = [
			'title' => 'Riwayat Pembayaran',
			'user' => $_SESSION['user'],
			'transaksi' => $transaksi,
		];

		$this->view('templates/header', $data);
		$this->view('pelanggan/riwayat-pembayaran', $data);
		$this->view('templates/footer', $data);
	}

	// Detail transaksi (JSON, data dari view)
	public function detailpembayaran(int $id = 0): void
	{
		header('Content-Type: application/json; charset=utf-8');

		$userId = (int) $_SESSION['user']['id'];
		$idReservasi = $id > 0 ? $id : (int) ($_GET['id'] ?? 0);

		if ($idReservasi <= 0) {
			$this->jsonResponse(['success' => false, 'message' => 'ID reservasi tidak valid'], 400);
		}

		$detail = $this->pembayaranModel->getRiwayatDetailByReservasi($idReservasi, $userId);

		if (!$detail) {
			$this->jsonResponse(['success' => false, 'message' => 'Transaksi tidak ditemukan'], 404);
		}

		$this->jsonResponse(['success' => true, 'data' => $detail]);
	}

	// Struk resmi per layanan (JSON, dari view v_struk_pembayaran)
	public function strukpembayaran(int $id = 0): void
	{
		header('Content-Type: application/json; charset=utf-8');

		$userId = (int) $_SESSION['user']['id'];
		$idReservasi = $id > 0 ? $id : (int) ($_GET['id'] ?? 0);

		if ($idReservasi <= 0) {
			$this->jsonResponse(['success' => false, 'message' => 'ID reservasi tidak valid'], 400);
		}

		$struk = $this->pembayaranModel->getStrukByReservasi($idReservasi, $userId);

		if (!$struk) {
			$this->jsonResponse(['success' => false, 'message' => 'Struk tidak ditemukan'], 404);
		}

		$this->jsonResponse(['success' => true, 'data' => $struk]);
	}

	// Halaman pembayaran sisa service
	public function bayar(): void
	{
		$userId = (int) $_SESSION['user']['id'];
		$pembayaranModel = $this->model('PembayaranModel');
		$midtrans = new MidtransService();

		$reservasiSelesai = $pembayaranModel->getReservasiSelesaiUnpaidFull($userId);

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

	// Proses pembayaran (DP atau FULL)
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

	private function buildRingkasanHarga(array $layanans, array $formData): array
	{
		$default = ['items' => [], 'total_dp' => 0, 'total_full' => 0, 'total_sisa' => 0];

		if (empty($formData['layanan_id']) || empty($formData['jenisKendaraan'])) {
			return $default;
		}

		$pembayaranModel = $this->model('PembayaranModel');
		return $pembayaranModel->ringkasanHargaLayanan(
			$layanans,
			$formData['layanan_id'],
			$formData['jenisKendaraan']
		);
	}

	private function isAjaxRequest(): bool
	{
		$xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
		return strtolower($xhr) === 'xmlhttprequest' || ($_POST['ajax'] ?? '') === '1';
	}

	private function jsonResponse(array $data, int $code = 200): void
	{
		http_response_code($code);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		exit;
	}

	private function kirimSnapResponse(
		MidtransService $midtrans,
		PembayaranModel $pembayaranModel,
		string $orderId,
		int $nominal,
		array $payload,
		string $tabelPembayaran
	): void {
		$snap = $midtrans->createSnapToken($payload);
		$pembayaranModel->updatePendingSnapToken($orderId, $snap['token']);

		$stmt = getDB()->prepare("UPDATE {$tabelPembayaran} SET snap_token = ? WHERE order_id = ?");
		$stmt->execute([$snap['token'], $orderId]);

		$this->jsonResponse([
			'success' => true,
			'snap_token' => $snap['token'],
			'order_id' => $orderId,
			'client_key' => $midtrans->getClientKey(),
			'snap_script' => $midtrans->getSnapScriptUrl(),
			'nominal' => $nominal,
		]);
	}

	private function respondSimpan(bool $ajax, bool $success, string $message, string $redirect): void
	{
		if ($ajax) {
			$this->jsonResponse([
				'success' => $success,
				'message' => $message,
				'redirect' => $redirect,
			], $success ? 200 : 400);
		}

		$_SESSION[$success ? 'success' : 'error'] = $message;
		header('Location: ' . $redirect);
		exit;
	}

	/**
	 * Helper cek role user login
	 * @param string $role
	 */

	private function requireRole(string $role): void
	{
		if (!isset($_SESSION['user'])) {
			header('Location: ' . BASEURL . '/auth');
			exit;
		}

		if ($_SESSION['user']['role'] !== $role) {
			header('Location: ' . BASEURL . '/admin');
			exit;
		}
	}

	public function batalReservasi(): void
	{
		unset($_SESSION['form_reservasi']);
		header('Location: ' . BASEURL . '/pelanggan');
		exit;
	}
}