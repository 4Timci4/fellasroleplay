<?php
/**
 * Forum Kategori Yönetimi Fonksiyonları
 */

/**
 * Kategori ekleme fonksiyonu
 *
 * @param array $data Kategori verileri
 * @return array İşlem sonucu ['success' => bool, 'message' => string]
 */
function add_forum_category($data) {
    $parent_id = isset($data['parent_id']) && !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
    $name = isset($data['name']) ? trim($data['name']) : '';
    $slug = isset($data['slug']) ? trim($data['slug']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $icon = isset($data['icon']) ? trim($data['icon']) : 'fa-folder';
    $icon_color = isset($data['icon_color']) ? trim($data['icon_color']) : '#747F8D';
    $display_order = isset($data['display_order']) ? (int)$data['display_order'] : 0;
    
    // Zorunlu alan kontrolü
    if (empty($name)) {
        return [
            'success' => false,
            'message' => 'Kategori adı boş olamaz.'
        ];
    }
    
    // Slug'ı otomatik oluştur
    if (empty($slug)) {
        $slug = create_slug($name);
    }
    
    // Veritabanına ekle
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    $stmt = $conn->prepare("
        INSERT INTO forum_categories (parent_id, name, slug, description, icon, icon_color, display_order) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$parent_id, $name, $slug, $description, $icon, $icon_color, $display_order])) {
        // Önbelleği temizle
        if (function_exists('Cache::forget')) {
            \Core\Cache::forget('forum_categories');
        }
        
        return [
            'success' => true,
            'message' => 'Kategori başarıyla eklendi.',
            'category_id' => $conn->lastInsertId()
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Kategori eklenirken bir hata oluştu.'
        ];
    }
}

/**
 * Kategori güncelleme fonksiyonu
 *
 * @param array $data Kategori verileri
 * @return array İşlem sonucu ['success' => bool, 'message' => string]
 */
function update_forum_category($data) {
    $category_id = isset($data['category_id']) ? (int)$data['category_id'] : 0;
    $parent_id = isset($data['parent_id']) && !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
    $name = isset($data['name']) ? trim($data['name']) : '';
    $slug = isset($data['slug']) ? trim($data['slug']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $icon = isset($data['icon']) ? trim($data['icon']) : 'fa-folder';
    $icon_color = isset($data['icon_color']) ? trim($data['icon_color']) : '#747F8D';
    $display_order = isset($data['display_order']) ? (int)$data['display_order'] : 0;
    
    // Zorunlu alan kontrolü
    if (empty($category_id) || empty($name)) {
        return [
            'success' => false,
            'message' => 'Kategori ID ve adı boş olamaz.'
        ];
    }
    
    // Kategori kendisinin üst kategorisi olamaz
    if ($parent_id == $category_id) {
        return [
            'success' => false,
            'message' => 'Bir kategori kendisinin üst kategorisi olamaz.'
        ];
    }
    
    // Slug'ı otomatik oluştur
    if (empty($slug)) {
        $slug = create_slug($name);
    }
    
    // Veritabanında güncelle
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    $stmt = $conn->prepare("
        UPDATE forum_categories 
        SET parent_id = ?, name = ?, slug = ?, description = ?, icon = ?, icon_color = ?, display_order = ? 
        WHERE id = ?
    ");
    
    if ($stmt->execute([$parent_id, $name, $slug, $description, $icon, $icon_color, $display_order, $category_id])) {
        // Önbelleği temizle
        if (function_exists('Cache::forget')) {
            \Core\Cache::forget('forum_categories');
        }
        
        return [
            'success' => true,
            'message' => 'Kategori başarıyla güncellendi.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Kategori güncellenirken bir hata oluştu.'
        ];
    }
}

/**
 * Kategori silme fonksiyonu
 *
 * @param int $category_id Kategori ID
 * @return array İşlem sonucu ['success' => bool, 'message' => string]
 */
function delete_forum_category($category_id) {
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
    
    // Önce alt kategorileri kontrol et
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM forum_categories WHERE parent_id = ?");
    $check_stmt->execute([$category_id]);
    $has_subcategories = ($check_stmt->fetchColumn() > 0);
    
    if ($has_subcategories) {
        return [
            'success' => false,
            'message' => 'Bu kategorinin alt kategorileri var. Önce alt kategorileri silmelisiniz.'
        ];
    }
    
    // Konuları kontrol et
    $check_topics = $conn->prepare("SELECT COUNT(*) FROM forum_topics WHERE category_id = ?");
    $check_topics->execute([$category_id]);
    $has_topics = ($check_topics->fetchColumn() > 0);
    
    if ($has_topics) {
        return [
            'success' => false,
            'message' => 'Bu kategoride konular var. Önce konuları silmeli veya başka bir kategoriye taşımalısınız.'
        ];
    }
    
    // Kategoriyi sil
    $delete_stmt = $conn->prepare("DELETE FROM forum_categories WHERE id = ?");
    
    if ($delete_stmt->execute([$category_id])) {
        // Önbelleği temizle
        if (function_exists('Cache::forget')) {
            \Core\Cache::forget('forum_categories');
        }
        
        return [
            'success' => true,
            'message' => 'Kategori başarıyla silindi.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Kategori silinirken bir hata oluştu.'
        ];
    }
}

/**
 * Slug oluşturma fonksiyonu
 *
 * @param string $string Slug oluşturulacak metin
 * @return string Oluşturulan slug
 */
function create_slug($string) {
    // Türkçe karakterleri dönüştür
    $turkish = array('ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç');
    $english = array('i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c');
    $string = str_replace($turkish, $english, $string);
    
    // Küçük harfe dönüştür
    $string = strtolower($string);
    
    // Alfanumerik olmayan karakterleri çıkart
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    
    // Boşlukları tirelerle değiştir
    $string = preg_replace('/[\s]+/', '-', $string);
    
    // Tekrarlayan tireleri kaldır
    $string = preg_replace('/-+/', '-', $string);
    
    // Baştaki ve sondaki tireleri temizle
    $string = trim($string, '-');
    
    return $string;
}

/**
 * Recursive olarak alt kategorileri gösterme fonksiyonu
 *
 * @param array $categories Tüm kategoriler listesi
 * @param int $parent_id Üst kategori ID
 * @param int $indent_level Girintileme seviyesi
 * @return void
 */
function display_subcategories($categories, $parent_id, $indent_level = 1) {
    // Bu kategorinin tüm alt kategorilerini bul
    $sub_categories = array_filter($categories, function($cat) use ($parent_id) {
        return isset($cat['parent_id']) && $cat['parent_id'] == $parent_id;
    });
    
    // Alt kategorileri göster
    foreach ($sub_categories as $sub_category) {
        ?>
        <tr class="hover:bg-gray-750 transition-colors duration-150 bg-gray-750/30">
            <td class="px-6 py-4">
                <div class="flex items-center" style="margin-left: <?php echo ($indent_level * 20); ?>px;">
                    <?php for ($i = 0; $i < $indent_level; $i++) { ?>
                        <span class="text-gray-500 mr-1">↳</span>
                    <?php } ?>
                    <div class="mr-3 flex-shrink-0">
                        <i class="fas <?php echo htmlspecialchars($sub_category['icon'] ?: 'fa-folder'); ?> text-lg" style="color: <?php echo htmlspecialchars($sub_category['icon_color'] ?: '#747F8D'); ?>"></i>
                    </div>
                    <div>
                        <div class="font-medium text-white"><?php echo htmlspecialchars($sub_category['name']); ?></div>
                        <div class="text-xs text-gray-400"><?php echo htmlspecialchars($sub_category['description']); ?></div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 text-center text-gray-300">
                <?php echo $sub_category['display_order']; ?>
            </td>
            <td class="px-6 py-4 text-right">
                <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($sub_category)); ?>)" class="text-blue-400 hover:text-blue-300 mr-3">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="confirmDeleteCategory(<?php echo $sub_category['id']; ?>, '<?php echo addslashes($sub_category['name']); ?>')" class="text-red-400 hover:text-red-300">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
        <?php
        // Bu alt kategorinin kendi alt kategorilerini göster (recursive çağrı)
        display_subcategories($categories, $sub_category['id'], $indent_level + 1);
    }
}
