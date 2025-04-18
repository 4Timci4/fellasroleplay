<?php
namespace Services;

use Core\Database;
use Models\Forum\Category;

/**
 * Forum işlemleri için servis sınıfı
 */
class ForumService {
    private static $instance = null;
    private $database;
    
    /**
     * Singleton pattern constructor
     */
    private function __construct() {
        $this->database = Database::getInstance();
    }
    
    /**
     * Singleton pattern instance getter
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Bir kategoride ve alt kategorilerinde toplam konu ve yorum sayısını hesaplar
     * 
     * @param int $category_id Kategori ID
     * @return array Konu ve yorum sayıları ['topic_count' => x, 'post_count' => y]
     */
    public function getCategoryStats($category_id) {
        // Kategori ve tüm alt kategorileri bul
        $category = Category::find($category_id);
        if (!$category) {
            return ['topic_count' => 0, 'post_count' => 0];
        }
        
        // Kendisi ve alt kategorileri
        $category_ids = [$category_id];
        $sub_categories = $category->getSubCategories(true); // recursive olarak tüm alt kategorileri getir
        
        foreach ($sub_categories as $sub) {
            $category_ids[] = $sub->getId();
        }
        
        // Kategori yoksa boş sonuç döndür
        if (empty($category_ids)) {
            return [
                'topic_count' => 0,
                'post_count' => 0
            ];
        }
        
        // Prepare statement kullanarak IN sorgusunu güvenli hale getir
        $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
        
        // Konu sayısı
        $topic_count = $this->database->fetchValue("
            SELECT COUNT(*) FROM forum_topics 
            WHERE category_id IN ({$placeholders}) AND status != 'deleted'
        ", $category_ids);
        
        // Yorum sayısı
        $post_count = $this->database->fetchValue("
            SELECT COUNT(*) FROM forum_comments c
            JOIN forum_topics t ON c.topic_id = t.id
            WHERE t.category_id IN ({$placeholders}) AND t.status != 'deleted'
        ", $category_ids);
        
        return [
            'topic_count' => $topic_count,
            'post_count' => $post_count
        ];
    }
    
    /**
     * Bir kategorideki son yazıyı (konu veya yorum) getirir
     * 
     * @param int $category_id Kategori ID
     * @return array|null Son yazı bilgileri veya yoksa null
     */
    public function getLastPost($category_id) {
        // Kategori ve tüm alt kategorileri bul
        $category = Category::find($category_id);
        if (!$category) {
            return null;
        }
        
        // Kendisi ve alt kategorileri
        $category_ids = [$category_id];
        $sub_categories = $category->getSubCategories(true);
        
        foreach ($sub_categories as $sub) {
            $category_ids[] = $sub->getId();
        }
        
        // Kategori yoksa null döndür
        if (empty($category_ids)) {
            return null;
        }
        
        // Prepare statement kullanarak IN sorgusunu güvenli hale getir
        $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
        
        // Son konu veya yorum
        $sql = "
            (SELECT 
                t.id AS topic_id,
                NULL AS comment_id,
                t.title,
                t.created_at AS post_date,
                t.discord_user_id,
                u.username,
                'topic' AS post_type
             FROM 
                forum_topics t
             LEFT JOIN 
                forum_users u ON t.discord_user_id = u.discord_id
             WHERE 
                t.category_id IN ({$placeholders}) AND t.status != 'deleted')
            UNION
            (SELECT 
                t.id AS topic_id,
                c.id AS comment_id,
                t.title,
                c.created_at AS post_date,
                c.discord_user_id,
                u.username,
                'comment' AS post_type
             FROM 
                forum_comments c
             JOIN 
                forum_topics t ON c.topic_id = t.id
             LEFT JOIN 
                forum_users u ON c.discord_user_id = u.discord_id
             WHERE 
                t.category_id IN ({$placeholders}) AND t.status != 'deleted')
            ORDER BY 
                post_date DESC
            LIMIT 1
        ";
        
        // İki kez kategori_ids'i parametre olarak eklemeliyiz çünkü iki UNION sorgumuz var
        $params = array_merge($category_ids, $category_ids);
        
        return $this->database->fetchOne($sql, $params);
    }
    
    /**
     * Üst kategorileri ve alt kategorileri getir
     * 
     * @param int $parent_id Üst kategori ID'si, null ise ana kategorileri getirir
     * @param bool $recursive Alt kategorileri de getir
     * @param int $maxDepth Maksimum derinlik
     * @return array Kategori nesneleri dizisi
     */
    public function getCategories($parent_id = null, $recursive = false, $maxDepth = 10) {
        if ($parent_id === null) {
            // Ana kategorileri getir
            return Category::getAll(true);
        } else {
            // Alt kategorileri getir
            $category = Category::find($parent_id);
            if (!$category) {
                return [];
            }
            
            return $category->getSubCategories($recursive, $maxDepth);
        }
    }
}
