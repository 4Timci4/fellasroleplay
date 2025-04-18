<?php
/**
 * Admin paneli için veritabanı işlemleri
 * 
 * Merkezi veritabanı fonksiyonlarını dahil eder ve admin paneli için
 * ek veritabanı işlevselliği sağlar.
 * 
 * @deprecated Doğrudan Core\Database sınıfını veya includes/db_functions.php'yi kullanın
 */

// Merkezi veritabanı fonksiyonlarını dahil et
require_once(__DIR__ . '/../../includes/db_functions.php');

// DbAdapter sınıfını tanımla (geriye dönük uyumluluk için DbHelper ile aynı)
if (!class_exists('DbAdapter')) {
    /**
     * OOP yaklaşımıyla veritabanı işlemlerini gerçekleştiren yardımcı adapter sınıfı
     * @deprecated Doğrudan Core\Database sınıfını kullanın
     */
    class DbAdapter extends DbHelper {
        // DbHelper'ın tüm işlevselliğini miras alır
    }
}
