<?php
/**
 * Forum için yardımcı fonksiyonlar
 */

use Core\Config;
use Core\Cache;
use Models\Forum\Category;

/**
 * Forum kategorilerini getirir
 * 
 * @param bool $topLevelOnly Sadece üst düzey kategorileri getir
 * @return array Kategori listesi
 */
function get_forum_categories($topLevelOnly = false) {
    $categories = Category::getAll($topLevelOnly);
    
    // Template için kullanılabilir dizi formatına dönüştür
    $result = [];
    foreach ($categories as $category) {
        $result[] = $category->toArray();
    }
    
    return $result;
}

/**
 * Bir kategorinin alt kategorilerini getirir
 * 
 * @param int $categoryId Kategori ID'si
 * @param bool $recursive Alt seviye kategorilerini de getir
 * @param int $maxDepth Maksimum derinlik (recursive true ise kullanılır)
 * @return array Alt kategori listesi
 */
function get_sub_categories($categoryId, $recursive = false, $maxDepth = 10) {
    $category = Category::find($categoryId);
    if (!$category) {
        return [];
    }
    
    $subCategories = $category->getSubCategories($recursive, $maxDepth);
    
    // Template için kullanılabilir dizi formatına dönüştür
    $result = [];
    foreach ($subCategories as $subCategory) {
        $result[] = $subCategory->toArray();
    }
    
    return $result;
}

/**
 * Bir kategorinin breadcrumb (ekmek kırıntısı) dizisini oluşturur
 * 
 * @param int $categoryId Kategori ID'si
 * @return array Kategori hiyerarşi listesi (üst kategoriden başlayarak)
 */
function get_category_breadcrumb($categoryId) {
    $category = Category::find($categoryId);
    if (!$category) {
        return [];
    }
    
    $breadcrumb = $category->getBreadcrumb();
    
    // Template için kullanılabilir dizi formatına dönüştür
    $result = [];
    foreach ($breadcrumb as $item) {
        $result[] = $item->toArray();
    }
    
    return $result;
}

/**
 * Bir kategorinin derinliğini döndürür (üst kategoriler zincirindeki pozisyonu)
 * 
 * @param int $categoryId Kategori ID'si
 * @return int Kategori derinliği (ana kategoriler için 0, ilk seviye alt kategoriler için 1)
 */
function get_category_depth($categoryId) {
    $category = Category::find($categoryId);
    if (!$category) {
        return 0;
    }
    
    return $category->getDepth();
}

// get_forum_category_by_slug fonksiyonu forum-functions.php içinde tanımlandığı için
// bu fonksiyon burada kaldırılmıştır. Bu foksiyon çağrıları artık
// Category modeli üzerinden yönlendirilmektedir.

// format_forum_text fonksiyonu forum-functions.php içinde tanımlandığı için
// bu fonksiyon burada kaldırılmıştır. Gelecekte, tam OOP yaklaşımıyla
// bir FormatHelper sınıfına taşınması planlanmaktadır.

// Aşağıdaki forum yardımcı fonksiyonları forum-functions.php içinde tanımlandığı için,
// burada kaldırılmış ve gelecekte Model-Service mimarisi ile yeniden yazılacaktır.
//
// * get_discord_avatar_url()
// * format_forum_date()
// * get_pagination_links()
// * is_topic_owner() 
// * is_comment_owner()
