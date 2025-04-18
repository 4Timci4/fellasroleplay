<?php
/**
 * Discord API konfigürasyon dosyası
 * 
 * Bu dosya, Discord API bağlantı bilgilerini içerir.
 */

return [
    'enabled' => true,
    'token' => getenv('DISCORD_BOT_TOKEN') ?: 'YOUR_DISCORD_BOT_TOKEN',
    'guild_id' => getenv('DISCORD_GUILD_ID') ?: 'YOUR_DISCORD_GUILD_ID',
    'whitelist_role_id' => getenv('DISCORD_WHITELIST_ROLE_ID') ?: 'YOUR_DISCORD_WHITELIST_ROLE_ID',
    
    // Discord rol adları ve ID'leri
    'roles' => [
        // Rol adları
        'ROLE_ID_1' => 'Fellas',
        'ROLE_ID_2' => 'Community',
        'ROLE_ID_3' => 'Developer',
        'ROLE_ID_4' => 'Whitelist',
        
        // Rol ID'leri - session.php ve diğer dosyalarda kullanılır
        'fellas_id' => getenv('DISCORD_FELLAS_ROLE_ID') ?: 'ROLE_ID_1',
        'community_id' => getenv('DISCORD_COMMUNITY_ROLE_ID') ?: 'ROLE_ID_2',
        'developer_id' => getenv('DISCORD_DEVELOPER_ROLE_ID') ?: 'ROLE_ID_3',
        'whitelist_id' => getenv('DISCORD_WHITELIST_ROLE_ID') ?: 'ROLE_ID_4'
    ],
    
    // Bot API ayarları
    'api' => [
        'endpoint' => 'http://localhost:3000',
        'dm_endpoint' => 'http://localhost:3000/send-dm'
    ]
];
