<?php
namespace Models\Forum;

use Core\Database;
use Core\Cache;

/**
 * Forum Kategori model sınıfı
 */
class Category {
    private $id;
    private $name;
    private $slug;
    private $description;
    private $parent_id;
    private $icon;
    private $icon_color;
    private $display_order;
    
    /**
     * Constructor
     */
    public function __construct($data = null) {
        if (is_array($data)) {
            $this->fill($data);
        }
    }
    
    /**
     * Veri ile model alanlarını doldur
     */
    public function fill($data) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->slug = $data['slug'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->parent_id = $data['parent_id'] ?? null;
        $this->icon = $data['icon'] ?? 'fa-folder';
        $this->icon_color = $data['icon_color'] ?? '#747F8D';
        $this->display_order = $data['display_order'] ?? 0;
    }
    
    /**
     * Tüm kategorileri getir
     * 
     * @param bool $topLevelOnly Sadece üst düzey kategorileri getir
     * @return array Kategori listesi
     */
    public static function getAll($topLevelOnly = false) {
        // Önbelleği temizle (geliştirme süresince)
        Cache::forget('forum_categories');
        
        // Önbellekten kontrol et
        $categoriesCache = Cache::get('forum_categories');
        
        if ($categoriesCache !== null) {
            // Önbellekten getirilen sonuçlardan filtreleme yap
            if ($topLevelOnly) {
                return array_filter($categoriesCache, function($category) {
                    return $category->getParentId() === null;
                });
            }
            return $categoriesCache;
        }
        
        // Veritabanından al
        $db = Database::getInstance();
        $sql = "SELECT * FROM forum_categories";
        
        if ($topLevelOnly) {
            $sql .= " WHERE parent_id IS NULL";
        }
        
        $sql .= " ORDER BY display_order ASC, name ASC";
        $result = $db->fetchAll($sql);
        
        $categories = [];
        foreach ($result as $row) {
            $categories[] = new self($row);
        }
        
        // Önbelleğe kaydet (5 dakika)
        if (!$topLevelOnly) {
            Cache::set('forum_categories', $categories, 300);
        }
        
        return $categories;
    }
    
    /**
     * ID ile kategori bul
     */
    public static function find($id) {
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM forum_categories WHERE id = ?", [$id]);
        
        if (!$row) {
            return null;
        }
        
        return new self($row);
    }
    
    /**
     * Slug ile kategori bul
     */
    public static function findBySlug($slug) {
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM forum_categories WHERE slug = ?", [$slug]);
        
        if (!$row) {
            return null;
        }
        
        return new self($row);
    }
    
    /**
     * Kategorideki konuları getir
     */
    public function getTopics($page = 1, $perPage = 10) {
        if (!$this->id) {
            return null;
        }
        
        $db = Database::getInstance();
        
        // Toplam konu sayısını hesapla
        $totalTopics = $db->fetchValue(
            "SELECT COUNT(*) FROM forum_topics WHERE category_id = ? AND status != 'deleted'", 
            [$this->id]
        );
        
        // Sayfalama hesapla
        $totalPages = ceil($totalTopics / $perPage);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // Konuları getir
        $sql = "
            SELECT t.*, 
                   u.username as creator_username, 
                   u.avatar as creator_avatar,
                   (SELECT COUNT(*) FROM forum_comments WHERE topic_id = t.id) as comment_count
            FROM forum_topics t
            LEFT JOIN forum_users u ON t.discord_user_id = u.discord_id
            WHERE t.category_id = ? AND t.status != 'deleted'
            ORDER BY t.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $topics = $db->fetchAll($sql, [$this->id, $perPage, $offset]);
        
        return [
            'topics' => $topics,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_topics' => $totalTopics,
                'total_pages' => $totalPages
            ]
        ];
    }
    
    // Getter metodları
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getSlug() { return $this->slug; }
    public function getDescription() { return $this->description; }
    public function getParentId() { return $this->parent_id; }
    public function getIcon() { return $this->icon; }
    public function getIconColor() { return $this->icon_color; }
    public function getDisplayOrder() { return $this->display_order; }
    
    /**
     * Bu kategori bir alt kategori mi?
     */
    public function isSubCategory() {
        return $this->parent_id !== null;
    }
    
    /**
     * Alt kategorileri getir, recursive olarak tüm alt seviyeleri de getirebilir
     * 
     * @param bool $recursive Alt seviye kategorilerini de getir
     * @param int $maxDepth Maksimum derinlik (recursive true ise kullanılır)
     * @return array Category nesneleri dizisi
     */
    public function getSubCategories($recursive = false, $maxDepth = 10) {
        $db = Database::getInstance();
        $result = $db->fetchAll(
            "SELECT * FROM forum_categories WHERE parent_id = ? ORDER BY display_order ASC, name ASC", 
            [$this->id]
        );
        
        $categories = [];
        foreach ($result as $row) {
            $category = new self($row);
            $categories[] = $category;
            
            // Özyinelemeli olarak alt kategorileri getir (max derinlik sınırını kontrol et)
            if ($recursive && $maxDepth > 1) {
                $subCategories = $category->getSubCategories($recursive, $maxDepth - 1);
                foreach ($subCategories as $subCategory) {
                    $categories[] = $subCategory;
                }
            }
        }
        
        return $categories;
    }
    
    /**
     * Kategorinin tam hiyerarşisini oluşturur (üst kategorileri getirir)
     * 
     * @return array Kategori hiyerarşi listesi, üst kategoriden başlayarak
     */
    public function getBreadcrumb() {
        if ($this->parent_id === null) {
            return [$this];
        }
        
        $breadcrumb = [$this];
        $parentId = $this->parent_id;
        $depth = 0; // Sonsuz döngüyü önlemek için
        
        while ($parentId !== null && $depth < 10) {
            $parent = self::find($parentId);
            if (!$parent) {
                break;
            }
            
            array_unshift($breadcrumb, $parent); // Listeye başa ekle
            $parentId = $parent->getParentId();
            $depth++;
        }
        
        return $breadcrumb;
    }
    
    /**
     * Kategorinin derinliğini bulur (üst kategoriler zincirindeki pozisyonu)
     * 
     * @return int Kategori derinliği (ana kategoriler için 0, ilk seviye alt kategoriler için 1)
     */
    public function getDepth() {
        return count($this->getBreadcrumb()) - 1;
    }
    
    /**
     * Model verilerini dizi olarak döndür
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'icon' => $this->icon,
            'icon_color' => $this->icon_color,
            'display_order' => $this->display_order,
        ];
    }
}
