<?php
// Oturum kontrolü
include_once 'includes/auth_check.php';

// Session fonksiyonlarını dahil et
include_once 'includes/session.php';

// Bakım modu kontrolü
include_once 'includes/maintenance.php';
if (is_maintenance_mode()) {
    display_maintenance_page();
}

// Forum fonksiyonlarını dahil et
include_once 'includes/forum-functions.php';

// URL'den kategori slug'ını al
$category_slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Kategoriyi slug'a göre bul
$category = get_forum_category_by_slug($category_slug);

// Eğer kategori bulunamazsa, forum ana sayfasına yönlendir
if (!$category) {
    header('Location: forum.php');
    exit;
}

// Sayfa numarasını al
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Sadece doğrudan alt kategorileri getir (recursive=false)
$direct_sub_categories = get_sub_categories($category['id'], false);

// Alt kategoriler yoksa veya alt kategorilere göz atıldıysa konuları göster
$show_topics = empty($direct_sub_categories);

// Kategorideki konuları getir
if ($show_topics) {
    $topics_data = get_forum_topics($category['id'], $page, 10);
    $topics = $topics_data['topics'];
    $pagination = $topics_data['pagination'];
}

include 'includes/header.php';

// Özel stil ekle - tüm hover efektlerini devre dışı bırak
?>
<style>
/* Tüm hover efektlerini devre dışı bırak */
a:hover, .btn-primary:hover, .card:hover, tr:hover, 
a:hover .text-primary-light, a:hover span, .hover\:bg-gray-700:hover, 
.hover\:text-primary:hover, .hover\:text-primary-light:hover, 
.hover\:text-white:hover, .hover\:text-blue-300:hover,
.hover\:bg-gray-600:hover, .hover\:bg-gray-750:hover {
    color: inherit !important;
    background-color: inherit !important;
    transform: none !important;
    box-shadow: inherit !important;
    transition: none !important;
}
</style>
<?php
?>

<!-- Forum Kategori Section -->
<section class="min-h-[80vh] pt-10 pb-16">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb - Kategori hiyerarşi ağacı -->
        <div class="text-sm text-gray-500 mb-6 breadcrumb">
            <a href="forum.php" class="text-primary">Forum</a>
            
            <?php 
            // Kategori yol ağacını (breadcrumb) oluştur
            $breadcrumb = get_category_breadcrumb($category['id']);
            foreach ($breadcrumb as $index => $item):
                if ($index === count($breadcrumb) - 1) continue; // Son öğeyi atla (mevcut sayfa)
            ?>
                <span class="mx-2">/</span>
                <a href="forum-category.php?slug=<?php echo $item['slug']; ?>" class="text-primary">
                    <?php echo htmlspecialchars($item['name']); ?>
                </a>
            <?php endforeach; ?>
            
            <span class="mx-2">/</span>
            <span class="text-gray-400"><?php echo htmlspecialchars($category['name']); ?></span>
        </div>
        
        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between mb-10 gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-1 text-primary">
                    <?php echo htmlspecialchars($category['name']); ?>
                </h1>
                <p class="text-gray-400 text-lg">
                    <?php echo htmlspecialchars($category['description']); ?>
                </p>
            </div>
            
            <!-- Yeni Konu Oluştur Butonu -->
            <div class="mt-2 md:mt-0 flex-shrink-0">
                <a href="new-topic.php?category_id=<?php echo $category['id']; ?>" class="btn-primary inline-flex items-center px-5 py-2.5 rounded-lg font-medium">
                    <i class="fas fa-plus mr-2"></i> Yeni Konu Oluştur
                </a>
            </div>
        </div>
        
        <?php if (!empty($direct_sub_categories)): ?>
            <!-- Alt Kategoriler -->
            <div class="card shadow-lg overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-700 bg-gray-800">
                    <h2 class="text-xl font-bold text-primary">Alt Kategoriler</h2>
                </div>
                
                <div class="divide-y divide-gray-700">
                    <?php foreach ($direct_sub_categories as $sub_category): 
                        // Her alt kategori için istatistikleri hesapla
                        $stats = get_category_stats($sub_category['id']);
                        $last_post = get_last_post($sub_category['id']);
                        
                        // Alt kategorinin kendi alt kategorileri var mı kontrol et
                        $third_level_categories = get_sub_categories($sub_category['id'], false);
                        
                        // Varsayılan ikon ve renk kontrolü
                        $icon = !empty($sub_category['icon']) ? $sub_category['icon'] : 'fa-folder';
                        $icon_color = !empty($sub_category['icon_color']) ? $sub_category['icon_color'] : '#747F8D';
                    ?>
                        <div class="category-card p-1">
                            <div class="flex flex-wrap md:flex-nowrap items-stretch bg-gray-800/80 rounded-lg overflow-hidden">
                                <!-- İkon/Logo kısmı -->
                                <div class="p-4 flex items-center justify-center w-16 md:w-20 bg-gray-850/70">
                                    <i class="fas <?php echo htmlspecialchars($icon); ?> text-3xl" style="color: <?php echo htmlspecialchars($icon_color); ?>"></i>
                                </div>
                                
                                <!-- Kategori bilgileri -->
                                <div class="p-4 flex-grow border-l border-gray-700 min-w-[50%]">
                                    <a href="forum-category.php?slug=<?php echo $sub_category['slug']; ?>" class="text-primary font-bold text-lg">
                                        <?php echo htmlspecialchars($sub_category['name']); ?>
                                    </a>
                                    <p class="text-sm text-gray-400 line-clamp-2"><?php echo htmlspecialchars($sub_category['description']); ?></p>
                                    
                                    <?php if (!empty($third_level_categories)): ?>
                                    <div class="mt-2 flex flex-wrap items-center">
                                        <span class="text-xs font-semibold text-gray-400 mr-2">Alt Kategoriler:</span>
                                        <?php foreach ($third_level_categories as $third_level_cat): ?>
                                            <a href="forum-category.php?slug=<?php echo $third_level_cat['slug']; ?>" 
                                                class="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded-md mx-1">
                                                <?php echo htmlspecialchars($third_level_cat['name']); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- İstatistikler -->
                                <div class="p-4 border-l border-gray-700 w-full md:w-28 text-center flex flex-col justify-center">
                                    <div class="text-gray-300"><?php echo (int)$stats['post_count']; ?> ileti</div>
                                    <div class="text-gray-300"><?php echo (int)$stats['topic_count']; ?> konu</div>
                                </div>
                                
                                <!-- Son yazı -->
                                <div class="p-4 border-l border-gray-700 w-full md:w-64 flex flex-col justify-center">
                                    <?php if ($last_post): ?>
                                        <div class="text-sm">
                                            <span class="text-gray-400">Gönderen:</span> 
                                            <span class="text-primary"><?php echo htmlspecialchars($last_post['username'] ?? 'Bilinmeyen'); ?></span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo date('d.m.Y H:i', strtotime($last_post['post_date'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-gray-500 text-sm">Henüz yazı bulunmuyor</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($show_topics): ?>
            <!-- Konular Listesi (Table) -->
            <div class="card shadow-lg overflow-hidden mb-8">
                <?php if (isset($topics) && count($topics) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Konu</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Yazar</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">İstatistikler</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Son Aktivite</th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-850 divide-y divide-gray-700">
                                <?php foreach ($topics as $topic): ?>
                                    <tr>
                                        <!-- Konu Başlığı -->
                                        <td class="px-6 py-4 max-w-sm">
                                            <a href="forum-topic.php?id=<?php echo $topic['id']; ?>" class="block text-primary font-medium text-md truncate">
                                                <?php echo htmlspecialchars($topic['title']); ?>
                                            </a>
                                            <div class="text-gray-400 text-xs mt-1">
                                                Oluşturulma: <?php echo date('d.m.Y', strtotime($topic['created_at'])); ?>
                                            </div>
                                        </td>

                                        <!-- Yazar -->
                                        <td class="px-4 py-4 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center">
                                                <?php
                                                // Kullanıcı adının ilk harfi
                                                $firstLetter = !empty($topic['creator_username']) ? strtoupper(substr($topic['creator_username'], 0, 1)) : '?';
                                                
                                                // Kullanıcı ID'sine göre renk seç
                                                $colors = ['#747F8D', '#43B581', '#FAA61A', '#F04747', '#7289DA'];
                                                $colorIndex = isset($topic['discord_user_id']) ? (intval(substr($topic['discord_user_id'], -1)) % 5) : 0;
                                                $bgColor = $colors[$colorIndex];
                                                ?>
                                                <div style="background-color: <?php echo $bgColor; ?>;"
                                                     class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mr-2">
                                                    <?php echo $firstLetter; ?>
                                                </div>
                                                <span class="text-sm text-gray-300 truncate">
                                                    <?php echo htmlspecialchars($topic['creator_username'] ?? 'Bilinmeyen'); ?>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- İstatistikler (Yanıtlar/Görüntülenme) -->
                                        <td class="px-4 py-4 text-center whitespace-nowrap text-sm text-gray-400">
                                            <div class="flex justify-center items-center space-x-3">
                                                <span title="Yanıtlar"><i class="far fa-comments mr-1 text-gray-500"></i> <?php echo (int)$topic['comment_count']; ?></span>
                                                <span title="Görüntülenme"><i class="far fa-eye mr-1 text-gray-500"></i> <?php echo $topic['views']; ?></span>
                                            </div>
                                        </td>

                                        <!-- Son Güncelleme -->
                                        <td class="px-6 py-4 text-right whitespace-nowrap text-sm text-gray-400">
                                            <?php echo format_forum_date($topic['updated_at']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="py-16 text-center text-gray-500 flex flex-col items-center justify-center">
                        <i class="far fa-folder-open text-6xl mb-4 text-gray-600"></i>
                        <p class="text-lg mb-2">Bu kategoride henüz konu bulunmamaktadır.</p>
                        <p class="text-sm">İlk konuyu siz oluşturun!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sayfalama -->
            <?php if (isset($pagination)): ?>
                <?php echo get_pagination_links($pagination, "forum-category.php?slug={$category_slug}"); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
