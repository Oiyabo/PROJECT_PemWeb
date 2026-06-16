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

		$data = [
			'session_expired' => $_SESSION['session_expired'] ?? false
		];

		unset($_SESSION['session_expired']);

		$this->view('auth/login', $data);
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
		}

		$_SESSION['error'] = 'Email atau password salah!';
		header('Location: ' . BASEURL . '/auth');
		exit;
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

		if (strlen($password) < 7 || !preg_match('/[0-9]/', $password)) {
			$_SESSION['error'] = 'Password harus minimal 7 karakter dan mengandung angka.';
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

	public function lupaPassword(): void
	{
		if (isset($_SESSION['user'])) {
			$this->redirectToDashboard();
		}

		$this->view('auth/lupa-password');
	}

	public function prosesLupaPassword(): void
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ' . BASEURL . '/auth/lupaPassword');
			exit;
		}

		$email = trim($_POST['email'] ?? '');
		$password = trim($_POST['password'] ?? '');
		$konfirmasiPassword = trim($_POST['konfirmasi_password'] ?? '');

		if (empty($email) || empty($password) || empty($konfirmasiPassword)) {
			$_SESSION['error'] = 'Semua field wajib diisi.';
			header('Location: ' . BASEURL . '/auth/lupaPassword');
			exit;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$_SESSION['error'] = 'Format email tidak valid.';
			header('Location: ' . BASEURL . '/auth/lupaPassword');
			exit;
		}

		if (strlen($password) < 6) {
			$_SESSION['error'] = 'Password minimal 6 karakter.';
			header('Location: ' . BASEURL . '/auth/lupaPassword');
			exit;
		}

		if ($password !== $konfirmasiPassword) {
			$_SESSION['error'] = 'Konfirmasi password tidak sesuai.';
			header('Location: ' . BASEURL . '/auth/lupaPassword');
			exit;
		}

		$user = $this->userModel->findByEmail($email);

		if (!$user) {
			$_SESSION['error'] = 'Email tidak ditemukan.';
			header('Location: ' . BASEURL . '/auth/lupaPassword');
			exit;
		}

		if ($this->userModel->updatePasswordByEmail($email, $password)) {
			$_SESSION['success'] = 'Password berhasil diubah. Silakan login.';
			header('Location: ' . BASEURL . '/auth');
			exit;
		}

		$_SESSION['error'] = 'Password gagal diubah. Silakan coba lagi.';
		header('Location: ' . BASEURL . '/auth/lupaPassword');
		exit;
	}

	public function logout(): void
	{
		session_unset();
		session_destroy();

		header('Location: ' . BASEURL . '/auth');
		exit;
	}

	public function extendSession(): void
	{
		header('Content-Type: application/json; charset=utf-8');

		if (!isset($_SESSION['user'])) {
			http_response_code(401);
			echo json_encode([
				'ok' => false,
				'session_expired' => true,
				'message' => 'Session habis',
			]);
			exit;
		}

		$_SESSION['last_activity'] = time();
		$expiresAt = time() + (int) SESSION_TIMEOUT;

		echo json_encode([
			'ok' => true,
			'expires_at' => $expiresAt,
			'timeout' => (int) SESSION_TIMEOUT,
		]);
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