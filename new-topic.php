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

// URL'den kategori ID'sini al
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Kategoriyi getir
$category = get_forum_category($category_id);

// Eğer kategori bulunamazsa, forum ana sayfasına yönlendir
if (!$category) {
    header('Location: forum.php');
    exit;
}

// Konu ekleme işlemi
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_topic'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    if (empty($title)) {
        $error = 'Konu başlığı boş olamaz.';
    } elseif (empty($content)) {
        $error = 'Konu içeriği boş olamaz.';
    } else {
        $discord_user_id = get_discord_user_id();
        
        if ($discord_user_id) {
            $result = add_forum_topic($category_id, $discord_user_id, $title, $content);
            
            if ($result) {
                $success = 'Konunuz başarıyla oluşturuldu.';
                // Kullanıcıyı konu sayfasına yönlendir
                header("Location: forum-topic.php?id={$result}");
                exit;
            } else {
                $error = 'Konu oluşturulurken bir hata oluştu. Lütfen tekrar deneyin.';
            }
        } else {
            $error = 'Konu oluşturabilmek için giriş yapmalısınız.';
        }
    }
}

include 'includes/header.php';
?>

<!-- Yeni Konu Oluşturma Section -->
<section class="min-h-[80vh] pt-10 pb-16">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <div class="text-sm text-gray-500 mb-6 breadcrumb">
            <a href="forum.php" class="hover:text-primary">Forum</a>
            <span class="mx-2">/</span>
            <a href="forum-category.php?slug=<?php echo $category['slug']; ?>" class="hover:text-primary">
                <?php echo htmlspecialchars($category['name']); ?>
            </a>
            <span class="mx-2">/</span>
            <span class="text-gray-400">Yeni Konu</span>
        </div>
        
        <!-- Page Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-2 text-primary">
                Yeni Konu Oluştur
            </h1>
            <p class="text-gray-400 text-lg">
                <?php echo htmlspecialchars($category['name']); ?> kategorisinde yeni bir konu açıyorsunuz.
            </p>
        </div>
        
        <?php if (is_logged_in()): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Konu Oluşturma Formu (Sol taraf) -->
                <div class="md:col-span-2">
                    <div class="card p-6 shadow-lg">
                        <?php if ($error): ?>
                            <div class="bg-red-700 border border-red-600 text-white p-4 mb-6 rounded-lg text-sm">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="bg-green-700 border border-green-600 text-white p-4 mb-6 rounded-lg text-sm">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="mb-5">
                                <label for="title" class="block text-gray-300 mb-1.5 text-sm font-medium">Konu Başlığı</label>
                                <input 
                                    type="text" 
                                    id="title" 
                                    name="title" 
                                    class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                    placeholder="Konunuza dikkat çekici bir başlık verin"
                                    required
                                    maxlength="200"
                                    value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                >
                            </div>
                            
                            <div class="mb-6">
                                <label for="content" class="block text-gray-300 mb-1.5 text-sm font-medium">Konu İçeriği</label>
                                <!-- Quill Editor Container -->
                                <div id="content-editor" style="height: 300px;" class="bg-gray-900 border border-gray-700 rounded-lg mb-1"></div>
                                <textarea 
                                    id="content" 
                                    name="content" 
                                    rows="12" 
                                    class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Konunuz hakkında ayrıntılı bilgi verin"
                                    required
                                ><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                            </div>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-gray-700">
                                <a href="forum-category.php?slug=<?php echo $category['slug']; ?>" class="text-gray-400 hover:text-white transition-colors text-sm font-medium">
                                    <i class="fas fa-arrow-left mr-1.5"></i> İptal
                                </a>
                                
                                <button type="submit" name="add_topic" class="btn-primary inline-flex items-center px-6 py-2.5 rounded-lg text-sm font-medium">
                                    <i class="fas fa-paper-plane mr-2"></i> Konuyu Oluştur
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- İpuçları (Sağ taraf) -->
                <div class="md:col-span-1">
                    <div class="bg-gray-800 rounded-lg p-6 sticky top-24 shadow">
                        <h3 class="text-lg font-semibold mb-4 text-primary border-b border-gray-700 pb-2">
                            <i class="fas fa-lightbulb mr-2 text-yellow-400"></i> İpuçları & Kurallar
                        </h3>
                        
                        <ul class="space-y-3 text-sm text-gray-300">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2.5 flex-shrink-0"></i>
                                <div>Açıklayıcı ve <strong class="text-white">dikkat çekici</strong> bir başlık seçin.</div>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2.5 flex-shrink-0"></i>
                                <div>İçeriğinizi <strong class="text-white">düzenli ve okunaklı</strong> yazın (paragraflar, listeler kullanın).</div>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2.5 flex-shrink-0"></i>
                                <div>Sorularınızı <strong class="text-white">net</strong> bir şekilde belirtin.</div>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-2.5 flex-shrink-0"></i>
                                <div>Forum kurallarına uyun, saygılı bir dil kullanın.</div>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-2.5 flex-shrink-0"></i>
                                <div><strong class="text-white">Kişisel bilgilerinizi</strong> (telefon, adres vb.) paylaşmaktan kaçının.</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Giriş Yapma Uyarısı -->
            <div class="card p-8 text-center">
                <i class="fas fa-lock text-5xl mb-4 text-gray-400"></i>
                <h2 class="text-2xl font-bold mb-4 text-primary">Giriş yapmanız gerekiyor</h2>
                <p class="text-gray-300 mb-6">Konu oluşturabilmek için Discord hesabınızla giriş yapmalısınız.</p>
                
                <a href="login.php" class="btn-primary inline-flex items-center px-6 py-3 rounded-lg">
                    <i class="fab fa-discord mr-2"></i> Discord ile Giriş Yap
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
