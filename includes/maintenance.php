<?php
/**
 * Bakım modu fonksiyonları ve kontrol mekanizması
 */

// Forum fonksiyonlarını dahil et (is_forum_admin() fonksiyonu için gerekli)
require_once __DIR__ . '/forum-functions.php';

/**
 * Bakım modunun aktif olup olmadığını kontrol eder
 * 
 * @return bool Bakım modu aktif ise true, değilse false
 */
function is_maintenance_mode() {
    $maintenance_file = __DIR__ . '/../maintenance.flag';
    return file_exists($maintenance_file);
}

/**
 * Bakım modunu aktifleştirir
 * 
 * @return bool İşlem başarılı ise true, değilse false
 */
function enable_maintenance_mode() {
    $maintenance_file = __DIR__ . '/../maintenance.flag';
    $result = file_put_contents($maintenance_file, time());
    return ($result !== false);
}

/**
 * Bakım modunu devre dışı bırakır
 * 
 * @return bool İşlem başarılı ise true, değilse false
 */
function disable_maintenance_mode() {
    $maintenance_file = __DIR__ . '/../maintenance.flag';
    if (file_exists($maintenance_file)) {
        return unlink($maintenance_file);
    }
    return true;
}

/**
 * CSRF token oluşturur ve oturuma kaydeder
 * 
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF tokenin geçerli olup olmadığını kontrol eder
 * 
 * @param string $token Kontrol edilecek token
 * @return bool Token geçerli ise true, değilse false
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Bakım modu sayfasını görüntüler ve çıkış yapar
 */
function display_maintenance_page() {
    // Bakım modu erişim kontrolü
    if (isset($_SESSION['discord_user_id']) && function_exists('has_maintenance_access') && has_maintenance_access($_SESSION['discord_user_id'])) {
        return;
    }
    
    // HTTP başlık kodunu ayarla
    http_response_code(503);
    header('Retry-After: 3600');
    
    // Site başlığını al
    if (function_exists('get_site_config')) {
        $site_name = get_site_config('site_name');
    } else {
        $site_name = 'Fellas Roleplay';
    }
    
    // Bakım sayfasını görüntüle
    ?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakım Modu - <?php echo htmlspecialchars($site_name); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
        }
        .maintenance-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
        }
        .logo {
            max-width: 200px;
            margin: 0 auto 2rem;
        }
        .icon-pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% {
                transform: scale(0.95);
                opacity: 0.7;
            }
            50% {
                transform: scale(1);
                opacity: 1;
            }
            100% {
                transform: scale(0.95);
                opacity: 0.7;
            }
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center">
    <div class="maintenance-container">
        <img src="assets/images/logo.png" alt="<?php echo htmlspecialchars($site_name); ?>" class="logo">
        
        <div class="icon-pulse text-blue-500 text-6xl mb-8">
            <i class="fas fa-tools"></i>
        </div>
        
        <h1 class="text-4xl font-bold mb-4">Bakım Çalışması</h1>
        
        <p class="text-xl text-gray-400 mb-8">
            Forum şu anda bakım nedeniyle geçici olarak kapalıdır.<br>
            Kısa süre içinde geri döneceğiz.
        </p>
        
        <div class="mt-6">
            <a href="anasayfa.php" class="inline-block px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-md">
                <i class="fas fa-home mr-2"></i> Ana Sayfa'ya Dön
            </a>
        </div>
        
        <div class="mt-12 text-gray-500">
            <p>Bizi takip edin:</p>
            <div class="flex justify-center mt-4 space-x-6">
                <a href="https://discord.gg/fellasrp" class="text-2xl hover:text-blue-400 transition-colors">
                    <i class="fab fa-discord"></i>
                </a>
                <a href="https://youtube.com" class="text-2xl hover:text-red-500 transition-colors">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </div>
</body>
</html><?php
    exit;
}
?>
