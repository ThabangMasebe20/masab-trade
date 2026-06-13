<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5 minute timeout
$timeout = 300;

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity'])) {
        if ((time() - $_SESSION['last_activity']) > $timeout) {
            $_SESSION = [];
            session_destroy();
            header("Location: /pages/auth/login.php?timeout=1");
            exit();
        }
    }
    $_SESSION['last_activity'] = time();
}
?>