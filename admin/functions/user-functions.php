<?php
/**
 * Forum Kullanıcı Yönetimi Fonksiyonları
 * 
 * Bu dosya, prosedürel API'den OOP yaklaşımına geçiş aşamasında geriye uyumluluk sağlar.
 * Eski fonksiyonlar korunmuştur, ancak yeni OOP sınıflarını kullanırlar.
 * 
 * @deprecated Doğrudan Services\UserService sınıfını kullanın
 */

// Bootstrapper'ı dahil et (gerekli sınıfların ve autoloader'ın yüklenmesi için)
require_once __DIR__ . '/../../includes/bootstrap.php';

use Services\UserService;

/**
 * Forum kullanıcılarını getir
 *
 * @param int $page Sayfa numarası
 * @param int $per_page Sayfa başına kullanıcı sayısı
 * @param string $search Arama sorgusu (opsiyonel)
 * @return array Kullanıcılar ve sayfalama bilgileri
 * @deprecated OOP yaklaşımı ile UserService::getInstance()->getUsers() kullanın
 */
function get_forum_users($page = 1, $per_page = 20, $search = null) {
    // UserService kullanarak kullanıcıları al
    return UserService::getInstance()->getUsers($page, $per_page, $search);
}

/**
 * Kullanıcı detaylarını getir
 *
 * @param string $user_id Kullanıcı ID
 * @return array|null Kullanıcı detayları veya bulunamazsa null
 * @deprecated OOP yaklaşımı ile UserService::getInstance()->getUserByDiscordId() kullanın
 */
function get_forum_user($user_id) {
    // UserService kullanarak kullanıcıyı al
    return UserService::getInstance()->getUserByDiscordId($user_id);
}

/**
 * Kullanıcı durumunu güncelle (ban/unban)
 *
 * @param string $user_id Kullanıcı ID
 * @param bool $is_banned Yasaklama durumu
 * @return bool İşlem başarılı mı
 * @deprecated OOP yaklaşımı ile UserService::getInstance()->updateUserBanStatus() kullanın
 */
function update_user_ban_status($user_id, $is_banned) {
    // UserService kullanarak kullanıcı ban durumunu güncelle
    return UserService::getInstance()->updateUserBanStatus($user_id, $is_banned);
}

/**
 * Kullanıcı rolünü güncelle
 *
 * @param string $user_id Kullanıcı ID
 * @param string $role Kullanıcı rolü (admin, moderator, user)
 * @return bool İşlem başarılı mı
 * @deprecated OOP yaklaşımı ile UserService::getInstance()->updateUserRole() kullanın
 */
function update_user_role($user_id, $role) {
    // Geçerli rol kontrolü UserService içerisinde yapılıyor
    return UserService::getInstance()->updateUserRole($user_id, $role);
}

/**
 * En aktif kullanıcıları getir
 *
 * @param int $limit Kaç kullanıcı getirileceği
 * @return array Kullanıcılar
 * @deprecated OOP yaklaşımı ile UserService::getInstance()->getMostActiveUsers() kullanın
 */
function get_most_active_users($limit = 5) {
    // UserService kullanarak en aktif kullanıcıları al
    return UserService::getInstance()->getMostActiveUsers($limit);
}

/**
 * Yeni kullanıcı oluştur
 * 
 * @param array $userData Kullanıcı bilgileri
 * @return array|null Oluşturulan kullanıcı bilgileri veya başarısız olursa null
 * @deprecated OOP yaklaşımı ile UserService::getInstance()->createUser() kullanın
 */
function create_forum_user($userData) {
    $user = UserService::getInstance()->createUser($userData);
    return $user ? $user->toArray() : null;
}

/**
 * Varolan kullanıcıyı güncelle
 * 
 * @param string $user_id Kullanıcı ID
 * @param array $userData Güncellenecek kullanıcı bilgileri
 * @return bool İşlem başarılı mı
 * @deprecated OOP yaklaşımı ile UserService::getInstance()->updateUser() kullanın
 */
function update_forum_user($user_id, $userData) {
    return UserService::getInstance()->updateUser($user_id, $userData);
}

/**
 * Kullanıcı son giriş zamanını güncelle
 * 
 * @param string $user_id Kullanıcı ID
 * @return bool İşlem başarılı mı
 * @deprecated OOP yaklaşımı ile UserService::getInstance()->updateUserLastLogin() kullanın
 */
function update_user_last_login($user_id) {
    return UserService::getInstance()->updateUserLastLogin($user_id);
}
