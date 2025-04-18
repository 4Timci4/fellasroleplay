<?php
session_start();

// Bootstrap dosyasını dahil et - tüm sınıfları ve yardımcıları yükler
require_once 'includes/bootstrap.php';

// Config değerlerini al
$discord_client_id = \Core\Config::get('discord.client_id', '1354412651857317928');
$discord_client_secret = \Core\Config::get('discord.client_secret', 'K4OwJaU4w95UaQO6Poz1cunqZmhaFRZc');
$discord_redirect_uri = \Core\Config::get('discord.redirect_uri', 'https://fellasroleplay.com/auth.php');
$discord_api_base_url = 'https://discord.com/api/v10';
$discord_bot_token = \Core\Config::get('discord.bot_token', 'YOUR_DISCORD_BOT_TOKEN');
$discord_guild_id = \Core\Config::get('discord.guild_id', '1267610711509438576');
$discord_whitelist_role_id = \Core\Config::get('discord.whitelist_role_id', '1267646750789861537');

$error_message = null;

if (isset($_GET['code'])) {
    $token_request = [
        'client_id' => $discord_client_id,
        'client_secret' => $discord_client_secret,
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => $discord_redirect_uri,
        'scope' => 'identify guilds.members.read'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $discord_api_base_url . '/oauth2/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_request));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $token_response = curl_exec($ch);
    $token_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($token_status !== 200) {
        $error_message = 'Discord ile giriş yapılırken bir hata oluştu. Lütfen tekrar deneyin.';
        header('Location: login.php?error=' . urlencode($error_message));
        exit;
    }

    $token_data = json_decode($token_response, true);
    $access_token = $token_data['access_token'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $discord_api_base_url . '/users/@me');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $user_response = curl_exec($ch);
    $user_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($user_status !== 200) {
        $error_message = 'Discord kullanıcı bilgileri alınamadı. Lütfen tekrar deneyin.';
        header('Location: login.php?error=' . urlencode($error_message));
        exit;
    }

    $user_data = json_decode($user_response, true);
    $discord_user_id = $user_data['id'];
    $discord_username = $user_data['username'];
    $discord_avatar = $user_data['avatar'] ? 'https://cdn.discordapp.com/avatars/' . $discord_user_id . '/' . $user_data['avatar'] . '.png' : null;

    // DiscordService sınıfını kullan
    $discord_api = new \Services\DiscordService();
    $is_in_guild = $discord_api->checkUserInGuild($discord_user_id);

    if (!$is_in_guild) {
        $error_message = 'Discord sunucumuzda bulunmuyorsunuz. Lütfen önce Discord sunucumuza katılın: ' . get_social_link('discord');
        header('Location: login.php?error=' . urlencode($error_message));
        exit;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $discord_api_base_url . '/users/@me/guilds/' . $discord_guild_id . '/member');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $member_response = curl_exec($ch);
    $member_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($member_status !== 200) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/check-role/' . $discord_user_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $role_response = curl_exec($ch);
        $role_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($role_status !== 200) {
            $error_message = 'Discord rol kontrolü yapılamadı. Lütfen tekrar deneyin.';
            header('Location: login.php?error=' . urlencode($error_message));
            exit;
        }

        $role_data = json_decode($role_response, true);
        $has_whitelist = isset($role_data['hasRole']) ? $role_data['hasRole'] : false;
        $user_roles = []; // Varsayılan boş roller
    } else {
        $member_data = json_decode($member_response, true);
        $user_roles = $member_data['roles'] ?? [];
        $has_whitelist = in_array($discord_whitelist_role_id, $user_roles);
    }
    $_SESSION['logged_in'] = true;
    $_SESSION['discord_user_id'] = $discord_user_id;
    $_SESSION['discord_username'] = $discord_username;
    $_SESSION['discord_avatar'] = $discord_avatar;
    $_SESSION['has_whitelist'] = $has_whitelist;
    $_SESSION['discord_roles'] = $user_roles; // Kullanıcının rollerini kaydet
    $_SESSION['login_time'] = time();

    header('Location: anasayfa');
    exit;
} else {
    $auth_url = $discord_api_base_url . '/oauth2/authorize?' . http_build_query([
        'client_id' => $discord_client_id,
        'redirect_uri' => $discord_redirect_uri,
        'response_type' => 'code',
        'scope' => 'identify guilds.members.read'
    ]);

    header('Location: ' . $auth_url);
    exit;
}
