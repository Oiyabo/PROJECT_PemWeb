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
        $statusAktif = trim($_GET['status'] ?? 'Semua');

        $statusTabs = ['Semua', 'Menunggu', 'Konfirmasi', 'Proses', 'Selesai', 'Batal'];

        if (!in_array($statusAktif, $statusTabs, true)) {
            $statusAktif = 'Semua';
        }

        $semuaReservasiAwal = $this->reservasiModel->getAll();

        $semuaReservasi = $keyword !== ''
            ? $this->reservasiModel->searchAll($keyword)
            : $semuaReservasiAwal;

        $jumlahStatus = [];

        foreach ($statusTabs as $status) {
            if ($status === 'Semua') {
                $jumlahStatus[$status] = count($semuaReservasiAwal);
            } else {
                $jumlahStatus[$status] = count(array_filter(
                    $semuaReservasiAwal,
                    fn($r) => ($r['status'] ?? '') === $status
                ));
            }
        }

        if ($statusAktif === 'Semua') {
            $reservasi = $semuaReservasi;
        } else {
            $reservasi = array_values(array_filter(
                $semuaReservasi,
                fn($r) => ($r['status'] ?? '') === $statusAktif
            ));
        }

        $data = [
            'title' => 'Manajemen Reservasi',
            'user' => $_SESSION['user'],
            'reservasi' => $reservasi,
            'keyword' => $keyword,
            'statusAktif' => $statusAktif,
            'statusTabs' => $statusTabs,
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

    public function jadwal(): void
    {
        $bulanParam = trim($_GET['bulan'] ?? '');
        $today = new DateTimeImmutable('today');

        if ($bulanParam !== '' && preg_match('/^\d{4}-\d{2}$/', $bulanParam)) {
            $bulanAktif = DateTimeImmutable::createFromFormat('Y-m-d', $bulanParam . '-01');
        } else {
            $bulanAktif = $today->modify('first day of this month');
        }

        if (!$bulanAktif) {
            $bulanAktif = $today->modify('first day of this month');
        }

        $year = (int) $bulanAktif->format('Y');
        $month = (int) $bulanAktif->format('m');
        $startDate = $bulanAktif->format('Y-m-d');
        $endDate = $bulanAktif->modify('last day of this month')->format('Y-m-d');

        $reservasiBulan = $this->reservasiModel->getByDateRange($startDate, $endDate);
        $eventsByDate = [];

        foreach ($reservasiBulan as $row) {
            $tanggal = $row['tanggal'] ?? '';
            if ($tanggal === '') {
                continue;
            }
            $eventsByDate[$tanggal][] = $row;
        }

        $jadwalAkanDatang = $this->reservasiModel->getUpcoming();
        $jadwalSelesai = $this->reservasiModel->getCompleted();

        $prevBulan = $bulanAktif->modify('-1 month')->format('Y-m');
        $nextBulan = $bulanAktif->modify('+1 month')->format('Y-m');
        $bulanKey = $bulanAktif->format('Y-m');

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $data = [
            'title' => 'Jadwal Tanggal',
            'user' => $_SESSION['user'],
            'year' => $year,
            'month' => $month,
            'bulanKey' => $bulanKey,
            'bulanLabel' => ($namaBulan[$month] ?? '') . ' ' . $year,
            'prevBulan' => $prevBulan,
            'nextBulan' => $nextBulan,
            'today' => $today->format('Y-m-d'),
            'eventsByDate' => $eventsByDate,
            'reservasiBulan' => $reservasiBulan,
            'jadwalAkanDatang' => $jadwalAkanDatang,
            'jadwalSelesai' => $jadwalSelesai,
            'stats' => [
                'bulanIni' => count($reservasiBulan),
                'akanDatang' => $this->reservasiModel->countUpcoming(),
                'selesai' => $this->reservasiModel->countCompleted(),
            ],
        ];

        $this->view('templates/header', $data);
        $this->view('admin/jadwal', $data);
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