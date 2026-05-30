<?php

require_once ROOT_PATH . '/app/controllers/traits/AdminPesanTrait.php';
require_once ROOT_PATH . '/app/controllers/traits/JadwalTrait.php';

class Admin extends Controller
{
    use AdminPesanTrait;
    use JadwalTrait;

    private object $reservasiModel;
    private object $userModel;

    public function __construct()
    {
        $this->requireRole('Admin');
        $this->reservasiModel = $this->model('ReservasiModel');
        $this->userModel = $this->model('UserModel');
    }

    public function index(): void
    {
        $reservasi = $this->reservasiModel->getAll();

        $totalReservasi = count($reservasi);
        $aktif = count(array_filter($reservasi, fn($r) => in_array($r['status'], ['Konfirmasi', 'Proses'])));
        $selesai = count(array_filter($reservasi, fn($r) => $r['status'] === 'Selesai'));
        $terbayar = count(array_filter($reservasi, fn($r) => $r['status'] === 'Terbayar'));

        $data = [
            'title' => 'Dashboard Admin',
            'user' => $_SESSION['user'],
            'reservasiTerbaru' => array_slice($reservasi, 0, 5),
            'stats' => [
                'total' => $totalReservasi,
                'aktif' => $aktif,
                'selesai' => $selesai,
                'terbayar' => $terbayar,
                'pelanggan' => count($this->userModel->getAllPelanggan()),
            ],
        ];

        $this->view('templates/header', $data);
        $this->view('admin/dashboard', $data);
        $this->view('templates/footer', $data);
    }

    public function pelanggan(): void
    {
        $keyword = trim($_GET['q'] ?? '');

        $pelanggan = $keyword !== ''
            ? $this->userModel->searchPelanggan($keyword)
            : $this->userModel->getAllPelanggan();

        $data = [
            'title' => 'Data Pelanggan',
            'user' => $_SESSION['user'],
            'pelanggan' => $pelanggan,
            'keyword' => $keyword,
        ];

        $this->view('templates/header', $data);
        $this->view('admin/pelanggan', $data);
        $this->view('templates/footer', $data);
    }

    private function requireRole(string $role): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        if ($_SESSION['user']['role'] !== $role) {
            header('Location: ' . BASEURL . '/pelanggan');
            exit;
        }
    }
}
