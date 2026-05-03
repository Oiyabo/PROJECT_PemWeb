<?php
session_start();
if (isset($_SESSION['user'])) {
  header('Location: dashboard.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login & Register</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="style/index.css">
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
          <div class="textLink">Sudah Punya Akun? <a id="signIn" href="#">Masuk Sekarang</a></div>
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
          <div class="textLink">Belum Punya Akun? <a id="signUp" href="#">Daftar Sekarang</a></div>
        </form>
      </div>

    </div>

    <div id="overlay-container" class="overlay-container">
      <div class="overlay-panel overlay-left">
      </div>
      <div class="overlay-panel overlay-right">
      </div>
    </div>
  </div>

  <script>
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const overlaContainer = document.getElementById('overlay-container');

    signUpButton.addEventListener('click', () => {
      overlaContainer.classList.add("changed");
    });

    signInButton.addEventListener('click', () => {
      overlaContainer.classList.remove("changed");
    });
  </script>
</body>

</html>