<?php
/**
 * Hata Ayıklama Scripti
 * 
 * Bu geçici script, PHP hatalarını görüntülemek için kullanılır.
 * Sorun çözüldükten sonra kaldırılabilir.
 */

// Tüm hataların gösterilmesini sağlar
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hata günlüklemesini açar
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');

// Temel bilgileri kontrol eder
echo "<h2>PHP Bilgisi</h2>";
echo "<p>PHP Versiyonu: " . phpversion() . "</p>";
echo "<p>Geçerli Çalışma Dizini: " . getcwd() . "</p>";

// settings.php'yi debug modunda include eder
echo "<h2>Settings Sayfası Debug</h2>";
echo "<pre>";
try {
    // Çıktı tamponlaması başlat
    ob_start();
    
    // Dosyayı include et
    require_once('settings.php');
    
    // Çıktı tamponlamasını durdur ve temizle (ekrana basma)
    ob_end_clean();
    
    echo "settings.php başarıyla include edildi.\n";
} catch (Throwable $e) {
    echo "Hata Yakalandı:\n";
    echo "Mesaj: " . $e->getMessage() . "\n";
    echo "Dosya: " . $e->getFile() . "\n";
    echo "Satır: " . $e->getLine() . "\n";
    echo "İz: " . $e->getTraceAsString() . "\n";
}
echo "</pre>";

// statistics.php'yi debug modunda include eder
echo "<h2>Statistics Sayfası Debug</h2>";
echo "<pre>";
try {
    // Çıktı tamponlaması başlat
    ob_start();
    
    // Dosyayı include et
    require_once('statistics.php');
    
    // Çıktı tamponlamasını durdur ve temizle (ekrana basma)
    ob_end_clean();
    
    echo "statistics.php başarıyla include edildi.\n";
} catch (Throwable $e) {
    echo "Hata Yakalandı:\n";
    echo "Mesaj: " . $e->getMessage() . "\n";
    echo "Dosya: " . $e->getFile() . "\n";
    echo "Satır: " . $e->getLine() . "\n";
    echo "İz: " . $e->getTraceAsString() . "\n";
}
echo "</pre>";
