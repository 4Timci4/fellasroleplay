<?php
/**
 * Forum Konu Yönetimi Fonksiyonları
 */

/**
 * Forum konularını getir
 *
 * @param int $page Sayfa numarası
 * @param int $per_page Sayfa başına konu sayısı
 * @param string $search Arama sorgusu (opsiyonel)
 * @param int $category_id Kategori filtresi (opsiyonel)
 * @return array Konular ve sayfalama bilgileri
 */
function get_admin_forum_topics($page = 1, $per_page = 20, $search = null, $category_id = null) {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    
    // Toplam konu sayısını hesapla
    $count_query = "SELECT COUNT(*) FROM forum_topics WHERE status != 'deleted'";
    $params = [];
    
    if ($search) {
        $count_query .= " AND (title LIKE ? OR content LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($category_id) {
        $count_query .= " AND category_id = ?";
        $params[] = $category_id;
    }
    
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute($params);
    $total_topics = $count_stmt->fetchColumn();
    
    // Sayfalama hesapla
    $total_pages = ceil($total_topics / $per_page);
    $page = max(1, min($page, $total_pages ?: 1));
    $offset = ($page - 1) * $per_page;
    
    // Konuları getir - PDO sorgu yapısını MariaDB'ye uygun güvenli hale getir
    $query = "
        SELECT t.*, 
               c.name as category_name,
               u.username as creator_username
        FROM forum_topics t
        JOIN forum_categories c ON t.category_id = c.id
        LEFT JOIN forum_users u ON t.discord_user_id = u.discord_id
        WHERE t.status != 'deleted'
    ";
    
    $params = [];
    
    if ($search) {
        $query .= " AND (t.title LIKE ? OR t.content LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($category_id) {
        $query .= " AND t.category_id = ?";
        $params[] = $category_id;
    }
    
    $query .= " ORDER BY t.created_at DESC";
    
    // LIMIT ve OFFSET değerlerini parametre olarak kullanmak yerine doğrudan sorguya ekle
    $query .= " LIMIT " . intval($per_page) . " OFFSET " . intval($offset);
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'topics' => $topics,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_topics' => $total_topics,
            'total_pages' => $total_pages
        ]
    ];
}

/**
 * Konu detaylarını getir
 *
 * @param int $topic_id Konu ID
 * @return array|null Konu detayları veya bulunamazsa null
 */
function get_admin_forum_topic($topic_id) {
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
        WHERE t.id = ?
    ");
    $stmt->execute([$topic_id]);
    
    if ($stmt->rowCount() > 0) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    return null;
}

/**
 * Konu durumunu güncelle (normal, sticky, locked, deleted)
 *
 * @param int $topic_id Konu ID
 * @param string $status Yeni durum (normal, sticky, locked, deleted)
 * @return bool İşlem başarılı mı
 */
function update_topic_status($topic_id, $status) {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    
    // Geçerli durum kontrolü
    $valid_statuses = ['normal', 'sticky', 'locked', 'deleted'];
    if (!in_array($status, $valid_statuses)) {
        return false;
    }
    
    $stmt = $conn->prepare("
        UPDATE forum_topics 
        SET status = ?, 
            updated_at = NOW() 
        WHERE id = ?
    ");
    
    return $stmt->execute([$status, $topic_id]);
}

/**
 * Konuyu başka bir kategoriye taşı
 *
 * @param int $topic_id Konu ID
 * @param int $category_id Yeni kategori ID
 * @return bool İşlem başarılı mı
 */
function move_topic($topic_id, $category_id) {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    
    // Kategori var mı kontrol et
    $cat_check = $conn->prepare("SELECT id FROM forum_categories WHERE id = ?");
    $cat_check->execute([$category_id]);
    
    if ($cat_check->rowCount() == 0) {
        return false;
    }
    
    $stmt = $conn->prepare("
        UPDATE forum_topics 
        SET category_id = ?, 
            updated_at = NOW() 
        WHERE id = ?
    ");
    
    return $stmt->execute([$category_id, $topic_id]);
}

/**
 * Konu başlığını düzenle
 *
 * @param int $topic_id Konu ID
 * @param string $title Yeni başlık
 * @return bool İşlem başarılı mı
 */
function update_topic_title($topic_id, $title) {
    if (empty(trim($title))) {
        return false;
    }
    
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    $stmt = $conn->prepare("
        UPDATE forum_topics 
        SET title = ?, 
            updated_at = NOW() 
        WHERE id = ?
    ");
    
    return $stmt->execute([trim($title), $topic_id]);
}

/**
 * Konu içeriğini düzenle
 *
 * @param int $topic_id Konu ID
 * @param string $content Yeni içerik
 * @return bool İşlem başarılı mı
 */
function update_topic_content($topic_id, $content) {
    if (empty(trim($content))) {
        return false;
    }
    
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    $stmt = $conn->prepare("
        UPDATE forum_topics 
        SET content = ?, 
            updated_at = NOW() 
        WHERE id = ?
    ");
    
    return $stmt->execute([trim($content), $topic_id]);
}

/**
 * Raporlanan konuları getir
 *
 * @param int $limit Kaç konu getirileceği
 * @return array Raporlanan konular
 */
function get_reported_topics($limit = 10) {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    
    $query = "
        SELECT t.*, 
               c.name as category_name,
               u.username as creator_username,
               COUNT(r.id) as report_count
        FROM forum_topics t
        JOIN forum_categories c ON t.category_id = c.id
        LEFT JOIN forum_users u ON t.discord_user_id = u.discord_id
        LEFT JOIN forum_reports r ON r.topic_id = t.id
        WHERE t.status != 'deleted'
        GROUP BY t.id
        HAVING report_count > 0
        ORDER BY report_count DESC
        LIMIT " . intval($limit);
    
    $stmt = $conn->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
