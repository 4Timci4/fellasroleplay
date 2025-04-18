<?php
/**
 * Uygulama başlatma dosyası
 * Tüm sayfalarda dahil edilmelidir
 */

// Oturum başlat
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hata raporlama artık ErrorHandler sınıfı tarafından yönetiliyor
// Eski kod:
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Manuel class autoloader - OOP sınıfları için
spl_autoload_register(function ($class) {
    // Namespace'i parçala
    $parts = explode('\\', $class);
    $className = array_pop($parts);
    
    // Namespace yolunu değiştir
    $path = __DIR__ . '/' . strtolower(implode('/', $parts));
    
    // Class dosyasının tam yolu
    $file = $path . '/' . $className . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});

// Core sınıfları öncelikle yükleyelim (Diğer bileşenler bunlara bağlı)
// Config ve Database önce yüklenmeli, diğer sınıflar bunları kullanır
if (file_exists(__DIR__ . '/core/Config.php')) {
    require_once __DIR__ . '/core/Config.php';
}

if (file_exists(__DIR__ . '/core/Database.php')) {
    require_once __DIR__ . '/core/Database.php';
}

// Hata işleme sınıfını yükle ve başlat
if (file_exists(__DIR__ . '/core/ErrorHandler.php')) {
    require_once __DIR__ . '/core/ErrorHandler.php';
    // Hata işleyiciyi başlat
    \Core\ErrorHandler::getInstance()->register();
}

// Veritabanı işlemleri ve yardımcı fonksiyonlarını dahil et
// Bu, eski prosedürel kodlardan OOP'ye geçiş için geriye uyumlu katman sağlar
require_once __DIR__ . '/db_functions.php';

// Eski fonksiyonlarla uyumluluk sağlamak için
// ÖNEMLİ: Legacy compatibility, yardımcı fonksiyonlardan ÖNCE yüklenmeli
// Bu sayede çakışma sorunları çözülür
require_once __DIR__ . '/legacy_compatibility.php';

// Yardımcı fonksiyonları dahil et
require_once __DIR__ . '/helpers/common_helpers.php';
require_once __DIR__ . '/helpers/forum_helpers.php';
require_once __DIR__ . '/helpers/error_helpers.php';

// Bakım modu işlemleri maintenance.php dosyasından geliyor
// Bu fonksiyonlar orada tanımlandığı için buradan kaldırıldı
require_once __DIR__ . '/maintenance.php';
