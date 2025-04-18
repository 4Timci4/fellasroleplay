<?php
/**
 * Forum kategori işlemleri için gerekli fonksiyonlar
 * 
 * Bu dosya, prosedürel API'den OOP yaklaşımına geçiş aşamasında geriye uyumluluk sağlar.
 * Eski fonksiyonlar korunmuştur, ancak yeni OOP sınıflarını kullanırlar.
 * 
 * @deprecated Doğrudan Services\ForumService sınıfını kullanın
 */

// Gerekli sınıfları ve yardımcı fonksiyonları içeren bootstrapper'ı kontrol et
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../bootstrap.php';
}

use Models\Forum\Category;
use Services\ForumService;

/**
 * Belirli bir kategoriyi ID'ye göre getirir
 * 
 * @param int $category_id Kategori ID
 * @return array|null Kategori bilgileri veya bulunamazsa null
 * @deprecated OOP yaklaşımı ile Category::find() kullanın
 */
function get_forum_category($category_id) {
    // Model sınıfını kullanarak kategoriyi getir
    $category = Category::find($category_id);
    return $category ? $category->toArray() : null;
}

/**
 * Belirli bir kategoriyi slug'a göre getirir
 * 
 * @param string $slug Kategori slug
 * @return array|null Kategori bilgileri veya bulunamazsa null
 * @deprecated OOP yaklaşımı ile Category::findBySlug() kullanın
 */
function get_forum_category_by_slug($slug) {
    // Model sınıfını kullanarak kategoriyi getir
    $category = Category::findBySlug($slug);
    return $category ? $category->toArray() : null;
}

/**
 * Belirli bir kategorinin alt kategorilerini getirir
 * 
 * Bu fonksiyon, includes/helpers/forum_helpers.php'deki get_sub_categories() fonksiyonuyla 
 * çakışma yaşamamak için sadece gerektiğinde tanımlanır.
 * 
 * @param int $parent_id Üst kategori ID'si
 * @param bool $recursive Alt kategorileri de getir (true) veya sadece bir seviye getir (false)
 * @param int $maxDepth Maksimum derinlik (recursive=true ise kullanılır)
 * @return array Alt kategoriler dizisi
 * @deprecated OOP yaklaşımı ile ForumService::getInstance()->getCategories() kullanın
 */
// Fonksiyonu sadece tanımlanmamışsa tanımla
if (!function_exists('get_sub_categories')) {
    function get_sub_categories($parent_id, $recursive = false, $maxDepth = 10) {
        // ForumService sınıfını kullanarak alt kategorileri getir
        $categories = ForumService::getInstance()->getCategories($parent_id, $recursive, $maxDepth);
        
        // Sonuçları dizi formatına dönüştür (geriye uyumluluk için)
        $result = [];
        foreach ($categories as $category) {
            $result[] = $category->toArray();
        }
        
        return $result;
    }
}

/**
 * Legacy uyumluluk için ayrı fonksiyon
 * 
 * @deprecated Bunun yerine get_sub_categories() kullanın
 */
function get_legacy_sub_categories($parent_id) {
    return get_sub_categories($parent_id);
}

/**
 * Bir kategoride ve alt kategorilerinde toplam konu ve yorum sayısını hesaplar
 * 
 * @param int $category_id Kategori ID
 * @return array Konu ve yorum sayıları ['topic_count' => x, 'post_count' => y]
 * @deprecated OOP yaklaşımı ile ForumService::getInstance()->getCategoryStats() kullanın
 */
function get_category_stats($category_id) {
    // ForumService sınıfını kullan
    return ForumService::getInstance()->getCategoryStats($category_id);
}

/**
 * Bir kategorideki son yazıyı (konu veya yorum) getirir
 * 
 * @param int $category_id Kategori ID
 * @return array|null Son yazı bilgileri veya yoksa null
 * @deprecated OOP yaklaşımı ile ForumService::getInstance()->getLastPost() kullanın
 */
function get_last_post($category_id) {
    // ForumService sınıfını kullan
    return ForumService::getInstance()->getLastPost($category_id);
}
