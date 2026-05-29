<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>AutoFix - Lupa Password</title>
	<link rel="stylesheet" href="<?= BASEURL ?>/assets/css/style.css">
</head>

<body class="auth-page">

	<div class="forgot-wrapper">
		<div class="forgot-card">

			<div class="forgot-image">
				<img src="<?= BASEURL ?>/assets/image/aunt-mobil.png" alt="AutoFix">
			</div>

			<div class="forgot-form">
				<?php if (isset($_SESSION['success'])): ?>
					<div class="flash-auth flash-success">
						<?= htmlspecialchars($_SESSION['success']) ?>
					</div>
					<?php unset($_SESSION['success']); ?>
				<?php endif; ?>

				<?php if (isset($_SESSION['error'])): ?>
					<div class="flash-auth flash-error">
						<?= htmlspecialchars($_SESSION['error']) ?>
					</div>
					<?php unset($_SESSION['error']); ?>
				<?php endif; ?>

				<form action="<?= BASEURL ?>/auth/prosesLupaPassword" method="POST">
					<h1>Lupa Password</h1>
					<span>Masukkan email dan password baru Anda</span>

					<input type="email" name="email" placeholder="Email" required />

					<input type="password" name="password" placeholder="Password Baru" required />

					<input type="password" name="konfirmasi_password" placeholder="Konfirmasi Password" required />

					<button type="submit">Ubah Password</button>

					<div class="textLink">
						Sudah ingat password?
						<a href="<?= BASEURL ?>/auth">Masuk Sekarang</a>
					</div>
				</form>
			</div>

		</div>
	</div>

	<script>
		const flashes = document.querySelectorAll('.flash-auth');

		flashes.forEach(el => {
			setTimeout(() => {
				el.style.transition = 'opacity 0.5s';
				el.style.opacity = '0';
				setTimeout(() => el.remove(), 500);
			}, 4000);
		});
	</script>

</body>

</html>