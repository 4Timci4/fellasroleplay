<?php
/**
 * Hata İşleme Yapılandırma Dosyası
 * 
 * Hata işleme mekanizmasının davranışını kontrol eden 
 * yapılandırma ayarlarını içerir.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Uygulama Ortamı
    |--------------------------------------------------------------------------
    |
    | Uygulama ortamı, hata işleme mekanizmasının davranışını belirler.
    | 'development' (geliştirme): Detaylı hata mesajları gösterir
    | 'testing' (test): Hataları gösterir ama minimum bilgiyle
    | 'production' (üretim): Kullanıcı dostu hata mesajları gösterir, teknik detayları gizler
    |
    */
    'environment' => 'production',

    /*
    |--------------------------------------------------------------------------
    | Hata Loglama Ayarları
    |--------------------------------------------------------------------------
    |
    | Hataların nasıl ve nereye loglanacağını belirler.
    |
    */
    'logging' => [
        // Log dosyasının yolu (proje kök dizinine göre)
        'log_file' => 'logs/error.log',
        
        // Minimum log seviyesi: debug, info, warning, error, critical
        'log_level' => 'warning',
        
        // Log rotasyonu (dosya boyutu MB cinsinden)
        'max_file_size' => 5, // 5 MB
        
        // Maksimum log dosya sayısı (rotasyon için)
        'max_files' => 5,
        
        // SAPI bazlı ayarlar
        'web' => [
            'enabled' => true,
        ],
        'cli' => [
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hata Görüntüleme Ayarları
    |--------------------------------------------------------------------------
    |
    | Hataların kullanıcıya nasıl gösterileceğini belirler.
    |
    */
    'display' => [
        // Kullanıcı dostu hata sayfası şablonu
        'error_view' => 'error', // PHP dosyası veya yol
        
        // HTTP durum kodu
        'http_status' => 500,
        
        // Varsayılan hata mesajı (production modunda kullanılır)
        'default_message' => 'İşleminiz sırasında bir hata oluştu. Teknik ekibimiz bilgilendirildi.',
        
        // Yeniden yönlendirme sayfası (opsiyonel)
        'redirect_to' => null, // '/error' gibi bir değer alabilir
    ],

    /*
    |--------------------------------------------------------------------------
    | Hata Bildirim Ayarları
    |--------------------------------------------------------------------------
    |
    | Kritik hatalar için bildirim ayarları.
    |
    */
    'notifications' => [
        // E-posta bildirimleri
        'email' => [
            'enabled' => false,
            'to' => 'admin@example.com',
            'from' => 'system@example.com',
            'min_level' => 'critical', // Minimum hata seviyesi
        ],
        
        // Discord webhook bildirimleri
        'discord' => [
            'enabled' => false,
            'webhook_url' => '',
            'min_level' => 'error', // Minimum hata seviyesi
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Filtreleme Ayarları
    |--------------------------------------------------------------------------
    |
    | Belirli hataları filtrelemek için ayarlar.
    |
    */
    'filters' => [
        // Yoksayılacak hata türleri (E_NOTICE, E_DEPRECATED gibi)
        'ignore_types' => [
            E_DEPRECATED,
            E_USER_DEPRECATED,
        ],
        
        // Yoksayılacak hata içerikleri (regex desenleri)
        'ignore_patterns' => [
            // Örnek: '/Undefined index:/'
        ],
        
        // Yoksayılacak dosya yolları (regex desenleri)
        'ignore_files' => [
            // Örnek: '/vendor\/package\//'
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Güvenlik Ayarları
    |--------------------------------------------------------------------------
    |
    | Güvenlikle ilgili ayarlar.
    |
    */
    'security' => [
        // Hassas verileri maskele (veritabanı şifreleri, API anahtarları vb.)
        'mask_sensitive_data' => true,
        
        // Maskelenecek hassas veri anahtarları
        'sensitive_keys' => [
            'password', 'api_key', 'token', 'secret', 'key', 'auth',
            'credential', 'authentication', 'username', 'email'
        ],
    ],
];
