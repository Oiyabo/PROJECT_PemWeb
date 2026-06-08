<?php

trait AdminPesanTrait
{
    public function reservasi(): void
    {
        $keyword = trim($_GET['q'] ?? '');
        $statusAktif = trim($_GET['status'] ?? 'Semua');

        $statusTabs = ['Semua', 'Menunggu', 'Konfirmasi', 'Proses', 'Selesai', 'Terbayar', 'Batal'];

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

    public function updatestatus(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? '';
            $validStatuses = ['Menunggu', 'Konfirmasi', 'Proses', 'Selesai', 'Batal'];

            if (in_array($status, $validStatuses, true)) {
                $reservasi = $this->reservasiModel->getById((int) $id);
                if ($reservasi) {
                    $this->reservasiModel->updateStatus((int) $id, $status);

                    require_once ROOT_PATH . '/app/models/NotifikasiModel.php';
                    $notifModel = new NotifikasiModel();
                    $notifModel->create(
                        (int) $reservasi['user_id'],
                        'Pelanggan',
                        'Status Reservasi Diperbarui',
                        'Status reservasi untuk kendaraan ' . $reservasi['kendaraan'] . ' (' . $reservasi['plat'] . ') telah diubah menjadi: ' . $status,
                        BASEURL . '/pelanggan/riwayat?status=' . urlencode($status)
                    );
                }
                $_SESSION['success'] = 'Status reservasi berhasil diperbarui.';
            } else {
                $_SESSION['error'] = 'Status tidak valid.';
            }
        }

        $back = $_POST['back'] ?? BASEURL . '/admin/reservasi';
        header('Location: ' . $back);
        exit;
    }
}
