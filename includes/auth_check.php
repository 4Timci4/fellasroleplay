<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session_lifetime = 86400;

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $session_lifetime)) {
    session_unset();
    session_destroy();
    
    header('Location: login.php?message=' . urlencode('Oturum süreniz doldu. Lütfen tekrar giriş yapın.'));
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    session_unset();
    session_destroy();
    
    header('Location: login.php');
    exit;
}

$_SESSION['login_time'] = time();
