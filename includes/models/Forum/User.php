<?php
namespace Models\Forum;

use Core\Database;

/**
 * Forum Kullanıcı model sınıfı
 */
class User {
    private $discord_id;
    private $username;
    private $avatar;
    private $role;
    private $created_at;
    private $updated_at;
    private $last_login;
    private $is_banned;
    private $email;
    
    /**
     * Constructor
     */
    public function __construct($data = null) {
        if (is_array($data)) {
            $this->fill($data);
        }
    }
    
    /**
     * Veri ile model alanlarını doldur
     */
    public function fill($data) {
        $this->discord_id = $data['discord_id'] ?? null;
        $this->username = $data['username'] ?? '';
        $this->avatar = $data['avatar'] ?? '';
        $this->role = $data['role'] ?? 'user';
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->last_login = $data['last_login'] ?? null;
        $this->is_banned = isset($data['is_banned']) ? (bool)$data['is_banned'] : false;
        $this->email = $data['email'] ?? null;
    }
    
    /**
     * Tüm kullanıcıları getir
     * 
     * @param int $page Sayfa numarası
     * @param int $perPage Sayfa başına kullanıcı sayısı
     * @param string|null $search Arama sorgusu
     * @return array Kullanıcılar ve sayfalama bilgileri
     */
    public static function getAll($page = 1, $perPage = 20, $search = null) {
        $db = Database::getInstance();
        
        // Toplam kullanıcı sayısını hesapla
        $countQuery = "SELECT COUNT(*) FROM forum_users";
        $params = [];
        
        if ($search) {
            $countQuery .= " WHERE username LIKE ? OR discord_id LIKE ?";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $totalUsers = $db->fetchValue($countQuery, $params);
        
        // Sayfalama hesapla
        $totalPages = ceil($totalUsers / $perPage);
        $page = max(1, min($page, $totalPages ?: 1));
        $offset = ($page - 1) * $perPage;
        
        // Kullanıcıları getir
        $query = "SELECT * FROM forum_users";
        
        if ($search) {
            $query .= " WHERE username LIKE ? OR discord_id LIKE ?";
        }
        
        $query .= " ORDER BY last_login DESC LIMIT ? OFFSET ?";
        
        // LIMIT ve OFFSET değerlerini ekle
        $queryParams = $params;
        $queryParams[] = $perPage;
        $queryParams[] = $offset;
        
        $results = $db->fetchAll($query, $queryParams);
        
        // Kullanıcı nesneleri oluştur
        $users = [];
        foreach ($results as $row) {
            $users[] = new self($row);
        }
        
        return [
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_users' => $totalUsers,
                'total_pages' => $totalPages
            ]
        ];
    }
    
    /**
     * Discord ID'ye göre kullanıcı bul
     * 
     * @param string $discordId Discord kullanıcı ID'si
     * @return User|null Kullanıcı nesnesi veya bulunamazsa null
     */
    public static function findByDiscordId($discordId) {
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM forum_users WHERE discord_id = ?", [$discordId]);
        
        if (!$row) {
            return null;
        }
        
        return new self($row);
    }
    
    /**
     * Username'e göre kullanıcı bul
     * 
     * @param string $username Kullanıcı adı
     * @return User|null Kullanıcı nesnesi veya bulunamazsa null
     */
    public static function findByUsername($username) {
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM forum_users WHERE username = ?", [$username]);
        
        if (!$row) {
            return null;
        }
        
        return new self($row);
    }
    
    /**
     * En aktif kullanıcıları getir
     *
     * @param int $limit Kaç kullanıcı getirileceği
     * @return array Kullanıcı nesneleri
     */
    public static function getMostActive($limit = 5) {
        $db = Database::getInstance();
        
        $query = "
            SELECT 
                u.*,
                (SELECT COUNT(*) FROM forum_topics WHERE discord_user_id = u.discord_id) +
                (SELECT COUNT(*) FROM forum_comments WHERE discord_user_id = u.discord_id) as activity
            FROM 
                forum_users u
            ORDER BY 
                activity DESC
            LIMIT ?";
        
        $results = $db->fetchAll($query, [$limit]);
        
        $users = [];
        foreach ($results as $row) {
            $users[] = new self($row);
        }
        
        return $users;
    }
    
    /**
     * Kullanıcının gönderdiği konu sayısını getir
     * 
     * @return int Konu sayısı
     */
    public function getTopicCount() {
        if (!$this->discord_id) return 0;
        
        $db = Database::getInstance();
        return $db->fetchValue(
            "SELECT COUNT(*) FROM forum_topics WHERE discord_user_id = ? AND status != 'deleted'", 
            [$this->discord_id]
        );
    }
    
    /**
     * Kullanıcının gönderdiği yorum sayısını getir
     * 
     * @return int Yorum sayısı
     */
    public function getCommentCount() {
        if (!$this->discord_id) return 0;
        
        $db = Database::getInstance();
        return $db->fetchValue(
            "SELECT COUNT(*) FROM forum_comments WHERE discord_user_id = ?", 
            [$this->discord_id]
        );
    }
    
    /**
     * Kullanıcı durumunu güncelle (ban/unban)
     * 
     * @param bool $isBanned Ban durumu
     * @return bool İşlem başarılı mı
     */
    public function updateBanStatus($isBanned) {
        if (!$this->discord_id) return false;
        
        $db = Database::getInstance();
        $result = $db->update('forum_users', 
            ['is_banned' => $isBanned ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')], 
            'discord_id = ?', 
            [$this->discord_id]
        );
        
        if ($result) {
            $this->is_banned = (bool)$isBanned;
            $this->updated_at = date('Y-m-d H:i:s');
        }
        
        return $result > 0;
    }
    
    /**
     * Kullanıcı rolünü güncelle
     * 
     * @param string $role Yeni rol
     * @return bool İşlem başarılı mı
     */
    public function updateRole($role) {
        if (!$this->discord_id) return false;
        
        $valid_roles = ['admin', 'moderator', 'user'];
        if (!in_array($role, $valid_roles)) {
            return false;
        }
        
        $db = Database::getInstance();
        $result = $db->update('forum_users', 
            ['role' => $role, 'updated_at' => date('Y-m-d H:i:s')], 
            'discord_id = ?', 
            [$this->discord_id]
        );
        
        if ($result) {
            $this->role = $role;
            $this->updated_at = date('Y-m-d H:i:s');
        }
        
        return $result > 0;
    }
    
    /**
     * Son giriş zamanını güncelle
     * 
     * @return bool İşlem başarılı mı
     */
    public function updateLastLogin() {
        if (!$this->discord_id) return false;
        
        $now = date('Y-m-d H:i:s');
        
        $db = Database::getInstance();
        $result = $db->update('forum_users', 
            ['last_login' => $now, 'updated_at' => $now], 
            'discord_id = ?', 
            [$this->discord_id]
        );
        
        if ($result) {
            $this->last_login = $now;
            $this->updated_at = $now;
        }
        
        return $result > 0;
    }
    
    /**
     * Kullanıcıyı kaydet (yeni oluştur veya güncelle)
     * 
     * @return bool İşlem başarılı mı
     */
    public function save() {
        $db = Database::getInstance();
        
        $data = [
            'username' => $this->username,
            'avatar' => $this->avatar,
            'role' => $this->role,
            'is_banned' => $this->is_banned ? 1 : 0,
        ];
        
        if ($this->email) {
            $data['email'] = $this->email;
        }
        
        // Varolan kullanıcıyı güncelle
        if ($this->discord_id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $result = $db->update('forum_users', $data, 'discord_id = ?', [$this->discord_id]);
            return $result > 0;
        } 
        // Yeni kullanıcı oluştur
        else {
            $data['discord_id'] = $this->discord_id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $result = $db->insert('forum_users', $data);
            if ($result) {
                $this->discord_id = $data['discord_id'];
                $this->created_at = $data['created_at'];
                $this->updated_at = $data['updated_at'];
                return true;
            }
            
            return false;
        }
    }
    
    // Getter metotları
    public function getDiscordId() { return $this->discord_id; }
    public function getUsername() { return $this->username; }
    public function getAvatar() { return $this->avatar; }
    public function getRole() { return $this->role; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getLastLogin() { return $this->last_login; }
    public function isBanned() { return $this->is_banned; }
    public function getEmail() { return $this->email; }
    
    // Setter metotları
    public function setDiscordId($value) { $this->discord_id = $value; }
    public function setUsername($value) { $this->username = $value; }
    public function setAvatar($value) { $this->avatar = $value; }
    public function setRole($value) { $this->role = $value; }
    public function setIsBanned($value) { $this->is_banned = (bool)$value; }
    public function setEmail($value) { $this->email = $value; }
    
    /**
     * Model verilerini dizi olarak döndür
     */
    public function toArray() {
        return [
            'discord_id' => $this->discord_id,
            'username' => $this->username,
            'avatar' => $this->avatar,
            'role' => $this->role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_login' => $this->last_login,
            'is_banned' => $this->is_banned,
            'email' => $this->email,
            'topic_count' => $this->getTopicCount(),
            'comment_count' => $this->getCommentCount(),
        ];
    }
}
