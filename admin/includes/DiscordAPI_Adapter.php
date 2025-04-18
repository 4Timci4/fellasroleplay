<?php
/**
 * DiscordAPI sınıfı için Adaptör
 * 
 * Bu sınıf, eski DiscordAPI sınıfını yeni DiscordService sınıfına bağlar.
 * Geriye dönük uyumluluk sağlamak için kullanılır.
 */

// Yeni sınıfları dahil et
require_once __DIR__ . '/../../includes/bootstrap.php';

/**
 * DiscordAPI Adaptör Sınıfı
 */
class DiscordAPI {
    private $service;

    /**
     * Constructor
     */
    public function __construct($token, $guildId, $roleId) {
        // Doğrudan DiscordService sınıfını kullan
        $this->service = new \Services\DiscordService();
    }

    /**
     * Discord kullanıcısının varlığını kontrol eder
     */
    public function checkUserExists($userId) {
        // Yeni servise yönlendir
        $userInfo = $this->service->getUserInfo($userId);
        return $userInfo !== null;
    }

    /**
     * Discord kullanıcısının sunucuda olup olmadığını kontrol eder
     */
    public function checkUserInGuild($userId) {
        return $this->service->checkUserInGuild($userId);
    }

    /**
     * Discord kullanıcısına rol verir
     */
    public function assignRole($userId) {
        return $this->service->assignRole($userId);
    }

    /**
     * Belirli bir rol ID'sine sahip üyelerin sayısını getirir
     */
    public function countMembersWithRole($roleId) {
        return $this->service->countMembersWithRole($roleId);
    }

    /**
     * Discord kullanıcı bilgilerini getirir
     */
    public function getUserInfo($userId) {
        return $this->service->getUserInfo($userId);
    }

    /**
     * Kullanıcının belirli bir role sahip olup olmadığını kontrol eder
     */
    public function checkUserHasRole($userId, $roleId) {
        return $this->service->checkUserHasRole($userId, $roleId);
    }
    
    /**
     * Belirli bir rol ID'sine sahip üyelerin listesini getirir
     */
    public function getMembersWithRole($roleId, $limit = 100) {
        return $this->service->getMembersWithRole($roleId, $limit);
    }
}
