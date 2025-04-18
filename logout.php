<?php
/**
 * Çıkış yapma sayfası
 * 
 * Bu dosya, kullanıcının oturumunu sonlandırır ve giriş sayfasına yönlendirir.
 */

// Oturum başlat
session_start();

// Oturum değişkenlerini temizle
session_unset();
session_destroy();

// Giriş sayfasına yönlendir
header('Location: login.php?message=' . urlencode('Başarıyla çıkış yaptınız.'));
exit;
