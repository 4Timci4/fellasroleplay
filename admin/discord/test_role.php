<?php
require_once '../includes/security_check.php';
require_once '../includes/functions.php';
require_once '../../includes/bootstrap.php';

// Yönetici yetkisi kontrolü
if (!isAdmin()) {
    header("Location: ../index");
    exit;
}

// Discord konfigürasyonunu yükle
$config = \Core\Config::get('discord');
$token = $config['token'];
$guildId = $config['guild_id'];
$whitelistRoleId = $config['whitelist_role_id'];

// Test sonuçlarını depolayacak dizi
$results = [];

// Hata ayıklama ve test fonksiyonları
function addResult($title, $success, $message = '', $details = null) {
    global $results;
    $results[] = [
        'title' => $title,
        'success' => $success,
        'message' => $message,
        'details' => $details
    ];
}

// 1. Discord sunucu/bot temel bilgileri
addResult('Discord Yapılandırması', true, 'Discord API bilgileri kontrol ediliyor', [
    'Token' => substr($token, 0, 10) . '...',
    'Guild ID' => $guildId,
    'Whitelist Rol ID' => $whitelistRoleId,
    'API Endpoint' => $config['api']['endpoint'] ?? 'Tanımlanmamış'
]);

// 2. Bot API'si çalışıyor mu kontrol et
$botApiTest = false;
$botApiMessage = 'Bot API\'sine bağlanılamadı';
$botApiDetails = null;

$ch = curl_init($config['api']['endpoint'] . '/check-role/1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false) {
    $botApiTest = true;
    $botApiMessage = 'Bot API\'sine bağlantı başarılı, HTTP ' . $httpCode;
    $botApiDetails = json_decode($response, true);
}

addResult('Bot API Bağlantısı', $botApiTest, $botApiMessage, $botApiDetails);

// 3. Test discord ID ile rol kontrolü
$testDiscordId = isset($_GET['discord_id']) ? $_GET['discord_id'] : '1234567890123456789';
$checkRoleTest = false;
$checkRoleMessage = 'Rol kontrolü yapılamadı';
$checkRoleDetails = null;

// Discord ID doğrulaması ekle
if (!preg_match('/^\d{17,20}$/', $testDiscordId)) {
    $checkRoleMessage = 'Geçersiz Discord ID formatı (17-20 rakam olmalı)';
    $checkRoleDetails = [
        'providedId' => $testDiscordId,
        'error' => 'Discord ID\'leri 17-20 rakam içermelidir'
    ];
    addResult('Discord ID Doğrulama', false, $checkRoleMessage, $checkRoleDetails);
} else {
    // ID formatı doğru, Discord API'sini kontrol et
    $ch = curl_init($config['api']['endpoint'] . '/check-role/' . $testDiscordId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false) {
        $checkRoleTest = $httpCode === 200;
        $checkRoleMessage = 'Rol kontrolü tamamlandı, HTTP ' . $httpCode;
        $checkRoleDetails = json_decode($response, true);
    }

    addResult('Discord ID Rol Kontrolü', $checkRoleTest, $checkRoleMessage, $checkRoleDetails);

    // 4. Discord API'si doğrudan test
    $discordApiTest = false;
    $discordApiMessage = 'Discord API\'sine doğrudan bağlantı yapılamadı';
    $discordApiDetails = null;

    $url = "https://discord.com/api/v10/guilds/{$guildId}/members/{$testDiscordId}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bot ' . $token,
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $discordApiTest = $httpCode === 200;
    $discordApiMessage = 'Discord API yanıtı: HTTP ' . $httpCode;
    $discordApiDetails = json_decode($response, true);

    addResult('Discord API Testi', $discordApiTest, $discordApiMessage, $discordApiDetails);
}

// 5. Manuel rol atama testi (yalnızca submit edildiğinde)
$roleAssignTest = false;
$roleAssignMessage = '';
$roleAssignDetails = null;

if (isset($_POST['assign_role']) && !empty($_POST['discord_id'])) {
    $targetDiscordId = $_POST['discord_id'];
    
    try {
        // Discord ID format kontrolü
        if (!preg_match('/^\d{17,20}$/', $targetDiscordId)) {
            throw new \Exception("Geçersiz Discord ID formatı. ID 17-20 rakam içermelidir.");
        }
        
        $discordService = new \Services\DiscordService();
        
        // Kullanıcı sunucuda mı kontrol et
        if (!$discordService->checkUserInGuild($targetDiscordId)) {
            throw new \Exception("Kullanıcı Discord sunucusunda bulunamadı (Guild ID: {$guildId})");
        }
        
        $roleAssignResult = $discordService->assignRole($targetDiscordId);
        
        $roleAssignTest = $roleAssignResult;
        $roleAssignMessage = $roleAssignResult ? 'Rol başarıyla atandı' : 'Rol atanamadı, hata ayıklama için PHP hata günlüğünü kontrol edin';
        
        // Rol ataması sonrası kullanıcı bilgilerini tekrar kontrol et
        sleep(1); // Discord API'nin güncellemesi için kısa bir bekleme
        $userInfo = $discordService->getUserInfo($targetDiscordId);
        $roleAssignDetails = [
            'İşlem Sonucu' => $roleAssignResult,
            'Kullanıcı Bilgisi' => $userInfo,
            'Rol Aktif Mi' => $userInfo && in_array($whitelistRoleId, $userInfo['roles'] ?? [])
        ];
    } catch (\Exception $e) {
        $roleAssignTest = false;
        $roleAssignMessage = 'Hata: ' . $e->getMessage();
    }
    
    addResult('Manuel Rol Atama Testi', $roleAssignTest, $roleAssignMessage, $roleAssignDetails);
}

// 6. DM gönderme testi
if (isset($_POST['send_dm']) && !empty($_POST['discord_id']) && !empty($_POST['message'])) {
    $targetDiscordId = $_POST['discord_id'];
    $message = $_POST['message'];
    
    try {
        // Discord ID format kontrolü
        if (!preg_match('/^\d{17,20}$/', $targetDiscordId)) {
            throw new \Exception("Geçersiz Discord ID formatı. ID 17-20 rakam içermelidir.");
        }
        
        $ch = curl_init($config['api']['dm_endpoint'] . '/' . $targetDiscordId);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => $message]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $success = $httpCode === 200;
        $responseData = json_decode($response, true);
        
        addResult('Discord DM Gönderme Testi', $success, 
            $success ? 'DM başarıyla gönderildi' : 'DM gönderilemedi, HTTP: ' . $httpCode,
            $responseData
        );
    } catch (\Exception $e) {
        addResult('Discord DM Gönderme Testi', false, 'Hata: ' . $e->getMessage(), null);
    }
}

// Sayfa
include '../includes/header.php';
?>

<div class="container mx-auto">
    <div class="mb-6">
        <a href="javascript:history.back()" class="text-primary hover:text-primary-light transition-colors inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Geri Dön
        </a>
    </div>
    
    <div class="card-bg rounded-lg shadow-lg overflow-hidden border border-primary-dark mb-8">
        <div class="px-6 py-4 border-b border-primary-dark">
            <h1 class="text-2xl font-bold text-text-light">Discord <span class="text-primary">Rol Hata Ayıklama</span></h1>
        </div>
        
        <div class="p-6">
            <p class="mb-6 text-gray-300">Bu sayfa, Discord rol atama sorunlarını tanılamak ve çözmek için kullanılır. Test sonuçları sunucu günlüklerine kaydedilir.</p>
            
            <div class="bg-yellow-800 text-amber-200 p-4 rounded mb-6">
                <div class="font-semibold mb-1"><i class="fas fa-info-circle mr-2"></i>Discord API Bilgisi</div>
                <p class="text-sm">
                    Discord kullanıcı ID'leri 17-20 rakamdan oluşur. Kullanıcı <strong>Discord sunucunuzda</strong> üye olmalıdır, aksi halde rol ataması yapılamaz. 
                    Hata ayıklama için, Discord bot izinlerini ve kullanıcının sunucuya katılıp katılmadığını kontrol edin.
                </p>
            </div>
            
            <!-- Test Sonuçları -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-primary-dark text-primary">Test Sonuçları</h2>
                
                <?php foreach ($results as $result): ?>
                <div class="mb-6">
                    <div class="flex items-center mb-2">
                        <span class="mr-2 <?php echo $result['success'] ? 'text-green-500' : 'text-red-500'; ?>">
                            <i class="fas <?php echo $result['success'] ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        </span>
                        <h3 class="font-medium text-text-light"><?php echo htmlspecialchars($result['title']); ?></h3>
                    </div>
                    
                    <p class="ml-7 text-sm text-gray-400"><?php echo htmlspecialchars($result['message']); ?></p>
                    
                    <?php if ($result['details']): ?>
                    <div class="ml-7 mt-2 p-3 bg-gray-900 rounded text-sm overflow-auto max-h-[200px] scrollbar-thin">
                        <pre class="whitespace-pre-wrap text-text-light"><?php echo htmlspecialchars(json_encode($result['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Manuel Test Formları -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Rol Atama Testi -->
                <div class="card-bg-secondary border border-primary-dark rounded-lg p-4">
                    <h3 class="text-lg font-semibold mb-4 text-primary">Manuel Rol Atama</h3>
                    <form method="POST" action="?discord_id=<?php echo htmlspecialchars($testDiscordId); ?>">
                        <div class="mb-4">
                            <label for="discord_id" class="block text-sm font-medium text-text-light mb-2">Discord ID</label>
                            <input type="text" id="discord_id" name="discord_id" 
                                   class="w-full px-4 py-2 bg-gray-900 border border-primary-dark rounded-lg shadow-sm text-text-light"
                                   value="<?php echo htmlspecialchars($testDiscordId); ?>" required
                                   placeholder="Örn: 1234567890123456789">
                            <p class="text-xs text-gray-400 mt-1">Discord ID 17-20 rakam içermelidir</p>
                        </div>
                        
                        <button type="submit" name="assign_role" value="1" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-dark focus:outline-none transition-colors">
                            <i class="fas fa-user-tag mr-2"></i> Rol Ata
                        </button>
                    </form>
                </div>
                
                <!-- DM Gönderme Testi -->
                <div class="card-bg-secondary border border-primary-dark rounded-lg p-4">
                    <h3 class="text-lg font-semibold mb-4 text-primary">Discord DM Testi</h3>
                    <form method="POST" action="?discord_id=<?php echo htmlspecialchars($testDiscordId); ?>">
                        <div class="mb-4">
                            <label for="dm_discord_id" class="block text-sm font-medium text-text-light mb-2">Discord ID</label>
                            <input type="text" id="dm_discord_id" name="discord_id" 
                                   class="w-full px-4 py-2 bg-gray-900 border border-primary-dark rounded-lg shadow-sm text-text-light"
                                   value="<?php echo htmlspecialchars($testDiscordId); ?>" required
                                   placeholder="Örn: 1234567890123456789">
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="block text-sm font-medium text-text-light mb-2">Mesaj</label>
                            <textarea id="message" name="message" rows="3" 
                                     class="w-full px-4 py-2 bg-gray-900 border border-primary-dark rounded-lg shadow-sm text-text-light"
                                     required>Test mesajı</textarea>
                        </div>
                        
                        <button type="submit" name="send_dm" value="1" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-dark focus:outline-none transition-colors">
                            <i class="fas fa-envelope mr-2"></i> DM Gönder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
