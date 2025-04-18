<?php
/**
 * Veritabanı bağlantısı adaptör fonksiyonları
 * 
 * Bu dosya, eski db.php fonksiyonlarını yeni Database sınıfına yönlendirir.
 */

// Yeni sınıfları dahil et
require_once __DIR__ . '/../../includes/bootstrap.php';

/**
 * Veritabanı bağlantısı için fonksiyon
 * 
 * @return PDO Veritabanı bağlantı nesnesi
 */
function getDbConnection() {
    return \Core\Database::getInstance()->getConnection();
}

/**
 * FellasRP oyun veritabanı bağlantısı
 * 
 * @return PDO|null Veritabanı bağlantı nesnesi veya hata durumunda null
 */
function getGameDbConnection() {
    return \Core\Database::getInstance('game')->getConnection();
}
