<?php
session_start();

$users = [
  [
    'email' => 'admin@email.com',
    'password' => 'admin123',
    'role' => 'Admin',
    'nama' => 'Admin Bengkel'
  ],
  [
    'email' => 'pelanggan@email.com',
    'password' => 'pelanggan123',
    'role' => 'Pelanggan',
    'nama' => 'Budi Santoso'
  ]
];

if (isset($_POST['login'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $login_success = false;
  foreach ($users as $user) {
    if ($user['email'] === $email && $user['password'] === $password) {
      $_SESSION['user'] = $user;
      $login_success = true;
      header('Location: index.php');
      exit;
    }
  }

  if (!$login_success) {
    echo "<script>alert('Email atau password salah!\\nSilakan login dengan salah satu akun berikut :\\n(admin@email.com; admin123)\\natau\\n(pelanggan@email.com; pelanggan123).'); window.location='autentikasi.php';</script>";
    exit;
  }
}

if (isset($_POST['register'])) {
  echo "<script>alert('Tidak bisa registrasi saat ini.\\nSilakan login dengan salah satu akun berikut :\\n(admin@email.com; admin123)\\natau\\n(pelanggan@email.com; pelanggan123).'); window.location='autentikasi.php';</script>";
  exit;
}

if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: autentikasi.php');
  exit;
}
?>