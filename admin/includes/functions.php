<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'config.php';
require_once 'DiscordAPI.php';

// DbHelper sınıfını kullanabilmek için
use Core\Database;

// Discord rollerini güncelleyen fonksiyon
function refreshDiscordRoles() {
    // isLoggedIn() yerine direkt discord_user_id kontrolü (döngüsel bağımlılığı önlemek için)
    if (empty($_SESSION['discord_user_id'])) {
        return false;
    }
    
    $config = getDiscordConfig();
    
    if (!$config['enabled'] || empty($config['token'])) {
        return false;
    }
    
    $discordId = $_SESSION['discord_user_id'];
    
    $discord = new DiscordAPI(
        $config['token'],
        $config['guild_id'],
        $config['role_id']
    );
    
    if (!$discord->checkUserInGuild($discordId)) {
        return false;
    }
    
    $url = "https://discord.com/api/v10/guilds/{$config['guild_id']}/members/{$discordId}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bot ' . $config['token']
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $member_response = curl_exec($ch);
    $member_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($member_status !== 200) {
        return false;
    }
    
    $member_data = json_decode($member_response, true);
    
    if (isset($member_data['roles']) && is_array($member_data['roles'])) {
        $_SESSION['discord_roles'] = $member_data['roles'];
        
        $permission = 0;
        
        if (in_array('1267751951307903017', $member_data['roles']) || 
            in_array('1353795720716746884', $member_data['roles'])) {
            $permission = 2;
        } elseif (in_array('1285694535766245408', $member_data['roles'])) {
            $permission = 1;
        }
        
        $_SESSION['admin_permission'] = $permission;
        
        return true;
    }
    
    return false;
}

// Kullanıcının yetki seviyesini kontrol eder
function hasPermission($requiredPermission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userPermission = $_SESSION['admin_permission'] ?? 0;
    
    return $userPermission >= $requiredPermission;
}

// Kullanıcının yönetici yetkisine sahip olup olmadığını kontrol eder
function isAdmin() {
    return hasPermission(2);
}

// Belirli bir yetki seviyesini gerektirir, yoksa ana sayfaya yönlendirir
function requirePermission($requiredPermission) {
    if (!hasPermission($requiredPermission)) {
        header("Location: index.php");
        exit;
    }
}

// Kullanıcının giriş yapmış olup olmadığını kontrol eder
function isLoggedIn() {
    // Discord entegrasyonu - admin_id yerine discord_user_id ve admin_permission kontrol ediliyor
    if (isset($_SESSION['discord_user_id']) && !empty($_SESSION['discord_user_id']) && isset($_SESSION['admin_permission'])) {
        // Discord giriş bilgileri var ve yetkilendirme yapılmış
        return true;
    }
    
    // Eski admin_id sistemi için geriye dönük uyumluluk
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Kullanıcının giriş yapmış olmasını gerektirir, yapmamışsa giriş sayfasına yönlendirir
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit;
    }
}

// Metni güvenli hale getirir (XSS koruması)
// Not: Bu fonksiyon includes/legacy_compatibility.php içinde de tanımlanmış durumda
// sanitizeOutput fonksiyonu zaten tanımlanmış ise, yeniden tanımlamayız
if (!function_exists('sanitizeOutput')) {
    function sanitizeOutput($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

// Başvuru durumunu Türkçe olarak döndürür
function getStatusText($status) {
    switch ($status) {
        case 'unread':
            return 'Okunmamış';
        case 'approved':
            return 'Onaylanmış';
        case 'rejected':
            return 'Reddedilmiş';
        default:
            return 'Bilinmiyor';
    }
}

// Başvuru durumuna göre renk sınıfı döndürür
function getStatusClass($status) {
    switch ($status) {
        case 'unread':
            return 'bg-blue-100 text-blue-800';
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Başvuruları duruma göre sayar
function countApplications($status = 'all') {
    // OOP yaklaşımıyla veritabanı erişimi
    $db = Database::getInstance();
    
    if ($status === 'all') {
        return $db->fetchValue("SELECT COUNT(*) FROM applications");
    } else {
        return $db->fetchValue("SELECT COUNT(*) FROM applications WHERE status = ?", [$status]);
    }
}

// Başvuruları duruma göre listeler
function getApplications($status = 'all', $limit = 10, $offset = 0) {
    // OOP yaklaşımıyla veritabanı erişimi
    $db = Database::getInstance();
    
    if ($status === 'all') {
        return $db->fetchAll(
            "SELECT * FROM applications ORDER BY created_at DESC LIMIT ? OFFSET ?", 
            [$limit, $offset]
        );
    } else {
        return $db->fetchAll(
            "SELECT * FROM applications WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?", 
            [$status, $limit, $offset]
        );
    }
}

// Başvuru detaylarını getirir
function getApplication($id) {
    // OOP yaklaşımıyla veritabanı erişimi
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM applications WHERE id = ?", [$id]);
}

// Başvuru durumunu günceller
function updateApplicationStatus($id, $status, $notes = null, $admin_id = null) {
    // Discord bilgilerini session'dan al
    $admin_discord_id = isset($_SESSION['discord_user_id']) ? $_SESSION['discord_user_id'] : null;
    
    // Discord API ile kullanıcı adını alma
    $admin_discord_username = '';
    if ($admin_discord_id) {
        $config = getDiscordConfig();
        $discord = new DiscordAPI(
            $config['token'],
            $config['guild_id'],
            $config['role_id']
        );
        
        $userInfo = $discord->getUserInfo($admin_discord_id);
        if ($userInfo) {
            $admin_discord_username = $userInfo['username'];
        }
    }
    
    // Eski sistem için default admin_id değeri (geriye dönük uyumluluk)
    $admin_id = null; // NULL değeri foreign key kısıtlamasına uyacaktır
    
    // OOP yaklaşımıyla veritabanı erişimi
    $db = Database::getInstance();
    
    // Tablo yapısını kontrol et ve yeni sütunlar mevcutsa kullan
    $hasNewColumns = false;
    $columnCount = $db->fetchValue("SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'applications' AND column_name = 'admin_discord_id'");
    
    if ($columnCount > 0) {
        $hasNewColumns = true;
    }
    
    if ($hasNewColumns) {
        // Yeni sütunlarla güncelleme
        $data = [
            'status' => $status,
            'admin_notes' => $notes,
            'admin_id' => $admin_id,
            'admin_discord_id' => $admin_discord_id,
            'admin_discord_username' => $admin_discord_username,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $db->update('applications', $data, 'id = ?', [$id]) > 0;
    } else {
        // Eski yapıyla güncelleme
        $data = [
            'status' => $status,
            'admin_notes' => $notes,
            'admin_id' => $admin_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $db->update('applications', $data, 'id = ?', [$id]) > 0;
    }
}

/**
 * Discord kullanıcı adını getirir
 * 
 * @param string $discordId Discord kullanıcı ID'si
 * @return string Discord kullanıcının adı veya boş string
 */
function getDiscordUsername($discordId) {
    if (empty($discordId)) {
        return '';
    }
    
    $config = getDiscordConfig();
    $discord = new DiscordAPI(
        $config['token'],
        $config['guild_id'],
        $config['role_id']
    );
    
    $userInfo = $discord->getUserInfo($discordId);
    if ($userInfo) {
        return $userInfo['username'];
    }
    
    return '';
}

/*
// Admin kullanıcısının bilgilerini getirir (Discord entegrasyonu nedeniyle devre dışı bırakıldı)
function getAdminUser($id) {
    if (empty($id)) {
        return false;
    }
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
*/

/*
// Admin kullanıcısını doğrular (Discord entegrasyonu nedeniyle devre dışı bırakıldı)
function validateAdmin($username, $password) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return false;
}
*/

// Tarih formatını düzenler
// Not: Bu fonksiyon includes/legacy_compatibility.php içinde de tanımlanmış durumda
if (!function_exists('formatDate')) {
    function formatDate($date) {
        return date('d.m.Y H:i', strtotime($date));
    }
}

// Başvuruyu veritabanından siler
function deleteApplication($id) {
    // OOP yaklaşımıyla veritabanı erişimi
    $db = Database::getInstance();
    return $db->delete('applications', 'id = ?', [$id]) > 0;
}

// Discord kullanıcısına özel mesaj gönderir
function sendDiscordDirectMessage($discordId, $message) {
    if (empty($discordId) || !is_numeric($discordId)) {
        return false;
    }
    
    $botApiUrl = "http://localhost:3000/send-dm/{$discordId}";
    $postData = json_encode(['message' => $message]);
    
    $ch = curl_init($botApiUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($response === false) {
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    if ($httpCode == 200) {
        $result = json_decode($response, true);
        return $result['success'] ?? false;
    }
    
    return false;
}

// Discord kullanıcısına rol atar
function assignDiscordRole($discordId) {
    $config = getDiscordConfig();
    
    if (!$config['enabled'] || empty($config['token'])) {
        error_log("Discord: API devre dışı veya token eksik");
        return false;
    }
    
    // Discord ID formatını doğrula
    if (empty($discordId) || !is_numeric($discordId) || strlen($discordId) < 17 || strlen($discordId) > 20) {
        error_log("Discord: Geçersiz Discord ID formatı: " . $discordId);
        return false;
    }
    
    // Guild ID ile Discord ID aynı olmamalı
    if ($discordId === $config['guild_id']) {
        error_log("Discord: Kullanıcı ID ile Guild ID aynı: " . $discordId);
        return false;
    }
    
    $discord = new DiscordAPI(
        $config['token'],
        $config['guild_id'],
        $config['role_id']
    );
    
    $result = $discord->assignRole($discordId);
    if (!$result) {
        error_log("Discord: Rol atama başarısız. userID=" . $discordId);
    } else {
        error_log("Discord: Rol atama başarılı. userID=" . $discordId);
    }
    
    return $result;
}

// Belirli bir Discord rolüne sahip kullanıcıları getirir
// Not: Bu fonksiyon includes/legacy_compatibility.php içinde de tanımlanmış durumda
if (!function_exists('getDiscordMembersWithRole')) {
    function getDiscordMembersWithRole($roleId, $limit = 10) {
        $config = getDiscordConfig();
        
        if (!$config['enabled'] || empty($config['token'])) {
            return [];
        }
        
        $discord = new DiscordAPI(
            $config['token'],
            $config['guild_id'],
            $config['role_id']
        );
        
        return $discord->getMembersWithRole($roleId, $limit);
    }
}

// Discord rol adını getirir
// Not: Bu fonksiyon includes/legacy_compatibility.php içinde de tanımlanmış durumda
if (!function_exists('getDiscordRoleName')) {
    function getDiscordRoleName($roleId) {
        global $discord_roles;
        
        // Config dosyasındaki rol adlarını kullan
        return $discord_roles[$roleId] ?? '';
    }
}
