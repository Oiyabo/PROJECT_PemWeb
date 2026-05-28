<?php
session_start();

require_once ROOT_PATH . '/app/config/config.php';

if(isset($_SESSION['user'])) {

    $timeout = 300;

    if(isset($_SESSION['last_activity'])) {

        if((time() - $_SESSION['last_activity']) > $timeout) {

            session_unset();
            session_destroy();

            session_start();

            $_SESSION['error'] = 'Session habis, silakan login kembali';

            header('Location: /PROJECT_PemWeb/public/auth/login');
            exit;
        }
    }

    $_SESSION['last_activity'] = time();
}

require_once ROOT_PATH . '/app/libraries/MidtransService.php';

require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/core/App.php';