<?php
namespace Core;

/**
 * Cache sınıfı, önbellek işlemlerini yönetir
 */
class Cache {
    private static $instance = null;
    private $cacheData = [];
    private $cacheTimes = [];
    
    private function __construct() {}
    
    /**
     * Singleton pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Önbellekten veri al
     */
    public static function get($key, $default = null) {
        $instance = self::getInstance();
        
        if (isset($instance->cacheData[$key]) && 
            isset($instance->cacheTimes[$key]) && 
            time() - $instance->cacheTimes[$key] < self::getExpiry($key)) {
            return $instance->cacheData[$key];
        }
        
        return $default;
    }
    
    /**
     * Önbelleğe veri kaydet
     */
    public static function set($key, $value, $expiry = null) {
        $instance = self::getInstance();
        $instance->cacheData[$key] = $value;
        $instance->cacheTimes[$key] = time();
        
        return true;
    }
    
    /**
     * Önbellekten veriyi sil
     */
    public static function forget($key) {
        $instance = self::getInstance();
        
        if (isset($instance->cacheData[$key])) {
            unset($instance->cacheData[$key]);
            unset($instance->cacheTimes[$key]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Tüm önbelleği temizle
     */
    public static function clear() {
        $instance = self::getInstance();
        $instance->cacheData = [];
        $instance->cacheTimes = [];
        
        return true;
    }
    
    /**
     * Anahtara özel sona erme süresini al
     */
    private static function getExpiry($key) {
        $expiryMap = [
            'forum_categories' => 300,      // 5 dakika
            'forum_stats' => 600,           // 10 dakika
            'discord_members' => 900,       // 15 dakika
            'discord_user' => 900,          // 15 dakika
            'discord_role_count' => 900,    // 15 dakika
            'default' => 300                // Varsayılan: 5 dakika
        ];
        
        // Anahtar ön eki ile eşleşme yap
        foreach ($expiryMap as $prefix => $expiry) {
            if (strpos($key, $prefix) === 0) {
                return $expiry;
            }
        }
        
        return $expiryMap['default'];
    }
    
    /**
     * Belirli bir öneki veya modüle sahip tüm önbellek girişlerini temizle
     */
    public static function clearPrefix($prefix) {
        $instance = self::getInstance();
        
        foreach ($instance->cacheData as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                unset($instance->cacheData[$key]);
                unset($instance->cacheTimes[$key]);
            }
        }
        
        return true;
    }
}
