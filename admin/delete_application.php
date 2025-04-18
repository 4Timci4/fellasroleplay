<?php
require_once 'includes/security_check.php'; // Güvenlik kontrolü
require_once 'includes/functions.php';

// Yönetici yetkisi kontrolü
requirePermission(2);

// Başvuru ID'sini al
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index';

// Başvuru silme işlemi
if ($id > 0) {
    if (deleteApplication($id)) {
        header("Location: $redirect?message=success");
    } else {
        header("Location: $redirect?error=delete_failed");
    }
} else {
    header("Location: $redirect?error=invalid_id");
}
exit;
