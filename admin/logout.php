<?php
require_once 'includes/security_check.php'; // Güvenlik kontrolü
require_once 'includes/functions.php';

// Session'ı temizle
session_unset();
session_destroy();

// Giriş sayfasına yönlendir
header("Location: index.php");
exit;
