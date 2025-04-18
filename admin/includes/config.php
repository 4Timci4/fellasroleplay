<?php
// Admin Paneli Genel Ayarları
$admin_config = [
    'panel_name' => 'Fellas Roleplay Admin',
    'items_per_page' => 10,
];

// Discord API Ayarları
$discord_config = [
    'token' => 'YOUR_DISCORD_BOT_TOKEN',
    'guild_id' => 'YOUR_DISCORD_GUILD_ID',
    'role_id' => 'YOUR_DISCORD_ROLE_ID',
    'enabled' => true,
];

function getDiscordConfig() {
    global $discord_config;
    return $discord_config;
}

function getAdminConfig($key) {
    global $admin_config;
    return isset($admin_config[$key]) ? $admin_config[$key] : null;
}
