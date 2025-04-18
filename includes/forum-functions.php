<?php
/**
 * Forum işlemleri için gerekli fonksiyonlar
 * 
 * Bu dosya, alt modüllere ayrılmış forum fonksiyonlarını içe aktarır.
 * Eski kodun yeniden düzenlenmiş ve modüler hale getirilmiş versiyonudur.
 */

// Bootstrapper'ı dahil et - tüm sınıfları, helpers'ları ve legacy_compatibility'yi içerir
require_once __DIR__ . '/bootstrap.php';

// Yeni yardımcı forum fonksiyonlarını dahil et
require_once __DIR__ . '/helpers/forum_helpers.php';

// Forum modüllerini dahil et
require_once __DIR__ . '/forum/categories.php';  // Kategori işlemleri
require_once __DIR__ . '/forum/topics.php';      // Konu işlemleri
require_once __DIR__ . '/forum/comments.php';    // Yorum işlemleri
require_once __DIR__ . '/forum/users.php';       // Kullanıcı işlemleri
require_once __DIR__ . '/forum/formatters.php';  // Formatlama işlemleri
