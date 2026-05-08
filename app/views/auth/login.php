<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AutoFix - Login & Register</title>
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/autentikasi.css">
</head>

<body>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="flash-auth flash-error">
      <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="flash-auth flash-success">
      <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <div class="container" id="container">
    <div class="form-container">

      <div class="sign-up-container">
        <form action="<?= BASEURL ?>/auth/register" method="POST">
          <h1>Buat Akun</h1>
          <span>Gunakan email untuk mendaftar</span>
          <input type="text" name="nama" placeholder="Nama Lengkap" required />
          <input type="email" name="email" placeholder="Email" required />
          <input type="password" name="password" placeholder="Password" required />
          <button type="submit">Daftar</button>
          <div class="textLink">Sudah Punya Akun? <a id="signIn" href="#">Masuk Sekarang</a></div>
        </form>
      </div>

      <div class="sign-in-container">
        <form action="<?= BASEURL ?>/auth/login" method="POST">
          <h1>Masuk</h1>
          <span>Masuk dengan akun Anda</span>
          <input type="email" name="email" placeholder="Email" required />
          <input type="password" name="password" placeholder="Password" required />
          <a href="#">Lupa password?</a>
          <button type="submit">Masuk</button>
          <div class="textLink">Belum Punya Akun? <a id="signUp" href="#">Daftar Sekarang</a></div>
        </form>
      </div>

    </div>

    <div id="overlay-container" class="overlay-container">
      <div class="overlay-panel overlay-left"></div>
      <div class="overlay-panel overlay-right"></div>
    </div>
  </div>

  <script>
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const overlayContainer = document.getElementById('overlay-container');

    signUpButton.addEventListener('click', () => {
      overlayContainer.classList.add('changed');
    });

    signInButton.addEventListener('click', () => {
      overlayContainer.classList.remove('changed');
    });

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