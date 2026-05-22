<?php
class Pelanggan extends Controller
{

    private object $reservasiModel;

    public function __construct()
    {
        $this->requireRole('Pelanggan');
        $this->reservasiModel = $this->model('ReservasiModel');
    }

    // Dashboard pelanggan
    public function index(): void
    {
        $userId = $_SESSION['user']['id'];
        $reservasi = $this->reservasiModel->getByUserId($userId);

        $data = [
            'title'     => 'Dashboard',
            'user'      => $_SESSION['user'],
            'reservasi' => $reservasi,
            'stats'     => [
                'total' => count($reservasi),
                'aktif' => count(array_filter($reservasi, fn($r) => in_array($r['status'], ['Menunggu', 'Konfirmasi', 'Proses']))),
                'selesai' => count(array_filter($reservasi, fn($r) => $r['status'] === 'Selesai')),
            ],
        ];

        $this->view('templates/header', $data);
        $this->view('pelanggan/dashboard', $data);
        $this->view('templates/footer', $data);
    }

    // Form reservasi baru, hanya nampilin view form (GET)
    public function buatReservasi(): void
    {
        $layanans = $this->reservasiModel->getLayanan();

        $layananMap = [];

        foreach ($layanans as $l) {
            $layananMap[$l['layanan_id']] = $l['nama_layanan'];
        }

        $data = [
            'title' => 'Buat Reservasi Baru',
            'user' => $_SESSION['user'],
            'layanans' => $layanans,
            'layananMap' => $layananMap
        ];

        $this->view('templates/header', $data);
        $this->view('pelanggan/buat-reservasi', $data);
        $this->view('templates/footer', $data);
    }

    // Proses pengiriman form (POST)
    public function simpanReservasi(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header('Location: ' . BASEURL . '/pelanggan/buatreservasi');
            exit;
        }

        $userId     = (int)$_SESSION['user']['id'];
        $kendaraan  = trim($_POST['kendaraan'] ?? '');
        $plat       = trim($_POST['plat'] ?? '');
        $layananIds = $_POST['layanan_id'] ?? [];
        $tanggal    = trim($_POST['tanggal'] ?? '');
        $jam        = trim($_POST['jam'] ?? '');
        $catatan    = trim($_POST['catatan'] ?? '');

        if (empty($kendaraan) || empty($plat) || empty($layananIds) || empty($tanggal) || empty($jam))
        {
            $_SESSION['error'] = 'Semua field wajib diisi!';
            header('Location: ' . BASEURL . '/pelanggan/buat-reservasi');
            exit;
        }

        $reservasiId = $this->reservasiModel->create(
            $userId,
            $kendaraan,
            $plat,
            $tanggal,
            $jam,
            $catatan
        );

        if ($reservasiId) {

            foreach ($layananIds as $layananId) {

                $this->reservasiModel->tambahLayanan(
                    $reservasiId,
                    $layananId
                );
            }

            unset($_SESSION['form_reservasi']);

            $_SESSION['success'] =
                'Reservasi berhasil dibuat! Kami akan segera mengkonfirmasi.';

            header('Location: ' . BASEURL . '/pelanggan/riwayat');

        } else {

            $_SESSION['error'] =
                'Gagal membuat reservasi. Silakan coba lagi.';

            header('Location: ' . BASEURL . '/pelanggan/buatreservasi');
        }

        exit;
    }

    // Riwayat reservasi
    public function riwayat(): void
    {
        $userId = (int)$_SESSION['user']['id'];
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

    // Halaman pembayaran sisa service
    public function bayar(): void
    {
        $userId = (int)$_SESSION['user']['id'];
        $pembayaranModel = $this->model('PembayaranModel');
        
        // Ambil semua reservasi dengan status selesai yang belum dibayar full
        $reservasiSelesai = $pembayaranModel->getReservasiSelesaiUnpaidFull($userId);
        
        // Hitung total harga untuk setiap reservasi
        foreach ($reservasiSelesai as &$r) {
            $harga = $pembayaranModel->hitungTotalHarga($r['id_reservasi'], 'Motor');
            
            // Cek jenis kendaraan dari data reservasi (defaultnya Motor, tapi bisa jadi Mobil)
            // Ambil dari field atau cek dari konteks - untuk sekarang asumsikan bisa dari metadata
            // Kita hitung dulu untuk Motor, jika ada maka gunakan itu
            if ($harga['total_full'] == 0) {
                // Coba dengan Mobil
                $harga = $pembayaranModel->hitungTotalHarga($r['id_reservasi'], 'Mobil');
            }
            
            $r['total_full'] = $harga['total_full'];
            $r['total_dp'] = $harga['total_dp'];
            $r['total_sisa'] = $harga['total_sisa'];
        }

        $data = [
            'title' => 'Pembayaran Sisanya',
            'user' => $_SESSION['user'],
            'reservasi' => $reservasiSelesai,
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

        $userId = (int)$_SESSION['user']['id'];
        $idReservasi = (int)($_POST['id_reservasi'] ?? 0);
        $tipePembayaran = trim($_POST['tipe_pembayaran'] ?? '');
        $nominal = (int)($_POST['nominal'] ?? 0);
        $metodePembayaran = trim($_POST['metode_pembayaran'] ?? '');

        // Validasi
        if (!$idReservasi || !$tipePembayaran || !$nominal) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Data pembayaran tidak lengkap']);
            exit;
        }

        if (!in_array($tipePembayaran, ['DP', 'FULL'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tipe pembayaran tidak valid']);
            exit;
        }

        // Cek reservasi milik user ini
        $db = getDB();
        $stmt = $db->prepare('SELECT user_id FROM reservasi WHERE id_reservasi = ?');
        $stmt->execute([$idReservasi]);
        $reservasi = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservasi || $reservasi['user_id'] != $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
            exit;
        }

        // Proses pembayaran
        $pembayaranModel = $this->model('PembayaranModel');
        
        try {
            // Buat record pembayaran
            $idPembayaran = $pembayaranModel->buatPembayaran(
                $idReservasi,
                $tipePembayaran,
                $nominal
            );

            // Update status pembayaran menjadi Selesai
            $noTransaksi = 'TRX' . date('YmdHis') . $idPembayaran;
            $pembayaranModel->updateStatusPembayaran(
                $idPembayaran,
                'Selesai',
                $noTransaksi,
                $metodePembayaran
            );

            // Jika pembayaran DP selesai, update status reservasi ke Konfirmasi
            if ($tipePembayaran === 'DP') {
                // Status bisa berubah dari Menunggu ke Konfirmasi
                // Tapi logic ini mungkin bisa dikustomisasi sesuai kebutuhan
            }

            echo json_encode([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'id_pembayaran' => $idPembayaran,
                'no_transaksi' => $noTransaksi
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        
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
}