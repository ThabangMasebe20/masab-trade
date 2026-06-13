<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin($redirect_back = '') {
    if (!isset($_SESSION['user_id'])) {
        if ($redirect_back) {
            $_SESSION['redirect_after_login'] = $redirect_back;
        }
        header("Location: /pages/auth/login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header("Location: /index.php");
        exit();
    }
}

function requireSeller() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'seller' && $_SESSION['user_role'] !== 'admin') {
        header("Location: /index.php");
        exit();
    }
}
?>