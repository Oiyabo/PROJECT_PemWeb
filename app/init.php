<?php
session_start();

require_once ROOT_PATH . '/app/config/config.php';

/**
 * @return string Path request tanpa leading slash
 */
function app_request_path(): string
{
    if (!isset($_GET['_url'])) {
        return '';
    }

    return trim((string) $_GET['_url'], '/');
}

function app_is_ajax_request(): bool
{
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return str_contains($accept, 'application/json');
}

function app_is_session_extend_request(): bool
{
    $path = strtolower(app_request_path());
    return $path === 'auth/extendsession';
}

if (isset($_SESSION['user'])) {
    $timeout = (int) SESSION_TIMEOUT;
    $isExtend = app_is_session_extend_request();

    if (isset($_SESSION['last_activity'])) {
        $idle = time() - (int) $_SESSION['last_activity'];

        if ($idle > $timeout && !$isExtend) {
            if (app_is_ajax_request()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode([
                    'session_expired' => true,
                    'message' => 'Session habis',
                ]);
                exit;
            }

            session_unset();
            session_destroy();
            session_start();

            $_SESSION['session_expired'] = true;
            $_SESSION['error'] = 'Session habis, silakan login kembali';

            header('Location: ' . BASEURL . '/auth');
            exit;
        }
    }

    if (!$isExtend) {
        $_SESSION['last_activity'] = time();
    }
}

require_once ROOT_PATH . '/app/libraries/MidtransService.php';

require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/core/App.php';
