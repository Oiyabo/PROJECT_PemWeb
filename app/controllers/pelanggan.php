<?php

require_once ROOT_PATH . '/app/controllers/traits/PesanTrait.php';
require_once ROOT_PATH . '/app/controllers/traits/BayarTrait.php';

class Pelanggan extends Controller
{
    use PesanTrait;
    use BayarTrait;

    private object $reservasiModel;

    public function __construct()
    {
        $this->requireRole('Pelanggan');
        $this->reservasiModel = $this->model('ReservasiModel');
    }

    public function index(): void
    {
        $userId = $_SESSION['user']['id'];
        $reservasi = $this->reservasiModel->getByUserId($userId);

        $reservasiAktif = array_filter(
            $reservasi,
            fn($r) => in_array($r['status'], ['Menunggu', 'Konfirmasi', 'Proses'])
        );
        
        $data = [
            'title' => 'Dashboard',
            'user' => $_SESSION['user'],
            'reservasi' => $reservasi,
            'reservasiAktif' => $reservasiAktif,
            'stats' => [
                'total' => count($reservasi),
                'aktif' => count($reservasiAktif),
                'selesai' => count(array_filter($reservasi, fn($r) => $r['status'] === 'Selesai')),
                'terbayar' => count(array_filter($reservasi, fn($r) => $r['status'] === 'Terbayar')),
            ],
        ];

        $this->view('templates/header', $data);
        $this->view('pelanggan/dashboard', $data);
        $this->view('templates/footer', $data);
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
        PendingModel $pembayaranModel,
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

    public function marknotifread(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Metode tidak valid']);
            exit;
        }

        require_once ROOT_PATH . '/app/models/NotifikasiModel.php';
        $notifModel = new NotifikasiModel();
        $userId = (int) $_SESSION['user']['id'];
        $notifModel->markAllAsRead($userId, 'Pelanggan');

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true]);
        exit;
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
