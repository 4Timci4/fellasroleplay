<?php
/**
 * Forum yorum işlemleri için gerekli fonksiyonlar
 */

// Gerekli sınıfları ve yardımcı fonksiyonları içeren bootstrapper'ı kontrol et
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../bootstrap.php';
}

/**
 * Belirli bir konuya ait yorumları getirir
 * 
 * @param int $topic_id Konu ID
 * @param int $page Sayfa numarası (1'den başlar)
 * @param int $per_page Sayfa başına yorum sayısı
 * @return array Yorumlar ve sayfalama bilgileri
 */
function get_forum_comments($topic_id, $page = 1, $per_page = 20) {
    $conn = getDbConnection();
    
    // Toplam yorum sayısını hesapla
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM forum_comments WHERE topic_id = ?");
    $count_stmt->execute([$topic_id]);
    $total_comments = $count_stmt->fetchColumn();
    
    // Sayfalama hesapla
    $total_pages = ceil($total_comments / $per_page);
    $page = max(1, min($page, $total_pages ?: 1)); // Toplam sayfa 0 olabilir
    $offset = ($page - 1) * $per_page;
    
    // Yorumları getir - Prepared statement için parametreleri ayrı ver
    $stmt = $conn->prepare("
        SELECT c.*, 
               u.username as creator_username, 
               u.avatar as creator_avatar
        FROM forum_comments c
        LEFT JOIN forum_users u ON c.discord_user_id = u.discord_id
        WHERE c.topic_id = ?
        ORDER BY c.created_at ASC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$topic_id, $per_page, $offset]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'comments' => $comments,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_comments' => $total_comments,
            'total_pages' => $total_pages
        ]
    ];
}

/**
 * Yeni bir yorum ekler
 * 
 * @param int $topic_id Konu ID
 * @param string $discord_user_id Discord kullanıcı ID
 * @param string $content Yorum içeriği
 * @param int|null $parent_id Ebeveyn yorum ID (yanıtlanan yorum, varsa)
 * @return int|false Oluşturulan yorumun ID'si veya başarısız olursa false
 */
function add_forum_comment($topic_id, $discord_user_id, $content, $parent_id = null) {
    $conn = getDbConnection();
    
    // Giriş yapan kullanıcının Discord bilgilerini kontrol et ve kaydet
    ensure_forum_user_exists($discord_user_id);
    
    // Yorumu ekle
    $stmt = $conn->prepare("
        INSERT INTO forum_comments (topic_id, discord_user_id, content, parent_id) 
        VALUES (?, ?, ?, ?)
    ");
    $result = $stmt->execute([$topic_id, $discord_user_id, $content, $parent_id]);
    
    if ($result) {
        $comment_id = $conn->lastInsertId();
        
        // Kullanıcının yorum sayısını artır
        $stmt = $conn->prepare("UPDATE forum_users SET comment_count = comment_count + 1 WHERE discord_id = ?");
        $stmt->execute([$discord_user_id]);
        
        return $comment_id;
    }
    
    return false;
}

/**
 * Bir yorumu düzenler
 * 
 * @param int $comment_id Yorum ID
 * @param string $discord_user_id Discord kullanıcı ID
 * @param string $content Yeni içerik
 * @return bool İşlem başarılı ise true, değilse false
 */
function edit_forum_comment($comment_id, $discord_user_id, $content) {
    // Sadece yorum sahibi veya admin düzenleyebilir
    if (!is_comment_owner($comment_id, $discord_user_id) && !is_forum_admin($discord_user_id)) {
        return false;
    }
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE forum_comments SET content = ?, updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$content, $comment_id]);
}

/**
 * Bir yorumu siler
 * 
 * @param int $comment_id Yorum ID
 * @param string $discord_user_id Discord kullanıcı ID
 * @return bool İşlem başarılı ise true, değilse false
 */
function delete_forum_comment($comment_id, $discord_user_id) {
    // Sadece yorum sahibi veya admin silebilir
    if (!is_comment_owner($comment_id, $discord_user_id) && !is_forum_admin($discord_user_id)) {
        return false;
    }
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM forum_comments WHERE id = ?");
    return $stmt->execute([$comment_id]);
}

/**
 * Kullanıcının belirli bir yorumun sahibi olup olmadığını kontrol eder
 * 
 * @param int $comment_id Yorum ID
 * @param string $discord_user_id Discord kullanıcı ID
 * @return bool Kullanıcı yorumun sahibi ise true, değilse false
 */
function is_comment_owner($comment_id, $discord_user_id) {
    // Giriş yapılmamışsa false döndür
    if (empty($discord_user_id)) {
        return false;
    }
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT discord_user_id FROM forum_comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    
    if ($stmt->rowCount() > 0) {
        $comment_owner_id = $stmt->fetchColumn();
        return $comment_owner_id === $discord_user_id;
    }
    
    return false;
}
