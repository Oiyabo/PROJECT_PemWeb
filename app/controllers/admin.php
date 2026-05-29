<?php

class Admin extends Controller
{
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

        $data = [
            'title' => 'Dashboard Admin',
            'user' => $_SESSION['user'],
            'reservasiTerbaru' => array_slice($reservasi, 0, 5),
            'stats' => [
                'total' => $totalReservasi,
                'aktif' => $aktif,
                'selesai' => $selesai,
                'pelanggan' => count($this->userModel->getAllPelanggan()),
            ],
        ];

        $this->view('templates/header', $data);
        $this->view('admin/dashboard', $data);
        $this->view('templates/footer', $data);
    }

    public function reservasi(): void
{
    $keyword = trim($_GET['q'] ?? '');
    $statusAktif = trim($_GET['status'] ?? 'Menunggu');

    $statusTabs = ['Menunggu', 'Konfirmasi', 'Proses', 'Selesai', 'Batal'];

    if (!in_array($statusAktif, $statusTabs, true)) {
        $statusAktif = 'Menunggu';
    }

    $semuaReservasi = $keyword !== ''
        ? $this->reservasiModel->searchAll($keyword)
        : $this->reservasiModel->getAll();

    $jumlahStatus = [];

    foreach ($statusTabs as $status) {
        $jumlahStatus[$status] = count(array_filter(
            $semuaReservasi,
            fn($r) => ($r['status'] ?? '') === $status
        ));
    }

    $reservasi = array_values(array_filter(
        $semuaReservasi,
        fn($r) => ($r['status'] ?? '') === $statusAktif
    ));

    $data = [
        'title'        => 'Manajemen Reservasi',
        'user'         => $_SESSION['user'],
        'reservasi'    => $reservasi,
        'keyword'      => $keyword,
        'statusAktif'  => $statusAktif,
        'statusTabs'   => $statusTabs,
        'jumlahStatus' => $jumlahStatus,
    ];

    $this->view('templates/header', $data);
    $this->view('admin/reservasi', $data);
    $this->view('templates/footer', $data);
}

public function dataservice(): void
{
    header('Location: ' . BASEURL . '/admin/reservasi?status=Proses');
    exit;
}

public function transaksi(): void
{
    header('Location: ' . BASEURL . '/admin/reservasi?status=Selesai');
    exit;
}

    private function adminServiceTabs(string $activeTab = 'reservasi'): void
    {
        $validTabs = ['reservasi', 'dataservice', 'transaksi'];

        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'reservasi';
        }

        $keyword = trim($_GET['q'] ?? '');

        $semuaReservasi = $keyword !== ''
            ? $this->reservasiModel->searchAll($keyword)
            : $this->reservasiModel->getAll();

        $dataService = array_values(array_filter(
            $semuaReservasi,
            fn($r) => ($r['status'] ?? '') === 'Proses'
        ));

        $transaksi = array_values(array_filter(
            $semuaReservasi,
            fn($r) => ($r['status'] ?? '') === 'Selesai'
        ));

        $titleMap = [
            'reservasi' => 'Manajemen Reservasi',
            'dataservice' => 'Data Service',
            'transaksi' => 'Transaksi & Pembayaran',
        ];

        $data = [
            'title' => $titleMap[$activeTab],
            'user' => $_SESSION['user'],
            'activeTab' => $activeTab,
            'keyword' => $keyword,
            'reservasi' => $semuaReservasi,
            'dataService' => $dataService,
            'transaksi' => $transaksi,
            'tabCounts' => [
                'reservasi' => count($semuaReservasi),
                'dataservice' => count($dataService),
                'transaksi' => count($transaksi),
            ],
        ];

        $this->view('templates/header', $data);
        $this->view('admin/reservasi', $data);
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

    public function updatestatus(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? '';
            $validStatuses = ['Menunggu', 'Konfirmasi', 'Proses', 'Selesai', 'Batal'];

            if (in_array($status, $validStatuses, true)) {
                $this->reservasiModel->updateStatus((int) $id, $status);
                $_SESSION['success'] = 'Status reservasi berhasil diperbarui.';
            } else {
                $_SESSION['error'] = 'Status tidak valid.';
            }
        }

        $back = $_POST['back'] ?? BASEURL . '/admin/reservasi';
        header('Location: ' . $back);
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