<?php
class Auth extends Controller
{
  private object $userModel;
  public function __construct()
  {
    $this->userModel = $this->model('UserModel');
  }

  public function index(): void
  {
    if (isset($_SESSION['user'])) {
      $this->redirectToDashboard();
    }
    $this->view('auth/login');
  }

  public function login(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: ' . BASEURL . '/auth');
      exit;
    }

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
      $_SESSION['error'] = 'Email dan password tidak boleh kosong.';
      header('Location: ' . BASEURL . '/auth');
      exit;
    }

    $user = $this->userModel->findByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user'] = [
        'id' => $user['id'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'role' => $user['role'],
      ];
      $this->redirectToDashboard();
    } else {
      $_SESSION['error'] = 'Email atau password salah!';
      header('Location: ' . BASEURL . '/auth');
      exit;
    }
  }

  public function register(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: ' . BASEURL . '/auth');
      exit;
    }

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($nama) || empty($email) || empty($password)) {
      $_SESSION['error'] = 'Semua field registrasi wajib diisi.';
      header('Location: ' . BASEURL . '/auth');
      exit;
    }

    if ($this->userModel->findByEmail($email)) {
      $_SESSION['error'] = 'Email sudah terdaftar. Silakan gunakan email lain.';
      header('Location: ' . BASEURL . '/auth');
      exit;
    }

    if ($this->userModel->register($nama, $email, $password)) {
      $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
    } else {
      $_SESSION['error'] = 'Registrasi gagal. Coba lagi.';
    }

    header('Location: ' . BASEURL . '/auth');
    exit;
  }

  public function logout(): void
  {
    session_unset();
    session_destroy();
    header('Location: ' . BASEURL . '/auth');
    exit;
  }

  private function redirectToDashboard(): void
  {
    $role = $_SESSION['user']['role'] ?? '';
    if ($role === 'Admin') {
      header('Location: ' . BASEURL . '/admin');
    } else {
      header('Location: ' . BASEURL . '/pelanggan');
    }
    exit;
  }
}
