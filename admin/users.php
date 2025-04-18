<?php
require_once 'includes/security_check.php'; // Güvenlik kontrolü
require_once 'includes/functions.php';
require_once '../includes/session.php';

// Yönetici yetkisi kontrolü - Sadece Developer ve Fellas rollerine sahip kullanıcılar erişebilir
requirePermission(2);

include 'includes/header.php';

// Discord veri ve rol bilgileri için açıklayıcı metin
$discord_role_info = "";
if (isset($_SESSION['discord_roles']) && is_array($_SESSION['discord_roles'])) {
    $roles = [];
    
    if (in_array('1353795720716746884', $_SESSION['discord_roles'])) {
        $roles[] = 'Fellas (Tam Yetki)';
    }
    
    if (in_array('1267751951307903017', $_SESSION['discord_roles'])) {
        $roles[] = 'Developer (Tam Yetki)';
    }
    
    if (in_array('1285694535766245408', $_SESSION['discord_roles'])) {
        $roles[] = 'Community (Kısıtlı Yetki)';
    }
    
    if (!empty($roles)) {
        $discord_role_info = 'Discord Rolleriniz: ' . implode(', ', $roles);
    }
}

// Yeni admin kullanıcısı ekleme
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_permission') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        
        if ($user_id === (int)$_SESSION['admin_id']) {
            $message = 'Kendi yetki seviyenizi değiştiremezsiniz.';
            $message_type = 'error';
        } else {
            // Discord entegrasyonu nedeniyle admin_users tablosu kullanılmıyor. Yetki Discord rolleri ile yönetilir.
            $message = 'Yetki seviyesi değişikliği Discord rolleri üzerinden yapılır.';
            $message_type = 'info';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'toggle_active') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        
        if ($user_id === (int)$_SESSION['admin_id']) {
            $message = 'Kendi hesabınızın durumunu değiştiremezsiniz.';
            $message_type = 'error';
        } else {
             // Discord entegrasyonu nedeniyle admin_users tablosu kullanılmıyor. Aktiflik durumu Discord rolleri ile yönetilir.
            $message = 'Kullanıcı aktiflik durumu Discord rolleri üzerinden yönetilir.';
            $message_type = 'info';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? '';
        $permission = (int)($_POST['permission'] ?? 1);

        // Discord entegrasyonu nedeniyle admin_users tablosu kullanılmıyor. Kullanıcı ekleme işlemi Discord üzerinden yapılır.
        $message = 'Admin kullanıcısı ekleme işlemi Discord üzerinden yapılır.';
        $message_type = 'info';

    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        
        if ($user_id === (int)$_SESSION['admin_id']) {
            $message = 'Kendinizi silemezsiniz.';
            $message_type = 'error';
        } else {
            // Discord entegrasyonu nedeniyle admin_users tablosu kullanılmıyor. Kullanıcı silme işlemi Discord üzerinden yapılır.
            $message = 'Admin kullanıcısı silme işlemi Discord üzerinden yapılır.';
            $message_type = 'info';
        }
    }
}

// Admin kullanıcıları Discord rolleri üzerinden listeleniyor. Veritabanı sorgusu kaldırıldı.
$users = []; // Eski $users değişkenini boş bir dizi olarak tanımla
try {
    // Veritabanı bağlantısı hala başka yerlerde gerekebilir diye bırakıldı.
    /** @var \PDO $conn */
    $conn = \Core\Database::getInstance()->getConnection();
} catch (PDOException $e) {
    $message = 'Veritabanı hatası: ' . $e->getMessage();
    $message_type = 'error';
    $users = [];
}
?>

<div class="container mx-auto">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-2">
        <h1 class="text-3xl font-bold text-text-light">Admin <span class="text-primary">Kullanıcıları</span></h1>
        <?php if (!empty($discord_role_info)): ?>
        <span class="bg-[#181A1B] text-blue-300 text-xs font-semibold px-3 py-2 rounded-lg border border-blue-500">
            <i class="fab fa-discord mr-1"></i> <?php echo $discord_role_info; ?>
        </span>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($message)): ?>
    <div class="mb-6 <?php echo $message_type === 'success' ? 'bg-[#002a00] border border-green-500 text-green-300' : 'bg-[#2a0000] border border-red-500 text-red-300'; ?> px-4 py-3 rounded-lg relative alert-dismissible" role="alert">
        <span class="block sm:inline"><?php echo $message; ?></span>
    </div>
    <?php endif; ?>
    
    <!-- Discord Rollere Sahip Kullanıcılar -->
    <div class="mt-10 mb-8">
        <h2 class="text-2xl font-bold text-text-light border-b border-primary-dark pb-2 mb-6">Discord <span class="text-primary">Rol Sahipleri</span></h2>
        
        <?php
        // Discord API'yi başlat
        $config = getDiscordConfig();
        if ($config['enabled'] && !empty($config['token'])) {
            $discord = new DiscordAPI(
                $config['token'],
                $config['guild_id'],
                $config['role_id']
            );
            
            // Discord rolleri ve ID'leri
            $roles = [
                [
                    'id' => '1353795720716746884',
                    'name' => 'Fellas',
                    'description' => 'Tam yetkili (Yönetici seviyesi)',
                    'color' => 'from-blue-600 to-blue-800'
                ],
                [
                    'id' => '1267751951307903017',
                    'name' => 'Developer',
                    'description' => 'Tam yetkili (Yönetici seviyesi)',
                    'color' => 'from-green-600 to-green-800'
                ],
                [
                    'id' => '1285694535766245408',
                    'name' => 'Community',
                    'description' => 'Kısıtlı yetkili (Yetkili seviyesi)',
                    'color' => 'from-purple-600 to-purple-800'
                ]
            ];
            
            // Her rol için kullanıcıları listele
            foreach ($roles as $role):
                // Role sahip kullanıcıları getir
                $members = $discord->getMembersWithRole($role['id'], 20);
        ?>
        
        <div class="card-bg rounded-lg shadow-lg overflow-hidden mb-6 border border-primary-dark">
            <div class="bg-gradient-to-r <?php echo $role['color']; ?> px-6 py-3">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <i class="fab fa-discord mr-2"></i> <?php echo $role['name']; ?> Rolü
                    <span class="ml-2 text-xs bg-white bg-opacity-20 px-2 py-1 rounded">
                        <?php echo $role['description']; ?>
                    </span>
                    <span class="ml-auto text-xs bg-white bg-opacity-20 px-2 py-1 rounded">
                        <?php echo count($members); ?> Kullanıcı
                    </span>
                </h3>
            </div>
            
            <?php if (empty($members)): ?>
            <div class="p-6 text-center">
                <p class="text-gray-400">Bu role sahip kullanıcı bulunmuyor.</p>
            </div>
            <?php else: ?>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($members as $member): ?>
                <div class="bg-[#181A1B] rounded-lg p-3 flex items-center">
                    <div class="flex-shrink-0 mr-3">
                        <?php if (!empty($member['avatar'])): ?>
                        <img src="https://cdn.discordapp.com/avatars/<?php echo $member['id']; ?>/<?php echo $member['avatar']; ?>.png" alt="Avatar" class="w-10 h-10 rounded-full">
                        <?php else: ?>
                        <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-white font-medium truncate"><?php echo htmlspecialchars($member['username']); ?></div>
                        <div class="text-xs text-gray-400 truncate">ID: <?php echo $member['id']; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
            endforeach;
        } else {
            echo '<div class="p-6 bg-[#2a0000] border border-red-500 text-red-300 rounded-lg mb-6"><i class="fas fa-exclamation-circle mr-2"></i> Discord API bağlantısı yapılandırılmamış. Discord ayarlarınızı kontrol edin.</div>';
        }
        ?>
    </div>
    
    <!-- Admin kullanıcı ekleme formu çıkarıldı - artık sadece Discord rolleri ile yetkilendirme yapılıyor -->
</div>

<?php include 'includes/footer.php'; ?>
