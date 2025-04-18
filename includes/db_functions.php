<?php
/**
 * Veritabanı işlemleri için merkezi fonksiyonlar ve yardımcılar
 * 
 * Bu dosya, prosedürel koddan OOP'ye geçiş sürecinde geriye uyumluluk sağlar.
 * Yeni kod bu fonksiyonları kullanmamalı, doğrudan Database sınıfını tercih etmelidir.
 * 
 * @deprecated OOP yaklaşımı ile Core\Database sınıfını kullanın
 */

// Önce bootstrap.php dosyasını dahil et
require_once __DIR__ . '/bootstrap.php';

use Core\Database;
use Core\Config;

/**
 * Veritabanı bağlantısı için fonksiyon, admin/includes/db.php ile uyumlu
 * 
 * @return PDO Veritabanı bağlantı nesnesi
 * @deprecated OOP Database::getInstance()->getConnection() kullanın
 */
function getDbConnection() {
    try {
        // OOP Database sınıfını kullanarak geriye dönük uyumluluk sağla
        return Database::getInstance('default')->getConnection();
    } catch(\Exception $e) {
        error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
        echo "Veritabanı bağlantı hatası. Lütfen daha sonra tekrar deneyin.";
        exit;
    }
}

/**
 * FellasRP oyun veritabanı bağlantısı, admin/includes/db.php ile uyumlu
 * 
 * @return PDO|null Veritabanı bağlantı nesnesi veya hata durumunda null
 * @deprecated OOP Database::getInstance('game')->getConnection() kullanın
 */
function getGameDbConnection() {
    try {
        // OOP Database sınıfını kullanarak geriye dönük uyumluluk sağla
        return Database::getInstance('game')->getConnection();
    } catch(\Exception $e) {
        error_log("Oyun veritabanı bağlantı hatası: " . $e->getMessage());
        return null;
    }
}

/**
 * Özel SQL sorgusu çalıştırır (SELECT için)
 * 
 * @param string $sql SQL sorgusu
 * @param array $params Sorgu parametreleri
 * @param string $connectionName Bağlantı adı (default/game)
 * @return array Sonuç dizisi
 * @deprecated OOP Database::getInstance()->fetchAll() kullanın
 */
function db_query($sql, $params = [], $connectionName = 'default') {
    return Database::getInstance($connectionName)->fetchAll($sql, $params);
}

/**
 * Tek bir sonuç satırı döndürür
 * 
 * @param string $sql SQL sorgusu
 * @param array $params Sorgu parametreleri
 * @param string $connectionName Bağlantı adı (default/game)
 * @return array|null Sonuç satırı veya null
 * @deprecated OOP Database::getInstance()->fetchOne() kullanın
 */
function db_query_single($sql, $params = [], $connectionName = 'default') {
    return Database::getInstance($connectionName)->fetchOne($sql, $params);
}

/**
 * Tek bir değer döndürür (ilk satır, ilk sütun)
 * 
 * @param string $sql SQL sorgusu
 * @param array $params Sorgu parametreleri
 * @param string $connectionName Bağlantı adı (default/game)
 * @return mixed Sorgu sonucu
 * @deprecated OOP Database::getInstance()->fetchValue() kullanın
 */
function db_query_value($sql, $params = [], $connectionName = 'default') {
    return Database::getInstance($connectionName)->fetchValue($sql, $params);
}

/**
 * INSERT işlemi gerçekleştirir ve eklenen kaydın ID'sini döndürür
 * 
 * @param string $table Tablo adı
 * @param array $data Eklenecek veriler [sütun => değer]
 * @param string $connectionName Bağlantı adı (default/game)
 * @return int|string Eklenen kaydın ID'si
 * @deprecated OOP Database::getInstance()->insert() kullanın
 */
function db_insert($table, $data, $connectionName = 'default') {
    return Database::getInstance($connectionName)->insert($table, $data);
}

/**
 * UPDATE işlemi gerçekleştirir ve etkilenen satır sayısını döndürür
 * 
 * @param string $table Tablo adı
 * @param array $data Güncellenecek veriler [sütun => değer]
 * @param string $where WHERE koşulu (? kullanılarak parametreler belirtilebilir)
 * @param array $whereParams WHERE koşulu için parametreler
 * @param string $connectionName Bağlantı adı (default/game)
 * @return int Etkilenen satır sayısı
 * @deprecated OOP Database::getInstance()->update() kullanın
 */
function db_update($table, $data, $where, $whereParams = [], $connectionName = 'default') {
    return Database::getInstance($connectionName)->update($table, $data, $where, $whereParams);
}

/**
 * DELETE işlemi gerçekleştirir ve etkilenen satır sayısını döndürür
 * 
 * @param string $table Tablo adı
 * @param string $where WHERE koşulu (? kullanılarak parametreler belirtilebilir)
 * @param array $whereParams WHERE koşulu için parametreler
 * @param string $connectionName Bağlantı adı (default/game)
 * @return int Etkilenen satır sayısı
 * @deprecated OOP Database::getInstance()->delete() kullanın
 */
function db_delete($table, $where, $whereParams = [], $connectionName = 'default') {
    return Database::getInstance($connectionName)->delete($table, $where, $whereParams);
}

/**
 * Transaction başlatır
 * 
 * @param string $connectionName Bağlantı adı (default/game)
 * @return bool İşlem sonucu
 * @deprecated OOP Database::getInstance()->beginTransaction() kullanın
 */
function db_begin_transaction($connectionName = 'default') {
    return Database::getInstance($connectionName)->beginTransaction();
}

/**
 * Transaction commit eder
 * 
 * @param string $connectionName Bağlantı adı (default/game) 
 * @return bool İşlem sonucu
 * @deprecated OOP Database::getInstance()->commit() kullanın
 */
function db_commit($connectionName = 'default') {
    return Database::getInstance($connectionName)->commit();
}

/**
 * Transaction geri alır
 * 
 * @param string $connectionName Bağlantı adı (default/game)
 * @return bool İşlem sonucu
 * @deprecated OOP Database::getInstance()->rollback() kullanın
 */
function db_rollback($connectionName = 'default') {
    return Database::getInstance($connectionName)->rollback();
}

/**
 * OOP yaklaşımıyla veritabanı işlemlerini gerçekleştiren yardımcı wrapper sınıfı
 * Admin panelindeki DbAdapter ile uyumlu API sunar
 */
class DbHelper {
    private static $defaultInstance = null;
    private static $gameInstance = null;
    private $database;
    
    /**
     * Singleton pattern - varsayılan veritabanı için instance
     */
    public static function getDefaultInstance() {
        if (self::$defaultInstance === null) {
            self::$defaultInstance = new self('default');
        }
        return self::$defaultInstance;
    }
    
    /**
     * Singleton pattern - oyun veritabanı için instance
     */
    public static function getGameInstance() {
        if (self::$gameInstance === null) {
            self::$gameInstance = new self('game');
        }
        return self::$gameInstance;
    }
    
    /**
     * DbHelper constructor
     */
    private function __construct($connectionName) {
        $this->database = Database::getInstance($connectionName);
    }
    
    /**
     * PDO bağlantısını döndür
     */
    public function getConnection() {
        return $this->database->getConnection();
    }
    
    /**
     * Sorgu çalıştır ve hazır ifade döndür
     */
    public function query($sql, $params = []) {
        return $this->database->query($sql, $params);
    }
    
    /**
     * Tek satır getir
     */
    public function fetchOne($sql, $params = []) {
        return $this->database->fetchOne($sql, $params);
    }
    
    /**
     * Tüm sonuçları getir
     */
    public function fetchAll($sql, $params = []) {
        return $this->database->fetchAll($sql, $params);
    }
    
    /**
     * Tek bir değer getir
     */
    public function fetchValue($sql, $params = []) {
        return $this->database->fetchValue($sql, $params);
    }
    
    /**
     * Insert işlemi gerçekleştir
     */
    public function insert($table, $data) {
        return $this->database->insert($table, $data);
    }
    
    /**
     * Update işlemi gerçekleştir
     */
    public function update($table, $data, $where, $whereParams = []) {
        return $this->database->update($table, $data, $where, $whereParams);
    }
    
    /**
     * Delete işlemi gerçekleştir
     */
    public function delete($table, $where, $whereParams = []) {
        return $this->database->delete($table, $where, $whereParams);
    }
    
    /**
     * Transaction işlemleri
     */
    public function beginTransaction() {
        return $this->database->beginTransaction();
    }
    
    public function commit() {
        return $this->database->commit();
    }
    
    public function rollback() {
        return $this->database->rollback();
    }
}
