# Teknik Bağlam

## Kullanılan Teknolojiler

### Backend
- **PHP 7.4+**: Ana uygulama dili
- **MySQL 5.7+**: Veritabanı yönetim sistemi
- **Node.js**: Discord bot için (discord-bot/ dizininde)

### Frontend
- **HTML5 / CSS3**: Temel görünüm
- **JavaScript**: İstemci tarafı etkileşimler
- **Bootstrap** (muhtemelen): Duyarlı tasarım için

### API ve Entegrasyonlar
- **Discord API**: Webhook ve REST API üzerinden Discord entegrasyonu
- **Discord.js**: Node.js Discord bot kütüphanesi

## Geliştirme Ortamı
- **XAMPP**: Apache, MySQL, PHP paketini içeren yerel geliştirme ortamı
- **Dosya yapısı**: Geleneksel PHP web uygulaması yapısı (tam MVC değil)
- **Apache**: .htaccess dosyaları ile URL yönlendirme ve güvenlik konfigürasyonu

## Teknik Kısıtlamalar
- **Eski Kod Tabanı Uyumluluğu**: legacy_compatibility.php dosyası, eski kod tabanı ile uyumluluğu sağlamak için kullanılıyor
- **PHP Sürüm Bağımlılığı**: Fonksiyonlar ve sözdizimi PHP 7.4+ özelliklerine bağlı
- **Shared Hosting Uyumluluğu**: Yaygın paylaşımlı hosting ortamlarında çalışabilme gerekliliği
- **Bakım Modu**: maintenance.flag dosyası ve includes/maintenance.php bakım modu için kullanılıyor

## Bağımlılıklar

### Sistem Gereksinimleri
- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Apache 2.4+ (mod_rewrite etkin)
- Node.js 14+ (Discord bot için)

### Harici Bağımlılıklar
- Discord API 
- Discord.js kütüphanesi (Node.js için)

## Veritabanı Şeması
Veritabanı yapısı `includes/config/database.php` içinde tanımlanmıştır ve muhtemelen şu tabloları içerir:

- **users**: Kullanıcı hesapları
- **applications**: Roleplay başvuruları
- **forum_categories**: Forum kategorileri
- **forum_topics**: Forum konuları
- **forum_comments**: Forum yorumları
- **settings**: Sistem ayarları

## Araç Kullanım Modelleri

### Veritabanı Erişimi
- PDO veya mysqli üzerine kurulu bir Database sınıfı (includes/core/Database.php)
- Prepared statements güvenli sorgular için kullanılıyor
- Model/servis sınıfları veritabanı mantığını soyutluyor

### Hata Yönetimi
- Özel exception sınıfları (includes/core/Exceptions/)
- Merkezileştirilmiş ErrorHandler sınıfı (includes/core/ErrorHandler.php)
- Hata günlükleri logs/error.log dosyasına kaydediliyor

### Güvenlik Uygulamaları
- Kullanıcı girişi doğrulama ve sanitizasyon
- CSRF koruması muhtemelen form işlemlerinde
- .htaccess dosyaları ile dizin erişim kısıtlamaları
- Yönetim panelinde security_check.php ile ek güvenlik kontrolleri

### Kod Düzeni
- Fonksiyon ve sınıf tabanlı modülerleştirme
- Servis katmanı iş mantığı için (includes/services/)
- Yardımcı fonksiyonlar (includes/helpers/)
- Parçalanmış görünüm dosyaları (örn. form_sections/)
