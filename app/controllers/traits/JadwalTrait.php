<?php

trait JadwalTrait
{
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
}
