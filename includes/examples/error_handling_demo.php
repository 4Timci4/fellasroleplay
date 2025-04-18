<?php
/**
 * Hata İşleme Mekanizması Demo Dosyası
 * 
 * Bu örnek dosya, yeni hata işleme mekanizmasının nasıl kullanılacağını
 * göstermek için oluşturulmuştur. Çeşitli hata türleri ve bunların
 * nasıl yakalanıp işleneceği gösterilmektedir.
 * 
 * NOT: Bu dosya sadece örnek amaçlıdır, gerçek bir sayfada çalıştırılması
 * amaçlanmamıştır.
 */

// Bootstrap dosyasını dahil edelim (tüm gerekli sınıflar ve fonksiyonlar yüklenir)
require_once __DIR__ . '/../bootstrap.php';

// Exception sınıflarını dahil edelim
use Core\Exceptions\AppException;
use Core\Exceptions\DatabaseException;
use Core\Exceptions\ValidationException;
use Core\Exceptions\APIException;
use Core\ErrorHandler;

// Ortamı development olarak ayarlayalım (örneklerin detaylı hata çıktılarını görebilmek için)
\Core\Config::getInstance()->set('app.environment', 'development');

/**
 * ÖRNEK 1: Basit Hata Loglama
 * ----------------------------
 * Farklı seviyelerde hataları loglamak için yardımcı fonksiyonlar kullanılır.
 */
echo "<h2>Örnek 1: Hata Loglama</h2>";

try {
    // Debug seviyesinde log
    log_debug("Bu bir debug mesajıdır", ["user_id" => 123]);
    echo "Debug mesajı loglandı<br>";
    
    // Info seviyesinde log
    log_info("Kullanıcı giriş yaptı");
    echo "Info mesajı loglandı<br>";
    
    // Uyarı seviyesinde log
    log_warning("Yanlış şifre denemeleri limiti aşıldı");
    echo "Uyarı mesajı loglandı<br>";
    
    // Hata seviyesinde log
    log_error("Veritabanı bağlantısı başarısız oldu");
    echo "Hata mesajı loglandı<br>";
    
    // Kritik hata seviyesinde log
    log_critical("Sistem dosyalarına erişilemiyor");
    echo "Kritik hata mesajı loglandı<br>";
} catch (\Throwable $e) {
    echo "Hata: " . $e->getMessage();
}

echo "<hr>";

/**
 * ÖRNEK 2: Veritabanı Hata İşleme
 * -------------------------------
 * Veritabanı işlemleri sırasında oluşabilecek hataların yakalanması ve işlenmesi.
 */
echo "<h2>Örnek 2: Veritabanı Hata İşleme</h2>";

function getUserData($userId) {
    // Gerçek uygulamada burası veritabanından veri çeker
    if (!is_numeric($userId)) {
        // Validasyon hatası
        throw ValidationException::forField("user_id", "Kullanıcı ID'si sayısal bir değer olmalıdır");
    }
    
    if ($userId <= 0) {
        // Geçersiz ID
        throw ValidationException::withErrors([
            "user_id" => "Kullanıcı ID'si pozitif bir değer olmalıdır"
        ]);
    }
    
    // Kullanıcının veritabanında bulunup bulunmadığını kontrol et (örnek amaçlı)
    if ($userId > 1000) {
        // Kayıt bulunamadı hatası
        throw DatabaseException::recordNotFound("kullanıcı", $userId, "SELECT * FROM users WHERE id = ?");
    }
    
    // Örnek veri (gerçek uygulamada veritabanından gelir)
    return [
        "id" => $userId,
        "name" => "Test Kullanıcı",
        "email" => "test@example.com"
    ];
}

try {
    // Geçerli kullanıcı
    $user = getUserData(42);
    echo "Kullanıcı verileri başarıyla alındı: " . json_encode($user) . "<br>";
    
    // Geçersiz kullanıcı ID (doğrulama hatası) - Yorum satırından çıkarılırsa hata gösterir
    // $user = getUserData("abc");
    
    // Kayıt bulunamadı hatası - Yorum satırından çıkarılırsa hata gösterir
    // $user = getUserData(1234);
    
} catch (ValidationException $e) {
    echo "<div style='color: red;'>";
    echo "Doğrulama Hatası: " . $e->getMessage() . "<br>";
    echo "Hatalar: " . $e->getErrorsAsHtml();
    echo "</div>";
} catch (DatabaseException $e) {
    echo "<div style='color: red;'>";
    echo "Veritabanı Hatası: " . $e->getMessage() . "<br>";
    if ($e->getQuery()) {
        echo "Sorgu: " . htmlspecialchars($e->getQuery()) . "<br>";
    }
    echo "</div>";
} catch (AppException $e) {
    echo "<div style='color: red;'>";
    echo "Uygulama Hatası: " . $e->getMessage() . "<br>";
    echo "HTTP Kodu: " . $e->getHttpStatusCode() . "<br>";
    echo "Kullanıcı Mesajı: " . $e->getUserMessage() . "<br>";
    echo "</div>";
} catch (\Throwable $e) {
    echo "<div style='color: red;'>";
    echo "Beklenmeyen Hata: " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";

/**
 * ÖRNEK 3: API Hata İşleme
 * -----------------------
 * Dış API'lerle iletişim sırasında oluşabilecek hataların işlenmesi.
 */
echo "<h2>Örnek 3: API Hata İşleme</h2>";

function callDiscordAPI($endpoint, $data) {
    // Gerçek uygulamada burada Discord API'ye istek atılır
    
    // Bağlantı hatası simülasyonu
    if ($endpoint == "/api/connection-error") {
        throw APIException::connectionError(
            $endpoint,
            "API sunucusuna bağlanılamadı: Connection timed out",
            $data
        );
    }
    
    // Discord API hatası simülasyonu
    if ($endpoint == "/api/invalid-token") {
        throw APIException::discordApiError(
            $endpoint,
            401,
            $data,
            ["error" => "invalid_token", "message" => "The provided token is invalid"]
        );
    }
    
    // Başarılı yanıt simülasyonu
    return ["success" => true, "data" => ["message" => "API isteği başarılı"]];
}

try {
    // Başarılı API isteği
    $result = callDiscordAPI("/api/channels", ["guild_id" => "1234567890"]);
    echo "API yanıtı: " . json_encode($result) . "<br>";
    
    // Bağlantı hatası - Yorum satırından çıkarılırsa hata gösterir
    // $result = callDiscordAPI("/api/connection-error", ["guild_id" => "1234567890"]);
    
    // Discord API hatası - Yorum satırından çıkarılırsa hata gösterir
    // $result = callDiscordAPI("/api/invalid-token", ["guild_id" => "1234567890"]);
    
} catch (APIException $e) {
    echo "<div style='color: red;'>";
    echo "API Hatası: " . $e->getMessage() . "<br>";
    echo "Endpoint: " . $e->getEndpoint() . "<br>";
    if ($e->getApiStatusCode()) {
        echo "HTTP Durum Kodu: " . $e->getApiStatusCode() . "<br>";
    }
    echo "Kullanıcı Mesajı: " . $e->getUserMessage() . "<br>";
    echo "</div>";
} catch (\Throwable $e) {
    echo "<div style='color: red;'>";
    echo "Beklenmeyen Hata: " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";

/**
 * ÖRNEK 4: Form Doğrulama
 * -----------------------
 * Form verilerinin doğrulanması ve hataların kullanıcıya gösterilmesi.
 */
echo "<h2>Örnek 4: Form Doğrulama</h2>";

function processRegistrationForm($formData) {
    // Form verilerini doğrula
    $rules = [
        "username" => ["required", "min:3", "max:20"],
        "email" => ["required", "email"],
        "password" => ["required", "min:8"],
        "age" => ["required", "numeric"]
    ];
    
    $messages = [
        "username.required" => "Kullanıcı adı alanı zorunludur",
        "username.min" => "Kullanıcı adı en az 3 karakter olmalıdır",
        "username.max" => "Kullanıcı adı en fazla 20 karakter olmalıdır",
        "email.required" => "E-posta adresi alanı zorunludur",
        "email.email" => "Geçerli bir e-posta adresi giriniz",
        "password.required" => "Şifre alanı zorunludur",
        "password.min" => "Şifre en az 8 karakter olmalıdır",
        "age.required" => "Yaş alanı zorunludur",
        "age.numeric" => "Yaş sayısal bir değer olmalıdır"
    ];
    
    // validate_form fonksiyonu ValidationException fırlatabilir
    $validatedData = validate_form($formData, $rules, $messages);
    
    // Form başarıyla doğrulandı, işleme devam et
    // Gerçek uygulamada burada kullanıcı kaydı yapılır
    return [
        "success" => true,
        "message" => "Kayıt başarılı",
        "data" => $validatedData
    ];
}

try {
    // Geçerli form verileri
    $formData = [
        "username" => "testuser",
        "email" => "test@example.com",
        "password" => "password123",
        "age" => "25"
    ];
    
    $result = processRegistrationForm($formData);
    echo "Form işleme sonucu: " . json_encode($result) . "<br>";
    
    // Geçersiz form verileri - Yorum satırından çıkarılırsa hata gösterir
    /*
    $invalidFormData = [
        "username" => "t", // çok kısa
        "email" => "invalid-email", // geçersiz e-posta
        "password" => "123", // çok kısa şifre
        "age" => "abc" // sayısal değil
    ];
    
    $result = processRegistrationForm($invalidFormData);
    */
    
} catch (ValidationException $e) {
    echo "<div style='color: red;'>";
    echo "Form Doğrulama Hatası:<br>";
    echo $e->getErrorsAsHtml();
    echo "</div>";
} catch (\Throwable $e) {
    echo "<div style='color: red;'>";
    echo "Beklenmeyen Hata: " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";

/**
 * ÖRNEK 5: try-catch ile Genel Hata Yakalama
 * ------------------------------------------
 * Çeşitli hata türlerini tek bir try-catch bloğunda yakalama.
 */
echo "<h2>Örnek 5: Genel Hata Yakalama</h2>";

try {
    // Rastgele bir hata türü üret (örnek amaçlı)
    $errorType = rand(1, 5);
    
    switch ($errorType) {
        case 1:
            // Genel uygulama hatası
            app_error(
                "Bir işlem sırasında beklenmeyen bir hata oluştu", 
                100, 
                "İşleminiz tamamlanamadı, lütfen daha sonra tekrar deneyin"
            );
            break;
            
        case 2:
            // Validasyon hatası
            validation_error([
                "username" => "Kullanıcı adı zaten kullanılıyor"
            ]);
            break;
            
        case 3:
            // Veritabanı hatası
            db_error(
                "Veritabanı sorgusu başarısız oldu", 
                "SELECT * FROM users WHERE username = ?",
                500,
                "Verilere erişilemiyor, lütfen daha sonra tekrar deneyin"
            );
            break;
            
        case 4:
            // API hatası
            api_error(
                "Discord API isteği başarısız oldu",
                "/api/channels",
                429,
                ["guild_id" => "1234567890"],
                ["error" => "rate_limited", "retry_after" => 5000]
            );
            break;
            
        default:
            // Standart PHP hatası
            throw new \Exception("Standart bir PHP istisnası");
    }
    
} catch (ValidationException $e) {
    echo "<div style='color: red;'>";
    echo "Doğrulama Hatası: " . $e->getMessage() . "<br>";
    echo "Hatalar: " . $e->getErrorsAsHtml();
    echo "</div>";
} catch (DatabaseException $e) {
    echo "<div style='color: red;'>";
    echo "Veritabanı Hatası: " . $e->getMessage() . "<br>";
    if ($e->getQuery()) {
        echo "Sorgu: " . htmlspecialchars($e->getQuery()) . "<br>";
    }
    echo "</div>";
} catch (APIException $e) {
    echo "<div style='color: red;'>";
    echo "API Hatası: " . $e->getMessage() . "<br>";
    echo "Endpoint: " . $e->getEndpoint() . "<br>";
    if ($e->getApiStatusCode()) {
        echo "HTTP Durum Kodu: " . $e->getApiStatusCode() . "<br>";
    }
    echo "</div>";
} catch (AppException $e) {
    echo "<div style='color: red;'>";
    echo "Uygulama Hatası: " . $e->getMessage() . "<br>";
    echo "HTTP Kodu: " . $e->getHttpStatusCode() . "<br>";
    echo "Kullanıcı Mesajı: " . $e->getUserMessage() . "<br>";
    echo "</div>";
} catch (\Throwable $e) {
    echo "<div style='color: red;'>";
    echo "Beklenmeyen Hata: " . $e->getMessage() . "<br>";
    echo "Sınıf: " . get_class($e) . "<br>";
    echo "</div>";
}

// NOT: Gerçek uygulamada, giriş/çıkış (I/O) işlemleri için ErrorHandler sınıfını
// kullanmak yerine, handle_exception() yardımcı fonksiyonu kullanılması önerilir.
// Örnek:
// handle_exception($e, false); // istisnayı işle ancak yeniden fırlatma
