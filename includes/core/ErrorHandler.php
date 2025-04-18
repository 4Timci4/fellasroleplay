<?php
declare(strict_types=1);

namespace Core;

/**
 * ErrorHandler sınıfı
 * 
 * Sistem genelinde hata yönetimi için merkezi bir mekanizma sağlar.
 * Farklı hata türlerini yakalayıp uygun şekilde işler, loglar ve 
 * ortam (development, production) bazlı yanıtlar üretir.
 * 
 * @package Core
 */
class ErrorHandler
{
    /** 
     * Singleton instance
     * @var ErrorHandler|null
     */
    private static ?ErrorHandler $instance = null;
    
    /**
     * Hata log dosyası yolu
     * @var string
     */
    private string $logFile;
    
    /**
     * Ortam: 'development', 'testing', 'production'
     * @var string
     */
    private string $environment;
    
    /**
     * Hata seviyeleri
     */
    public const ERROR_LEVEL_DEBUG = 'debug';
    public const ERROR_LEVEL_INFO = 'info';
    public const ERROR_LEVEL_WARNING = 'warning';
    public const ERROR_LEVEL_ERROR = 'error';
    public const ERROR_LEVEL_CRITICAL = 'critical';
    
    /**
     * Private constructor, singleton pattern
     */
    private function __construct()
    {
        // Config sınıfını kullanarak ortamı belirle
        $this->environment = Config::getInstance()->get('app.environment', 'production');
        
        // Log dosyası yolunu belirle
        $this->logFile = dirname(__DIR__, 2) . '/logs/error.log';
        
        // Dizin yoksa oluştur
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Singleton instance'ı döndürür
     * 
     * @return ErrorHandler
     */
    public static function getInstance(): ErrorHandler
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * PHP hata yakalayıcısını ayarlar
     * 
     * @return void
     */
    public function register(): void
    {
        // Hata gösterme ayarları ortama göre ayarlanır
        if ($this->environment === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', '0');
        }
        
        // Özel hata yakalayıcıyı ayarla
        set_error_handler([$this, 'handleError']);
        
        // Özel exception yakalayıcıyı ayarla
        set_exception_handler([$this, 'handleException']);
        
        // Terminate yakalayıcı
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    /**
     * PHP hatalarını işler
     * 
     * @param int $level Hata seviyesi
     * @param string $message Hata mesajı
     * @param string $file Hatanın oluştuğu dosya
     * @param int $line Hatanın oluştuğu satır
     * @return bool
     */
    public function handleError(int $level, string $message, string $file, int $line): bool
    {
        // Hata seviyesini belirle
        $errorLevel = $this->mapErrorLevel($level);
        
        // Hata log formatı
        $errorMessage = sprintf(
            "[%s] [%s] %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $errorLevel,
            $message,
            $file,
            $line
        );
        
        // Hatayı logla
        $this->logError($errorMessage);
        
        // Development ortamında hatayı göster
        if ($this->environment === 'development') {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "<h3>PHP Error</h3>";
            echo "<p><strong>Type:</strong> " . $this->getErrorTypeString($level) . "</p>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($message) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($file) . "</p>";
            echo "<p><strong>Line:</strong> " . $line . "</p>";
            echo "</div>";
        }
        
        // Kritik hata değilse normal işleme devam et
        if ($level !== E_ERROR && $level !== E_PARSE && $level !== E_CORE_ERROR && 
            $level !== E_COMPILE_ERROR && $level !== E_USER_ERROR) {
            return true;
        }
        
        // Kritik hatalar için uygulama çıkışını işle
        exit(1);
    }
    
    /**
     * İstisnaları (exceptions) işler
     * 
     * @param \Throwable $exception Yakalanan istisna
     * @return void
     */
    public function handleException(\Throwable $exception): void
    {
        // İstisna log formatı
        $errorMessage = sprintf(
            "[%s] [EXCEPTION] %s in %s on line %d\nStack trace: %s",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        // İstisnayı logla
        $this->logError($errorMessage);
        
        // Development ortamında istisnayı göster
        if ($this->environment === 'development') {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "<h3>Uncaught Exception</h3>";
            echo "<p><strong>Type:</strong> " . get_class($exception) . "</p>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
            echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
            echo "<p><strong>Stack Trace:</strong></p>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
            echo "</div>";
        } else {
            // Production ortamında kullanıcı dostu hata sayfası göster
            $this->displayFriendlyErrorPage();
        }
    }
    
    /**
     * Kapatma sırasında yakalanan fatal hataları işler
     * 
     * @return void
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            // Production ortamında kullanıcı dostu hata sayfası göster
            if ($this->environment !== 'development') {
                $this->displayFriendlyErrorPage();
            }
        }
    }
    
    /**
     * Hata loglar
     * 
     * @param string $message Log mesajı
     * @return void
     */
    public function logError(string $message): void
    {
        // Dosyaya yaz
        file_put_contents($this->logFile, $message . PHP_EOL, FILE_APPEND);
        
        // wp_log() fonksiyonu varsa WordPress entegrasyonu için kullan
        if (function_exists('wp_log')) {
            wp_log($message);
        }
    }
    
    /**
     * PHP hata seviyesini metin seviyesine dönüştürür
     * 
     * @param int $level PHP hata seviyesi
     * @return string
     */
    private function mapErrorLevel(int $level): string
    {
        switch ($level) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return self::ERROR_LEVEL_CRITICAL;
                
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return self::ERROR_LEVEL_WARNING;
                
            case E_NOTICE:
            case E_USER_NOTICE:
                return self::ERROR_LEVEL_INFO;
                
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return self::ERROR_LEVEL_DEBUG;
                
            default:
                return self::ERROR_LEVEL_ERROR;
        }
    }
    
    /**
     * PHP hata seviyesini okunabilir metne dönüştürür
     * 
     * @param int $level PHP hata seviyesi
     * @return string
     */
    private function getErrorTypeString(int $level): string
    {
        switch ($level) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
            default:
                return 'UNKNOWN';
        }
    }
    
    /**
     * Kullanıcı dostu hata sayfası gösterir
     * 
     * @return void
     */
    public function displayFriendlyErrorPage(): void
    {
        ob_clean();
        http_response_code(500);
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Sistem Hatası</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f5f5f5;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }
                .error-container {
                    background-color: #fff;
                    padding: 2rem;
                    border-radius: 5px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    text-align: center;
                    max-width: 500px;
                }
                h1 {
                    color: #e74c3c;
                    margin-top: 0;
                }
                p {
                    color: #555;
                    margin-bottom: 1.5rem;
                }
                .btn {
                    display: inline-block;
                    background-color: #3498db;
                    color: #fff;
                    padding: 0.5rem 1rem;
                    text-decoration: none;
                    border-radius: 3px;
                    transition: background-color 0.3s;
                }
                .btn:hover {
                    background-color: #2980b9;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>Üzgünüz!</h1>
                <p>İşleminiz sırasında bir hata oluştu. Teknik ekibimiz bu sorun hakkında bilgilendirildi.</p>
                <p>Lütfen daha sonra tekrar deneyin veya destek ekibimizle iletişime geçin.</p>
                <a href="/" class="btn">Ana Sayfaya Dön</a>
            </div>
        </body>
        </html>';
        
        exit(1);
    }
}
