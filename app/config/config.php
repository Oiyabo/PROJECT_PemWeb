<?php
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

function env($key, $default = null) {
    return getenv($key) ?: $default;
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$project_path = str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']);
define('BASEURL', $protocol . "://" . $host . $project_path);

define('SESSION_TIMEOUT', 300);
define('SESSION_WARNING', 60);

define('DB_HOST', env('DB_HOST'));
define('DB_USER', env('DB_USER'));
define('DB_PASS', env('DB_PASS'));
define('DB_NAME', env('DB_NAME'));

define('MIDTRANS_SERVER_KEY', env('MIDTRANS_SERVER_KEY'));
define('MIDTRANS_CLIENT_KEY', env('MIDTRANS_CLIENT_KEY'));
define('MIDTRANS_IS_PRODUCTION', filter_var(env('IS_PRODUCTION'), FILTER_VALIDATE_BOOLEAN));

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Koneksi database gagal: ' . $e->getMessage());
        }
    }

    return $pdo;
}