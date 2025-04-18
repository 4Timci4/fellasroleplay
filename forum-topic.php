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

// URL'den konu ID'sini al
$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Konuyu getir
$topic = get_forum_topic($topic_id);

// Eğer konu bulunamazsa, forum ana sayfasına yönlendir
if (!$topic) {
    header('Location: forum.php');
    exit;
}

// Sayfa numarasını al
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Görüntüleme sayısını artır
increment_topic_views($topic_id);

// Konuya ait yorumları getir
$comments_data = get_forum_comments($topic_id, $page, 20);
$comments = $comments_data['comments'];
$pagination = $comments_data['pagination'];

// Yorum ekleme işlemi
$comment_error = '';
$comment_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $content = trim($_POST['content'] ?? '');
    
    if (empty($content)) {
        $comment_error = 'Yorum içeriği boş olamaz.';
    } else {
        $discord_user_id = get_discord_user_id();
        
        if ($discord_user_id) {
            $result = add_forum_comment($topic_id, $discord_user_id, $content);
            
            if ($result) {
                $comment_success = 'Yorumunuz başarıyla eklendi.';
                // Yorumu ekledikten sonra sayfayı yenile
                header("Location: forum-topic.php?id={$topic_id}&success=1");
                exit;
            } else {
                $comment_error = 'Yorum eklenirken bir hata oluştu. Lütfen tekrar deneyin.';
            }
        } else {
            $comment_error = 'Yorum yapabilmek için giriş yapmalısınız.';
        }
    }
}

// Başarı mesajını kontrol et
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $comment_success = 'Yorumunuz başarıyla eklendi.';
}

include 'includes/header.php';
?>

<!-- Forum Konu Section -->
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
            <span class="text-gray-400 truncate inline-block max-w-[250px] align-bottom">
                <?php echo htmlspecialchars($topic['title']); ?>
            </span>
        </div>
        
        <!-- Konu Detayları -->
        <div class="card mb-8 overflow-hidden">
            <div class="bg-gray-800 py-5 px-6 border-b border-gray-700">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold text-primary mb-3">
                            <?php echo htmlspecialchars($topic['title']); ?>
                        </h1>
                        <div class="flex flex-wrap items-center text-sm text-gray-400 gap-x-5 gap-y-1">
                            <div class="flex items-center">
                                <i class="far fa-user mr-1.5"></i>
                                <?php echo htmlspecialchars($topic['creator_username'] ?? 'Bilinmeyen Kullanıcı'); ?>
                            </div>
                            <div class="flex items-center">
                                <i class="far fa-calendar-alt mr-1.5"></i>
                                <?php echo date('d.m.Y H:i', strtotime($topic['created_at'])); ?>
                            </div>
                            <div class="flex items-center">
                                <i class="far fa-eye mr-1.5"></i>
                                <?php echo $topic['views']; ?> görüntülenme
                            </div>
                            <div class="flex items-center">
                                <i class="far fa-comments mr-1.5"></i>
                                <?php echo $pagination['total_comments']; ?> yorum
                            </div>
                        </div>
                    </div>
                    
                    <?php 
                    // Konu sahibi ise düzenleme/silme butonlarını göster
                    if (is_logged_in() && is_topic_owner($topic_id, get_discord_user_id())): 
                    ?>
                    <div class="flex-shrink-0 mt-1 space-x-2">
                        <a href="edit-topic.php?id=<?php echo $topic_id; ?>" class="btn-secondary text-sm inline-flex items-center px-3 py-1.5 rounded hover:bg-blue-600 transition-colors">
                            <i class="fas fa-edit mr-1.5"></i> Düzenle
                        </a>
                        <a href="delete-topic.php?id=<?php echo $topic_id; ?>" class="bg-red-600 hover:bg-red-700 text-white text-sm inline-flex items-center px-3 py-1.5 rounded transition-colors">
                            <i class="fas fa-trash mr-1.5"></i> Sil
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="flex flex-wrap md:flex-nowrap">
                <!-- Yazar Bilgisi (Sidebar) -->
                <div class="w-full md:w-56 bg-gray-800 p-5 border-b md:border-b-0 md:border-r border-gray-700 flex-shrink-0">
                    <div class="flex flex-col items-center text-center">
                        <?php
                        // Kullanıcı adının ilk harfi
                        $firstLetter = !empty($topic['creator_username']) ? strtoupper(substr($topic['creator_username'], 0, 1)) : '?';
                        
                        // Kullanıcı ID'sine göre renk seç
                        $colors = ['#747F8D', '#43B581', '#FAA61A', '#F04747', '#7289DA'];
                        $colorIndex = isset($topic['discord_user_id']) ? (intval(substr($topic['discord_user_id'], -1)) % 5) : 0;
                        $bgColor = $colors[$colorIndex];
                        ?>
                        
                        <div style="background-color: <?php echo $bgColor; ?>;" 
                             class="w-24 h-24 rounded-full flex items-center justify-center mb-4 text-white font-bold text-4xl shadow-md">
                            <?php echo $firstLetter; ?>
                        </div>
                        
                        <div class="font-semibold text-lg text-white mb-2">
                            <?php echo htmlspecialchars($topic['creator_username'] ?? 'Bilinmeyen Kullanıcı'); ?>
                        </div>
                        
                        <div class="text-xs text-gray-400 space-y-1">
                            <div>Konu Sayısı: <?php // Kullanıcının konu sayısını getir
                                $conn = getDbConnection();
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM forum_topics WHERE discord_user_id = ? AND status != 'deleted'");
                                $stmt->execute([$topic['discord_user_id']]);
                                echo $stmt->fetchColumn();
                            ?></div>
                            <div>Yorum Sayısı: <?php // Kullanıcının yorum sayısını getir
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM forum_comments WHERE discord_user_id = ?");
                                $stmt->execute([$topic['discord_user_id']]);
                                echo $stmt->fetchColumn();
                            ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Ana İçerik -->
                <div class="flex-1 min-w-0 p-6">
                    <div class="prose prose-invert max-w-none prose-p:text-gray-300 prose-headings:text-primary">
                        <?php echo format_forum_text($topic['content']); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Yorumlar Başlığı -->
        <div class="flex items-center justify-between mb-6 border-b border-gray-700 pb-3">
            <h2 class="text-2xl font-semibold text-primary">
                Yorumlar (<?php echo $pagination['total_comments']; ?>)
            </h2>
            
            <a href="#add-comment" class="btn-primary inline-flex items-center px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-reply mr-2"></i> Yorum Yap
            </a>
        </div>
        
        <!-- Yorumlar Listesi -->
        <div class="space-y-5 mb-8">
            <?php if (count($comments) > 0): ?>
                <?php foreach ($comments as $index => $comment): ?>
                    <?php
                        // Alternating background color for comments
                        $comment_bg_class = $index % 2 == 0 ? 'bg-gray-850' : 'bg-gray-800';
                        if (!class_exists('TailwindColor')) {
                            echo "<style>.bg-gray-850 { background-color: #1f2937e6; }</style>";
                            class TailwindColor {}
                        }
                    ?>
                    <div class="card overflow-hidden <?php echo $comment_bg_class; ?>" id="comment-<?php echo $comment['id']; ?>">
                        <div class="flex flex-wrap md:flex-nowrap">
                            <!-- Yazar Bilgisi (Comment Sidebar) -->
                            <div class="w-full md:w-48 p-4 border-b md:border-b-0 md:border-r border-gray-700 flex-shrink-0">
                                <div class="flex flex-col items-center text-center">
                                    <?php
                                    // Kullanıcı adının ilk harfi
                                    $commentFirstLetter = !empty($comment['creator_username']) ? strtoupper(substr($comment['creator_username'], 0, 1)) : '?';
                                    
                                    // Kullanıcı ID'sine göre renk seç
                                    $commentColorIndex = isset($comment['discord_user_id']) ? (intval(substr($comment['discord_user_id'], -1)) % 5) : 0;
                                    $commentBgColor = $colors[$commentColorIndex];
                                    ?>
                                    
                                    <div style="background-color: <?php echo $commentBgColor; ?>;" 
                                         class="w-16 h-16 rounded-full flex items-center justify-center mb-3 text-white font-bold text-2xl shadow-sm">
                                        <?php echo $commentFirstLetter; ?>
                                    </div>
                                    
                                    <div class="font-semibold text-md text-white mb-1">
                                        <?php echo htmlspecialchars($comment['creator_username'] ?? 'Bilinmeyen Kullanıcı'); ?>
                                    </div>
                                    
                                    <div class="text-xs text-gray-400">
                                        <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Yorum Metni ve İşlemler -->
                            <div class="flex-1 min-w-0 p-4 pl-5">
                                <!-- Yorum İşlemleri (Düzenle/Sil) -->
                                <div class="flex justify-end items-center mb-2 text-xs">
                                    <?php
                                    // Yorum sahibi ise düzenleme/silme butonlarını göster
                                    if (is_logged_in() && is_comment_owner($comment['id'], get_discord_user_id())):
                                    ?>
                                    <div class="flex space-x-2">
                                        <a href="edit-comment.php?id=<?php echo $comment['id']; ?>" class="text-blue-400 hover:text-blue-300 transition-colors inline-flex items-center">
                                            <i class="fas fa-edit mr-1"></i> Düzenle
                                        </a>
                                        <a href="delete-comment.php?id=<?php echo $comment['id']; ?>" class="text-red-500 hover:text-red-400 transition-colors inline-flex items-center">
                                            <i class="fas fa-trash mr-1"></i> Sil
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    <span class="ml-auto text-gray-500">#<?php echo $comment['id']; ?></span>
                                </div>
                                
                                <!-- Yorum İçeriği -->
                                <div class="prose prose-sm prose-invert max-w-none prose-p:text-gray-300">
                                    <?php echo format_forum_text($comment['content']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="py-12 text-center text-gray-400 card items-center justify-center flex flex-col">
                    <i class="far fa-comment-dots text-5xl mb-4 text-gray-500"></i>
                    <p class="text-lg">Bu konuda henüz yorum bulunmamaktadır.</p>
                    <p class="mt-2 text-gray-500">İlk yorumu siz yapın!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sayfalama -->
        <?php echo get_pagination_links($pagination, "forum-topic.php?id={$topic_id}"); ?>
        
        <!-- Yorum Ekleme Formu -->
        <div class="card p-6 mt-10" id="add-comment">
            <h3 class="text-xl font-semibold mb-5 text-primary border-b border-gray-700 pb-3">Yorum Ekle</h3>
            
            <?php if (is_logged_in()): ?>
                <?php if ($comment_success): ?>
                    <div class="bg-green-700 border border-green-600 text-white p-4 mb-4 rounded-lg text-sm">
                        <?php echo $comment_success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($comment_error): ?>
                    <div class="bg-red-700 border border-red-600 text-white p-4 mb-4 rounded-lg text-sm">
                        <?php echo $comment_error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="#add-comment">
                    <div class="mb-4">
                        <label for="content" class="block text-gray-300 mb-2 text-sm font-medium">Yorumunuz</label>
                        <!-- Quill Editor Container -->
                        <div id="content-editor" style="height: 200px;" class="bg-gray-900 border border-gray-700 rounded-lg mb-1"></div>
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="6" 
                            class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        ></textarea>
                    </div>
                    
                    <div class="text-right mt-5">
                        <button type="submit" name="add_comment" class="btn-primary px-6 py-2.5 rounded-lg text-sm font-medium">
                            <i class="fas fa-paper-plane mr-2"></i> Yorum Gönder
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="bg-gray-800 text-center p-6 rounded-lg">
                    <p class="text-gray-300 mb-4">Yorum yapabilmek için giriş yapmalısınız.</p>
                    <a href="login.php" class="btn-primary inline-flex items-center px-6 py-3 rounded-lg">
                        <i class="fab fa-discord mr-2"></i> Discord ile Giriş Yap
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Sayfa içi bağlantılar için pürüzsüz kaydırma özelliği -->
<style>
    html {
        scroll-behavior: smooth;
    }
</style>

<?php include 'includes/footer.php'; ?>
