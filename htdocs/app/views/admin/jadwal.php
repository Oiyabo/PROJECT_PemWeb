<?php
if (!function_exists('badgeStatus')) {
	function badgeStatus(string $status): string
	{
		$statusMap = [
			'Menunggu' => 'status-waiting',
			'Konfirmasi' => 'status-confirmed',
			'Proses' => 'status-process',
			'Selesai' => 'status-done',
			'Batal' => 'status-danger',
		];

		$class = $statusMap[$status] ?? 'status-waiting';

		return '<span class="status-badge ' . $class . '">'
			. htmlspecialchars($status) .
			'</span>';
	}
}

if (!function_exists('formatTanggalIndo')) {
	function formatTanggalIndo(string $tanggal): string
	{
		$dt = DateTimeImmutable::createFromFormat('Y-m-d', $tanggal);
		if (!$dt) {
			return $tanggal;
		}

		$bulan = [
			1 => 'Jan',
			2 => 'Feb',
			3 => 'Mar',
			4 => 'Apr',
			5 => 'Mei',
			6 => 'Jun',
			7 => 'Jul',
			8 => 'Agu',
			9 => 'Sep',
			10 => 'Okt',
			11 => 'Nov',
			12 => 'Des',
		];

		$monthNum = (int) $dt->format('n');

		return $dt->format('j') . ' ' . ($bulan[$monthNum] ?? $dt->format('M')) . ' ' . $dt->format('Y');
	}
}

if (!function_exists('formatJamSingkat')) {
	function formatJamSingkat(string $jam): string
	{
		return substr($jam, 0, 5);
	}
}

$eventsByDate = $eventsByDate ?? [];
$year = (int) ($year ?? date('Y'));
$month = (int) ($month ?? date('n'));
$today = $today ?? date('Y-m-d');
$bulanKey = $bulanKey ?? date('Y-m');
$bulanLabel = $bulanLabel ?? '';
$prevBulan = $prevBulan ?? '';
$nextBulan = $nextBulan ?? '';
$jadwalAkanDatang = $jadwalAkanDatang ?? [];
$jadwalSelesai = $jadwalSelesai ?? [];
$stats = $stats ?? ['bulanIni' => 0, 'akanDatang' => 0, 'selesai' => 0];

$firstDay = DateTimeImmutable::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $year, $month));
$daysInMonth = (int) $firstDay->format('t');
$startWeekday = (int) $firstDay->format('N');
$hariLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

$calendarCells = [];
$leadingBlanks = $startWeekday - 1;

for ($i = 0; $i < $leadingBlanks; $i++) {
	$calendarCells[] = null;
}

for ($day = 1; $day <= $daysInMonth - 1; $day++) {
	$calendarCells[] = $day;
}

while (count($calendarCells) % 7 !== 0) {
	$calendarCells[] = null;
}
?>

<div class="jadwal-page">

	<div class="admin-stats-grid jadwal-stats-grid">
		<div class="admin-stat-card">
			<div class="admin-stat-header">
				<div class="admin-stat-label">Jadwal Bulan Ini</div>
				<div class="admin-stat-icon-wrapper">
					<i data-lucide="calendar-range" color="#168aad"></i>
				</div>
			</div>
			<div class="admin-stat-value"><?= (int) $stats['bulanIni'] ?></div>
			<div class="admin-stat-description">Reservasi di <?= htmlspecialchars($bulanLabel) ?></div>
		</div>

		<div class="admin-stat-card">
			<div class="admin-stat-header">
				<div class="admin-stat-label">Akan Datang</div>
				<div class="admin-stat-icon-wrapper">
					<i data-lucide="clock" color="#34a0a4"></i>
				</div>
			</div>
			<div class="admin-stat-value"><?= (int) $stats['akanDatang'] ?></div>
			<div class="admin-stat-description">Jadwal mendatang</div>
		</div>

		<div class="admin-stat-card">
			<div class="admin-stat-header">
				<div class="admin-stat-label">Selesai</div>
				<div class="admin-stat-icon-wrapper">
					<i data-lucide="check-circle-2" color="#76c893"></i>
				</div>
			</div>
			<div class="admin-stat-value"><?= (int) $stats['selesai'] ?></div>
			<div class="admin-stat-description">Service telah selesai</div>
		</div>
	</div>

	<div class="jadwal-layout">

		<div class="jadwal-calendar-section">
			<div class="admin-card jadwal-calendar-card">
				<div class="jadwal-calendar-toolbar">
					<div class="jadwal-calendar-nav">
						<a href="<?= BASEURL ?>/admin/jadwal?bulan=<?= urlencode($prevBulan) ?>" class="jadwal-nav-btn"
							title="Bulan sebelumnya">
							<i data-lucide="chevron-left" width="18" height="18"></i>
						</a>
						<h3 class="jadwal-calendar-title"><?= htmlspecialchars($bulanLabel) ?></h3>
						<a href="<?= BASEURL ?>/admin/jadwal?bulan=<?= urlencode($nextBulan) ?>" class="jadwal-nav-btn"
							title="Bulan berikutnya">
							<i data-lucide="chevron-right" width="18" height="18"></i>
						</a>
					</div>

					<a href="<?= BASEURL ?>/admin/jadwal" class="jadwal-today-btn">Hari Ini</a>
				</div>

				<div class="jadwal-calendar-legend">
					<span class="legend-item"><span class="legend-dot legend-upcoming"></span> Aktif / akan
						datang</span>
					<span class="legend-item"><span class="legend-dot legend-done"></span> Selesai</span>
					<span class="legend-item"><span class="legend-dot legend-cancel"></span> Batal</span>
				</div>

				<div class="jadwal-calendar-grid">
					<?php foreach ($hariLabels as $label): ?>
						<div class="jadwal-weekday"><?= $label ?></div>
					<?php endforeach; ?>

					<?php foreach ($calendarCells as $cellDay): ?>
						<?php if ($cellDay === null): ?>
							<div class="jadwal-day jadwal-day-empty" aria-hidden="true"></div>
						<?php else: ?>
							<?php
							$dateKey = sprintf('%04d-%02d-%02d', $year, $month, $cellDay);
							$dayEvents = $eventsByDate[$dateKey] ?? [];
							$eventCount = count($dayEvents);
							$hasUpcoming = false;
							$hasDone = false;
							$hasCancel = false;

							foreach ($dayEvents as $ev) {
								$st = $ev['status'] ?? '';
								if ($st === 'Selesai') {
									$hasDone = true;
								} elseif ($st === 'Batal') {
									$hasCancel = true;
								} else {
									$hasUpcoming = true;
								}
							}

							$dayClasses = ['jadwal-day'];
							if ($dateKey === $today) {
								$dayClasses[] = 'is-today';
							}
							if ($eventCount > 0) {
								$dayClasses[] = 'has-events';
							}
							?>
							<button type="button" class="<?= implode(' ', $dayClasses) ?>"
								data-date="<?= htmlspecialchars($dateKey) ?>"
								aria-label="Tanggal <?= $cellDay ?>, <?= $eventCount ?> jadwal">
								<span class="jadwal-day-number"><?= $cellDay ?></span>
								<?php if ($eventCount > 0): ?>
									<span class="jadwal-day-dots">
										<?php if ($hasUpcoming): ?><span class="legend-dot legend-upcoming"></span><?php endif; ?>
										<?php if ($hasDone): ?><span class="legend-dot legend-done"></span><?php endif; ?>
										<?php if ($hasCancel): ?><span class="legend-dot legend-cancel"></span><?php endif; ?>
									</span>
									<span class="jadwal-day-count"><?= $eventCount ?></span>
								<?php endif; ?>
							</button>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="admin-card jadwal-day-detail" id="jadwalDayDetail" hidden>
				<div class="jadwal-day-detail-header">
					<h3 class="admin-card-title" id="jadwalDayDetailTitle">Detail Jadwal</h3>
					<button type="button" class="btn-close-day-detail" id="btnCloseDayDetail" aria-label="Tutup detail">
						<i data-lucide="x" width="16" height="16"></i>
					</button>
				</div>
				<div class="jadwal-day-detail-content" id="jadwalDayDetailContent"></div>
			</div>
		</div>

		<div class="jadwal-lists-section">

			<div class="admin-card jadwal-list-card">
				<div class="admin-card-header">
					<h3 class="admin-card-title">
						<i data-lucide="arrow-up-circle" width="16" height="16"></i>
						Jadwal Akan Datang
					</h3>
					<a href="<?= BASEURL ?>/admin/reservasi?status=Konfirmasi" class="admin-card-link">Lihat Semua</a>
				</div>

				<div class="admin-card-content jadwal-list-content">
					<?php if (empty($jadwalAkanDatang)): ?>
						<p class="jadwal-empty">Tidak ada jadwal mendatang.</p>
					<?php else: ?>
						<?php foreach ($jadwalAkanDatang as $r): ?>
							<?php
							$reservasiJson = htmlspecialchars(
								json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
								ENT_QUOTES,
								'UTF-8'
							);
							?>
							<div class="jadwal-list-item jadwal-list-upcoming">
								<div class="jadwal-list-main">
									<div class="jadwal-list-date">
										<i data-lucide="calendar" width="14" height="14"></i>
										<?= formatTanggalIndo($r['tanggal'] ?? '') ?>
										<span class="jadwal-list-time"><?= formatJamSingkat($r['jam'] ?? '') ?></span>
									</div>
									<div class="jadwal-list-name"><?= htmlspecialchars($r['nama'] ?? '-') ?></div>
									<div class="jadwal-list-meta">
										<?= htmlspecialchars($r['kendaraan'] ?? '-') ?>
										<?php if (!empty($r['plat'])): ?>
											&middot; <?= htmlspecialchars($r['plat']) ?>
										<?php endif; ?>
									</div>
								</div>
								<div class="jadwal-list-actions">
									<?= badgeStatus($r['status'] ?? 'Menunggu') ?>
									<button type="button" class="btn btn-detail-reservasi btn-sm"
										data-reservasi="<?= $reservasiJson ?>" title="Lihat detail">
										<i data-lucide="eye" width="14" height="14"></i>
									</button>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

			<div class="admin-card jadwal-list-card">
				<div class="admin-card-header">
					<h3 class="admin-card-title">
						<i data-lucide="check-circle" width="16" height="16"></i>
						Jadwal Selesai
					</h3>
					<a href="<?= BASEURL ?>/admin/reservasi?status=Selesai" class="admin-card-link">Lihat Semua</a>
				</div>

				<div class="admin-card-content jadwal-list-content">
					<?php if (empty($jadwalSelesai)): ?>
						<p class="jadwal-empty">Belum ada jadwal yang selesai.</p>
					<?php else: ?>
						<?php foreach ($jadwalSelesai as $r): ?>
							<?php
							$reservasiJson = htmlspecialchars(
								json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
								ENT_QUOTES,
								'UTF-8'
							);
							?>
							<div class="jadwal-list-item jadwal-list-done">
								<div class="jadwal-list-main">
									<div class="jadwal-list-date">
										<i data-lucide="calendar-check" width="14" height="14"></i>
										<?= formatTanggalIndo($r['tanggal'] ?? '') ?>
										<span class="jadwal-list-time"><?= formatJamSingkat($r['jam'] ?? '') ?></span>
									</div>
									<div class="jadwal-list-name"><?= htmlspecialchars($r['nama'] ?? '-') ?></div>
									<div class="jadwal-list-meta">
										<?= htmlspecialchars($r['kendaraan'] ?? '-') ?>
										<?php if (!empty($r['layanan'])): ?>
											&middot; <?= htmlspecialchars($r['layanan']) ?>
										<?php endif; ?>
									</div>
								</div>
								<div class="jadwal-list-actions">
									<?= badgeStatus($r['status'] ?? 'Selesai') ?>
									<button type="button" class="btn btn-detail-reservasi btn-sm"
										data-reservasi="<?= $reservasiJson ?>" title="Lihat detail">
										<i data-lucide="eye" width="14" height="14"></i>
									</button>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

		</div>
	</div>
</div>

<?php
$adminBackUrl = BASEURL . '/admin/jadwal?bulan=' . urlencode($bulanKey);
?>
<?php require __DIR__ . '/../partials/detail-reservasi-modal.php'; ?>

<script>
	window.APP_BASEURL = <?= json_encode(BASEURL) ?>;
	window.DETAIL_RESERVASI_ADMIN = true;
	window.DETAIL_RESERVASI_BACK = <?= json_encode($adminBackUrl) ?>;
	window.JADWAL_EVENTS = <?= json_encode($eventsByDate, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
<script src="<?= BASEURL ?>/assets/js/detail-reservasi.js"></script>
<script src="<?= BASEURL ?>/assets/js/admin-jadwal.js"></script>