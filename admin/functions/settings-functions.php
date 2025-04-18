<?php
/**
 * Forum Ayarları ve Bakım Modu Fonksiyonları
 * 
 * Bu dosya, bakım modu ve forum istatistikleri için yardımcı fonksiyonlar içerir.
 * Not: Bakım modu fonksiyonları includes/maintenance.php içinde tanımlandığı için burada
 * sadece yeni fonksiyonlar tanımlanmıştır.
 */

// Not: enable_maintenance_mode(), disable_maintenance_mode() ve is_maintenance_mode() 
// fonksiyonları includes/maintenance.php içinde zaten tanımlandığı için burada
// yeniden tanımlanmamıştır.

/**
 * Bakım modu ayarlarını getirir
 * 
 * @return array Bakım modu ayarları
 */
function get_maintenance_settings() {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM forum_settings WHERE setting_key = ?");
    $stmt->execute(['maintenance_settings']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return json_decode($result['setting_value'], true);
    }
    
    // Varsayılan ayarlar
    return [
        'allow_staff_access' => false
    ];
}

/**
 * Bakım modu ayarlarını günceller
 * 
 * @param array $settings Ayarlanacak bakım modu ayarları
 * @return bool İşlem başarılı ise true, değilse false
 */
function update_maintenance_settings($settings) {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    
    // JSON formatına dönüştür
    $settings_json = json_encode($settings);
    
    // Ayar var mı kontrol et
    $stmt = $conn->prepare("SELECT COUNT(*) FROM forum_settings WHERE setting_key = ?");
    $stmt->execute(['maintenance_settings']);
    
    if ($stmt->fetchColumn() > 0) {
        // Mevcut ayarı güncelle
        $stmt = $conn->prepare("UPDATE forum_settings SET setting_value = ? WHERE setting_key = ?");
        return $stmt->execute([$settings_json, 'maintenance_settings']);
    } else {
        // Yeni ayar ekle
        $stmt = $conn->prepare("INSERT INTO forum_settings (setting_key, setting_value) VALUES (?, ?)");
        return $stmt->execute(['maintenance_settings', $settings_json]);
    }
}

/**
 * Bakım modunda yetkililer için erişim kontrolü
 *
 * @param string $discord_user_id Discord kullanıcı ID'si
 * @return bool Kullanıcı erişimi varsa true, yoksa false
 */
function has_maintenance_access($discord_user_id) {
    // Admin kontrolü - admin her zaman erişebilir
    if (is_forum_admin($discord_user_id)) {
        return true;
    }
    
    // Bakım modu ayarlarını kontrol et
    $settings = get_maintenance_settings();
    
    // Yetkililere erişim izni kontrolü
    if ($settings['allow_staff_access']) {
        // Yetkili kullanıcı kontrolü
        return is_forum_staff($discord_user_id);
    }
    
    return false;
}

/**
 * Forum istatistiklerini getir
 * 
 * Kategoriler, konular, yorumlar ve kullanıcılar hakkında sayısal istatistikler
 * 
 * @return array İstatistik verileri
 */
function get_forum_statistics() {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    $stats = [];
    
    // Kategori sayısı
    $stats['category_count'] = $conn->query("SELECT COUNT(*) FROM forum_categories")->fetchColumn();

    // Konu sayısı
    $stats['topic_count'] = $conn->query("SELECT COUNT(*) FROM forum_topics WHERE status != 'deleted'")->fetchColumn();

    // Yorum sayısı
    $stats['comment_count'] = $conn->query("SELECT COUNT(*) FROM forum_comments")->fetchColumn();

    // Kullanıcı sayısı
    $stats['user_count'] = $conn->query("SELECT COUNT(*) FROM forum_users")->fetchColumn();

    return $stats;
}

/**
 * Son konuları getir
 *
 * @param int $limit Kaç konu getirileceği
 * @return array Konular
 */
function get_latest_topics($limit = 5) {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    
    // PDO ile limit parametresini doğrudan sorguya yerleştir
    // MariaDB'de hazırlanmış ifadelerde LIMIT için yer tutucu kullanmak sorun çıkarabilir
    $stmt = $conn->query("
        SELECT t.*, c.name as category_name, u.username as creator_username
        FROM forum_topics t
        JOIN forum_categories c ON t.category_id = c.id
        LEFT JOIN forum_users u ON t.discord_user_id = u.discord_id
        WHERE t.status != 'deleted'
        ORDER BY t.created_at DESC
        LIMIT " . intval($limit)
    );
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Son yorumları getir
 *
 * @param int $limit Kaç yorum getirileceği
 * @return array Yorumlar
 */
function get_latest_comments($limit = 5) {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    
    // PDO ile limit parametresini doğrudan sorguya yerleştir
    // MariaDB'de hazırlanmış ifadelerde LIMIT için yer tutucu kullanmak sorun çıkarabilir
    $stmt = $conn->query("
        SELECT c.*, t.title as topic_title, u.username as creator_username
        FROM forum_comments c
        JOIN forum_topics t ON c.topic_id = t.id
        LEFT JOIN forum_users u ON c.discord_user_id = u.discord_id
        ORDER BY c.created_at DESC
        LIMIT " . intval($limit)
    );
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
