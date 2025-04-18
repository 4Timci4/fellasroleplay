<?php
/**
 * Hata ayıklama için geçici dosya
 * NOT: Yalnızca geliştirme ortamında kullanılmalıdır, canlı ortamda silinmelidir!
 */

// Hata raporlamasını etkinleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hata mesajlarını bir dosyaya da kaydet
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

echo "<!-- PHP Hata ayıklama modu etkin -->\n";
?>
