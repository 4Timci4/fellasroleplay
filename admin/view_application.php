<?php
require_once 'includes/security_check.php'; // Güvenlik kontrolü
require_once 'includes/functions.php';
include 'includes/header.php';

// Başvuru ID'sini al
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Başvuru detaylarını al
$application = getApplication($id);

// Başvuru bulunamadıysa hata mesajı göster
if (!$application) {
    echo '<div class="container mx-auto mt-8">';
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
    echo '<strong class="font-bold">Hata!</strong>';
    echo '<span class="block sm:inline"> Başvuru bulunamadı.</span>';
    echo '</div>';
echo '<div class="mt-4"><a href="index" class="text-blue-500 hover:text-blue-700"><i class="fas fa-arrow-left mr-2"></i>Geri Dön</a></div>';
    echo '</div>';
    include 'includes/footer.php';
    exit;
}

// Başvuru durumu değiştirme işlemi
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'] ?? '';
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    if (in_array($new_status, ['approved', 'rejected'])) {
        if (updateApplicationStatus($id, $new_status, $admin_notes)) {
            $message = 'Başvuru durumu başarıyla güncellendi.';
            
            // Başvuru onaylandıysa ve Discord entegrasyonu aktifse bilgi mesajı ekle
            if ($new_status === 'approved') {
                $discord_config = getDiscordConfig();
                if ($discord_config['enabled']) {
                    // Discord ID'si var mı kontrol et
                    if (!empty($application['discord_id'])) {
                        // Rol atama işleminin sonucunu kontrol et
                        $discord_result = assignDiscordRole($application['discord_id']);
                        if ($discord_result === true) {
                            $message .= ' Discord rolü başarıyla atandı.';
                            
                            // Onay mesajı gönder
                            $approval_message = "Merhaba! Fellas Roleplay sunucusuna yaptığınız başvuru onaylanmıştır. Ses Teyit rolünüz verilmiştir. İyi roller!";
                            $dm_result = sendDiscordDirectMessage($application['discord_id'], $approval_message);
                            
                            if ($dm_result) {
                                $message .= ' Discord özel mesajı başarıyla gönderildi.';
                            } else {
                                $message .= ' Discord özel mesajı gönderilemedi. Kullanıcı DM\'leri kapalı olabilir.';
                            }
                        } else {
                            // Discord rol atama hatasını kontrol et
                            $discordService = new \Services\DiscordService();
                            $userInGuild = $discordService->checkUserInGuild($application['discord_id']);
                            
                            if (!$userInGuild) {
                                $message .= ' <span class="text-red-400">Discord rolü atanamadı: Kullanıcı Discord sunucusunda bulunamadı.</span> Kullanıcı, Discord sunucuya katılmadan rol verilemez. ' . 
                                '<a href="../admin/discord/check_user.php?id=' . $application['discord_id'] . '" class="text-blue-400 hover:underline" target="_blank">Kullanıcı detaylarını kontrol edin</a>.';
                            } else {
                                $message .= ' Discord rolü atanırken bir hata oluştu. Hata ayıklama için sunucu loglarını kontrol edin ve ' . 
                                '<a href="../admin/discord/test_role.php?discord_id=' . $application['discord_id'] . '" class="text-blue-400 hover:underline" target="_blank">rol atama test aracını</a> kullanın.';
                            }
                        }
                    } else {
                        $message .= ' Kullanıcının Discord ID\'si bulunamadı, rol ataması yapılamadı.';
                    }
                } else {
                    $message .= ' Discord entegrasyonu aktif değil, rol ataması yapılmadı. <a href="discord/settings">Discord ayarlarını</a> kontrol edin.';
                }
            }
            // Başvuru reddedildiyse
            else if ($new_status === 'rejected') {
                $discord_config = getDiscordConfig();
                if ($discord_config['enabled']) {
                    // Discord ID'si var mı kontrol et
                    if (!empty($application['discord_id'])) {
                        // Red mesajı gönder
                        $rejection_message = "Merhaba! Fellas Roleplay sunucusuna yaptığınız başvuru maalesef reddedilmiştir. Daha sonra tekrar başvurabilirsiniz.";
                        $dm_result = sendDiscordDirectMessage($application['discord_id'], $rejection_message);
                        
                        if ($dm_result) {
                            $message .= ' Discord özel mesajı başarıyla gönderildi.';
                        } else {
                            $message .= ' Discord özel mesajı gönderilemedi. Kullanıcı DM\'leri kapalı olabilir.';
                        }
                    }
                }
            }
            
            $message_type = 'success';
            
            // Başvuru bilgilerini güncelle
            $application = getApplication($id);
        } else {
            $message = 'Başvuru durumu güncellenirken bir hata oluştu.';
            $message_type = 'error';
        }
    }
}
?>

<div class="container mx-auto">
    <div class="mb-6">
        <a href="javascript:history.back()" class="text-primary hover:text-primary-light transition-colors inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Geri Dön
        </a>
    </div>
    
    <?php if (!empty($message)): ?>
    <div class="mb-6 <?php echo $message_type === 'success' ? 'bg-[#002a00] border border-green-500 text-green-300' : 'bg-[#2a0000] border border-red-500 text-red-300'; ?> px-4 py-3 rounded-lg relative alert-dismissible" role="alert">
        <span class="block sm:inline"><?php echo $message; ?></span>
    </div>
    <?php endif; ?>
    
    <div class="card-bg rounded-lg shadow-lg overflow-hidden border border-primary-dark">
        <div class="px-6 py-4 border-b border-primary-dark flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <h1 class="text-2xl font-bold text-text-light">Başvuru <span class="text-primary">Detayları</span></h1>
            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusClass($application['status']); ?>">
                <?php echo getStatusText($application['status']); ?>
            </span>
        </div>
        
        <div class="p-6">
            <!-- Başvuru Bilgileri -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-primary-dark text-primary">Kişisel Bilgiler</h2>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-400">Discord ID</p>
                        <p class="font-medium text-text-light"><?php echo sanitizeOutput($application['discord_id']); ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-400">Ad</p>
                        <p class="font-medium text-text-light"><?php echo sanitizeOutput($application['first_name']); ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-400">Yaş</p>
                        <p class="font-medium text-text-light"><?php echo sanitizeOutput($application['age']); ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-400">Sunucuyu Tercih Etme Sebebi</p>
                        <p class="font-medium text-text-light"><?php echo nl2br(sanitizeOutput($application['server_reason'])); ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-400">Roleplay İçin Günlük Saat</p>
                        <p class="font-medium text-text-light"><?php echo sanitizeOutput($application['rp_hours']); ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-400">Yayın Platformu</p>
                        <p class="font-medium text-text-light">
                            <?php 
                            $streaming = sanitizeOutput($application['streaming']);
                            echo ucfirst($streaming); 
                            
                            if ($streaming === 'kick' && !empty($application['streaming_kick_text'])) {
                                echo ' <a href="https://kick.com/' . sanitizeOutput($application['streaming_kick_text']) . '" target="_blank" class="text-green-400 hover:text-green-300 transition-colors">kick.com/' . sanitizeOutput($application['streaming_kick_text']) . '</a>';
                            } 
                            else if ($streaming === 'twitch' && !empty($application['streaming_twitch_text'])) {
                                echo ' <a href="https://twitch.tv/' . sanitizeOutput($application['streaming_twitch_text']) . '" target="_blank" class="text-purple-400 hover:text-purple-300 transition-colors">twitch.tv/' . sanitizeOutput($application['streaming_twitch_text']) . '</a>';
                            }
                            else if ($streaming === 'youtube' && !empty($application['streaming_youtube_text'])) {
                                echo ' <a href="https://youtube.com/' . sanitizeOutput($application['streaming_youtube_text']) . '" target="_blank" class="text-red-500 hover:text-red-400 transition-colors">youtube.com/' . sanitizeOutput($application['streaming_youtube_text']) . '</a>';
                            }
                            else if ($streaming === 'other' && !empty($application['stream_platform'])) {
                                // Eğer stream_platform bir URL ise, tıklanabilir link olarak göster
                                $platform = sanitizeOutput($application['stream_platform']);
                                if (filter_var($platform, FILTER_VALIDATE_URL)) {
                                    echo ' <a href="' . $platform . '" target="_blank" class="text-blue-400 hover:text-blue-300 transition-colors">' . $platform . '</a>';
                                } else {
                                    echo ' (' . $platform . ')';
                                }
                            }
                            else if ($streaming === 'no') {
                                echo ' (Yayın yapmıyor)';
                            }
                            ?>
                        </p>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-primary-dark text-primary">IC Bilgiler</h2>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-400">Karakter Adı</p>
                        <p class="font-medium text-text-light"><?php echo sanitizeOutput($application['character_name']); ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-400">Karakter Hikayesi</p>
                        <textarea readonly class="w-full px-4 py-3 bg-[#0a0a0a] border border-primary-dark rounded-lg shadow-lg text-text-light focus:outline-none focus:ring-primary focus:border-primary h-[300px] resize-y custom-scrollbar"><?php echo sanitizeOutput($application['character_story']); ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-400">Karakteri 3 Kelime ile Özetleme</p>
                        <p class="font-medium text-text-light"><?php echo sanitizeOutput($application['character_summary']); ?></p>
                    </div>
                    
                    <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-primary-dark mt-8 text-primary">Roleplay Deneyimi</h2>
                    
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-8 mb-4">
                            <p class="text-sm text-gray-400">Önceden Oynanan Sunucular</p>
                            <textarea readonly class="w-full px-4 py-3 bg-[#0a0a0a] border border-primary-dark rounded-lg shadow-lg text-text-light focus:outline-none focus:ring-primary focus:border-primary h-[100px] resize-y custom-scrollbar"><?php echo sanitizeOutput($application['previous_servers']); ?></textarea>
                        </div>
                        
                        <div class="col-span-4 mb-4">
                            <p class="text-sm text-gray-400">FiveM Oynama Süresi</p>
                            <p class="font-medium text-text-light"><?php echo sanitizeOutput($application['fivem_experience']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-primary-dark pt-6">
                <h2 class="text-lg font-semibold mb-4 text-primary">Başvuru Bilgileri</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-400">Başvuru Tarihi</p>
                        <p class="font-medium text-text-light"><?php echo formatDate($application['created_at']); ?></p>
                    </div>
                    
                    <?php if ($application['status'] !== 'unread'): ?>
                    <div>
                        <p class="text-sm text-gray-400">Durum Değişikliği Tarihi</p>
                        <p class="font-medium text-text-light"><?php echo formatDate($application['updated_at']); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-400">İşlemi Yapan Admin</p>
                        <p class="font-medium text-text-light">
                            <?php 
                            // Önce Discord bilgilerini kontrol et
                            if (!empty($application['admin_discord_username'])): 
                            ?>
                                <span class="text-primary"><?php echo sanitizeOutput($application['admin_discord_username']); ?></span>
                            <?php
                            // Sadece Discord ID varsa kullanıcı adını almaya çalış
                            elseif (!empty($application['admin_discord_id'])): 
                                $discordUsername = getDiscordUsername($application['admin_discord_id']);
                            ?>
                                <span class="text-primary">
                                    <?php echo empty($discordUsername) ? 'Discord: ' . $application['admin_discord_id'] : $discordUsername; ?>
                                </span>
                            <?php
                            // Eski admin_id sistemi kaldırıldı
                            elseif (!empty($application['admin_id'])): 
                            ?>
                                <span class="text-primary">
                                    Admin ID: <?php echo $application['admin_id']; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-500">Bilinmiyor</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Admin Notları ve Durum Değiştirme Formu -->
                <form method="POST" class="mt-6">
                    <div class="mb-6">
                        <label for="admin_notes" class="block text-sm font-medium text-text-light mb-2">Admin Notları</label>
                        <textarea id="admin_notes" name="admin_notes" rows="4" class="w-full px-4 py-3 bg-[#0a0a0a] border border-primary-dark rounded-lg shadow-lg text-text-light focus:outline-none focus:ring-primary focus:border-primary resize-y custom-scrollbar"><?php echo sanitizeOutput($application['admin_notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <?php if ($application['status'] === 'unread'): ?>
                    <div class="flex flex-col sm:flex-row gap-3 sm:space-x-4">
                        <button type="submit" name="status" value="approved" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-[#002a00] hover:bg-green-700 focus:outline-none transition-colors duration-300">
                            <i class="fas fa-check mr-2"></i> Başvuruyu Onayla
                        </button>
                        <button type="submit" name="status" value="rejected" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-[#2a0000] hover:bg-red-700 focus:outline-none transition-colors duration-300">
                            <i class="fas fa-times mr-2"></i> Başvuruyu Reddet
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="flex">
                        <button type="submit" name="status" value="<?php echo $application['status']; ?>" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-dark focus:outline-none transition-colors duration-300">
                            <i class="fas fa-save mr-2"></i> Notları Güncelle
                        </button>
                    </div>
                    <?php endif; ?>
                </form>
                
                <!-- Silme Butonu - Sadece Yöneticiler İçin -->
                <?php if (isAdmin()): ?>
                <div class="mt-4">
                    <a href="delete_application?id=<?php echo $application['id']; ?>&redirect=index" 
                       onclick="return confirm('Bu başvuruyu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');" 
                       class="inline-flex justify-center py-2 px-4 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-red-700 hover:bg-red-800 focus:outline-none transition-colors duration-300">
                        <i class="fas fa-trash-alt mr-2"></i> Başvuruyu Sil
                    </a>
                </div>
                <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
