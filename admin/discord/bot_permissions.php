<?php
require_once '../includes/functions.php';
require_once '../../includes/bootstrap.php';

use Services\DiscordConfigService;

include '../includes/header.php';

// Discord Config Service örneğini al
$discordConfigService = DiscordConfigService::getInstance();

// Discord yapılandırma ayarlarını al
$discord_config = $discordConfigService->getConfig();

// Mesaj değişkenleri
$message = '';
$message_type = '';
$permissions_info = [];

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bot izinlerini kontrol et
    $checkResult = $discordConfigService->checkBotPermissions();
    
    // Sonuçları değişkenlere ata
    $message = $checkResult['message'];
    $message_type = $checkResult['success'] ? 'success' : 'error';
    
    // İzin bilgilerini al (template için)
    if (isset($checkResult['details'])) {
        $permissions_info = $checkResult['details'];
    }
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
            <h1 class="text-2xl font-bold text-text-light">Discord Bot <span class="text-primary">İzin Kontrolü</span></h1>
        </div>
        
        <div class="p-6">
            <div class="mb-6">
                <p class="text-text-light mb-4">
                    Bu sayfa, Discord bot'un rol atama yetkisine sahip olup olmadığını kontrol eder.
                    Bot'un rol atama yetkisine sahip olması için, bot'un en yüksek rolünün, verilecek rolden daha yüksek olması gerekir.
                </p>
                
                <?php if (!$discord_config['enabled']): ?>
                <div class="bg-[#2a0000] border border-red-500 text-red-300 p-4 rounded-lg mb-4">
                    <strong>Uyarı:</strong> Discord entegrasyonu aktif değil. Ayarlar sayfasından aktifleştirin.
                </div>
                <?php endif; ?>
                
                <?php if (empty($discord_config['token'])): ?>
                <div class="bg-[#2a0000] border border-red-500 text-red-300 p-4 rounded-lg mb-4">
                    <strong>Uyarı:</strong> Discord bot token girilmemiş. Ayarlar sayfasından token ekleyin.
                </div>
                <?php endif; ?>
            </div>
            
            <form method="POST" class="space-y-6">
                <div>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-[#5865F2] hover:bg-[#4752C4] focus:outline-none transition-colors duration-300">
                        <i class="fab fa-discord mr-2"></i> Bot İzinlerini Kontrol Et
                    </button>
                </div>
            </form>
            
            <?php if (!empty($permissions_info)): ?>
            <div class="mt-8">
                <h2 class="text-lg font-semibold mb-4 text-primary">Bot Bilgileri</h2>
                
                <?php if (isset($permissions_info['bot'])): ?>
                <div class="bg-[#0a0a0a] p-4 rounded-lg border border-primary-dark mb-6">
                    <div class="flex items-center mb-4">
                        <?php if (!empty($permissions_info['bot']['avatar'])): ?>
                        <img src="https://cdn.discordapp.com/avatars/<?php echo $permissions_info['bot']['id']; ?>/<?php echo $permissions_info['bot']['avatar']; ?>.png" alt="Bot Avatar" class="w-12 h-12 rounded-full mr-4">
                        <?php endif; ?>
                        <div>
                            <p class="font-medium text-text-light"><?php echo sanitizeOutput($permissions_info['bot']['username']); ?></p>
                            <p class="text-sm text-gray-400">ID: <?php echo sanitizeOutput($permissions_info['bot']['id']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($permissions_info['guild'])): ?>
                <h2 class="text-lg font-semibold mb-4 text-primary">Sunucu Bilgileri</h2>
                <div class="bg-[#0a0a0a] p-4 rounded-lg border border-primary-dark mb-6">
                    <div class="flex items-center mb-4">
                        <?php if (!empty($permissions_info['guild']['icon'])): ?>
                        <img src="https://cdn.discordapp.com/icons/<?php echo $permissions_info['guild']['id']; ?>/<?php echo $permissions_info['guild']['icon']; ?>.png" alt="Server Icon" class="w-12 h-12 rounded-full mr-4">
                        <?php endif; ?>
                        <div>
                            <p class="font-medium text-text-light"><?php echo sanitizeOutput($permissions_info['guild']['name']); ?></p>
                            <p class="text-sm text-gray-400">ID: <?php echo sanitizeOutput($permissions_info['guild']['id']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($permissions_info['target_role']) && isset($permissions_info['bot_highest_role'])): ?>
                <h2 class="text-lg font-semibold mb-4 text-primary">Rol Hiyerarşisi</h2>
                <div class="bg-[#0a0a0a] p-4 rounded-lg border border-primary-dark mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-md font-semibold mb-2 text-primary">Bot'un En Yüksek Rolü</h3>
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full mr-2" style="background-color: #<?php echo dechex($permissions_info['bot_highest_role']['color']); ?>"></div>
                                <p class="font-medium text-text-light"><?php echo sanitizeOutput($permissions_info['bot_highest_role']['name']); ?></p>
                            </div>
                            <p class="text-sm text-gray-400">Pozisyon: <?php echo sanitizeOutput($permissions_info['bot_highest_role']['position']); ?></p>
                        </div>
                        
                        <div>
                            <h3 class="text-md font-semibold mb-2 text-primary">Verilecek Rol</h3>
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full mr-2" style="background-color: #<?php echo dechex($permissions_info['target_role']['color']); ?>"></div>
                                <p class="font-medium text-text-light"><?php echo sanitizeOutput($permissions_info['target_role']['name']); ?></p>
                            </div>
                            <p class="text-sm text-gray-400">Pozisyon: <?php echo sanitizeOutput($permissions_info['target_role']['position']); ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <?php if ($permissions_info['bot_highest_role']['position'] > $permissions_info['target_role']['position']): ?>
                        <div class="bg-[#002a00] border border-green-500 text-green-300 p-3 rounded-lg">
                            <i class="fas fa-check-circle mr-2"></i> Bot'un en yüksek rolü, verilecek rolden daha yüksek. Rol atama yetkisi var.
                        </div>
                        <?php else: ?>
                        <div class="bg-[#2a0000] border border-red-500 text-red-300 p-3 rounded-lg">
                            <i class="fas fa-times-circle mr-2"></i> Bot'un en yüksek rolü, verilecek rolden daha düşük. Rol atama yetkisi yok.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="mt-6 bg-[#0a0a0a] p-4 rounded-lg border border-primary-dark">
                <h3 class="text-lg font-semibold mb-2 text-primary">Önemli Notlar</h3>
                <ul class="list-disc list-inside space-y-2 text-text-light">
                    <li>Discord bot'un rol atama yetkisine sahip olması için, bot'un en yüksek rolünün, verilecek rolden daha yüksek olması gerekir.</li>
                    <li>Bot'un rolünü, Discord sunucu ayarlarından yükseltebilirsiniz.</li>
                    <li>Bot'un "Üyeleri Yönet" (Manage Members) yetkisine sahip olması gerekir.</li>
                    <li>Bot'un sunucuda olduğundan emin olun.</li>
                    <li>Hata durumunda, sunucu loglarını kontrol edin.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
