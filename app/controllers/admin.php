<?php

class Admin extends Controller
{
    private object $reservasiModel;
    private object $userModel;

    public function __construct()
    {
        $this->requireRole('Admin');

        $this->reservasiModel = $this->model('ReservasiModel');
        $this->userModel      = $this->model('UserModel');
    }

    public function index(): void
    {
        $reservasi = $this->reservasiModel->getAll();

        $totalReservasi = count($reservasi);
        $aktif = count(array_filter($reservasi, fn($r) => in_array($r['status'], ['Konfirmasi', 'Proses'])));
        $selesai = count(array_filter($reservasi, fn($r) => $r['status'] === 'Selesai'));

        $data = [
            'title'          => 'Dashboard Admin',
            'user'           => $_SESSION['user'],
            'reservasiTerbaru' => array_slice($reservasi, 0, 5),
            'stats'          => [
                'total'     => $totalReservasi,
                'aktif'     => $aktif,
                'selesai'   => $selesai,
                'pelanggan' => count($this->userModel->getAllPelanggan()),
            ],
        ];

        $this->view('templates/header', $data);
        $this->view('admin/dashboard', $data);
        $this->view('templates/footer', $data);
    }

    public function reservasi(): void
    {
        $data = [
            'title'     => 'Manajemen Reservasi',
            'user'      => $_SESSION['user'],
            'reservasi' => $this->reservasiModel->getAll(),
        ];

        $this->view('templates/header', $data);
        $this->view('admin/reservasi', $data);
        $this->view('templates/footer', $data);
    }

    public function dataservice(): void
    {
        $semuaReservasi = $this->reservasiModel->getAll();
        $dataService = array_filter($semuaReservasi, fn($r) => $r['status'] === 'Proses');

        $data = [
            'title'       => 'Data Service',
            'user'        => $_SESSION['user'],
            'dataService' => array_values($dataService),
        ];

        $this->view('templates/header', $data);
        $this->view('admin/dataservice', $data);
        $this->view('templates/footer', $data);
    }

    public function pelanggan(): void
    {
        $data = [
            'title'     => 'Data Pelanggan',
            'user'      => $_SESSION['user'],
            'pelanggan' => $this->userModel->getAllPelanggan(),
        ];

        $this->view('templates/header', $data);
        $this->view('admin/pelanggan', $data);
        $this->view('templates/footer', $data);
    }

    public function transaksi(): void
    {
        $semuaReservasi = $this->reservasiModel->getAll();
        $transaksi = array_filter($semuaReservasi, fn($r) => $r['status'] === 'Selesai');

        $data = [
            'title'     => 'Transaksi & Pembayaran',
            'user'      => $_SESSION['user'],
            'transaksi' => array_values($transaksi),
        ];

        $this->view('templates/header', $data);
        $this->view('admin/transaksi', $data);
        $this->view('templates/footer', $data);
    }

    public function updatestatus(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? '';
            $validStatuses = ['Menunggu', 'Konfirmasi', 'Proses', 'Selesai', 'Batal'];

            if (in_array($status, $validStatuses)) {
                $this->reservasiModel->updateStatus((int)$id, $status);
                $_SESSION['success'] = 'Status reservasi berhasil diperbarui.';
            } else {
                $_SESSION['error'] = 'Status tidak valid.';
            }
        }

        header('Location: ' . BASEURL . '/admin/reservasi');
        exit;
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