<?php
require_once '../includes/functions.php';
include '../includes/header.php';

// Mesaj değişkenleri
$message = '';
$message_type = '';

// Discord yapılandırma ayarlarını al
$discord_config = getDiscordConfig();

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['bot_token'] ?? '';
    $client_id = $_POST['client_id'] ?? '';
    
    if (empty($token)) {
        $message = 'Bot token boş olamaz.';
        $message_type = 'error';
    } elseif (empty($client_id)) {
        $message = 'Client ID boş olamaz.';
        $message_type = 'error';
    } else {
        // Discord bot config.json dosyasını güncelle
        $bot_config_file = dirname(dirname(__DIR__)) . '/discord-bot/config.json';
        
        if (file_exists($bot_config_file)) {
            $bot_config = json_decode(file_get_contents($bot_config_file), true);
            
            // Değerleri güncelle
            $bot_config['token'] = $token;
            $bot_config['clientId'] = $client_id;
            $bot_config['guildId'] = $discord_config['guild_id'];
            
            // Dosyaya yaz
            $success = file_put_contents($bot_config_file, json_encode($bot_config, JSON_PRETTY_PRINT)) !== false;
            
            if ($success) {
                $message = 'Discord bot ayarları başarıyla güncellendi.';
                $message_type = 'success';
            } else {
                $message = 'Discord bot ayarları güncellenirken bir hata oluştu.';
                $message_type = 'error';
            }
        } else {
            $message = 'Discord bot config.json dosyası bulunamadı.';
            $message_type = 'error';
        }
    }
}

// Mevcut bot ayarlarını al
$bot_config_file = dirname(dirname(__DIR__)) . '/discord-bot/config.json';
$bot_config = [];

if (file_exists($bot_config_file)) {
    $bot_config = json_decode(file_get_contents($bot_config_file), true);
}
?>

<div class="container mx-auto">
    <div class="mb-6">
        <a href="settings" class="text-primary hover:text-primary-light transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Geri Dön
        </a>
    </div>
    
    <?php if (!empty($message)): ?>
    <div class="mb-6 <?php echo $message_type === 'success' ? 'bg-[#002a00] border border-green-500 text-green-300' : 'bg-[#2a0000] border border-red-500 text-red-300'; ?> px-4 py-3 rounded-lg relative alert-dismissible" role="alert">
        <span class="block sm:inline"><?php echo $message; ?></span>
    </div>
    <?php endif; ?>
    
    <div class="card-bg rounded-lg shadow-lg overflow-hidden border border-primary-dark">
        <div class="px-6 py-4 border-b border-primary-dark">
            <h1 class="text-2xl font-bold text-text-light">Discord Bot <span class="text-primary">Ayarları</span></h1>
        </div>
        
        <div class="p-6">
            <div class="mb-6">
                <p class="text-text-light mb-4">
                    Bu sayfada, Discord botunun ayarlarını güncelleyebilirsiniz. Bot token ve Client ID bilgilerini girerek, Discord botunun config.json dosyasını güncelleyebilirsiniz.
                </p>
                
                <?php if (!file_exists($bot_config_file)): ?>
                <div class="bg-[#2a0000] border border-red-500 text-red-300 p-4 rounded-lg mb-4">
                    <strong>Uyarı:</strong> Discord bot config.json dosyası bulunamadı. Lütfen discord-bot dizinini kontrol edin.
                </div>
                <?php endif; ?>
            </div>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="bot_token" class="block text-sm font-medium text-text-light mb-2">Bot Token</label>
                    <input type="password" id="bot_token" name="bot_token" value="<?php echo isset($bot_config['token']) ? sanitizeOutput($bot_config['token']) : ''; ?>" class="w-full px-4 py-2 bg-[#0a0a0a] border border-primary-dark rounded-lg shadow-lg text-text-light focus:outline-none focus:ring-primary focus:border-primary">
                    <p class="mt-1 text-sm text-gray-400">Discord Developer Portal'dan aldığınız bot token.</p>
                </div>
                
                <div>
                    <label for="client_id" class="block text-sm font-medium text-text-light mb-2">Client ID</label>
                    <input type="text" id="client_id" name="client_id" value="<?php echo isset($bot_config['clientId']) ? sanitizeOutput($bot_config['clientId']) : ''; ?>" class="w-full px-4 py-2 bg-[#0a0a0a] border border-primary-dark rounded-lg shadow-lg text-text-light focus:outline-none focus:ring-primary focus:border-primary">
                    <p class="mt-1 text-sm text-gray-400">Discord Developer Portal'da uygulamanızın ID'si.</p>
                </div>
                
                <div>
                    <label for="guild_id" class="block text-sm font-medium text-text-light mb-2">Guild ID</label>
                    <input type="text" id="guild_id" name="guild_id" value="<?php echo sanitizeOutput($discord_config['guild_id']); ?>" class="w-full px-4 py-2 bg-[#0a0a0a] border border-primary-dark rounded-lg shadow-lg text-text-light focus:outline-none focus:ring-primary focus:border-primary" readonly>
                    <p class="mt-1 text-sm text-gray-400">Discord sunucunuzun ID'si. Bu değer, Discord ayarlarından alınmıştır.</p>
                </div>
                
                <div>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-[#5865F2] hover:bg-[#4752C4] focus:outline-none transition-colors duration-300">
                        <i class="fab fa-discord mr-2"></i> Bot Ayarlarını Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
