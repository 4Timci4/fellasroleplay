<?php
/**
 * Forum Yönetim Paneli
 * 
 * Bu dosya, forum yönetim panelinin ana giriş noktasıdır. Tüm sekmeleri ve işlevleri yönetir.
 */

// Hata ayıklama modunu etkinleştir
include_once 'debug_error.php';

// Session fonksiyonlarını dahil et
include_once 'includes/session.php';

// Forum fonksiyonlarını dahil et - önce bunları dahil etmemiz gerekiyor
include_once 'includes/forum-functions.php';
include_once 'includes/helpers/forum_helpers.php';

// Oturum kontrolü - session.php dahil edildikten sonra
include_once 'includes/auth_check.php';

// Admin yetkilendirme için gerekli session değişkenlerini tanımla
$discord_user_id = isset($_SESSION['discord_user_id']) ? $_SESSION['discord_user_id'] : null;
if ($discord_user_id && function_exists('is_forum_admin') && is_forum_admin($discord_user_id)) {
    $_SESSION['admin_id'] = $discord_user_id;
    $_SESSION['admin_permission'] = 'forum_admin';
}

// Admin güvenlik kontrolü - yolu düzeltiyoruz
include_once 'admin/includes/security_check.php';

// Bakım modu kontrolü - Burada display_maintenance_page çağrılmıyor
// çünkü admin sayfasında bakım modunu açıp kapatabilmek istiyoruz
include_once 'includes/maintenance.php';

// Admin fonksiyonlarını dahil et - yolları düzeltiyoruz
include_once 'admin/functions/category-functions.php';
include_once 'admin/functions/topic-functions.php';
include_once 'admin/functions/user-functions.php';
include_once 'admin/functions/settings-functions.php';

// Admin kontrolü
$discord_user_id = isset($_SESSION['discord_user_id']) ? $_SESSION['discord_user_id'] : null;
if (!$discord_user_id || !is_forum_admin($discord_user_id)) {
    // Kullanıcı admin değilse, foruma yönlendir
    header('Location: forum.php');
    exit;
}

// Varsayılan aktif sekme
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Form işlemleri
$message = '';
$message_type = 'success';

// Form işlemlerini işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Bakım modu işlemleri
if (isset($_POST['enable_maintenance'])) {
    // Bakım modunu aktifleştir
    if (enable_maintenance_mode()) {
        // Bakım modu ayarlarını kaydet
        $allow_staff_access = isset($_POST['allow_staff_access']) ? true : false;
        update_maintenance_settings(['allow_staff_access' => $allow_staff_access]);
        
        $message = 'Bakım modu başarıyla aktifleştirildi.';
    } else {
        $message = 'Bakım modu aktifleştirilirken bir hata oluştu.';
        $message_type = 'error';
    }
} elseif (isset($_POST['disable_maintenance'])) {
    // Bakım modunu devre dışı bırak
    if (disable_maintenance_mode()) {
        $message = 'Bakım modu başarıyla devre dışı bırakıldı.';
    } else {
        $message = 'Bakım modu devre dışı bırakılırken bir hata oluştu.';
        $message_type = 'error';
    }
} elseif (isset($_POST['update_maintenance_settings'])) {
    // Bakım modu ayarlarını güncelle
    $allow_staff_access = isset($_POST['allow_staff_access']) ? true : false;
    
    if (update_maintenance_settings(['allow_staff_access' => $allow_staff_access])) {
        $message = 'Bakım modu ayarları başarıyla güncellendi.';
    } else {
        $message = 'Bakım modu ayarları güncellenirken bir hata oluştu.';
        $message_type = 'error';
    }
    } elseif (isset($_POST['add_category'])) {
        // Kategori verilerini topla
        $category_data = [
            'parent_id' => isset($_POST['parent_id']) ? $_POST['parent_id'] : null,
            'name' => isset($_POST['name']) ? $_POST['name'] : '',
            'slug' => isset($_POST['slug']) ? $_POST['slug'] : '',
            'description' => isset($_POST['description']) ? $_POST['description'] : '',
            'icon' => isset($_POST['icon']) ? $_POST['icon'] : 'fa-folder',
            'icon_color' => isset($_POST['icon_color']) ? $_POST['icon_color'] : '#747F8D',
            'display_order' => isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0
        ];
        
        // Kategori ekle
        $result = add_forum_category($category_data);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    } elseif (isset($_POST['update_category'])) {
        // Kategori verilerini topla
        $category_data = [
            'category_id' => isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0,
            'parent_id' => isset($_POST['parent_id']) ? $_POST['parent_id'] : null,
            'name' => isset($_POST['name']) ? $_POST['name'] : '',
            'slug' => isset($_POST['slug']) ? $_POST['slug'] : '',
            'description' => isset($_POST['description']) ? $_POST['description'] : '',
            'icon' => isset($_POST['icon']) ? $_POST['icon'] : 'fa-folder',
            'icon_color' => isset($_POST['icon_color']) ? $_POST['icon_color'] : '#747F8D',
            'display_order' => isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0
        ];
        
        // Kategori güncelle
        $result = update_forum_category($category_data);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    } elseif (isset($_POST['delete_category'])) {
        // Kategori sil
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $result = delete_forum_category($category_id);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    } elseif (isset($_POST['update_topic_status'])) {
        // Konu durumunu güncelle
        $topic_id = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : 0;
        $status = isset($_POST['topic_status']) ? $_POST['topic_status'] : 'normal';
        
        if (update_topic_status($topic_id, $status)) {
            $message = 'Konu durumu başarıyla güncellendi.';
        } else {
            $message = 'Konu durumu güncellenirken bir hata oluştu.';
            $message_type = 'error';
        }
    } elseif (isset($_POST['delete_topic'])) {
        // Konu sil (veya deleted olarak işaretle)
        $topic_id = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : 0;
        
        if (update_topic_status($topic_id, 'deleted')) {
            $message = 'Konu başarıyla silindi.';
        } else {
            $message = 'Konu silinirken bir hata oluştu.';
            $message_type = 'error';
        }
    } elseif (isset($_POST['update_user_role'])) {
        // Kullanıcı rolünü güncelle
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
        $role = isset($_POST['user_role']) ? $_POST['user_role'] : 'user';
        
        if (\Services\UserService::getInstance()->updateUserRole($user_id, $role)) {
            $message = 'Kullanıcı rolü başarıyla güncellendi.';
        } else {
            $message = 'Kullanıcı rolü güncellenirken bir hata oluştu.';
            $message_type = 'error';
        }
    } elseif (isset($_POST['action_type'])) {
        // Kullanıcı eylemi (ban/unban/delete)
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
        $action_type = $_POST['action_type'];
        
        if ($action_type === 'ban') {
            // Kullanıcıyı yasakla
            if (\Services\UserService::getInstance()->updateUserBanStatus($user_id, true)) {
                $message = 'Kullanıcı başarıyla yasaklandı.';
            } else {
                $message = 'Kullanıcı yasaklanırken bir hata oluştu.';
                $message_type = 'error';
            }
        } elseif ($action_type === 'unban') {
            // Kullanıcı yasağını kaldır
            if (\Services\UserService::getInstance()->updateUserBanStatus($user_id, false)) {
                $message = 'Kullanıcı yasağı başarıyla kaldırıldı.';
            } else {
                $message = 'Kullanıcı yasağı kaldırılırken bir hata oluştu.';
                $message_type = 'error';
            }
        } elseif ($action_type === 'delete') {
            // Kullanıcıyı sil (Bu işlev örnek amaçlıdır. Gerçek uygulamada daha dikkatli olunmalı)
            $message = 'Kullanıcı silme işlevi sadece örnek olarak eklenmiştir ve şu anda aktif değildir.';
            $message_type = 'error';
        }
    }
}

// Veri Hazırlama
// -----------------------------

// Bakım modunun mevcut durumunu kontrol et
$maintenance_active = is_maintenance_mode();

// Forum istatistiklerini al
$stats = get_forum_statistics();

// Tüm kategorileri al
$categories = get_forum_categories();

// Son konular ve yorumlar
$latest_topics = get_latest_topics(5);
$latest_comments = get_latest_comments(5);

// Tab-specific data
if ($active_tab === 'topics') {
    // Konu sayfası için verileri hazırla
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    
    $topics_data = get_admin_forum_topics($page, 10, $search, $category_id);
} elseif ($active_tab === 'users') {
    // Kullanıcı sayfası için verileri hazırla
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    
    $users_data = \Services\UserService::getInstance()->getUsers($page, 10, $search);
} elseif ($active_tab === 'settings') {
    // Ayarlar sayfası için verileri hazırla
    $maintenance_settings = get_maintenance_settings();
    $allow_staff_access = isset($maintenance_settings['allow_staff_access']) ? $maintenance_settings['allow_staff_access'] : false;
    // Bu değişken admin/views/settings.php dosyasında kullanılıyor, bu yüzden tanımlıyoruz
    $maintenance_active = is_maintenance_mode();
}

// Header dosyasını dahil et
include 'includes/header.php';
?>

<!-- Forum Admin Section -->
<section class="min-h-[80vh] pt-10 pb-16">
    <div class="container mx-auto px-4">
        <!-- Page Header -->
        <div class="relative text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-primary">
                Forum Yönetim Paneli
            </h1>
            
            <!-- Dekoratif ayırıcı -->
            <div class="h-1 w-24 bg-primary mx-auto mb-6"></div>
            
            <p class="text-lg text-gray-400 max-w-3xl mx-auto">
                Forum kategorilerini, konuları, yorumları ve kullanıcıları yönetin. Forum ayarlarını özelleştirebilir ve bakım modunu kontrol edebilirsiniz.
            </p>
        </div>
        
        <!-- Tabs ve İçerik -->
        <div class="mt-8">
            <!-- Nav Tabs -->
            <div class="border-b border-gray-700 flex overflow-x-auto pb-px mb-6">
                <a href="?tab=dashboard" class="px-5 py-3 font-medium text-sm leading-5 rounded-t-lg <?php echo $active_tab === 'dashboard' ? 'text-primary border-b-2 border-primary' : 'text-gray-400 hover:text-white'; ?> mr-2">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="?tab=categories" class="px-5 py-3 font-medium text-sm leading-5 rounded-t-lg <?php echo $active_tab === 'categories' ? 'text-primary border-b-2 border-primary' : 'text-gray-400 hover:text-white'; ?> mr-2">
                    <i class="fas fa-folder mr-2"></i> Kategoriler
                </a>
                <a href="?tab=topics" class="px-5 py-3 font-medium text-sm leading-5 rounded-t-lg <?php echo $active_tab === 'topics' ? 'text-primary border-b-2 border-primary' : 'text-gray-400 hover:text-white'; ?> mr-2">
                    <i class="fas fa-comments mr-2"></i> Konular
                </a>
                <a href="?tab=users" class="px-5 py-3 font-medium text-sm leading-5 rounded-t-lg <?php echo $active_tab === 'users' ? 'text-primary border-b-2 border-primary' : 'text-gray-400 hover:text-white'; ?> mr-2">
                    <i class="fas fa-users mr-2"></i> Kullanıcılar
                </a>
                <a href="?tab=settings" class="px-5 py-3 font-medium text-sm leading-5 rounded-t-lg <?php echo $active_tab === 'settings' ? 'text-primary border-b-2 border-primary' : 'text-gray-400 hover:text-white'; ?>">
                    <i class="fas fa-cogs mr-2"></i> Ayarlar
                </a>
            </div>
            
            <!-- Mesaj Alanı -->
            <?php if ($message): ?>
                <div class="bg-<?php echo $message_type === 'success' ? 'blue-800' : 'red-700'; ?> text-white p-4 mb-6 rounded-lg flex items-center">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> text-xl mr-3"></i>
                    <span><?php echo $message; ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Tab İçerik -->
            <div class="tab-content">
                <?php 
                // Aktif sekmeye göre ilgili görünümü dahil et
                switch ($active_tab) {
                    case 'dashboard':
                        include 'admin/views/dashboard.php';
                        break;
                    case 'categories':
                        include 'admin/views/categories.php';
                        break;
                    case 'topics':
                        include 'admin/views/topics.php';
                        break;
                    case 'users':
                        include 'admin/views/users.php';
                        break;
                    case 'settings':
                        include 'admin/views/settings.php';
                        break;
                    default:
                        include 'admin/views/dashboard.php';
                }
                ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
