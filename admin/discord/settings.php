<?php
require_once '../includes/functions.php';
require_once '../../includes/bootstrap.php';

use Services\DiscordConfigService;

// Yönetici yetkisi kontrolü
requirePermission(2);

include '../includes/header.php';

// Discord Config Service örneğini al
$discordConfigService = DiscordConfigService::getInstance();

// Yalnızca POST isteği olduğunda işlem yap
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al
    $token = $_POST['discord_token'] ?? '';
    $guild_id = $_POST['discord_guild_id'] ?? '';
    $role_id = $_POST['discord_role_id'] ?? '';
    $enabled = isset($_POST['discord_enabled']) ? true : false;
    
    // Ayarları güncelle
    $configData = [
        'token' => $token,
        'guild_id' => $guild_id,
        'role_id' => $role_id,
        'enabled' => $enabled
    ];
    
    $success = $discordConfigService->updateConfig($configData);
    
    // Başarı veya hata mesajı
    $message = $success ? 'Discord ayarları başarıyla güncellendi.' : 'Discord ayarları güncellenirken bir hata oluştu.';
    $message_type = $success ? 'success' : 'error';
    
    // Bot konfigürasyonunu da güncelle
    if ($success) {
        $botConfigUpdated = $discordConfigService->updateBotConfig();
        if (!$botConfigUpdated) {
            $message .= ' Ancak bot konfigürasyon dosyası güncellenemedi.';
        }
    }
}

// Mevcut ayarları al
$discord_config = $discordConfigService->getConfig();
?>

<div class="container mx-auto">
    <div class="mb-6">
        <a href="../index" class="text-primary hover:text-primary-light transition-colors inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Geri Dön
        </a>
    </div>
    
    <?php if (isset($message)): ?>
    <div class="mb-6 <?php echo $message_type === 'success' ? 'bg-[#002a00] border border-green-500 text-green-300' : 'bg-[#2a0000] border border-red-500 text-red-300'; ?> px-4 py-3 rounded-lg relative alert-dismissible" role="alert">
        <span class="block sm:inline"><?php echo $message; ?></span>
    </div>
    <?php endif; ?>
    
    <div class="card-bg rounded-lg shadow-lg overflow-hidden border border-primary-dark">
        <div class="px-6 py-4 border-b border-primary-dark">
            <h1 class="text-2xl font-bold text-text-light">Discord <span class="text-primary">Ayarları</span></h1>
        </div>
        
        <div class="p-6">
            <form method="POST" class="space-y-6">
                <div>
                    <label for="discord_token" class="block text-sm font-medium text-text-light mb-2">Discord Bot Token</label>
                    <input type="password" id="discord_token" name="discord_token" value="<?php echo sanitizeOutput($discord_config['token']); ?>" class="w-full px-4 py-2 bg-[#0a0a0a] border border-primary-dark rounded-lg shadow-lg text-text-light focus:outline-none focus:ring-primary focus:border-primary">
                    <p class="mt-1 text-sm text-gray-400">Discord Developer Portal'dan bot token'ınızı alabilirsiniz.</p>
                </div>
                
                <div>
                    <label for="discord_guild_id" class="block text-sm font-medium text-text-light mb-2">Discord Sunucu ID</label>
                    <input type="text" id="discord_guild_id" name="discord_guild_id" value="<?php echo sanitizeOutput($discord_config['guild_id']); ?>" class="w-full px-4 py-2 bg-[#0a0a0a] border border-primary-dark rounded-lg shadow-lg text-text-light focus:outline-none focus:ring-primary focus:border-primary">
                    <p class="mt-1 text-sm text-gray-400">Discord sunucunuzun ID'si.</p>
                </div>
                
                <div>
                    <label for="discord_role_id" class="block text-sm font-medium text-text-light mb-2">Discord Rol ID</label>
                    <input type="text" id="discord_role_id" name="discord_role_id" value="<?php echo sanitizeOutput($discord_config['role_id']); ?>" class="w-full px-4 py-2 bg-[#0a0a0a] border border-primary-dark rounded-lg shadow-lg text-text-light focus:outline-none focus:ring-primary focus:border-primary">
                    <p class="mt-1 text-sm text-gray-400">Başvurusu onaylanan kullanıcılara verilecek rolün ID'si.</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="discord_enabled" name="discord_enabled" <?php echo $discord_config['enabled'] ? 'checked' : ''; ?> class="mr-2 text-primary focus:ring-primary">
                    <label for="discord_enabled" class="text-text-light">Discord entegrasyonunu aktifleştir</label>
                </div>
                
                <div class="bg-[#0a0a0a] p-4 rounded-lg border border-primary-dark">
                    <h3 class="text-lg font-semibold mb-2 text-primary">Kurulum Adımları</h3>
                    <ol class="list-decimal list-inside space-y-2 text-text-light">
                        <li><a href="https://discord.com/developers/applications" target="_blank" class="text-primary hover:text-primary-light">Discord Developer Portal</a>'a gidin ve yeni bir uygulama oluşturun.</li>
                        <li>"Bot" sekmesinden bir bot oluşturun ve token'ı kopyalayın.</li>
                        <li>"OAuth2" sekmesinden bot için bir davet linki oluşturun. "bot" scope'unu ve "Manage Roles" iznini seçin.</li>
                        <li>Botu Discord sunucunuza ekleyin.</li>
                        <li>Discord'da "Geliştirici Modu"nu açın ve sunucu ID'nizi ve rol ID'nizi kopyalayın.</li>
                        <li>Yukarıdaki formu doldurun ve "Discord entegrasyonunu aktifleştir" seçeneğini işaretleyin.</li>
                    </ol>
                </div>
                
                <div class="bg-[#0a0a0a] p-4 rounded-lg border border-primary-dark mt-6">
                    <h3 class="text-lg font-semibold mb-2 text-primary">Discord Bot</h3>
                    <p class="text-text-light mb-4">
                        Discord botunu kullanarak, botun çalışıp çalışmadığını test edebilirsiniz. Bot çalıştığında, Discord sunucunuzda <code>/test</code> komutunu kullanabilirsiniz.
                    </p>
                    <p class="text-text-light mb-4">
                        Bot, <code>discord-bot</code> dizininde bulunmaktadır. Botu çalıştırmak için aşağıdaki adımları izleyin:
                    </p>
                    <ol class="list-decimal list-inside space-y-2 text-text-light mb-4">
                        <li>Node.js ve npm'i yükleyin: <a href="https://nodejs.org/" target="_blank" class="text-primary hover:text-primary-light">https://nodejs.org/</a></li>
                        <li>Discord botunun bulunduğu dizine gidin: <code>cd <?php echo realpath(dirname(__DIR__) . '/../discord-bot'); ?></code></li>
                        <li>Bağımlılıkları yükleyin: <code>npm install</code></li>
                        <li><code>config.json</code> dosyasını düzenleyin ve token ile clientId bilgilerini girin</li>
                        <li>Botu başlatın: <code>npm start</code> veya <code>start-bot.bat</code> dosyasını çalıştırın</li>
                    </ol>
                    <div class="bg-[#1a1a1a] p-4 rounded-lg border border-primary-dark mb-4">
                        <h4 class="font-semibold text-primary mb-2">config.json Örneği</h4>
                        <pre class="text-text-light overflow-x-auto whitespace-pre-wrap">
{
  "token": "YOUR_BOT_TOKEN",
  "clientId": "YOUR_CLIENT_ID",
  "guildId": "<?php echo sanitizeOutput($discord_config['guild_id']); ?>"
}
                        </pre>
                    </div>
                    <p class="text-text-light mb-4">
                        <strong>Not:</strong> Bot'un çalışması için, Discord Developer Portal'da "Bot" sekmesinde "Privileged Gateway Intents" ayarlarını aktifleştirmeniz gerekiyor.
                    </p>
                    <div class="mt-4">
                        <a href="update_bot" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-[#5865F2] hover:bg-[#4752C4] focus:outline-none transition-colors duration-300 mb-2">
                            <i class="fas fa-cog mr-2"></i> <span>Bot Ayarlarını Güncelle</span>
                        </a>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-dark focus:outline-none transition-colors duration-300 mb-2">
                        <i class="fas fa-save mr-2"></i> <span>Ayarları Kaydet</span>
                    </button>
                    
                    <?php if ($discord_config['enabled'] && !empty($discord_config['token'])): ?>
                    <a href="test" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-[#5865F2] hover:bg-[#4752C4] focus:outline-none transition-colors duration-300 mb-2">
                        <i class="fab fa-discord mr-2"></i> <span>Bağlantıyı Test Et</span>
                    </a>
                    
                    <a href="test_role" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-[#5865F2] hover:bg-[#4752C4] focus:outline-none transition-colors duration-300 mb-2">
                        <i class="fas fa-user-tag mr-2"></i> <span>Rol Atamayı Test Et</span>
                    </a>
                    
                    <a href="bot_permissions" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-[#5865F2] hover:bg-[#4752C4] focus:outline-none transition-colors duration-300 mb-2">
                        <i class="fas fa-shield-alt mr-2"></i> <span>Bot İzinlerini Kontrol Et</span>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
