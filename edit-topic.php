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

// CSRF token oluştur
$csrf_token = generate_csrf_token();

// Discord kullanıcı ID al
$discord_user_id = get_discord_user_id();
if (!$discord_user_id) {
    header('Location: login.php');
    exit;
}

// URL'den konu ID'sini al
$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Konuyu getir
$topic = get_forum_topic($topic_id);

// Eğer konu bulunamazsa veya kullanıcı konu sahibi değilse, forum ana sayfasına yönlendir
if (!$topic || !is_topic_owner($topic_id, $discord_user_id)) {
    header('Location: forum.php');
    exit;
}

$error = '';
$success = '';

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_topic'])) {
    // CSRF kontrolü
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen tekrar deneyin.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        
        // Basit doğrulama
        if (empty($title)) {
            $error = 'Konu başlığı boş olamaz.';
        } elseif (empty($content)) {
            $error = 'Konu içeriği boş olamaz.';
        } else {
            // Konuyu güncelle
            $result = edit_forum_topic($topic_id, $discord_user_id, $title, $content);
            
            if ($result) {
                // Güncelleme başarılı, konuya geri yönlendir
                header("Location: forum-topic.php?id={$topic_id}&edited=1");
                exit;
            } else {
                $error = 'Konu güncellenirken bir hata oluştu. Lütfen tekrar deneyin.';
            }
        }
    }
}

include 'includes/header.php';
?>

<!-- Konu Düzenleme Section -->
<section class="min-h-[80vh] pt-10 pb-16">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <div class="text-sm text-gray-500 mb-6 breadcrumb">
            <a href="forum.php" class="hover:text-primary">Forum</a>
            <span class="mx-2">/</span>
            <a href="forum-category.php?slug=<?php echo $topic['category_slug']; ?>" class="hover:text-primary">
                <?php echo htmlspecialchars($topic['category_name']); ?>
            </a>
            <span class="mx-2">/</span>
            <a href="forum-topic.php?id=<?php echo $topic_id; ?>" class="hover:text-primary truncate inline-block max-w-[200px] align-bottom">
                <?php echo htmlspecialchars($topic['title']); ?>
            </a>
            <span class="mx-2">/</span>
            <span class="text-gray-400">Düzenle</span>
        </div>
        
        <!-- Page Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-2 text-primary">
                Konu Düzenle
            </h1>
            <p class="text-gray-400 text-lg">
                Konu başlığını veya içeriğini güncelleyebilirsiniz.
            </p>
        </div>
        
        <!-- Konu Düzenleme Formu -->
        <div class="card p-6 shadow-lg max-w-4xl mx-auto">
            <?php if ($error): ?>
                <div class="bg-red-700 border border-red-600 text-white p-4 mb-6 rounded-lg text-sm">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-5">
                    <label for="title" class="block text-gray-300 mb-1.5 text-sm font-medium">Konu Başlığı</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                        value="<?php echo htmlspecialchars($topic['title']); ?>"
                        required
                        maxlength="200"
                    >
                </div>
                
                <div class="mb-6">
                    <label for="content" class="block text-gray-300 mb-1.5 text-sm font-medium">Konu İçeriği</label>
                    <!-- Quill Editor Container -->
                    <div id="content-editor" style="height: 300px;" class="bg-gray-900 border border-gray-700 rounded-lg mb-1"></div>
                    <textarea 
                        id="content" 
                        name="content" 
                        rows="10" 
                        class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    ><?php echo htmlspecialchars($topic['content']); ?></textarea>
                </div>
                
                <div class="flex justify-between items-center pt-4 border-t border-gray-700">
                    <a href="forum-topic.php?id=<?php echo $topic_id; ?>" class="text-gray-400 hover:text-white transition-colors text-sm font-medium">
                        <i class="fas fa-arrow-left mr-1.5"></i> İptal
                    </a>
                    <button type="submit" name="edit_topic" class="btn-primary px-6 py-2.5 rounded-lg text-sm font-medium">
                        <i class="fas fa-save mr-2"></i> Değişiklikleri Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
