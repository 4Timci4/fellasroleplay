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

// URL'den yorum ID'sini al
$comment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Yorumu getir
$conn = getDbConnection();
$stmt = $conn->prepare("
    SELECT c.*, t.id as topic_id, t.title as topic_title, t.category_id
    FROM forum_comments c
    JOIN forum_topics t ON c.topic_id = t.id
    WHERE c.id = ?
");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

// Kategori bilgilerini al
$category = null;
if ($comment) {
    $stmt = $conn->prepare("SELECT * FROM forum_categories WHERE id = ?");
    $stmt->execute([$comment['category_id']]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Eğer yorum bulunamazsa veya kullanıcı yorum sahibi değilse, forum ana sayfasına yönlendir
if (!$comment || !is_comment_owner($comment_id, $discord_user_id) || !$category) {
    header('Location: forum.php');
    exit;
}

$error = '';
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] == 1;

// Silme işlemi onaylandıysa
if ($confirmed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen tekrar deneyin.';
    } else {
        // Yorumu sil
        $result = delete_forum_comment($comment_id, $discord_user_id);
        
        if ($result) {
            // Silme başarılı, konuya geri yönlendir
            header("Location: forum-topic.php?id={$comment['topic_id']}&comment_deleted=1");
            exit;
        } else {
            $error = 'Yorum silinirken bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}

include 'includes/header.php';
?>

<!-- Yorum Silme Section -->
<section class="min-h-[80vh] pt-16 pb-16">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <div class="text-sm text-gray-400 mb-6">
            <a href="forum.php" class="hover:text-white">Forum</a>
            <span class="mx-2">&gt;</span>
            <a href="forum-category.php?slug=<?php echo $category['slug']; ?>" class="hover:text-white">
                <?php echo htmlspecialchars($category['name']); ?>
            </a>
            <span class="mx-2">&gt;</span>
            <a href="forum-topic.php?id=<?php echo $comment['topic_id']; ?>" class="hover:text-white truncate inline-block max-w-[200px] align-bottom">
                <?php echo htmlspecialchars($comment['topic_title']); ?>
            </a>
            <span class="mx-2">&gt;</span>
            <span class="text-white">Yorumu Sil</span>
        </div>
        
        <!-- Page Header -->
        <div class="card mb-8">
            <div class="card-header bg-gray-800 py-4 px-6 border-b border-gray-700">
                <h1 class="text-2xl md:text-3xl font-bold text-primary">
                    Yorumu Sil
                </h1>
            </div>
            
            <div class="card-body p-6">
                <?php if ($error): ?>
                    <div class="bg-red-800 text-white p-4 mb-4 rounded">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$confirmed): ?>
                    <div class="bg-gray-800 p-6 rounded-lg mb-6">
                        <h2 class="text-xl font-bold mb-4 text-yellow-400">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Uyarı
                        </h2>
                        <p class="text-gray-300 mb-4">
                            Yorumunuzu silmek istediğinizden emin misiniz?
                        </p>
                        <div class="bg-gray-900 p-4 rounded mb-6">
                            <p class="text-gray-400 italic">
                                "<?php echo htmlspecialchars(substr($comment['content'], 0, 150) . (strlen($comment['content']) > 150 ? '...' : '')); ?>"
                            </p>
                        </div>
                        <p class="text-gray-300 mb-6">
                            Bu işlem geri alınamaz.
                        </p>
                        
                        <div class="flex justify-between">
                            <a href="forum-topic.php?id=<?php echo $comment['topic_id']; ?>#comment-<?php echo $comment_id; ?>" class="btn-secondary px-6 py-3 rounded-lg">
                                İptal
                            </a>
                            <a href="delete-comment.php?id=<?php echo $comment_id; ?>&confirm=1" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                                <i class="fas fa-trash mr-2"></i> Yorumu Sil
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="post" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="bg-red-900 text-white p-6 rounded-lg mb-6">
                            <p class="font-bold mb-4">Son Onay</p>
                            <p class="mb-6">
                                Bu işlem geri alınamaz. Yorumu silmek istediğinizden emin misiniz?
                            </p>
                            
                            <div class="flex justify-between">
                                <a href="forum-topic.php?id=<?php echo $comment['topic_id']; ?>#comment-<?php echo $comment_id; ?>" class="btn-secondary px-6 py-3 rounded-lg">
                                    İptal
                                </a>
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                                    <i class="fas fa-trash mr-2"></i> Kalıcı Olarak Sil
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
