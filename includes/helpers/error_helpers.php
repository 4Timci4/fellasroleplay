<?php
/**
 * Hata işleme yardımcı fonksiyonları
 * 
 * Uygulama genelinde hata yönetimi için kullanılabilecek
 * yardımcı fonksiyonları içerir.
 */

use Core\ErrorHandler;
use Core\Exceptions\AppException;
use Core\Exceptions\DatabaseException;
use Core\Exceptions\ValidationException;
use Core\Exceptions\APIException;

/**
 * Hata loglar
 * 
 * @param string $message Hata mesajı
 * @param string $level Hata seviyesi (debug, info, warning, error, critical)
 * @return void
 */
function log_error(string $message, string $level = ErrorHandler::ERROR_LEVEL_ERROR): void
{
    ErrorHandler::getInstance()->logError(sprintf("[%s] %s", strtoupper($level), $message));
}

/**
 * Debug mesajı loglar (sadece development ortamında)
 * 
 * @param string $message Debug mesajı
 * @param array $context Ek veriler
 * @return void
 */
function log_debug(string $message, array $context = []): void
{
    $contextStr = !empty($context) ? " Context: " . json_encode($context) : "";
    log_error($message . $contextStr, ErrorHandler::ERROR_LEVEL_DEBUG);
}

/**
 * Bilgi mesajı loglar
 * 
 * @param string $message Bilgi mesajı
 * @return void
 */
function log_info(string $message): void
{
    log_error($message, ErrorHandler::ERROR_LEVEL_INFO);
}

/**
 * Uyarı mesajı loglar
 * 
 * @param string $message Uyarı mesajı
 * @return void
 */
function log_warning(string $message): void
{
    log_error($message, ErrorHandler::ERROR_LEVEL_WARNING);
}

/**
 * Kritik hata loglar
 * 
 * @param string $message Kritik hata mesajı
 * @return void
 */
function log_critical(string $message): void
{
    log_error($message, ErrorHandler::ERROR_LEVEL_CRITICAL);
}

/**
 * Veritabanı hatası oluşturur
 * 
 * @param string $message Hata mesajı
 * @param string|null $query SQL sorgusu
 * @param int $code Hata kodu
 * @param string $userMessage Kullanıcıya gösterilecek mesaj
 * @throws DatabaseException
 */
function db_error(string $message, ?string $query = null, int $code = 0, string $userMessage = ""): void
{
    throw new DatabaseException($message, $query, $code, $userMessage);
}

/**
 * Doğrulama hatası oluşturur
 * 
 * @param array $errors Doğrulama hataları
 * @param string $message Genel hata mesajı
 * @throws ValidationException
 */
function validation_error(array $errors, string $message = "Doğrulama hatası"): void
{
    throw ValidationException::withErrors($errors);
}

/**
 * API hatası oluşturur
 * 
 * @param string $message Hata mesajı
 * @param string|null $endpoint API endpoint
 * @param int|null $statusCode HTTP durum kodu
 * @param array|null $request İstek verileri
 * @param mixed $response Yanıt verileri
 * @throws APIException
 */
function api_error(string $message, ?string $endpoint = null, ?int $statusCode = null, ?array $request = null, $response = null): void
{
    throw new APIException($message, $endpoint, $statusCode, $request, $response);
}

/**
 * Genel uygulama hatası oluşturur
 * 
 * @param string $message Hata mesajı
 * @param int $code Hata kodu
 * @param string $userMessage Kullanıcıya gösterilecek mesaj
 * @param array $context Ek hata verileri
 * @throws AppException
 */
function app_error(string $message, int $code = 0, string $userMessage = "", array $context = []): void
{
    throw new AppException($message, $code, 500, $userMessage, $context);
}

/**
 * Fatal hata ile uygulamadan çıkar
 * 
 * @param string $message Hata mesajı
 * @param int $code Çıkış kodu
 * @return void
 */
function fatal_error(string $message, int $code = 1): void
{
    log_critical($message);
    
    // Üretim ortamında kullanıcı dostu hata mesajı
    $env = \Core\Config::getInstance()->get('app.environment', 'production');
    
    if ($env !== 'development') {
        ErrorHandler::getInstance()->displayFriendlyErrorPage();
    } else {
        // Geliştirme ortamında detaylı hata mesajı
        echo '<div style="background-color: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border: 1px solid #f5c6cb; border-radius: 5px;">';
        echo '<h2>Fatal Error</h2>';
        echo '<p>' . htmlspecialchars($message) . '</p>';
        echo '<p>Uygulama sonlandırıldı.</p>';
        echo '</div>';
    }
    
    exit($code);
}

/**
 * WordPress formatında hata loglar (WordPress entegrasyonu için)
 * 
 * @param string $message Log mesajı
 * @return void
 */
function wp_log(string $message): void
{
    if (defined('WP_DEBUG') && WP_DEBUG === true) {
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG === true) {
            error_log($message);
        }
    }
}

/**
 * Form verilerini doğrular ve hata durumunda ValidationException fırlatır
 * 
 * @param array $data Doğrulanacak veriler
 * @param array $rules Doğrulama kuralları [alan => [kural1, kural2, ...]]
 * @param array $messages Özel hata mesajları [alan.kural => mesaj]
 * @return array Doğrulanmış veriler
 * @throws ValidationException
 */
function validate_form(array $data, array $rules, array $messages = []): array
{
    $errors = [];
    $validatedData = [];
    
    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? null;
        $validatedData[$field] = $value;
        
        foreach ($fieldRules as $rule) {
            // "required" kuralı
            if ($rule === 'required' && (is_null($value) || $value === '')) {
                $errors[$field] = $messages[$field . '.required'] ?? sprintf("%s alanı zorunludur", $field);
                break;
            }
            
            // "email" kuralı
            if ($rule === 'email' && !is_null($value) && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = $messages[$field . '.email'] ?? sprintf("%s geçerli bir e-posta adresi değil", $field);
                break;
            }
            
            // "numeric" kuralı
            if ($rule === 'numeric' && !is_null($value) && $value !== '' && !is_numeric($value)) {
                $errors[$field] = $messages[$field . '.numeric'] ?? sprintf("%s sayısal bir değer olmalıdır", $field);
                break;
            }
            
            // "min:X" kuralı
            if (strpos($rule, 'min:') === 0) {
                $min = (int) substr($rule, 4);
                if (!is_null($value) && $value !== '' && strlen($value) < $min) {
                    $errors[$field] = $messages[$field . '.min'] ?? sprintf("%s en az %d karakter olmalıdır", $field, $min);
                    break;
                }
            }
            
            // "max:X" kuralı
            if (strpos($rule, 'max:') === 0) {
                $max = (int) substr($rule, 4);
                if (!is_null($value) && $value !== '' && strlen($value) > $max) {
                    $errors[$field] = $messages[$field . '.max'] ?? sprintf("%s en fazla %d karakter olmalıdır", $field, $max);
                    break;
                }
            }
        }
    }
    
    if (!empty($errors)) {
        throw ValidationException::withErrors($errors);
    }
    
    return $validatedData;
}

/**
 * İstisnayı (exception) yakalayıp işler
 * 
 * @param \Throwable $e Yakalanan istisna
 * @param bool $rethrow İstisnayı tekrar fırlatma
 * @return void
 * @throws \Throwable Eğer $rethrow true ise
 */
function handle_exception(\Throwable $e, bool $rethrow = false): void
{
    // ErrorHandler ile istisnayı işle
    ErrorHandler::getInstance()->handleException($e);
    
    // İstenirse istisna tekrar fırlatılır
    if ($rethrow) {
        throw $e;
    }
}
