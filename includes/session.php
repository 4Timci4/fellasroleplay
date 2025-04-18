<?php
/**
 * Oturum yönetimi için yardımcı fonksiyonlar
 * 
 * Bu dosya, oturum yönetimi için kullanılan yardımcı fonksiyonları içerir.
 */

/**
 * Kullanıcının giriş yapıp yapmadığını kontrol eder
 * 
 * @return bool Kullanıcı giriş yapmışsa true, yapmamışsa false
 */
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Kullanıcının whitelist rolüne sahip olup olmadığını kontrol eder
 * 
 * @return bool Kullanıcı whitelist rolüne sahipse true, değilse false
 */
function has_whitelist_role() {
    return isset($_SESSION['has_whitelist']) && $_SESSION['has_whitelist'] === true;
}

/**
 * Kullanıcının Discord ID'sini döndürür
 * 
 * @return string|null Kullanıcının Discord ID'si, eğer giriş yapmamışsa null
 */
function get_discord_user_id() {
    return isset($_SESSION['discord_user_id']) ? $_SESSION['discord_user_id'] : null;
}

/**
 * Kullanıcının Discord kullanıcı adını döndürür
 * 
 * @return string|null Kullanıcının Discord kullanıcı adı, eğer giriş yapmamışsa null
 */
function get_discord_username() {
    return isset($_SESSION['discord_username']) ? $_SESSION['discord_username'] : null;
}

/**
 * Kullanıcının Discord avatar URL'sini döndürür
 * 
 * @return string|null Kullanıcının Discord avatar URL'si, eğer giriş yapmamışsa veya avatar yoksa null
 */
function get_discord_avatar() {
    return isset($_SESSION['discord_avatar']) ? $_SESSION['discord_avatar'] : null;
}

/**
 * Kullanıcının admin paneline erişim izni olan rollere sahip olup olmadığını kontrol eder
 * 
 * @return bool Kullanıcı gerekli rollere sahipse true, değilse false
 */
function has_admin_access() {
    // Eğer kullanıcı giriş yapmamışsa
    if (!is_logged_in()) {
        return false;
    }
    
    // Admin paneline erişim izni olan rol ID'leri
    $admin_role_ids = [
        '1353795720716746884', // Fellas
        '1285694535766245408', // Community
        '1267751951307903017'  // Developer
    ];
    
    // Kullanıcının rollerini kontrol et
    if (isset($_SESSION['discord_roles']) && is_array($_SESSION['discord_roles'])) {
        foreach ($admin_role_ids as $role_id) {
            if (in_array($role_id, $_SESSION['discord_roles'])) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Kullanıcının tam admin yetkisine sahip olup olmadığını kontrol eder (Developer veya Fellas rolleri)
 * 
 * @return bool Kullanıcı tam yetkiye sahipse true, değilse false
 */
function has_full_admin_access() {
    // Eğer kullanıcı giriş yapmamışsa
    if (!is_logged_in()) {
        return false;
    }
    
    // Tam yetkili roller
    $admin_role_ids = [
        '1353795720716746884', // Fellas
        '1267751951307903017'  // Developer
    ];
    
    // Kullanıcının rollerini kontrol et
    if (isset($_SESSION['discord_roles']) && is_array($_SESSION['discord_roles'])) {
        foreach ($admin_role_ids as $role_id) {
            if (in_array($role_id, $_SESSION['discord_roles'])) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Kullanıcının oturumunu sonlandırır
 * 
 * @return void
 */
function logout() {
    // Oturum değişkenlerini temizle
    session_unset();
    session_destroy();
    
    // Giriş sayfasına yönlendir
    header('Location: login.php?message=' . urlencode('Başarıyla çıkış yaptınız.'));
    exit;
}

/**
 * Kullanıcının oturum süresini günceller
 * 
 * @return void
 */
function update_session_time() {
    $_SESSION['login_time'] = time();
}

/**
 * Kullanıcının Discord rollerini günceller
 * Bu fonksiyon sayfa yenilendiğinde kullanıcının Discord rollerini güncel tutar
 * Böylece kullanıcı çıkış yapmadan yeni rollerinin yetkilerine sahip olabilir
 * 
 * @return bool Güncelleme başarılıysa true, değilse false
 */
function refresh_discord_roles() {
    // Eğer kullanıcı giriş yapmamışsa false döndür
    if (!is_logged_in() || !isset($_SESSION['discord_user_id'])) {
        return false;
    }
    
    // Kullanıcı ID'si
    $discord_user_id = $_SESSION['discord_user_id'];
    
    // DiscordService'i dahil et ve kullan
    if (class_exists('\Services\DiscordService')) {
        try {
            $discord_service = new \Services\DiscordService();
            
            // Kullanıcının Discord sunucusunda olup olmadığını kontrol et
            if (!$discord_service->checkUserInGuild($discord_user_id)) {
                error_log("Kullanıcı Discord sunucusunda değil: $discord_user_id");
                return false;
            }
            
            // Kullanıcının rollerini getir
            $member_data = $discord_service->getMemberDetails($discord_user_id);
            
            if (!$member_data) {
                error_log("Üye bilgileri alınamadı: $discord_user_id");
                return false;
            }
            
            // Rol bilgilerini kontrol et ve güncelle
            if (isset($member_data['roles']) && is_array($member_data['roles'])) {
                // Debug için eski ve yeni rolleri kaydet
                $old_roles = isset($_SESSION['discord_roles']) ? $_SESSION['discord_roles'] : [];
                $new_roles = $member_data['roles'];
                
                // Session'ı güncelle
                $_SESSION['discord_roles'] = $new_roles;
                
                // Whitelist rolünü kontrol et
                $whitelist_role_id = \Core\Config::get('discord.whitelist_role_id', '1267646750789861537');
                $_SESSION['has_whitelist'] = in_array($whitelist_role_id, $_SESSION['discord_roles']);
                
                // Debug için rol değişimini kaydet
                if ($old_roles != $new_roles) {
                    error_log("Roller güncellendi. Kullanıcı: $discord_user_id, Eski: " . implode(',', $old_roles) . ", Yeni: " . implode(',', $new_roles));
                }
                
                return true;
            }
        } catch (Exception $e) {
            error_log("Discord rol güncelleme hatası: " . $e->getMessage());
            return false;
        }
    } else {
        // Fallback olarak eski metodu kullan
        require_once 'config.php';
        
        // Discord API bilgilerini al
        $discord_bot_token = \Core\Config::get('discord.bot_token', 'MTM1MTU0NTUzNjU3Mzk5NzA4Nw.GMq93K.V25xA1ejWrRQggRapeLVZSpWGbftO5kMhFnUNo');
        $discord_guild_id = \Core\Config::get('discord.guild_id', '1267610711509438576');
        $discord_whitelist_role_id = \Core\Config::get('discord.whitelist_role_id', '1267646750789861537');
        
        // Kullanıcının üye olduğu sunucunun detaylarını al
        $url = "https://discord.com/api/v10/guilds/{$discord_guild_id}/members/{$discord_user_id}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bot ' . $discord_bot_token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $member_response = curl_exec($ch);
        $member_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Üyelik bilgileri alınamazsa false döndür
        if ($member_status !== 200) {
            error_log("Discord API üye bilgileri hatası ($member_status): $member_response");
            return false;
        }
        
        // Üyelik bilgilerini parse et
        $member_data = json_decode($member_response, true);
        
        // Kullanıcının rollerini güncelle
        if (isset($member_data['roles']) && is_array($member_data['roles'])) {
            // Debug için eski ve yeni rolleri kaydet
            $old_roles = isset($_SESSION['discord_roles']) ? $_SESSION['discord_roles'] : [];
            $new_roles = $member_data['roles'];
            
            // Session'ı güncelle ve oturumu kaydet
            $_SESSION['discord_roles'] = $new_roles;
            
            // Whitelist rolünü kontrol et
            $_SESSION['has_whitelist'] = in_array($discord_whitelist_role_id, $_SESSION['discord_roles']);
            
            // Oturum değişikliklerini kaydet
            session_write_close();
            
            // Debug için rol değişimini kaydet
            if ($old_roles != $new_roles) {
                error_log("Roller güncellendi (curl). Kullanıcı: $discord_user_id, Eski: " . implode(',', $old_roles) . ", Yeni: " . implode(',', $new_roles));
            }
            
            return true;
        }
    }
    
    return false;
}
