<?php
require_once '../includes/security_check.php';
require_once '../includes/functions.php';
require_once '../../includes/bootstrap.php';

// Yönetici yetkisi kontrolü
if (!isAdmin()) {
    header("Location: ../index");
    exit;
}

// Çıktı dizisi
$output = [];

// Discord konfigürasyonunu yükle
$config = \Core\Config::get('discord');
$token = $config['token'];
$guildId = $config['guild_id'];
$whitelistRoleId = $config['whitelist_role_id'];

// Discord ID'yi al (URL'den veya POST'tan)
$discordId = '';
if (isset($_GET['id'])) {
    $discordId = $_GET['id'];
} elseif (isset($_POST['discord_id'])) {
    $discordId = $_POST['discord_id'];
}

// Sonuçları temizle
function cleanOutput() {
    ob_end_clean();
    header('Content-Type: application/json');
}

// Discord API'sine istek gönderen fonksiyon
function checkUserInDiscord($discordId, $guildId, $token) {
    // Kullanıcıyı Discord API üzerinden kontrol et
    $url = "https://discord.com/api/v10/users/{$discordId}";
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

    $userExists = ($httpCode === 200);
    $userData = json_decode($response, true);

    // Kullanıcı Discord'da var, şimdi sunucuda olup olmadığını kontrol et
    if ($userExists) {
        $url = "https://discord.com/api/v10/guilds/{$guildId}/members/{$discordId}";
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

        $inGuild = ($httpCode === 200);
        $memberData = json_decode($response, true);

        return [
            'discord_exists' => true,
            'discord_user' => $userData,
            'in_guild' => $inGuild,
            'guild_member' => $inGuild ? $memberData : null,
            'http_code' => $httpCode
        ];
    }

    return [
        'discord_exists' => false,
        'discord_user' => null,
        'in_guild' => false,
        'guild_member' => null,
        'error' => 'Discord kullanıcısı bulunamadı',
        'http_code' => $httpCode
    ];
}

// API modu
if (isset($_GET['api'])) {
    cleanOutput();
    
    if (empty($discordId)) {
        echo json_encode(['error' => 'Discord ID gerekli']);
        exit;
    }
    
    $result = checkUserInDiscord($discordId, $guildId, $token);
    echo json_encode($result);
    exit;
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($discordId)) {
    $output = checkUserInDiscord($discordId, $guildId, $token);
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
            <h1 class="text-2xl font-bold text-text-light">Discord <span class="text-primary">Kullanıcı Kontrolü</span></h1>
        </div>
        
        <div class="p-6">
            <p class="mb-6 text-gray-300">
                Bu araç, bir Discord kullanıcısının hem Discord platformunda hem de sunucunuzda var olup olmadığını kontrol eder. "Unknown User" hatası (Error Code 10013) genellikle kullanıcının Discord'da bulunamadığını veya sunucunuzda olmadığını gösterir.
            </p>
            
            <div class="bg-yellow-800 text-amber-200 p-4 rounded mb-6">
                <div class="font-semibold mb-1"><i class="fas fa-info-circle mr-2"></i>Discord API Bilgisi</div>
                <p class="text-sm">
                    Discord, kullanıcı ID'si verildiğinde önce global Discord platformunda kullanıcının var olduğunu kontrol eder, daha sonra belirli bir sunucuda (guild) üye olup olmadığını sorgular. Bir kullanıcıya rol atayabilmek için, kullanıcının sunucunuzda üye olması gerekmektedir.
                </p>
            </div>
            
            <!-- Kontrol Formu -->
            <div class="card-bg-secondary border border-primary-dark rounded-lg p-4 mb-8">
                <h3 class="text-lg font-semibold mb-4 text-primary">Discord Kullanıcı Kontrolü</h3>
                <form method="POST">
                    <div class="mb-4">
                        <label for="discord_id" class="block text-sm font-medium text-text-light mb-2">Discord ID</label>
                        <input type="text" id="discord_id" name="discord_id" 
                               class="w-full px-4 py-2 bg-gray-900 border border-primary-dark rounded-lg shadow-sm text-text-light"
                               value="<?php echo htmlspecialchars($discordId); ?>" required
                               placeholder="Örn: 1234567890123456789">
                        <p class="text-xs text-gray-400 mt-1">Discord ID 17-20 rakam içermelidir</p>
                    </div>
                    
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-dark focus:outline-none transition-colors">
                        <i class="fas fa-search mr-2"></i> Kullanıcıyı Kontrol Et
                    </button>
                </form>
            </div>
            
            <!-- Kontrol Sonuçları -->
            <?php if (!empty($output)): ?>
            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-primary-dark text-primary">Kontrol Sonuçları</h2>
                
                <div class="mb-4">
                    <h3 class="font-medium text-text-light mb-2">Discord Platformu Kontrolü</h3>
                    <div class="flex items-center mb-2">
                        <span class="mr-2 <?php echo $output['discord_exists'] ? 'text-green-500' : 'text-red-500'; ?>">
                            <i class="fas <?php echo $output['discord_exists'] ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        </span>
                        <p class="text-gray-300">
                            <?php if ($output['discord_exists']): ?>
                                Kullanıcı Discord platformunda mevcut.
                            <?php else: ?>
                                Kullanıcı Discord platformunda bulunamadı. (HTTP: <?php echo $output['http_code']; ?>)
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if ($output['discord_exists']): ?>
                    <div class="flex items-center mb-2">
                        <span class="mr-2 <?php echo $output['in_guild'] ? 'text-green-500' : 'text-red-500'; ?>">
                            <i class="fas <?php echo $output['in_guild'] ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        </span>
                        <p class="text-gray-300">
                            <?php if ($output['in_guild']): ?>
                                Kullanıcı Discord sunucunuzda üye. (Guild ID: <?php echo $guildId; ?>)
                            <?php else: ?>
                                Kullanıcı Discord sunucunuzda üye değil. (HTTP: <?php echo $output['http_code']; ?>)
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($output['discord_exists']): ?>
                    <div class="mt-4 p-3 bg-gray-900 rounded text-sm overflow-auto max-h-[300px] scrollbar-thin">
                        <pre class="whitespace-pre-wrap text-text-light"><?php echo htmlspecialchars(json_encode($output['discord_user'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($output['in_guild']): ?>
                    <h3 class="font-medium text-text-light mt-4 mb-2">Discord Sunucu Üyeliği</h3>
                    <div class="p-3 bg-gray-900 rounded text-sm overflow-auto max-h-[300px] scrollbar-thin">
                        <pre class="whitespace-pre-wrap text-text-light"><?php echo htmlspecialchars(json_encode($output['guild_member'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <h3 class="font-medium text-text-light mb-2">Sorun Giderme Önerileri</h3>
                    <ul class="list-disc pl-5 text-gray-300 text-sm">
                        <?php if (!$output['discord_exists']): ?>
                        <li class="mb-2">Discord ID doğru olmayabilir. Kullanıcıdan doğru Discord ID'sini göndermesini isteyin.</li>
                        <li class="mb-2">Discord API token'ınız geçersiz olabilir, doğruluğunu kontrol edin.</li>
                        <?php endif; ?>
                        
                        <?php if ($output['discord_exists'] && !$output['in_guild']): ?>
                        <li class="mb-2">
                            Kullanıcı henüz sunucunuzda değil.
                            <span class="block mt-1 text-amber-300">Kullanıcı, Discord sunucunuza katılmadan rol ataması yapılamaz.</span>
                        </li>
                        <li class="mb-2">Kullanıcıdan önce Discord sunucunuza katılmasını isteyin, ardından tekrar deneyin.</li>
                        <?php endif; ?>
                        
                        <?php if ($output['in_guild']): ?>
                        <li class="mb-2">Kullanıcı sunucunuzda ve rol atamak için hazır.</li>
                        <li class="mb-2">Eğer rol ataması hala başarısız oluyorsa, bot'un "Rol Yönetimi" iznine sahip olduğundan emin olun.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
