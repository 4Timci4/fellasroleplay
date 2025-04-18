<?php
/**
 * Forum konu işlemleri için gerekli fonksiyonlar
 */

// Gerekli sınıfları ve yardımcı fonksiyonları içeren bootstrapper'ı kontrol et
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../bootstrap.php';
}

/**
 * Belirli bir kategorideki konuları getirir
 * 
 * @param int $category_id Kategori ID
 * @param int $page Sayfa numarası (1'den başlar)
 * @param int $per_page Sayfa başına konu sayısı
 * @return array Konular ve sayfalama bilgileri
 */
function get_forum_topics($category_id, $page = 1, $per_page = 10) {
    $category = \Models\Forum\Category::find($category_id);
    if (!$category) {
        return [
            'topics' => [],
            'pagination' => [
                'current_page' => 1,
                'per_page' => $per_page,
                'total_topics' => 0,
                'total_pages' => 0
            ]
        ];
    }
    
    return $category->getTopics($page, $per_page);
}

/**
 * Belirli bir konuyu ID'ye göre getirir
 * 
 * @param int $topic_id Konu ID
 * @return array|null Konu bilgileri veya bulunamazsa null
 */
function get_forum_topic($topic_id) {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    $stmt = $conn->prepare("
        SELECT t.*, 
               c.name as category_name,
               c.slug as category_slug,
               u.username as creator_username, 
               u.avatar as creator_avatar
        FROM forum_topics t
        JOIN forum_categories c ON t.category_id = c.id
        LEFT JOIN forum_users u ON t.discord_user_id = u.discord_id
        WHERE t.id = ? AND t.status != 'deleted'
    ");
    $stmt->execute([$topic_id]);
    
    if ($stmt->rowCount() > 0) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    return null;
}

/**
 * Konuyu görüntüleme sayısını artırır
 * 
 * @param int $topic_id Konu ID
 * @return void
 */
function increment_topic_views($topic_id) {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    $stmt = $conn->prepare("UPDATE forum_topics SET views = views + 1 WHERE id = ?");
    $stmt->execute([$topic_id]);
}

/**
 * Yeni bir konu ekler
 * 
 * @param int $category_id Kategori ID
 * @param string $discord_user_id Discord kullanıcı ID
 * @param string $title Konu başlığı
 * @param string $content Konu içeriği
 * @return int|false Oluşturulan konunun ID'si veya başarısız olursa false
 */
function add_forum_topic($category_id, $discord_user_id, $title, $content) {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    
    // Giriş yapan kullanıcının Discord bilgilerini kontrol et ve kaydet
    ensure_forum_user_exists($discord_user_id);
    
    // Konuyu ekle
    $stmt = $conn->prepare("
        INSERT INTO forum_topics (category_id, discord_user_id, title, content) 
        VALUES (?, ?, ?, ?)
    ");
    $result = $stmt->execute([$category_id, $discord_user_id, $title, $content]);
    
    if ($result) {
        $topic_id = $conn->lastInsertId();
        
        // Kullanıcının post sayısını artır
        $stmt = $conn->prepare("UPDATE forum_users SET post_count = post_count + 1 WHERE discord_id = ?");
        $stmt->execute([$discord_user_id]);
        
        return $topic_id;
    }
    
    return false;
}

/**
 * Konu başlığını ve içeriğini düzenler
 * 
 * @param int $topic_id Konu ID
 * @param string $discord_user_id Discord kullanıcı ID
 * @param string $title Yeni başlık
 * @param string $content Yeni içerik
 * @return bool İşlem başarılı ise true, değilse false
 */
function edit_forum_topic($topic_id, $discord_user_id, $title, $content) {
    // Sadece konu sahibi düzenleyebilir
    if (!is_topic_owner($topic_id, $discord_user_id) && !is_forum_admin($discord_user_id)) {
        return false;
    }
    
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    $stmt = $conn->prepare("UPDATE forum_topics SET title = ?, content = ?, updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$title, $content, $topic_id]);
}

/**
 * Konuyu siler (veya silindi olarak işaretler)
 * 
 * @param int $topic_id Konu ID
 * @param string $discord_user_id Discord kullanıcı ID
 * @return bool İşlem başarılı ise true, değilse false
 */
function delete_forum_topic($topic_id, $discord_user_id) {
    // Sadece konu sahibi veya admin silebilir
    if (!is_topic_owner($topic_id, $discord_user_id) && !is_forum_admin($discord_user_id)) {
        return false;
    }
    
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    $stmt = $conn->prepare("UPDATE forum_topics SET status = 'deleted', updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$topic_id]);
}

/**
 * Kullanıcının belirli bir konunun sahibi olup olmadığını kontrol eder
 * 
 * @param int $topic_id Konu ID
 * @param string $discord_user_id Discord kullanıcı ID
 * @return bool Kullanıcı konunun sahibi ise true, değilse false
 */
function is_topic_owner($topic_id, $discord_user_id) {
    // Giriş yapılmamışsa false döndür
    if (empty($discord_user_id)) {
        return false;
    }
    
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    $stmt = $conn->prepare("SELECT discord_user_id FROM forum_topics WHERE id = ?");
    $stmt->execute([$topic_id]);
    
    if ($stmt->rowCount() > 0) {
        $topic_owner_id = $stmt->fetchColumn();
        return $topic_owner_id === $discord_user_id;
    }
    
    return false;
}
