<?php



class PembayaranModel
{

	private PDO $db;

	public function __construct()
	{
		$this->db = getDB();
	}

	public function hitungTotalHarga(int $reservasiId, string $jenisKendaraan = ''): array
	{
		$stmt = $this->db->prepare(
			'SELECT jenis_kendaraan, total_dp, total_full, total_sisa FROM v_reservasi_harga WHERE id_reservasi = ?'
		);

		$stmt->execute([$reservasiId]);

		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$row) {
			return ['total_dp' => 0, 'total_full' => 0, 'total_sisa' => 0, 'jenis_kendaraan' => $jenisKendaraan];
		}

		$jenis = $row['jenis_kendaraan'] ?: $jenisKendaraan;

		return [
			'total_dp' => (int) $row['total_dp'],
			'total_full' => (int) $row['total_full'],
			'total_sisa' => (int) $row['total_sisa'],
			'jenis_kendaraan' => $jenis,
		];
	}

	public function ringkasanHargaLayanan(array $semuaLayanan, array $layananIds, string $jenisKendaraan): array
	{
		$ids = array_map('intval', $layananIds);
		$motor = $jenisKendaraan === 'Motor';
		$items = [];
		$totalDP = 0;
		$totalFull = 0;
		foreach ($semuaLayanan as $layanan) {

			if (!in_array((int) $layanan['layanan_id'], $ids, true)) {
				continue;
			}

			$dp = (int) ($motor ? ($layanan['dp_motor'] ?? 0) : ($layanan['dp_mobil'] ?? 0));
			$full = (int) ($motor ? ($layanan['harga_motor_full'] ?? 0) : ($layanan['harga_mobil_full'] ?? 0));
			$items[] = [
				'nama_layanan' => $layanan['nama_layanan'],
				'dp' => $dp,
				'full' => $full,
			];

			$totalDP += $dp;
			$totalFull += $full;
		}
		return [
			'items' => $items,
			'total_dp' => $totalDP,
			'total_full' => $totalFull,
			'total_sisa' => $totalFull - $totalDP,
		];
	}

	public function hitungHargaDariLayanan(array $layananIds, string $jenisKendaraan): array
	{
		if (empty($layananIds)) {
			return ['total_dp' => 0, 'total_full' => 0, 'total_sisa' => 0];
		}

		$ids = implode(',', array_map('intval', $layananIds));
		$stmt = $this->db->prepare('CALL sp_hitung_harga_layanan(?, ?, @dp, @full)');
		$stmt->execute([$ids, $jenisKendaraan]);
		$stmt->closeCursor();
		$row = $this->db->query('SELECT @dp AS total_dp, @full AS total_full')->fetch(PDO::FETCH_ASSOC);
		$dp = (int) ($row['total_dp'] ?? 0);
		$full = (int) ($row['total_full'] ?? 0);
		return ['total_dp' => $dp, 'total_full' => $full, 'total_sisa' => $full - $dp];
	}

	public function deteksiJenisKendaraan(int $reservasiId): string
	{
		$stmt = $this->db->prepare('SELECT fn_deteksi_jenis_kendaraan(?) AS jenis');
		$stmt->execute([$reservasiId]);
		return (string) $stmt->fetchColumn();
	}

	public function sudahBayarDP(int $reservasiId): bool
	{
		$stmt = $this->db->prepare('SELECT fn_sudah_bayar_dp(?) AS sudah');
		$stmt->execute([$reservasiId]);
		return (bool) $stmt->fetchColumn();
	}

	public function sudahBayarFull(int $reservasiId): bool
	{
		$stmt = $this->db->prepare('SELECT fn_sudah_bayar_full(?) AS sudah');
		$stmt->execute([$reservasiId]);
		return (bool) $stmt->fetchColumn();
	}

	public function generateOrderId(string $prefix, int $userId): string
	{
		return sprintf('%s-%d-%s', $prefix, $userId, bin2hex(random_bytes(6)));
	}

	public function simpanPendingMidtrans(array $data): int
	{
		$stmt = $this->db->prepare(
			'INSERT INTO pembayaran_midtrans_pending (order_id, user_id, tipe, id_reservasi, layanan_ids, jenis_kendaraan, nominal, snap_token, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
		);

		$stmt->execute([
			$data['order_id'],
			$data['user_id'],
			$data['tipe'],
			$data['id_reservasi'] ?? null,
			$data['layanan_ids'] ?? null,
			$data['jenis_kendaraan'] ?? null,
			$data['nominal'],
			$data['snap_token'] ?? null,
			'pending',
		]);
		return (int) $this->db->lastInsertId();
	}

	public function getPendingByOrderId(string $orderId): array|false
	{
		$stmt = $this->db->prepare('SELECT * FROM pembayaran_midtrans_pending WHERE order_id = ? LIMIT 1');
		$stmt->execute([$orderId]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function orderMilikiUser(string $orderId, int $userId): bool
	{
		$pending = $this->getPendingByOrderId($orderId);
		return $pending && (int) $pending['user_id'] === $userId;
	}

	public function dpPreBisaDihubungkan(string $orderId, int $userId): bool
	{
		if (!$this->orderMilikiUser($orderId, $userId)) {
			return false;
		}
		$pending = $this->getPendingByOrderId($orderId);
		if (!$pending || $pending['tipe'] !== 'DP_PRE') {
			return false;
		}
		$dp = $this->getPembayaranDpByOrderId($orderId);
		if (!$dp || !empty($dp['id_reservasi'])) {
			return false;
		}
		return $this->isOrderPaid($orderId);
	}



	public function updatePendingSnapToken(string $orderId, string $snapToken): void
	{
		$stmt = $this->db->prepare(
			'UPDATE pembayaran_midtrans_pending SET snap_token = ? WHERE order_id = ?'
		);
		$stmt->execute([$snapToken, $orderId]);
	}

	public function updatePendingStatus(string $orderId, string $status): void
	{
		$stmt = $this->db->prepare(
			'UPDATE pembayaran_midtrans_pending SET status = ? WHERE order_id = ?'
		);
		$stmt->execute([$status, $orderId]);
	}

	public function buatPembayaranDpPending(
		?int $reservasiId,
		int $nominal,
		string $orderId,
		?string $snapToken = null
	): int {
		$stmt = $this->db->prepare(
			'INSERT INTO pembayaran_dp (id_reservasi, nominal, status, metode_pembayaran, order_id, snap_token) VALUES (?, ?, ?, ?, ?, ?)'
		);
		$stmt->execute([$reservasiId, $nominal, 'Pending', 'midtrans', $orderId, $snapToken]);
		return (int) $this->db->lastInsertId();
	}

	public function buatPembayaranFullPending(
		int $reservasiId,
		int $nominal,
		string $orderId,
		?string $snapToken = null
	): int {
		$stmt = $this->db->prepare(
			'INSERT INTO pembayaran_full (id_reservasi, nominal, status, metode_pembayaran, order_id, snap_token) VALUES (?, ?, ?, ?, ?, ?)'
		);

		$stmt->execute([$reservasiId, $nominal, 'Pending', 'midtrans', $orderId, $snapToken]);
		return (int) $this->db->lastInsertId();
	}



	public function linkPendingToPembayaran(string $orderId, string $tipe, int $idPembayaran): void
	{
		if ($tipe === 'DP_PRE' || $tipe === 'DP') {
			$stmt = $this->db->prepare(
				'UPDATE pembayaran_midtrans_pending SET id_pembayaran_dp = ? WHERE order_id = ?'
			);

			$stmt->execute([$idPembayaran, $orderId]);
			return;
		}

		$stmt = $this->db->prepare(
			'UPDATE pembayaran_midtrans_pending SET id_pembayaran_full = ? WHERE order_id = ?'
		);

		$stmt->execute([$idPembayaran, $orderId]);
	}

	public function konfirmasiPembayaranDp(
		string $orderId,
		string $transactionId,
		string $paymentType,
		string $midtransStatus
	): bool {
		$stmt = $this->db->prepare(
			'UPDATE pembayaran_dp SET status = ?, no_transaksi = ?, metode_pembayaran = ?, payment_channel = ?, midtrans_status = ?, tanggal_pembayaran = NOW() WHERE order_id = ? AND status = ?'
		);

		$localStatus = $this->mapMidtransToPembayaranStatus($midtransStatus);

		return $stmt->execute([
			$localStatus,
			$transactionId,
			'midtrans',
			$paymentType,
			$midtransStatus,
			$orderId,
			'Pending',
		]) && $stmt->rowCount() > 0;
	}

	public function konfirmasiPembayaranFull(
		string $orderId,
		string $transactionId,
		string $paymentType,
		string $midtransStatus
	): bool {
		$stmt = $this->db->prepare(
			'UPDATE pembayaran_full SET status = ?, no_transaksi = ?, metode_pembayaran = ?, payment_channel = ?, midtrans_status = ?, tanggal_pembayaran = NOW() WHERE order_id = ? AND status = ?'
		);

		$localStatus = $this->mapMidtransToPembayaranStatus($midtransStatus);

		$ok = $stmt->execute([
			$localStatus,
			$transactionId,
			'midtrans',
			$paymentType,
			$midtransStatus,
			$orderId,
			'Pending',
		]) && $stmt->rowCount() > 0;

		if ($ok && $localStatus === 'Selesai') {
			$row = $this->getPembayaranFullByOrderId($orderId);
			if ($row) {
				$this->upsertHubung((int) $row['id_reservasi'], null, (int) $row['id_pembayaran_full']);
			}
		}
		return $ok;
	}



	public function getPembayaranDpByOrderId(string $orderId): array|false
	{
		$stmt = $this->db->prepare('SELECT * FROM pembayaran_dp WHERE order_id = ? LIMIT 1');
		$stmt->execute([$orderId]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}



	public function getPembayaranFullByOrderId(string $orderId): array|false
	{
		$stmt = $this->db->prepare('SELECT * FROM pembayaran_full WHERE order_id = ? LIMIT 1');
		$stmt->execute([$orderId]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}



	public function isOrderPaid(string $orderId): bool
	{
		$pending = $this->getPendingByOrderId($orderId);
		if ($pending && in_array($pending['status'], ['settlement', 'capture'], true)) {
			return true;
		}

		$dp = $this->getPembayaranDpByOrderId($orderId);
		if ($dp && $dp['status'] === 'Selesai') {
			return true;
		}

		$full = $this->getPembayaranFullByOrderId($orderId);
		return $full && $full['status'] === 'Selesai';
	}



	public function sinkronkanDariMidtrans(string $orderId): bool
	{
		if ($this->isOrderPaid($orderId)) {
			return true;
		}

		$midtrans = new MidtransService();

		try {
			$status = $midtrans->getTransactionStatus($orderId);
		} catch (Throwable) {
			return false;
		}

		$transactionStatus = $status['transaction_status'] ?? '';

		if (!$midtrans->isSettlementStatus($transactionStatus)) {
			return false;
		}

		$this->prosesNotifikasiMidtrans([
			'order_id' => $orderId,
			'transaction_status' => $transactionStatus,
			'transaction_id' => $status['transaction_id'] ?? $orderId,
			'payment_type' => $status['payment_type'] ?? 'midtrans',
			'status_code' => (string) ($status['status_code'] ?? '200'),
			'gross_amount' => (string) ($status['gross_amount'] ?? '0'),
			'signature_key' => $status['signature_key'] ?? '',
			'fraud_status' => $status['fraud_status'] ?? 'accept',
		]);

		return $this->isOrderPaid($orderId);
	}



	public function linkDpPreToReservasi(string $orderId, int $reservasiId): void
	{
		$this->db->beginTransaction();

		try {
			$stmt = $this->db->prepare(
				'UPDATE pembayaran_dp SET id_reservasi = ? WHERE order_id = ?'
			);
			$stmt->execute([$reservasiId, $orderId]);

			$dp = $this->getPembayaranDpByOrderId($orderId);
			if ($dp) {
				$this->upsertHubung($reservasiId, (int) $dp['id_pembayaran_dp'], null);
			}

			$stmt = $this->db->prepare(
				'UPDATE pembayaran_midtrans_pending SET id_reservasi = ? WHERE order_id = ?'
			);
			$stmt->execute([$reservasiId, $orderId]);

			$this->db->commit();
		} catch (Throwable $e) {
			$this->db->rollBack();
			throw $e;
		}
	}



	private function upsertHubung(int $reservasiId, ?int $idDp, ?int $idFull): void
	{
		$stmt = $this->db->prepare('CALL sp_upsert_hubung(?, ?, ?)');
		$stmt->execute([$reservasiId, $idDp, $idFull]);
		$stmt->closeCursor();
	}



	private function mapMidtransToPembayaranStatus(string $midtransStatus): string
	{
		if (in_array($midtransStatus, ['capture', 'settlement'], true)) {
			return 'Selesai';
		}

		if (in_array($midtransStatus, ['expire', 'expired'], true)) {
			return 'Expire';
		}

		if (in_array($midtransStatus, ['cancel', 'deny', 'failure'], true)) {
			return 'Gagal';
		}

		return 'Pending';
	}



	public function prosesNotifikasiMidtrans(array $notif): array
	{
		$orderId = $notif['order_id'] ?? '';
		$transactionStatus = $notif['transaction_status'] ?? '';
		$transactionId = $notif['transaction_id'] ?? $orderId;
		$paymentType = $notif['payment_type'] ?? 'midtrans';
		$fraudStatus = $notif['fraud_status'] ?? 'accept';

		if ($orderId === '') {
			return ['handled' => false, 'message' => 'order_id kosong'];
		}

		$pending = $this->getPendingByOrderId($orderId);
		if (!$pending) {
			return ['handled' => false, 'message' => 'Order tidak ditemukan'];
		}

		$localPending = (new MidtransService())->mapTransactionToLocalStatus($transactionStatus);
		$this->updatePendingStatus($orderId, $localPending);

		if ($fraudStatus === 'challenge') {
			return ['handled' => true, 'message' => 'Menunggu verifikasi fraud'];
		}

		if (!in_array($transactionStatus, ['capture', 'settlement'], true)) {
			if (in_array($transactionStatus, ['expire', 'deny', 'cancel', 'failure'], true)) {
				$this->tandaiPembayaranGagal($orderId, $transactionStatus);
			}
			return ['handled' => true, 'message' => 'Status: ' . $transactionStatus];
		}

		$tipe = $pending['tipe'];
		if ($tipe === 'FULL') {
			$this->konfirmasiPembayaranFull($orderId, $transactionId, $paymentType, $transactionStatus);
		} else {
			$this->konfirmasiPembayaranDp($orderId, $transactionId, $paymentType, $transactionStatus);
			$dp = $this->getPembayaranDpByOrderId($orderId);
			if ($dp && !empty($dp['id_reservasi'])) {
				$this->upsertHubung((int) $dp['id_reservasi'], (int) $dp['id_pembayaran_dp'], null);
			}
		}

		return ['handled' => true, 'message' => 'Pembayaran dikonfirmasi'];
	}



	private function tandaiPembayaranGagal(string $orderId, string $midtransStatus): void
	{
		$status = $this->mapMidtransToPembayaranStatus($midtransStatus);

		$stmt = $this->db->prepare(
			'UPDATE pembayaran_dp SET status = ?, midtrans_status = ? WHERE order_id = ? AND status = ?'
		);
		$stmt->execute([$status, $midtransStatus, $orderId, 'Pending']);

		$stmt = $this->db->prepare(
			'UPDATE pembayaran_full SET status = ?, midtrans_status = ? WHERE order_id = ? AND status = ?'
		);
		$stmt->execute([$status, $midtransStatus, $orderId, 'Pending']);
	}



	public function prosesPembayaranDP(
		int $reservasiId,
		string $jenisKendaraan,
		?string $metodePembayaran = 'midtrans'
	): array {
		$stmt = $this->db->prepare(
			'CALL sp_proses_pembayaran_dp(?, ?, ?, @id_dp, @nominal, @no_trx)'
		);
		$stmt->execute([$reservasiId, $jenisKendaraan, $metodePembayaran ?? 'midtrans']);
		$stmt->closeCursor();
		$out = $this->db->query(
			'SELECT @id_dp AS id_pembayaran_dp, @nominal AS nominal, @no_trx AS no_transaksi'
		)->fetch(PDO::FETCH_ASSOC);
		return [
			'id_pembayaran_dp' => (int) $out['id_pembayaran_dp'],
			'nominal' => (int) $out['nominal'],
			'no_transaksi' => $out['no_transaksi'],
		];
	}



	public function prosesPembayaranFull(
		int $reservasiId,
		string $jenisKendaraan,
		?string $metodePembayaran = 'midtrans'
	): array {
		$stmt = $this->db->prepare(
			'CALL sp_proses_pembayaran_full(?, ?, ?, @id_full, @nominal, @sisa, @no_trx)'
		);
		$stmt->execute([$reservasiId, $jenisKendaraan, $metodePembayaran ?? 'midtrans']);
		$stmt->closeCursor();
		$out = $this->db->query(
			'SELECT @id_full AS id_pembayaran_full, @nominal AS nominal,
                    @sisa AS nominal_sisa, @no_trx AS no_transaksi'
		)->fetch(PDO::FETCH_ASSOC);
		return [
			'id_pembayaran_full' => (int) $out['id_pembayaran_full'],
			'nominal' => (int) $out['nominal'],
			'nominal_sisa' => (int) $out['nominal_sisa'],
			'no_transaksi' => $out['no_transaksi'],
		];
	}



	public function getPembayaranPendingByUser(int $userId): array
	{
		$stmt = $this->db->prepare(
			'SELECT * FROM v_pembayaran_pending WHERE user_id = ? ORDER BY created_at DESC'
		);
		$stmt->execute([$userId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}



	public function getReservasiSelesaiUnpaidFull(int $userId): array
	{
		$stmt = $this->db->prepare(
			'SELECT * FROM v_reservasi_unpaid_full
             WHERE user_id = ?
             ORDER BY tanggal DESC, jam DESC'
		);
		$stmt->execute([$userId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}



	public function getRiwayatLunasByUser(int $userId): array
	{
		$stmt = $this->db->prepare(
			'SELECT id_reservasi, kendaraan, plat, tanggal, jam, jenis_kendaraan,
                    harga_dp, harga_full, nominal_dp_dibayar, nominal_full_dibayar, tanggal_lunas
             FROM v_riwayat_pembayaran_lunas
             WHERE user_id = ?
             ORDER BY tanggal_lunas DESC, id_reservasi DESC'
		);
		$stmt->execute([$userId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}



	public function getRiwayatDetailByReservasi(int $reservasiId, int $userId): array|false
	{
		$stmt = $this->db->prepare(
			'SELECT * FROM v_riwayat_pembayaran_detail
             WHERE id_reservasi = ? AND user_id = ?
             LIMIT 1'
		);
		$stmt->execute([$reservasiId, $userId]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row ?: false;
	}



	public function getStrukByReservasi(int $reservasiId, int $userId): array|false
	{
		$stmt = $this->db->prepare(
			'SELECT id_reservasi, user_id, nama_pelanggan, kendaraan, plat, jenis_kendaraan,
                    tanggal, jam, layanan_id, nama_layanan, kategori,
                    harga_dp, harga_full, harga_sisa,
                    no_transaksi_dp, no_transaksi_full, tanggal_struk
             FROM v_struk_pembayaran
             WHERE id_reservasi = ? AND user_id = ?
             ORDER BY nama_layanan ASC'
		);
		$stmt->execute([$reservasiId, $userId]);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (empty($rows)) {
			return false;
		}

		$first = $rows[0];
		$items = [];
		$totalDp = 0;
		$totalFull = 0;
		$totalSisa = 0;

		foreach ($rows as $row) {
			$dp = (int) $row['harga_dp'];
			$full = (int) $row['harga_full'];
			$sisa = (int) $row['harga_sisa'];
			$totalDp += $dp;
			$totalFull += $full;
			$totalSisa += $sisa;
			$items[] = [
				'layanan_id' => (int) $row['layanan_id'],
				'nama_layanan' => $row['nama_layanan'],
				'kategori' => $row['kategori'],
				'harga_dp' => $dp,
				'harga_full' => $full,
				'harga_sisa' => $sisa,
			];
		}

		return [
			'header' => [
				'id_reservasi' => (int) $first['id_reservasi'],
				'nama_pelanggan' => $first['nama_pelanggan'],
				'kendaraan' => $first['kendaraan'],
				'plat' => $first['plat'],
				'jenis_kendaraan' => $first['jenis_kendaraan'],
				'tanggal' => $first['tanggal'],
				'jam' => $first['jam'],
				'no_transaksi_dp' => $first['no_transaksi_dp'],
				'no_transaksi_full' => $first['no_transaksi_full'],
				'tanggal_struk' => $first['tanggal_struk'],
			],
			'items' => $items,
			'totals' => [
				'harga_dp' => $totalDp,
				'harga_full' => $totalFull,
				'harga_sisa' => $totalSisa,
			],
		];
	}

}

