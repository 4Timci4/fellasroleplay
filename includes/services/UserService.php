<?php
namespace Services;

use Core\Database;
use Models\Forum\User;

/**
 * Kullanıcı işlemleri için servis sınıfı
 */
class UserService {
    private static $instance = null;
    private $database;
    
    /**
     * Singleton pattern constructor
     */
    private function __construct() {
        $this->database = Database::getInstance();
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
     * Tüm kullanıcıları getir
     * 
     * @param int $page Sayfa numarası
     * @param int $perPage Sayfa başına kullanıcı sayısı
     * @param string|null $search Arama sorgusu
     * @return array Kullanıcılar ve sayfalama bilgileri
     */
    public function getUsers($page = 1, $perPage = 20, $search = null) {
        $result = User::getAll($page, $perPage, $search);
        
        // User nesnelerini diziye dönüştür (geriye uyumluluk için)
        $users = [];
        foreach ($result['users'] as $user) {
            $users[] = $user->toArray();
        }
        
        return [
            'users' => $users,
            'pagination' => $result['pagination']
        ];
    }
    
    /**
     * Discord ID'ye göre kullanıcı bul
     * 
     * @param string $discordId Discord kullanıcı ID'si
     * @return array|null Kullanıcı bilgileri veya bulunamazsa null
     */
    public function getUserByDiscordId($discordId) {
        $user = User::findByDiscordId($discordId);
        return $user ? $user->toArray() : null;
    }
    
    /**
     * Kullanıcı adına göre kullanıcı bul
     * 
     * @param string $username Kullanıcı adı
     * @return array|null Kullanıcı bilgileri veya bulunamazsa null
     */
    public function getUserByUsername($username) {
        $user = User::findByUsername($username);
        return $user ? $user->toArray() : null;
    }
    
    /**
     * En aktif kullanıcıları getir
     * 
     * @param int $limit Kaç kullanıcı getirileceği
     * @return array Kullanıcı bilgileri dizisi
     */
    public function getMostActiveUsers($limit = 5) {
        $users = User::getMostActive($limit);
        
        // User nesnelerini diziye dönüştür
        $result = [];
        foreach ($users as $user) {
            $result[] = $user->toArray();
        }
        
        return $result;
    }
    
    /**
     * Kullanıcı durumunu güncelle (ban/unban)
     * 
     * @param string $discordId Kullanıcı ID
     * @param bool $isBanned Yasaklama durumu
     * @return bool İşlem başarılı mı
     */
    public function updateUserBanStatus($discordId, $isBanned) {
        $user = User::findByDiscordId($discordId);
        if (!$user) {
            return false;
        }
        
        return $user->updateBanStatus($isBanned);
    }
    
    /**
     * Kullanıcı rolünü güncelle
     * 
     * @param string $discordId Kullanıcı ID
     * @param string $role Kullanıcı rolü (admin, moderator, user)
     * @return bool İşlem başarılı mı
     */
    public function updateUserRole($discordId, $role) {
        $user = User::findByDiscordId($discordId);
        if (!$user) {
            return false;
        }
        
        return $user->updateRole($role);
    }
    
    /**
     * Kullanıcı son giriş zamanını güncelle
     * 
     * @param string $discordId Kullanıcı ID
     * @return bool İşlem başarılı mı
     */
    public function updateUserLastLogin($discordId) {
        $user = User::findByDiscordId($discordId);
        if (!$user) {
            return false;
        }
        
        return $user->updateLastLogin();
    }
    
    /**
     * Yeni kullanıcı oluştur
     * 
     * @param array $userData Kullanıcı bilgileri
     * @return User|null Oluşturulan kullanıcı nesnesi veya başarısız olursa null
     */
    public function createUser($userData) {
        // Gerekli alanların kontrolü
        if (empty($userData['discord_id']) || empty($userData['username'])) {
            return null;
        }
        
        // Aynı Discord ID ile kullanıcı var mı?
        $existingUser = User::findByDiscordId($userData['discord_id']);
        if ($existingUser) {
            return null;
        }
        
        $user = new User($userData);
        
        if ($user->save()) {
            return $user;
        }
        
        return null;
    }
    
    /**
     * Varolan kullanıcıyı güncelle
     * 
     * @param string $discordId Kullanıcı ID
     * @param array $userData Güncellenecek kullanıcı bilgileri
     * @return bool İşlem başarılı mı
     */
    public function updateUser($discordId, $userData) {
        $user = User::findByDiscordId($discordId);
        if (!$user) {
            return false;
        }
        
        // Güncellenmesine izin verilen alanları ayarla
        if (isset($userData['username'])) {
            $user->setUsername($userData['username']);
        }
        
        if (isset($userData['avatar'])) {
            $user->setAvatar($userData['avatar']);
        }
        
        if (isset($userData['email'])) {
            $user->setEmail($userData['email']);
        }
        
        if (isset($userData['role'])) {
            $user->setRole($userData['role']);
        }
        
        if (isset($userData['is_banned'])) {
            $user->setIsBanned($userData['is_banned']);
        }
        
        return $user->save();
    }
}
