<?php
/**
 * Forum Ana Sayfası
 */

// Bootstrap dosyasını dahil et
require_once 'includes/bootstrap.php';

// Auth ve session kontrolü
require_once 'includes/auth_check.php';

// Admin fonksiyonlarını dahil et - bakım modu erişimi için
require_once 'admin/functions/settings-functions.php';

// Bakım modu kontrolü - doğrudan has_maintenance_access() fonksiyonunu kullanarak kontrol ediyoruz
if (is_maintenance_mode()) {
    // Kullanıcının bakım modunda erişim izni var mı kontrol et
    $discord_user_id = isset($_SESSION['discord_user_id']) ? $_SESSION['discord_user_id'] : null;
    if (!$discord_user_id || !has_maintenance_access($discord_user_id)) {
        display_maintenance_page();
    }
    // Erişim izni varsa normal sayfayı göstermeye devam et
}

// Sadece üst düzey kategorileri, modeli kullanarak getir
$categories = get_forum_categories(true);

// Son aktiviteleri getir
$db = \Core\Database::getInstance();
$recent_topics = $db->fetchAll("
    SELECT t.id, t.title, t.created_at, c.name as category_name, c.slug as category_slug, u.username
    FROM forum_topics t
    JOIN forum_categories c ON t.category_id = c.id
    LEFT JOIN forum_users u ON t.discord_user_id = u.discord_id
    WHERE t.status != 'deleted'
    ORDER BY t.created_at DESC
    LIMIT 5
");

// Kategori detayları
$category_details = [
    'karakterler' => ['icon' => 'fa-user', 'color' => '#3490dc'],
    'departmanlar' => ['icon' => 'fa-building', 'color' => '#38c172'],
    'isletmeler' => ['icon' => 'fa-briefcase', 'color' => '#f6993f'],
    'birlikler' => ['icon' => 'fa-handshake', 'color' => '#9561e2']
];
$default_icon = 'fa-comments';
$default_color = '#6c757d';

include 'includes/header.php';
?>

<!-- Forum Ana Sayfa Section -->
<section class="min-h-[80vh] pt-12 pb-16">
    <div class="container mx-auto px-4">
        <!-- Page Header -->
        <div class="relative text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-primary tracking-tight">
                Fellas Roleplay Forum
            </h1>
            <p class="text-lg text-gray-400 mb-6">Topluluğumuzla bağlantı kurun, tartışmalara katılın.</p>
            <!-- Decorative ayırıcı -->
            <div class="h-1 w-20 bg-gradient-to-r from-primary-dark to-primary mx-auto"></div>
        </div>
        
        <!-- Forum Kategorileri -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8 mb-16 justify-items-center mx-auto max-w-6xl">
            <?php foreach ($categories as $category):
                // Oluşumlar kategorisini atla
                if ($category['slug'] === 'olusumlar') continue;

                $details = $category_details[$category['slug']] ?? null;
                $icon = $details['icon'] ?? $default_icon;
                $color = $details['color'] ?? $default_color;
            ?>
            <!-- Kategori Kartı (Eski Stil) -->
            <div class="category-card w-full max-w-xs">
                <a href="forum-category.php?slug=<?php echo $category['slug']; ?>" class="block h-full">
                    <div class="text-center p-6 rounded-lg bg-gray-800 border-2 border-gray-700 hover:border-primary transition-all duration-300 h-full flex flex-col items-center justify-start shadow-lg hover:shadow-primary/20">
                        <!-- Kategori İkonu -->
                        <div class="mb-4">
                            <div class="w-24 h-24 rounded-full flex items-center justify-center shadow-md" style="background-color: <?php echo $color; ?>;">
                                <i class="fas <?php echo $icon; ?> fa-3x text-white"></i>
                            </div>
                        </div>

                        <!-- Kategori Başlığı -->
                        <h3 class="text-xl font-bold text-white mb-2">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </h3>

                        <!-- Kategori Açıklaması -->
                        <p class="text-gray-400 text-sm flex-grow">
                            <?php
                            $desc = $category['description'] ?? '';
                            echo (strlen($desc) > 60) ? substr(htmlspecialchars($desc), 0, 60) . '...' : htmlspecialchars($desc);
                            ?>
                        </p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Son Aktiviteler -->
        <div class="card p-6 shadow-lg">
            <h2 class="text-2xl font-semibold mb-5 text-primary border-b border-gray-700 pb-3">Son Aktiviteler</h2>

            <div class="overflow-hidden">
                <?php if (count($recent_topics) > 0): ?>
                <div class="overflow-x-auto -mx-6 px-6">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Konu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Yazar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tarih</th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-850 divide-y divide-gray-700">
                            <?php foreach ($recent_topics as $topic): ?>
                            <tr class="hover:bg-gray-700 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="forum-topic.php?id=<?php echo $topic['id']; ?>" class="text-primary hover:text-primary-light transition-colors text-sm font-medium">
                                        <?php echo htmlspecialchars($topic['title']); ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                    <a href="forum-category.php?slug=<?php echo $topic['category_slug']; ?>" class="hover:text-white transition-colors">
                                        <?php echo htmlspecialchars($topic['category_name']); ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                    <?php echo htmlspecialchars($topic['username'] ?? 'Bilinmeyen Kullanıcı'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                    <?php echo format_forum_date($topic['created_at']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-center text-gray-400 py-6">Henüz konu bulunmamaktadır.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Kategori Resimlerini Preload Et (Kaldırıldı) -->

<!-- Forum CSS Stilleri -->
<style>
    .category-card {
        transition: all 0.3s ease;
    }
    
    .category-card:hover {
        transform: translateY(-5px);
    }

    /* bg-gray-850 tanımı (eğer style.css'de yoksa) */
    .bg-gray-850 {
        background-color: rgba(31, 41, 55, 0.9); /* gray-800 with some transparency or slightly different */
    }
</style>

<?php include 'includes/footer.php'; ?>
