<?php
namespace Services;

use Core\Config;

/**
 * Discord ayarları yönetimi için servis sınıfı
 */
class DiscordConfigService {
    private static $instance = null;
    private $configPath;
    
    /**
     * Singleton pattern constructor
     */
    private function __construct() {
        $this->configPath = dirname(dirname(dirname(__FILE__))) . '/admin/includes/config.php';
    }
    
    /**
     * Singleton pattern instance getter
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Discord ayarlarını al
     * 
     * @return array Discord konfigürasyon ayarları
     */
    public function getConfig() {
        return [
            'token' => Config::get('discord.token', ''),
            'guild_id' => Config::get('discord.guild_id', ''),
            'role_id' => Config::get('discord.whitelist_role_id', ''),
            'enabled' => Config::get('discord.enabled', false),
            'api' => [
                'endpoint' => Config::get('discord.api.endpoint', ''),
                'dm_endpoint' => Config::get('discord.api.dm_endpoint', '')
            ]
        ];
    }
    
    /**
     * Discord ayarlarını güncelle
     * 
     * @param array $config Güncellenecek ayarlar
     * @return bool İşlem başarılı mı
     */
    public function updateConfig($config) {
        try {
            // Config dosyasını oku
            $configContent = file_get_contents($this->configPath);
            
            if ($configContent === false) {
                error_log("DiscordConfigService: Config dosyası okunamadı: {$this->configPath}");
                return false;
            }
            
            // Token değerini güncelle
            if (isset($config['token'])) {
                $configContent = preg_replace(
                    "/'token' => '.*?'/",
                    "'token' => '" . addslashes($config['token']) . "'",
                    $configContent
                );
            }
            
            // Guild ID değerini güncelle
            if (isset($config['guild_id'])) {
                $configContent = preg_replace(
                    "/'guild_id' => '.*?'/",
                    "'guild_id' => '" . addslashes($config['guild_id']) . "'",
                    $configContent
                );
            }
            
            // Role ID değerini güncelle
            if (isset($config['role_id'])) {
                $configContent = preg_replace(
                    "/'role_id' => '.*?'/",
                    "'role_id' => '" . addslashes($config['role_id']) . "'",
                    $configContent
                );
                
                // whitelist_role_id değerini de güncelle
                $configContent = preg_replace(
                    "/'whitelist_role_id' => '.*?'/",
                    "'whitelist_role_id' => '" . addslashes($config['role_id']) . "'",
                    $configContent
                );
            }
            
            // Enabled değerini güncelle
            if (isset($config['enabled'])) {
                $enabled = $config['enabled'] ? 'true' : 'false';
                $configContent = preg_replace(
                    "/'enabled' => (?:true|false)/",
                    "'enabled' => " . $enabled,
                    $configContent
                );
            }
            
            // API endpoint değerini güncelle
            if (isset($config['api']['endpoint'])) {
                $configContent = preg_replace(
                    "/'endpoint' => '.*?'/",
                    "'endpoint' => '" . addslashes($config['api']['endpoint']) . "'",
                    $configContent
                );
            }
            
            // DM endpoint değerini güncelle
            if (isset($config['api']['dm_endpoint'])) {
                $configContent = preg_replace(
                    "/'dm_endpoint' => '.*?'/",
                    "'dm_endpoint' => '" . addslashes($config['api']['dm_endpoint']) . "'",
                    $configContent
                );
            }
            
            // Dosyaya yaz
            $result = file_put_contents($this->configPath, $configContent);
            
            if ($result === false) {
                error_log("DiscordConfigService: Config dosyasına yazılamadı: {$this->configPath}");
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("DiscordConfigService Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Discord bot ayarlarını Json formatında güncelle
     * 
     * @return bool İşlem başarılı mı
     */
    public function updateBotConfig() {
        try {
            $discordConfig = $this->getConfig();
            $botConfigPath = dirname(dirname(dirname(__FILE__))) . '/discord-bot/config.json';
            
            if (empty($discordConfig['token']) || empty($discordConfig['guild_id'])) {
                error_log("DiscordConfigService: Bot konfigürasyonu için gerekli alanlar eksik");
                return false;
            }
            
            // Bot config yapısını oluştur
            $botConfig = [
                'token' => $discordConfig['token'],
                'clientId' => '', // Bu değeri Discord Developer Portal'dan manuel olarak almak gerekiyor
                'guildId' => $discordConfig['guild_id'],
                'whitelistRoleId' => $discordConfig['role_id']
            ];
            
            // Eğer var olan bir config dosyası varsa, clientId değerini koru
            if (file_exists($botConfigPath)) {
                $existingConfig = json_decode(file_get_contents($botConfigPath), true);
                if (json_last_error() === JSON_ERROR_NONE && isset($existingConfig['clientId'])) {
                    $botConfig['clientId'] = $existingConfig['clientId'];
                }
            }
            
            // Json formatına dönüştür ve dosyaya yaz
            $jsonContent = json_encode($botConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $result = file_put_contents($botConfigPath, $jsonContent);
            
            if ($result === false) {
                error_log("DiscordConfigService: Bot config dosyasına yazılamadı: {$botConfigPath}");
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("DiscordConfigService Bot Config Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Discord botunun geçerli olup olmadığını kontrol et
     * 
     * @return array Kontrol sonuçları
     */
    public function validateBotSettings() {
        $config = $this->getConfig();
        $results = [
            'token_valid' => false,
            'guild_valid' => false,
            'role_valid' => false,
            'bot_perms' => [],
            'message' => ''
        ];
        
        // Discord Service nesnesini oluştur
        try {
            $discord = new DiscordService();
            
            // Token kontrolü
            $results['token_valid'] = !empty($config['token']);
            
            // Guild ID kontrolü - basit doğrulama
            $results['guild_valid'] = !empty($config['guild_id']) && preg_match('/^\d{17,19}$/', $config['guild_id']);
            
            // Role ID kontrolü - basit doğrulama
            $results['role_valid'] = !empty($config['role_id']) && preg_match('/^\d{17,19}$/', $config['role_id']);
            
            // Bot izinleri kontrolü - gerçek API çağrısı
            if ($results['token_valid'] && $results['guild_valid']) {
                // Temel guild bilgilerini çekmeye çalış
                $guildMember = $discord->getMemberDetails($config['guild_id']);
                
                if ($guildMember) {
                    $results['bot_perms'] = [
                        'view_guild' => true,
                        'manage_roles' => true // Bu detaylı bir kontrol gerektirebilir
                    ];
                } else {
                    $results['bot_perms'] = [
                        'view_guild' => false,
                        'manage_roles' => false
                    ];
                    $results['message'] = 'Bot sunucuya erişemiyor. Token veya Guild ID kontrol edin.';
                }
            } else {
                $results['message'] = 'Token, Guild ID veya Role ID eksik veya geçersiz format.';
            }
        } catch (\Exception $e) {
            $results['message'] = 'Discord bağlantısı sırasında hata: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Discord rolünü test etmek için bir kullanıcıya rol ataması yap
     * 
     * @param string $userId Discord kullanıcı ID
     * @return array İşlem sonucu
     */
    public function testRoleAssignment($userId) {
        $result = [
            'success' => false,
            'message' => '',
            'details' => []
        ];
        
        if (empty($userId)) {
            $result['message'] = 'Kullanıcı ID boş olamaz';
            return $result;
        }
        
        try {
            $discord = new DiscordService();
            
            // Kullanıcının sunucuda olup olmadığını kontrol et
            $userInGuild = $discord->checkUserInGuild($userId);
            $result['details']['user_in_guild'] = $userInGuild;
            
            if (!$userInGuild) {
                $result['message'] = 'Kullanıcı Discord sunucusunda bulunamadı';
                return $result;
            }
            
            // Kullanıcının role sahip olup olmadığını kontrol et
            $config = $this->getConfig();
            $userHasRole = $discord->checkUserHasRole($userId, $config['role_id']);
            $result['details']['user_has_role'] = $userHasRole;
            
            if ($userHasRole) {
                $result['success'] = true;
                $result['message'] = 'Kullanıcı zaten bu role sahip';
                return $result;
            }
            
            // Rol atama
            $roleAssigned = $discord->assignRole($userId);
            $result['details']['role_assigned'] = $roleAssigned;
            
            if ($roleAssigned) {
                $result['success'] = true;
                $result['message'] = 'Rol başarıyla atandı';
            } else {
                $result['message'] = 'Rol atama başarısız oldu';
            }
        } catch (\Exception $e) {
            $result['message'] = 'Rol atama sırasında hata: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Bot izinlerini kontrol et
     * 
     * @return array İzin kontrolü sonuçları
     */
    public function checkBotPermissions() {
        $result = [
            'success' => false,
            'message' => '',
            'details' => []
        ];
        
        try {
            $config = $this->getConfig();
            
            // Discord entegrasyonu aktif mi?
            if (!$config['enabled']) {
                $result['message'] = 'Discord entegrasyonu aktif değil. Ayarlar sayfasından aktifleştirin.';
                return $result;
            }
            
            // Token girilmiş mi?
            if (empty($config['token'])) {
                $result['message'] = 'Discord bot token girilmemiş. Ayarlar sayfasından token ekleyin.';
                return $result;
            }
            
            $discord = new DiscordService();
            
            // Bot bilgilerini al (@me endpoint'i)
            $botInfo = $this->getBotInfo($config['token']);
            
            if (!$botInfo) {
                $result['message'] = 'Bot bilgileri alınamadı. Token geçerli mi kontrol edin.';
                return $result;
            }
            
            $result['details']['bot'] = $botInfo;
            
            // Sunucu bilgilerini al
            $guildInfo = $this->getGuildInfo($config['token'], $config['guild_id']);
            
            if (!$guildInfo) {
                $result['message'] = 'Sunucu bilgileri alınamadı. Sunucu ID geçerli mi kontrol edin.';
                return $result;
            }
            
            $result['details']['guild'] = $guildInfo;
            
            // Bot'un sunucudaki rollerini al
            $botRoles = $this->getBotRoles($config['token'], $config['guild_id'], $botInfo['id']);
            $result['details']['bot_roles'] = $botRoles;
            
            // Sunucudaki tüm rolleri al
            $guildRoles = $this->getGuildRoles($config['token'], $config['guild_id']);
            $result['details']['guild_roles'] = $guildRoles;
            
            // Verilecek rol bilgisini al
            $targetRoleId = $config['role_id'];
            $targetRole = null;
            $botHighestRole = null;
            $botHighestPosition = -1;
            
            // Bot'un en yüksek rolünü ve hedef rolü bul
            foreach ($guildRoles as $role) {
                if (in_array($role['id'], $botRoles) && $role['position'] > $botHighestPosition) {
                    $botHighestRole = $role;
                    $botHighestPosition = $role['position'];
                }
                
                if ($role['id'] === $targetRoleId) {
                    $targetRole = $role;
                }
            }
            
            $result['details']['target_role'] = $targetRole;
            $result['details']['bot_highest_role'] = $botHighestRole;
            
            // Rol hiyerarşisini kontrol et
            if (!$targetRole) {
                $result['message'] = 'Verilecek rol bulunamadı. Rol ID geçerli mi kontrol edin.';
                return $result;
            }
            
            if (!$botHighestRole) {
                $result['message'] = 'Bot\'un hiçbir rolü yok. Bot\'a bir rol atayın.';
                return $result;
            }
            
            // Bot'un en yüksek rolü, verilecek rolden daha yüksek mi?
            if ($botHighestPosition <= $targetRole['position']) {
                $result['message'] = 'Bot\'un en yüksek rolü, verilecek rolden daha düşük. Bot\'a daha yüksek bir rol atayın.';
                return $result;
            }
            
            // Tüm kontroller başarılı
            $result['success'] = true;
            $result['message'] = 'Bot\'un rol atama yetkisi var. Rol hiyerarşisi doğru.';
            
        } catch (\Exception $e) {
            $result['message'] = 'Bot izinleri kontrol edilirken hata: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Bot bilgilerini al
     * 
     * @param string $token Discord bot token
     * @return array|null Bot bilgileri
     */
    private function getBotInfo($token) {
        return $this->makeDiscordRequest('https://discord.com/api/v10/users/@me', $token);
    }
    
    /**
     * Sunucu bilgilerini al
     * 
     * @param string $token Discord bot token
     * @param string $guildId Sunucu ID
     * @return array|null Sunucu bilgileri
     */
    private function getGuildInfo($token, $guildId) {
        return $this->makeDiscordRequest("https://discord.com/api/v10/guilds/{$guildId}", $token);
    }
    
    /**
     * Bot'un sunucudaki rollerini al
     * 
     * @param string $token Discord bot token
     * @param string $guildId Sunucu ID
     * @param string $botId Bot ID
     * @return array|null Bot rolleri
     */
    private function getBotRoles($token, $guildId, $botId) {
        $member = $this->makeDiscordRequest("https://discord.com/api/v10/guilds/{$guildId}/members/{$botId}", $token);
        return $member['roles'] ?? [];
    }
    
    /**
     * Sunucudaki tüm rolleri al
     * 
     * @param string $token Discord bot token
     * @param string $guildId Sunucu ID
     * @return array|null Sunucu rolleri
     */
    private function getGuildRoles($token, $guildId) {
        return $this->makeDiscordRequest("https://discord.com/api/v10/guilds/{$guildId}/roles", $token);
    }
    
    /**
     * Discord API'ye istek gönder
     * 
     * @param string $url API endpoint
     * @param string $token Discord bot token
     * @param string $method HTTP metodu
     * @param array|null $data İstek verisi
     * @return array|null API yanıtı
     */
    private function makeDiscordRequest($url, $token, $method = 'GET', $data = null) {
        $ch = curl_init();
        
        $headers = [
            'Authorization: Bot ' . $token,
            'Content-Type: application/json',
        ];
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method !== 'GET' && $data !== null) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }
        
        error_log("Discord API request failed. URL: {$url}, HTTP Code: {$httpCode}, Response: {$response}");
        return null;
    }
}
