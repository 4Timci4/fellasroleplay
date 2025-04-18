<?php
/**
 * Fellas Roleplay Site Konfigürasyon Dosyası
 * 
 * Bu dosya, site genelinde kullanılan ayarları ve linkleri içerir.
 * Herhangi bir değişiklik yapmak için sadece bu dosyayı düzenlemeniz yeterlidir.
 */

// Site Genel Ayarları
$site_config = [
    'site_name' => 'Fellas Roleplay',                // Sitenin başlık ve meta etiketlerinde kullanılan ismi
    'site_description' => 'Gerçekçi roleplay deneyimi için doğru adrestesiniz.', // Meta açıklama etiketi için kullanılır
    'copyright_year' => '2025',                      // Telif hakkı yılı (footer'da görünür)
    'copyright_text' => 'Tüm hakları saklıdır. Fellas Roleplay, gerçek bir roleplay deneyimi sunar.', // Footer telif metni
    'designed_by' => 'Designed by Timci',            // Site tasarımcısı bilgisi (footer'da görünür)
];

// Sosyal Medya Linkleri
$social_links = [
    'discord' => 'https://discord.gg/fellasrp',      // Discord sunucu davet linki - Header ve footer'da görünür
    'youtube' => 'https://youtube.com'               // YouTube kanal linki - Footer'da görünür
];

// Slider Ayarları
$slider_settings = [
    'transition_time' => 4000,                       // Slider geçiş süresi (milisaniye) - anasayfa.php'de kullanılır
    'images' => [                                    // Slider'da gösterilecek resimler - anasayfa.php'de kullanılır
        'assets/images/slider/slider1.png'          // Doğru resim yolunu kullanıyoruz
    ],
];

// Discord Rol Adları
$discord_roles = [
    'ROLE_ID_1' => 'Fellas',             // Fellas rolü - index.php'deki ekip listesi için
    'ROLE_ID_2' => 'Community',            // Community rolü - index.php'deki ekip listesi için
    'ROLE_ID_3' => 'Developer',              // Developer rolü - index.php'deki ekip listesi için
    'ROLE_ID_4' => 'Whitelist'             // Whitelist rolü - Başvuru onaylandığında verilen rol
];

// Sayfa Görünürlük Ayarları
$showTeamPage = false; // Yönetim ekibi sayfasının görünürlüğü - true: aktif, false: devre dışı

// NOT: Yardımcı fonksiyonlar legacy_compatibility.php dosyasına taşındı
// Burada sadece konfigürasyon değişkenleri kaldı
