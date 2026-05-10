<?php

class Pelanggan extends Controller
{
    private object $reservasiModel;

    public function __construct()
    {
        $this->requireRole('Pelanggan');
        $this->reservasiModel = $this->model('ReservasiModel');
    }

    public function index(): void
    {
        $userId    = $_SESSION['user']['id'];
        $reservasi = $this->reservasiModel->getByUserId($userId);

        $data = [
            'title'     => 'Dashboard',
            'user'      => $_SESSION['user'],
            'reservasi' => $reservasi,
            'stats' => [
                'total'   => count($reservasi),
                'aktif'   => count(array_filter($reservasi, fn($r) => in_array($r['status'], ['Menunggu', 'Konfirmasi', 'Proses']))),
                'selesai' => count(array_filter($reservasi, fn($r) => $r['status'] === 'Selesai')),
            ],
        ];

        $this->view('templates/header', $data);
        $this->view('pelanggan/dashboard', $data);
        $this->view('templates/footer', $data);
    }

    public function buatreservasi(): void
    {
        $data = [
            'title' => 'Buat Reservasi Baru',
            'user'  => $_SESSION['user'],
        ];

        $this->view('templates/header', $data);
        $this->view('pelanggan/buat-reservasi', $data);
        $this->view('templates/footer', $data);
    }

    public function simpanreservasi(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/pelanggan/buatreservasi');
            exit;
        }

        $userId    = (int)$_SESSION['user']['id'];
        $kendaraan = trim($_POST['kendaraan'] ?? '');
        $plat      = trim($_POST['plat'] ?? '');
        $layanan   = trim($_POST['layanan'] ?? '');
        $tanggal   = trim($_POST['tanggal'] ?? '');
        $jam       = trim($_POST['jam'] ?? '');
        $catatan   = trim($_POST['catatan'] ?? '');

        if (empty($kendaraan) || empty($plat) || empty($layanan) || empty($tanggal) || empty($jam)) {
            $_SESSION['error'] = 'Semua field wajib diisi!';
            header('Location: ' . BASEURL . '/pelanggan/buatreservasi');
            exit;
        }

        $berhasil = $this->reservasiModel->create($userId, $kendaraan, $plat, $layanan, $tanggal, $jam, $catatan);

        if ($berhasil) {
            $_SESSION['success'] = 'Reservasi berhasil dibuat! Kami akan segera mengkonfirmasi.';
            header('Location: ' . BASEURL . '/pelanggan/riwayat');
        } else {
            $_SESSION['error'] = 'Gagal menyimpan reservasi. Silakan coba lagi.';
            header('Location: ' . BASEURL . '/pelanggan/buatreservasi');
        }
        exit;
    }

    public function riwayat(): void
    {
        $userId    = (int)$_SESSION['user']['id'];
        $reservasi = $this->reservasiModel->getByUserId($userId);

        $data = [
            'title'     => 'Riwayat Service',
            'user'      => $_SESSION['user'],
            'reservasi' => $reservasi,
        ];

        $this->view('templates/header', $data);
        $this->view('pelanggan/riwayat', $data);
        $this->view('templates/footer', $data);
    }

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