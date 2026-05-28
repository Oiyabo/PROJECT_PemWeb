<?php

class Midtrans extends Controller
{
    public function notification(): void
    {
        $raw = file_get_contents('php://input');
        $notif = json_decode((string) $raw, true);

        if (!is_array($notif)) {
            http_response_code(400);
            echo 'Invalid payload';
            exit;
        }

        $orderId       = $notif['order_id'] ?? '';
        $statusCode    = (string) ($notif['status_code'] ?? '');
        $grossAmount   = (string) ($notif['gross_amount'] ?? '');
        $signatureKey  = $notif['signature_key'] ?? '';

        $midtrans = new MidtransService();

        if (
            $orderId === ''
            || !$midtrans->verifyNotificationSignature(
                $orderId,
                $statusCode,
                $grossAmount,
                $signatureKey
            )
        ) {
            http_response_code(403);
            echo 'Invalid signature';
            exit;
        }

        try {
            $pembayaranModel = $this->model('PembayaranModel');
            $pembayaranModel->prosesNotifikasiMidtrans($notif);
            http_response_code(200);
            echo 'OK';
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Error';
        }
        exit;
    }

    public function finish(): void
    {
        $orderId = $_GET['order_id'] ?? '';
        $status  = $_GET['transaction_status'] ?? $_GET['status'] ?? '';

        $qs = 'payment=' . urlencode($status);
        if ($orderId !== '') {
            $qs .= '&order_id=' . urlencode($orderId);
        }

        if (str_contains($orderId, 'BKP-FULL')) {
            header('Location: ' . BASEURL . '/pelanggan/bayar?' . $qs);
            exit;
        }

        header('Location: ' . BASEURL . '/pelanggan/buatreservasi?' . $qs);
        exit;
    }
}
