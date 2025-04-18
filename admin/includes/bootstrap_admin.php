<?php
/**
 * Admin paneli için bootstrap dosyası
 * Tüm admin sayfalarına dahil edilmelidir
 */

// Session kontrolü
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ana session.php dosyasını dahil et - proje kök dizininden
require_once dirname(dirname(__DIR__)) . '/includes/session.php';

// Community rolü (1285694535766245408) için gerekli session değişkenlerinin ayarlanması
if (isset($_SESSION['discord_roles']) && is_array($_SESSION['discord_roles'])) {
    // Community rolü
    if (in_array('1285694535766245408', $_SESSION['discord_roles'])) {
        $_SESSION['admin_id'] = $_SESSION['discord_user_id'] ?? null;
        $_SESSION['admin_permission'] = 1; // Community rolü için
    }
    
    // Fellas veya Developer rolleri (tam yetki)
    if (in_array('1353795720716746884', $_SESSION['discord_roles']) || 
        in_array('1267751951307903017', $_SESSION['discord_roles'])) {
        $_SESSION['admin_id'] = $_SESSION['discord_user_id'] ?? null;
        $_SESSION['admin_permission'] = 2; // Tam yetkili roller
    }
}

// has_admin_access fonksiyonu kontrolü
if (!function_exists('has_admin_access')) {
    header("Location: ../login.php?error=" . urlencode('Oturum kontrolü yapılamadı. Lütfen giriş yapın.'));
    exit;
}

// Erişim yetkisi kontrolü
if (!has_admin_access()) {
    header("Location: ../anasayfa.php?error=" . urlencode('Bu sayfaya erişim yetkiniz bulunmamaktadır.'));
    exit;
}

// functions.php dosyasını dahil et
require_once dirname(__FILE__) . '/functions.php';
