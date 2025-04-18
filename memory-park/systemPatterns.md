# Sistem Kalıpları

## Sistem Mimarisi
Bu proje, MVC (Model-View-Controller) mimarisine benzer bir yapı kullanmaktadır, ancak tam klasik MVC yapısını takip etmemektedir.

```
project/
│
├── admin/                # Yönetim paneli
│   ├── controllers/      # Admin kontrol sınıfları
│   ├── views/            # Admin görünümleri
│   ├── functions/        # Admin yardımcı fonksiyonları
│   └── includes/         # Admin için yapılandırma ve bağımlılıklar
│
├── includes/             # Ana sistem bileşenleri
│   ├── core/             # Çekirdek sınıflar (Database, ErrorHandler vb.)
│   ├── models/           # Veri modelleri
│   ├── services/         # İş mantığı servisleri
│   ├── helpers/          # Yardımcı fonksiyonlar
│   └── config/           # Yapılandırma dosyaları
│
├── assets/               # Statik dosyalar (CSS, JS, resimler)
│
└── form_sections/        # Başvuru formunun bölümleri
```

## Temel Tasarım Kalıpları
1. **Servis Katmanı (Service Layer)**
   - Servisler, veritabanı işlemleri ile kullanıcı arayüzü arasında bir soyutlama katmanı sağlar
   - `includes/services/` altında bulunur (ForumService, UserService vb.)

2. **Tek Sorumluluk Prensibi (SRP)**
   - Her sınıf ve dosya belirli bir işlevsellikten sorumludur
   - Örneğin, ForumService forum işlemlerinden, UserService kullanıcı işlemlerinden sorumludur

3. **Bağımlılık Enjeksiyonu**
   - Servisler genellikle yapıcı metodlarına gereken bağımlılıkları alır
   - Örnek: DiscordService, Database nesnesini yapıcıda alır

4. **Adapter Deseni**
   - Harici API'ler için adaptörler kullanılıyor (DiscordAPI_Adapter)
   - Adaptörler, harici sistemlerle etkileşimi soyutlar

## Önemli Uygulama Yolları

### Kimlik Doğrulama Akışı
1. Kullanıcı login.php sayfasına gider
2. Kimlik bilgileri doğrulanır (includes/functions.php)
3. Başarılı girişte oturum başlatılır (includes/session.php)
4. Kullanıcı yetkilendirmesi kontrol edilir (includes/auth_check.php)

### Başvuru İşleme Akışı
1. Kullanıcı basvuru.php sayfasında formu doldurur
2. Veri doğrulama gerçekleştirilir (assets/js/form_scripts.js)
3. Başvuru process_application.php tarafından kaydedilir
4. Yöneticiler admin/view_application.php üzerinden başvuruyu görüntüler
5. Onay/red durumu için Discord webhook tetiklenir

### Forum Etkileşimi
1. Forum kategorileri forum.php üzerinde listelenir
2. Konular forum-category.php üzerinde gösterilir
3. Konu detayları forum-topic.php üzerinde görüntülenir
4. Yorumlar new-topic.php ve edit-comment.php üzerinden işlenir

### Discord Entegrasyonu
1. Discord ayarları admin/discord/settings.php üzerinden yapılandırılır
2. Bot yönetimi admin/discord/index.php üzerinden gerçekleştirilir
3. Discord bot Node.js ile çalışır (discord-bot/index.js)
4. Webhook ve REST API üzerinden web portalıyla iletişim kurar

## Bileşen İlişkileri
- **Database Sınıfı**: Çoğu servis tarafından kullanılır, veritabanı erişimini yönetir
- **ErrorHandler**: Tüm sistemde hata yönetimi için kullanılır
- **DiscordService**: Discord API ile etkileşim için diğer servisler tarafından kullanılır
- **Auth ve Session**: Kullanıcı kimlik doğrulama ve oturum yönetimi
- **ForumService**: Forum işlemlerini yönetir (categories.php, topics.php, comments.php)
