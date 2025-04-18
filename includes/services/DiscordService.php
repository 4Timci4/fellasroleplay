<?php
namespace Services;

use Core\Config;
use Core\Cache;

/**
 * Discord API işlemleri için servis sınıfı
 */
class DiscordService {
    private $token;
    private $guildId;
    private $whitelistRoleId;
    private $apiEndpoint;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->token = Config::get('discord.token');
        $this->guildId = Config::get('discord.guild_id');
        $this->whitelistRoleId = Config::get('discord.whitelist_role_id');
        $this->apiEndpoint = Config::get('discord.api.endpoint');
        
        if (empty($this->token)) {
            throw new \Exception("Discord API token bulunamadı");
        }
    }
    
    /**
     * Discord API request gönder
     * 
     * @param string $endpoint API endpoint
     * @param string $method HTTP metodu (GET, POST, PUT, DELETE, vb.)
     * @param array|null $data Gönderilecek veri
     * @return array|null API yanıtı veya hata durumunda null
     */
    private function sendRequest($endpoint, $method = 'GET', $data = null) {
        $url = "https://discord.com/api/v10" . $endpoint;
        
        // İstek detaylarını logla
        error_log("Discord API Request: {$method} {$url}");
        if ($data !== null) {
            error_log("Discord API Request Data: " . json_encode($data));
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bot ' . $this->token,
            'Content-Type: application/json'
        ]);
        
        if ($method !== 'GET' && $data !== null) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);
        
        // CURL hatalarını logla
        if ($curlError) {
            error_log("Discord API CURL Error: {$curlError}");
            return null;
        }
        
        // Tüm hata durumlarını logla
        if ($httpCode >= 400) {
            error_log("Discord API Error: {$url} returned status {$httpCode}");
            error_log("Discord API Error Response: {$response}");
            
            // JSON yanıtı detaylı olarak parse et
            $errorData = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($errorData['code'])) {
                error_log("Discord API Error Code: {$errorData['code']}, Message: " . ($errorData['message'] ?? 'Unknown error'));
            }
            
            return null;
        }
        
        // Başarılı yanıtı logla
        error_log("Discord API Success: {$url} returned status {$httpCode}");
        
        // PUT ve DELETE metodları genellikle 204 No Content dönebilir, boş yanıt olabilir
        if (empty($response) && in_array($method, ['PUT', 'DELETE']) && $httpCode >= 200 && $httpCode < 300) {
            return [];
        }
        
        // Yanıtı JSON olarak parse et
        $parsedResponse = json_decode($response, true);
        
        // JSON parse hatası varsa logla ve boş bir dizi dön
        if (json_last_error() !== JSON_ERROR_NONE && !empty($response)) {
            error_log("Discord API JSON parse error: " . json_last_error_msg() . ", Raw response: {$response}");
            return [];
        }
        
        return $parsedResponse;
    }
    
    /**
     * Kullanıcı bilgisini getir
     */
    public function getUserInfo($userId) {
        if (empty($userId)) {
            return null;
        }
        
        // Önbellekten kontrol et
        $cacheKey = "discord_user_{$userId}";
        $cachedUser = Cache::get($cacheKey);
        
        if ($cachedUser !== null) {
            return $cachedUser;
        }
        
        // API'den kullanıcı bilgisini çek
        $endpoint = "/guilds/{$this->guildId}/members/{$userId}";
        $userData = $this->sendRequest($endpoint);
        
        if (!$userData) {
            return null;
        }
        
        // Kullanıcı bilgisini formatla
        $formattedUser = [
            'id' => $userId,
            'username' => $userData['user']['username'] ?? 'Unknown',
            'avatar' => $userData['user']['avatar'] ?? null,
            'roles' => $userData['roles'] ?? []
        ];
        
        // Önbelleğe kaydet (15 dakika)
        Cache::set($cacheKey, $formattedUser, 900);
        
        return $formattedUser;
    }
    
    /**
     * Kullanıcının tüm üyelik detaylarını getir
     * 
     * @param string $userId Discord kullanıcı ID
     * @param bool $useCache Önbellek kullanılsın mı
     * @return array|null Üyelik detayları veya null
     */
    public function getMemberDetails($userId, $useCache = false) {
        if (empty($userId)) {
            return null;
        }
        
        // Önbellekten kontrol et
        $cacheKey = "discord_member_{$userId}";
        
        if ($useCache) {
            $cachedMember = Cache::get($cacheKey);
            if ($cachedMember !== null) {
                return $cachedMember;
            }
        }
        
        // API'den kullanıcı bilgisini çek
        $endpoint = "/guilds/{$this->guildId}/members/{$userId}";
        $memberData = $this->sendRequest($endpoint);
        
        if (!$memberData) {
            return null;
        }
        
        // Önbelleğe kaydet (5 dakika - kısa süre çünkü roller değişebilir)
        if ($useCache) {
            Cache::set($cacheKey, $memberData, 300);
        }
        
        return $memberData;
    }
    
    /**
     * Belirli bir role sahip üyeleri getir
     */
    public function getMembersWithRole($roleId, $limit = 10) {
        if (empty($roleId)) {
            return [];
        }
        
        // Önbellekten kontrol et
        $cacheKey = "discord_role_members_{$roleId}_{$limit}";
        $cachedMembers = Cache::get($cacheKey);
        
        if ($cachedMembers !== null) {
            return $cachedMembers;
        }
        
        // Tüm üyeleri çekiyoruz (Discord API sınırlamaları nedeniyle)
        $endpoint = "/guilds/{$this->guildId}/members?limit=1000";
        $members = $this->sendRequest($endpoint);
        
        if (!$members || !is_array($members)) {
            return [];
        }
        
        // Role sahip üyeleri filtrele
        $roleMembers = [];
        foreach ($members as $member) {
            if (isset($member['roles']) && in_array($roleId, $member['roles'])) {
                $roleMembers[] = [
                    'id' => $member['user']['id'],
                    'username' => $member['user']['username'],
                    'avatar' => $member['user']['avatar'] ?? null,
                    'discriminator' => $member['user']['discriminator'] ?? null,
                    'nick' => $member['nick'] ?? null
                ];
                
                if (count($roleMembers) >= $limit) {
                    break;
                }
            }
        }
        
        // Önbelleğe kaydet (15 dakika)
        Cache::set($cacheKey, $roleMembers, 900);
        
        return $roleMembers;
    }
    
    /**
     * Kullanıcının Discord sunucusunda üye olup olmadığını kontrol eder
     *
     * @param string $discordId Discord kullanıcı ID'si
     * @return bool Kullanıcı sunucuda üye ise true, değilse false
     */
    public function checkUserInGuild($discordId) {
        if (empty($discordId) || !is_string($discordId) || !preg_match('/^\d{17,20}$/', $discordId)) {
            error_log("DiscordService: Geçersiz Discord ID formatı: $discordId");
            return false;
        }
        
        // Bot API üzerinden kontrol et
        if (!empty($this->apiEndpoint)) {
            $endpoint = $this->apiEndpoint . '/check-role/' . $discordId;
            
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                $responseData = json_decode($response, true);
                
                // Kullanıcı Discord'da var ama sunucuda yoksa
                if (isset($responseData['userExists']) && $responseData['userExists'] && 
                    isset($responseData['errorCode']) && $responseData['errorCode'] === 'USER_NOT_IN_GUILD') {
                    error_log("DiscordService: Kullanıcı Discord'da var ama sunucuda yok: $discordId");
                    return false;
                }
                
                // Diğer hatalar
                error_log("DiscordService: Kullanıcı sunucuda mı kontrolü başarısız: $discordId, HTTP: $httpCode");
                
                // Doğrudan API üzerinden tekrar dene
                $endpoint = "/guilds/{$this->guildId}/members/{$discordId}";
                $response = $this->sendRequest($endpoint);
                return $response !== null;
            }
            
            return true;
        }
        
        // Doğrudan API üzerinden kontrol et
        $endpoint = "/guilds/{$this->guildId}/members/{$discordId}";
        $response = $this->sendRequest($endpoint);
        
        return $response !== null;
    }
    
    /**
     * Kullanıcının belirli bir role sahip olup olmadığını kontrol et
     */
    public function checkUserHasRole($userId, $roleId) {
        if (empty($userId) || empty($roleId)) {
            return false;
        }
        
        $userInfo = $this->getUserInfo($userId);
        
        if (!$userInfo || !isset($userInfo['roles'])) {
            return false;
        }
        
        return in_array($roleId, $userInfo['roles']);
    }
    
    /**
     * Discord sunucusunda bir kullanıcıya whitelist rolü atar.
     *
     * @param string $discordId Discord kullanıcı ID'si
     * @return bool Başarılı olduğunda true, başarısız olduğunda false döner
     */
    public function assignRole($discordId) {
        if (empty($discordId) || !is_string($discordId) || !preg_match('/^\d{17,20}$/', $discordId)) {
            error_log("DiscordService: Geçersiz Discord ID formatı: $discordId");
            return false;
        }
        
        // Kullanıcı Discord ID'si ile Guild ID aynı olmamalı
        if ($discordId === $this->guildId) {
            error_log("DiscordService: Kullanıcı ID ile Guild ID aynı: $discordId");
            return false;
        }
        
        // Önce kullanıcının Discord sunucusunda olup olmadığını kontrol et
        if (!$this->checkUserInGuild($discordId)) {
            error_log("DiscordService: Kullanıcı Discord sunucusunda bulunamadı: $discordId");
            return false;
        }

        // Bot API varsa, onu kullan
        if (!empty($this->apiEndpoint)) {
            // Önce kullanıcının rol durumunu kontrol et
            $endpoint = $this->apiEndpoint . '/check-role/' . $discordId;
            
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("DiscordService: Rol kontrolü başarısız. HTTP Kodu: $httpCode, Yanıt: $response");
                
                // Doğrudan Discord API üzerinden tekrar dene
                $endpoint = "/guilds/{$this->guildId}/members/{$discordId}/roles/{$this->whitelistRoleId}";
                $response = $this->sendRequest($endpoint, 'PUT');
                $success = $response !== null;
                
                if ($success) {
                    error_log("DiscordService: Discord API üzerinden rol başarıyla atandı: $discordId");
                } else {
                    error_log("DiscordService: Discord API üzerinden rol atama başarısız: $discordId");
                }
                
                return $success;
            }
            
            $responseData = json_decode($response, true);
            
            // Kullanıcı zaten role sahipse true döndür
            if (isset($responseData['hasRole']) && $responseData['hasRole']) {
                error_log("DiscordService: Kullanıcı zaten role sahip: $discordId");
                return true;
            }
            
            // Kullanıcıya rolü vermek için PUT isteği gönder
            $putEndpoint = $this->apiEndpoint . '/assign-role/' . $discordId;
            
            error_log("DiscordService: Rol atama isteği gönderiliyor: $putEndpoint");
            
            $ch = curl_init($putEndpoint);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $success = $httpCode === 200;
            
            if ($success) {
                error_log("DiscordService: Bot API üzerinden rol başarıyla atandı: $discordId");
            } else {
                error_log("DiscordService: Bot API üzerinden rol atama başarısız. HTTP Kodu: $httpCode, Yanıt: $response");
            }
            
            return $success;
        }
        
        // Doğrudan Discord API üzerinden rol ata
        $endpoint = "/guilds/{$this->guildId}/members/{$discordId}/roles/{$this->whitelistRoleId}";
        $response = $this->sendRequest($endpoint, 'PUT');
        
        $success = $response !== null;
        
        if ($success) {
            error_log("DiscordService: Discord API üzerinden rol başarıyla atandı: $discordId");
            
            // Önbellek temizle, roller değişti
            $cacheKey = "discord_user_{$discordId}";
            Cache::forget($cacheKey);
            $cacheKey = "discord_member_{$discordId}";
            Cache::forget($cacheKey);
        } else {
            error_log("DiscordService: Discord API üzerinden rol atama başarısız: $discordId");
        }
        
        return $success;
    }
    
    /**
     * Kullanıcıdan rol kaldır
     */
    public function removeRole($userId, $roleId = null) {
        if (empty($userId)) {
            return false;
        }
        
        // Eğer rol belirtilmemişse, whitelist rolünü kullan
        $roleId = $roleId ?? $this->whitelistRoleId;
        
        $endpoint = "/guilds/{$this->guildId}/members/{$userId}/roles/{$roleId}";
        $response = $this->sendRequest($endpoint, 'DELETE');
        
        // DELETE istekleri genellikle 204 No Content döner ve boş yanıt olur
        $success = $response !== null;
        
        if ($success) {
            error_log("Discord removeRole: Rol başarıyla kaldırıldı. userId={$userId}, roleId={$roleId}");
            
            // Önbellek temizle, roller değişti
            $cacheKey = "discord_user_{$userId}";
            Cache::forget($cacheKey);
            $cacheKey = "discord_member_{$userId}";
            Cache::forget($cacheKey);
        } else {
            error_log("Discord removeRole: Rol kaldırma başarısız. userId={$userId}, roleId={$roleId}");
        }
        
        return $success;
    }
    
    /**
     * Bot API üzerinden DM gönder
     */
    private function sendDirectMessageViaBotApi($userId, $message) {
        $botApiEndpoint = Config::get('discord.api.dm_endpoint') . "/{$userId}";
        $postData = json_encode(['message' => $message]);
        
        error_log("Discord sendDirectMessageViaBotApi: Endpoint={$botApiEndpoint}, userId={$userId}, message_length=" . strlen($message));
        
        $ch = curl_init($botApiEndpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);
        
        // CURL hatalarını logla
        if ($curlError) {
            error_log("Discord sendDirectMessageViaBotApi CURL Error: {$curlError}");
            return false;
        }
        
        if ($response === false) {
            error_log("Discord sendDirectMessageViaBotApi: Başarısız, yanıt alınamadı");
            return false;
        }
        
        if ($httpCode == 200) {
            $result = json_decode($response, true);
            $success = $result['success'] ?? false;
            
            if ($success) {
                error_log("Discord sendDirectMessageViaBotApi: Mesaj başarıyla gönderildi. userId={$userId}");
            } else {
                error_log("Discord sendDirectMessageViaBotApi: Bot API başarılı yanıt verdi fakat success=false döndü");
            }
            
            return $success;
        }
        
        error_log("Discord sendDirectMessageViaBotApi: HTTP hata kodu {$httpCode}, yanıt: " . substr($response, 0, 100));
        return false;
    }
    
    /**
     * Belirli rolün üye sayısını getir
     */
    public function countMembersWithRole($roleId) {
        if (empty($roleId)) {
            return 0;
        }
        
        // Önbellekten kontrol et
        $cacheKey = "discord_role_count_{$roleId}";
        $cachedCount = Cache::get($cacheKey);
        
        if ($cachedCount !== null) {
            return $cachedCount;
        }
        
        // API üzerinden hesapla (tüm üyeleri çekmemiz gerekiyor)
        $endpoint = "/guilds/{$this->guildId}/members?limit=1000";
        $members = $this->sendRequest($endpoint);
        
        if (!$members || !is_array($members)) {
            return 0;
        }
        
        $count = 0;
        foreach ($members as $member) {
            if (isset($member['roles']) && in_array($roleId, $member['roles'])) {
                $count++;
            }
        }
        
        // Önbelleğe kaydet (15 dakika)
        Cache::set($cacheKey, $count, 900);
        
        return $count;
    }
    
    /**
     * Rol bilgilerini getir
     */
    public static function getRoleName($roleId) {
        $roles = Config::get('discord.roles', []);
        return $roles[$roleId] ?? '';
    }
}
