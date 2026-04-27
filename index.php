<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login & Register Slide</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <div class="container" id="container">
    <div class="form-container">

      <div class="sign-up-container">
        <form action="proses.php" method="POST">
          <h1>Buat Akun</h1>
          <span>Gunakan email untuk mendaftar</span>
          <input type="text" name="name" placeholder="Nama Lengkap" required />
          <input type="email" name="email" placeholder="Email" required />
          <input type="password" name="password" placeholder="Password" required />
          <button type="submit" name="register">Daftar</button>
        </form>
      </div>

      <div class="sign-in-container">
        <form action="proses.php" method="POST">
          <h1>Masuk</h1>
          <span>Masuk dengan akun Anda</span>
          <input type="email" name="email" placeholder="Email" required />
          <input type="password" name="password" placeholder="Password" required />
          <a href="#">Lupa password?</a>
          <button type="submit" name="login">Masuk</button>
        </form>
      </div>
      
    </div>

    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <h1>Selamat Datang Kembali!</h1>
          <p>Tetap terhubung dengan kami, silakan login dengan data pribadi Anda</p>
          <button class="ghost" id="signIn">Ke Halaman Login</button>
        </div>
        <div class="overlay-panel overlay-right">
          <h1>Halo, Teman!</h1>
          <p>Masukkan detail pribadi Anda dan mulailah perjalanan bersama kami</p>
          <button class="ghost" id="signUp">Ke Halaman Daftar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="script.js"></script>
</body>

</html>