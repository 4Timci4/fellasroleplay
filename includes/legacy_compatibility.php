<?php
/**
 * Geriye dönük uyumluluk köprüsü
 * 
 * Bu dosya, eski fonksiyonları yeni yapıya bağlamak için kullanılır.
 * Refaktoring tamamlandığında kaldırılabilir.
 */

// Henüz dahil edilmemişse bootstrap dosyasını dahil et
if (!class_exists('\\Core\\Config')) {
    require_once __DIR__ . '/bootstrap.php';
}

// Veritabanı bağlantı fonksiyonları için admin/includes/db.php'yi dahil et
// Bu sayede aynı fonksiyonun tekrar tanımlanmamasını sağlarız
require_once __DIR__ . '/../admin/includes/db.php';

// Eski Discord API fonksiyonları için uyumluluk köprüsü
if (!function_exists('getDiscordMembersWithRole') && class_exists('\\Services\\DiscordService')) {
    function getDiscordMembersWithRole($roleId, $limit = 10) {
        $discord = new \Services\DiscordService();
        return $discord->getMembersWithRole($roleId, $limit);
    }
}

if (!function_exists('getDiscordRoleName') && class_exists('\\Services\\DiscordService')) {
    function getDiscordRoleName($roleId) {
        return \Services\DiscordService::getRoleName($roleId);
    }
}

if (!function_exists('userHasDiscordRole') && class_exists('\\Services\\DiscordService')) {
    function userHasDiscordRole($userId, $roleId) {
        if (empty($userId)) {
            return false;
        }
        
        $discord = new \Services\DiscordService();
        return $discord->checkUserHasRole($userId, $roleId);
    }
}

if (!function_exists('userHasAnyDiscordRole')) {
    function userHasAnyDiscordRole($userId, $roleIds) {
        if (empty($userId) || !is_array($roleIds)) {
            return false;
        }
        
        foreach ($roleIds as $roleId) {
            if (userHasDiscordRole($userId, $roleId)) {
                return true;
            }
        }
        
        return false;
    }
}

// Eski config fonksiyonları için uyumluluk köprüsü
// Config dosyasından yeni Config sınıfına yönlendirmeler

// get_social_link fonksiyonu için uyumluluk (Config'e yönlendirme)
if (!function_exists('get_social_link')) {
    function get_social_link($platform) {
        return \Core\Config::get('social.' . $platform, '');
    }
}

// İstatistik fonksiyonu için uyumluluk (Config'e yönlendirme)
if (!function_exists('get_statistic')) {
    function get_statistic($key) {
        // Config'den varsayılan değerleri al
        $defaultValues = \Core\Config::get('statistics', []);
        
        switch ($key) {
            case 'active_characters':
                try {
                    // Oyun veritabanından aktif karakter sayısını çeker
                    $db = \Core\Database::getInstance('game');
                    $count = $db->fetchValue("SELECT COUNT(*) FROM players");
                    return $count;
                } catch (\Exception $e) {
                    error_log("Aktif karakter sayısı alınamadı: " . $e->getMessage());
                }
                break;
                
            case 'whitelist_players':
                try {
                    // Discord API kullanarak whitelist rolüne sahip oyuncu sayısını çeker
                    $roleId = \Core\Config::get('discord.whitelist_role_id');
                    $discord = new \Services\DiscordService();
                    $count = $discord->countMembersWithRole($roleId);
                    return $count;
                } catch (\Exception $e) {
                    error_log("Whitelist oyuncu sayısı alınamadı: " . $e->getMessage());
                }
                break;
        }
        
        // Hata durumunda varsayılan değeri döndür
        return $defaultValues[$key] ?? '0';
    }
}

// Site config fonksiyonu için uyumluluk
if (!function_exists('get_site_config')) {
    function get_site_config($key) {
        return \Core\Config::get('site.' . $key, '');
    }
}

// Forum fonksiyonları için uyumluluk köprüsü
if (!function_exists('get_forum_category_by_slug_new')) {
    /**
     * Belirli bir kategoriyi slug'a göre getirir (yeni model kullanımı)
     * 
     * @param string $slug Kategori slug
     * @return array|null Kategori bilgileri veya bulunamazsa null
     */
    function get_forum_category_by_slug_new($slug) {
        $category = \Models\Forum\Category::findBySlug($slug);
        
        if ($category) {
            return $category->toArray();
        }
        
        return null;
    }
}

// Genel yardımcı fonksiyonlar için uyumluluk köprüsü
if (!function_exists('sanitizeOutput')) {
    /**
     * Metni güvenli hale getirir (XSS koruması)
     * admin/includes/functions.php'den yönlendirilmiş
     * 
     * @param string $text Güvenli hale getirilecek metin
     * @return string Güvenli hale getirilmiş metin
     */
    function sanitizeOutput($text) {
        if (function_exists('htmlspecialchars')) {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }
        // Fallback, eğer htmlspecialchars fonksiyonu bulunmazsa
        return str_replace(
            ['&', '<', '>', '"', "'"],
            ['&amp;', '&lt;', '&gt;', '&quot;', '&#039;'],
            $text
        );
    }
}

// formatDate fonksiyonu için uyumluluk
if (!function_exists('formatDate')) {
    /**
     * Tarih formatını düzenler
     * admin/includes/functions.php'den yönlendirilmiş
     * 
     * @param string $date Tarih
     * @return string Formatlanmış tarih
     */
    function formatDate($date) {
        return date('d.m.Y H:i', strtotime($date));
    }
}
