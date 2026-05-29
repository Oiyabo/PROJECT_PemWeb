<?php



class Midtrans extends Controller
{

	public function notification(): void
	{
		$notif = json_decode((string) file_get_contents('php://input'), true);
		if (!is_array($notif)) {
			http_response_code(400);
			echo 'Invalid payload';
			exit;
		}

		$orderId = $notif['order_id'] ?? '';
		$midtrans = new MidtransService();

		if (
			$orderId === '' || !$midtrans->verifyNotificationSignature(
				$orderId,
				(string) ($notif['status_code'] ?? ''),
				(string) ($notif['gross_amount'] ?? ''),
				$notif['signature_key'] ?? ''
			)
		) {
			http_response_code(403);
			echo 'Invalid signature';
			exit;
		}

		try {
			$this->model('PembayaranModel')->prosesNotifikasiMidtrans($notif);
			http_response_code(200);
			echo 'OK';
		} catch (Throwable) {
			http_response_code(500);
			echo 'Error';
		}
		exit;
	}
	public function finish(): void
	{
		$orderId = $_GET['order_id'] ?? '';
		$status = $_GET['transaction_status'] ?? $_GET['status'] ?? '';

		$qs = 'payment=' . urlencode($status);
		if ($orderId !== '') {
			$qs .= '&order_id=' . urlencode($orderId);
		}

		$path = str_contains($orderId, 'BKP-FULL') ? '/pelanggan/bayar' : '/pelanggan/buatreservasi';
		header('Location: ' . BASEURL . $path . '?' . $qs);
		exit;
	}
}