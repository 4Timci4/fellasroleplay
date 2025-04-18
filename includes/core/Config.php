<?php
namespace Core;

/**
 * Config sınıfı, tüm yapılandırma değişkenlerini yönetir
 */
class Config {
    private static $instance = null;
    private $configs = [];
    
    private function __construct() {
        // Config dosyalarını yükle
        $this->loadConfigFiles();
    }
    
    /**
     * Singleton pattern - tek bir instance oluştur
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Tüm config dosyalarını yükle
     */
    private function loadConfigFiles() {
        $configFiles = [
            'config'   => __DIR__ . '/../config/config.php',
            'database' => __DIR__ . '/../config/database.php',
            'discord'  => __DIR__ . '/../config/discord.php',
            'error'    => __DIR__ . '/../config/error.php'
        ];
        
        foreach ($configFiles as $key => $file) {
            if (file_exists($file)) {
                $this->configs[$key] = require $file;
            }
        }
    }
    
    /**
     * Config değerini al (nokta notasyonu destekler)
     * Örnek: Config::get('discord.token')
     */
    public static function get($key, $default = null) {
        $instance = self::getInstance();
        
        // Nokta notasyonunu parçala
        $keys = explode('.', $key);
        $config = $instance->configs;
        
        // İlk anahtar config dosyasını belirtir
        $file = array_shift($keys);
        
        if (!isset($config[$file])) {
            return $default;
        }
        
        $config = $config[$file];
        
        // Değeri bul
        foreach ($keys as $segment) {
            if (isset($config[$segment])) {
                $config = $config[$segment];
            } else {
                return $default;
            }
        }
        
        return $config;
    }
    
    /**
     * Tüm konfigürasyonu veya belirli bir bölümünü döndür
     */
    public static function all($section = null) {
        $instance = self::getInstance();
        
        if ($section !== null) {
            return $instance->configs[$section] ?? [];
        }
        
        return $instance->configs;
    }
    
    /**
     * Bir yapılandırma değerini ayarla
     * 
     * @param string $key Nokta notasyonu ile yapılandırma anahtarı
     * @param mixed $value Ayarlanacak değer
     * @return void
     */
    public function set($key, $value) {
        // Nokta notasyonunu parçala
        $keys = explode('.', $key);
        
        // İlk anahtar config dosyasını belirtir
        $file = array_shift($keys);
        
        // Config dosyası yoksa oluştur
        if (!isset($this->configs[$file])) {
            $this->configs[$file] = [];
        }
        
        // İç içe dizilerde ilgili yere eriş
        $config = &$this->configs[$file];
        
        // Son anahtara kadar ilerle
        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                // Son anahtar ise değeri set et
                $config[$segment] = $value;
            } else {
                // Ara anahtar ise dizinin o kısmını hazırla
                if (!isset($config[$segment]) || !is_array($config[$segment])) {
                    $config[$segment] = [];
                }
                $config = &$config[$segment];
            }
        }
    }
}
