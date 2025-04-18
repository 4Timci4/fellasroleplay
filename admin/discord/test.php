<?php
require_once '../includes/functions.php';
include '../includes/header.php';

// Discord yapılandırma ayarlarını al
$discord_config = getDiscordConfig();

// Test sonuçları
$test_results = [];
$all_tests_passed = true;

// Test 1: Discord entegrasyonu aktif mi?
$test_results['integration_enabled'] = [
    'name' => 'Discord Entegrasyonu',
    'status' => $discord_config['enabled'],
    'message' => $discord_config['enabled'] ? 'Discord entegrasyonu aktif.' : 'Discord entegrasyonu aktif değil. Ayarlar sayfasından aktifleştirin.'
];
if (!$discord_config['enabled']) {
    $all_tests_passed = false;
}

// Test 2: Bot token girilmiş mi?
$test_results['token_exists'] = [
    'name' => 'Bot Token',
    'status' => !empty($discord_config['token']),
    'message' => !empty($discord_config['token']) ? 'Bot token girilmiş.' : 'Bot token girilmemiş. Ayarlar sayfasından token ekleyin.'
];
if (empty($discord_config['token'])) {
    $all_tests_passed = false;
}

// Test 3: Sunucu ID girilmiş mi?
$test_results['guild_id_exists'] = [
    'name' => 'Sunucu ID',
    'status' => !empty($discord_config['guild_id']),
    'message' => !empty($discord_config['guild_id']) ? 'Sunucu ID girilmiş.' : 'Sunucu ID girilmemiş. Ayarlar sayfasından sunucu ID ekleyin.'
];
if (empty($discord_config['guild_id'])) {
    $all_tests_passed = false;
}

// Test 4: Rol ID girilmiş mi?
$test_results['role_id_exists'] = [
    'name' => 'Rol ID',
    'status' => !empty($discord_config['role_id']),
    'message' => !empty($discord_config['role_id']) ? 'Rol ID girilmiş.' : 'Rol ID girilmemiş. Ayarlar sayfasından rol ID ekleyin.'
];
if (empty($discord_config['role_id'])) {
    $all_tests_passed = false;
}

// Eğer tüm temel testler geçildiyse, Discord API'ye bağlantıyı test et
if ($all_tests_passed) {
    // Discord API sınıfını başlat
    $discord = new DiscordAPI(
        $discord_config['token'],
        $discord_config['guild_id'],
        $discord_config['role_id']
    );
    
    // Test 5: Bot'un sunucuya erişimi var mı?
    try {
        // Sunucu bilgilerini al
        $url = "https://discord.com/api/v10/guilds/{$discord_config['guild_id']}";
        $ch = curl_init();
        
        $headers = [
            'Authorization: Bot ' . $discord_config['token'],
            'Content-Type: application/json',
        ];
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        $guild_access = ($httpCode >= 200 && $httpCode < 300);
        $test_results['guild_access'] = [
            'name' => 'Sunucu Erişimi',
            'status' => $guild_access,
            'message' => $guild_access ? 'Bot sunucuya erişebiliyor.' : 'Bot sunucuya erişemiyor. Bot\'un sunucuya eklendiğinden emin olun.'
        ];
        if (!$guild_access) {
            $all_tests_passed = false;
        }
        
        // Sunucu erişimi varsa, rol bilgilerini kontrol et
        if ($guild_access) {
            // Rol bilgilerini al
            $url = "https://discord.com/api/v10/guilds/{$discord_config['guild_id']}/roles";
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);
            
            $roles = json_decode($response, true);
            $role_exists = false;
            
            if (is_array($roles)) {
                foreach ($roles as $role) {
                    if ($role['id'] === $discord_config['role_id']) {
                        $role_exists = true;
                        break;
                    }
                }
            }
            
            $test_results['role_exists'] = [
                'name' => 'Rol Varlığı',
                'status' => $role_exists,
                'message' => $role_exists ? 'Belirtilen rol sunucuda bulunuyor.' : 'Belirtilen rol sunucuda bulunamadı. Rol ID\'sini kontrol edin.'
            ];
            if (!$role_exists) {
                $all_tests_passed = false;
            }
            
            // Bot'un rol verme yetkisi var mı?
            $bot_has_permission = false;
            
            // Bot'un kendi rolünü al
            $url = "https://discord.com/api/v10/users/@me";
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);
            
            $bot_info = json_decode($response, true);
            
            if (isset($bot_info['id'])) {
                $bot_id = $bot_info['id'];
                
                // Bot'un sunucudaki üyelik bilgilerini al
                $url = "https://discord.com/api/v10/guilds/{$discord_config['guild_id']}/members/{$bot_id}";
                $ch = curl_init();
                
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                curl_close($ch);
                
                $bot_member = json_decode($response, true);
                
                if (isset($bot_member['roles'])) {
                    // Bot'un rollerini kontrol et
                    foreach ($roles as $role) {
                        if (in_array($role['id'], $bot_member['roles'])) {
                            // Rol verme yetkisi var mı?
                            if (($role['permissions'] & 0x10000000) === 0x10000000) { // ADMINISTRATOR
                                $bot_has_permission = true;
                                break;
                            }
                            if (($role['permissions'] & 0x08000000) === 0x08000000) { // MANAGE_ROLES
                                $bot_has_permission = true;
                                break;
                            }
                        }
                    }
                }
            }
            
            $test_results['bot_permission'] = [
                'name' => 'Bot Yetkisi',
                'status' => $bot_has_permission,
                'message' => $bot_has_permission ? 'Bot\'un rol verme yetkisi var.' : 'Bot\'un rol verme yetkisi yok. Bot\'a "Rolleri Yönet" yetkisi verin.'
            ];
            if (!$bot_has_permission) {
                $all_tests_passed = false;
            }
        }
    } catch (Exception $e) {
        $test_results['api_connection'] = [
            'name' => 'API Bağlantısı',
            'status' => false,
            'message' => 'Discord API\'ye bağlanırken bir hata oluştu: ' . $e->getMessage()
        ];
        $all_tests_passed = false;
    }
}
?>

<div class="container mx-auto">
    <div class="mb-6">
        <a href="settings" class="text-primary hover:text-primary-light transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Geri Dön
        </a>
    </div>
    
    <div class="card-bg rounded-lg shadow-lg overflow-hidden border border-primary-dark">
        <div class="px-6 py-4 border-b border-primary-dark">
            <h1 class="text-2xl font-bold text-text-light">Discord <span class="text-primary">Bağlantı Testi</span></h1>
        </div>
        
        <div class="p-6">
            <div class="mb-6">
                <div class="flex items-center mb-4">
                    <div class="mr-4 text-4xl <?php echo $all_tests_passed ? 'text-green-500' : 'text-red-500'; ?>">
                        <i class="fas <?php echo $all_tests_passed ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-text-light">
                            <?php echo $all_tests_passed ? 'Tüm testler başarılı!' : 'Bazı testler başarısız!'; ?>
                        </h2>
                        <p class="text-gray-400">
                            <?php echo $all_tests_passed ? 'Discord entegrasyonu düzgün çalışıyor.' : 'Discord entegrasyonunda bazı sorunlar var.'; ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4">
                <?php foreach ($test_results as $test): ?>
                <div class="bg-[#0a0a0a] p-4 rounded-lg border border-primary-dark">
                    <div class="flex items-center">
                        <div class="mr-4 text-2xl <?php echo $test['status'] ? 'text-green-500' : 'text-red-500'; ?>">
                            <i class="fas <?php echo $test['status'] ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-text-light"><?php echo $test['name']; ?></h3>
                            <p class="text-gray-400"><?php echo $test['message']; ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!$all_tests_passed): ?>
            <div class="mt-6 bg-[#2a0000] border border-red-500 text-red-300 p-4 rounded-lg">
                <h3 class="font-semibold mb-2">Sorun Giderme Adımları</h3>
                <ul class="list-disc list-inside space-y-2">
                    <li>Discord Developer Portal'da bot ayarlarınızı kontrol edin.</li>
                    <li>Bot'un "SERVER MEMBERS INTENT" ve "ROLE MANAGEMENT" yetkilerinin aktif olduğundan emin olun.</li>
                    <li>Bot'un Discord sunucunuza eklendiğinden emin olun.</li>
                    <li>Bot'a "Rolleri Yönet" yetkisi verildiğinden emin olun.</li>
                    <li>Bot'un rol hiyerarşisinde, vereceği rolden daha üstte olduğundan emin olun.</li>
                    <li>Sunucu ID ve Rol ID'lerinin doğru olduğundan emin olun.</li>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="mt-6">
                <a href="settings" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-dark focus:outline-none transition-colors duration-300">
                    <i class="fas fa-cog mr-2"></i> Ayarlara Dön
                </a>
                
                <a href="test" class="ml-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-[#5865F2] hover:bg-[#4752C4] focus:outline-none transition-colors duration-300">
                    <i class="fas fa-sync-alt mr-2"></i> Testi Tekrarla
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
