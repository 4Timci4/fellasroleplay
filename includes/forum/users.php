<?php
/**
 * Forum kullanıcı işlemleri için gerekli fonksiyonlar
 */

// Gerekli sınıfları ve yardımcı fonksiyonları içeren bootstrapper'ı kontrol et
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../bootstrap.php';
}

/**
 * Discord kullanıcısının forum kullanıcı kaydını kontrol eder ve yoksa oluşturur
 * 
 * @param string $discord_user_id Discord kullanıcı ID
 * @return bool İşlem başarılı mı
 */
function ensure_forum_user_exists($discord_user_id) {
    if (empty($discord_user_id)) {
        return false;
    }
    
    // Varsayılan olarak oturum bilgilerini kullan
    if (isset($_SESSION['discord_username']) && isset($_SESSION['discord_user_id'])) {
        $discord_user = [
            'id' => $_SESSION['discord_user_id'],
            'username' => $_SESSION['discord_username'],
            'avatar' => $_SESSION['discord_avatar'] ?? null
        ];
    } else {
        // Oturum bilgileri yoksa, discord_user_id'yi kullanarak bir kullanıcı oluştur
        $discord_user = [
            'id' => $discord_user_id,
            'username' => 'Kullanıcı_' . substr($discord_user_id, 0, 6),
            'avatar' => null
        ];
    }
    
    // Discord API erişilebilir ve tanımlı ise, ek bilgileri getir
    global $discord_api;
    if (isset($discord_api) && $discord_api !== null) {
        try {
            $api_discord_user = $discord_api->getUserInfo($discord_user_id);
            if ($api_discord_user) {
                // API'den gelen bilgilerle varsayılan bilgileri güncelle
                $discord_user = $api_discord_user;
            }
        } catch (Exception $e) {
            // API hatası durumunda sessizce devam et, varsayılan bilgileri kullan
            error_log("Discord API Hatası: " . $e->getMessage());
        }
    }
    
    $conn = \Core\Database::getInstance()->getConnection();
    
    // Kullanıcı var mı kontrol et
    $stmt = $conn->prepare("SELECT * FROM forum_users WHERE discord_id = ?");
    $stmt->execute([$discord_user_id]);
    
    if ($stmt->rowCount() > 0) {
        // Kullanıcıyı güncelle
        $update = $conn->prepare("UPDATE forum_users SET 
            username = ?, 
            avatar = ?, 
            last_login = NOW() 
            WHERE discord_id = ?");
        return $update->execute([
            $discord_user['username'],
            $discord_user['avatar'] ?? null,
            $discord_user_id
        ]);
    } else {
        // Yeni kullanıcı ekle
        $insert = $conn->prepare("INSERT INTO forum_users 
            (discord_id, username, avatar) 
            VALUES (?, ?, ?)");
        return $insert->execute([
            $discord_user_id,
            $discord_user['username'],
            $discord_user['avatar'] ?? null
        ]);
    }
}

/**
 * Kullanıcının forum admin olup olmadığını kontrol eder
 * 
 * @param string $discord_user_id Discord kullanıcı ID
 * @return bool Kullanıcı admin ise true, değilse false
 */
function is_forum_admin($discord_user_id = null) {
    // Discord ID sağlanmamışsa oturumdaki ID'yi kullan
    if (!$discord_user_id && isset($_SESSION['discord_user_id'])) {
        $discord_user_id = $_SESSION['discord_user_id'];
    }
    
    // Discord ID boş ise false döndür
    if (empty($discord_user_id)) {
        return false;
    }
    
    // Admin discord ID'leri
    $admin_ids = [
        '1353795720716746884', // Örnek ID
    ];
    
    // Admin Discord rollerini kontrol et
    $admin_roles = [
        '1353795720716746884', // Fellas
        '1285694535766245408', // Community
        '1267751951307903017'  // Developer
    ];
    
    // Admin ID'si direkt listede mi?
    if (in_array($discord_user_id, $admin_ids)) {
        return true;
    }
    
    // Kullanıcı rollerini kontrol et (Discord API bağlantısı gerekli)
    if (isset($_SESSION['discord_roles']) && is_array($_SESSION['discord_roles'])) {
        foreach ($_SESSION['discord_roles'] as $role_id) {
            if (in_array($role_id, $admin_roles)) {
                return true;
            }
        }
    }
    
    // Özel durum: Geliştirme ortamında admin olarak kabul et
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE === true) {
        return true;
    }
    
    return false;
}

/**
 * Discord kullanıcı avatarını formatlar
 * 
 * @param string $avatar_hash Avatar hash kodu
 * @param string $user_id Discord kullanıcı ID
 * @param int $size Avatar boyutu (piksel)
 * @return string Avatar URL'si veya varsayılan avatar için data URI
 */
/**
 * Kullanıcının forum yetkilisi (moderatör/yetkili) olup olmadığını kontrol eder
 * 
 * @param string $discord_user_id Discord kullanıcı ID
 * @return bool Kullanıcı yetkili ise true, değilse false
 */
function is_forum_staff($discord_user_id = null) {
    // Discord ID sağlanmamışsa oturumdaki ID'yi kullan
    if (!$discord_user_id && isset($_SESSION['discord_user_id'])) {
        $discord_user_id = $_SESSION['discord_user_id'];
    }
    
    // Discord ID boş ise false döndür
    if (empty($discord_user_id)) {
        return false;
    }
    
    // Admin zaten yetkili olarak kabul edilir
    if (is_forum_admin($discord_user_id)) {
        return true;
    }
    
    // Yetkili Discord rolleri
    $staff_roles = [
        '1353795720716746844', // Moderatör
        '1285694535766245418', // Yetkili
        '1267751951307903037'  // Support
    ];
    
    // Kullanıcı rollerini kontrol et (Discord API bağlantısı gerekli)
    if (isset($_SESSION['discord_roles']) && is_array($_SESSION['discord_roles'])) {
        foreach ($_SESSION['discord_roles'] as $role_id) {
            if (in_array($role_id, $staff_roles)) {
                return true;
            }
        }
    }
    
    return false;
}

function get_discord_avatar_url($avatar_hash, $user_id, $size = 128) {
    // Varsayılan avatar renkleri
    $colors = ['#747F8D', '#43B581', '#FAA61A', '#F04747', '#7289DA'];
    
    // Avatarı var mı kontrol et
    if (!empty($avatar_hash)) {
        // Discord avatar hash'i varsa, Discord CDN'den gerçek avatarı al
        $extension = strpos($avatar_hash, 'a_') === 0 ? 'gif' : 'png';
        return "https://cdn.discordapp.com/avatars/{$user_id}/{$avatar_hash}.{$extension}?size={$size}";
    }
    
    // Sayısal ID'den son rakamı al, yoksa 0 olsun
    $color_index = isset($user_id) ? (intval(substr($user_id, -1)) % 5) : 0;
    $color = $colors[$color_index];
    
    // İlk harfi hesapla (kullanıcı adından)
    $first_letter = isset($_SESSION['discord_username']) ? strtoupper(substr($_SESSION['discord_username'], 0, 1)) : '?';
    
    // Kullanıcı adı yoksa varsayılan Discord avatarlarını kullan
    if ($first_letter == '?') {
        $discriminator = isset($user_id) ? (intval($user_id) % 5) : 0;
        return "https://cdn.discordapp.com/embed/avatars/{$discriminator}.png?size={$size}";
    }
    
    // SVG formatında veri URI kullanarak avatar oluştur
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="' . $color . '"/><text x="50" y="65" font-size="40" font-family="Arial, sans-serif" fill="white" text-anchor="middle">' . $first_letter . '</text></svg>';
    
    // Data URI şeklinde dön
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
