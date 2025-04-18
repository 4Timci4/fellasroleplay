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
    
    // Discord rol adları - config.php dosyasıyla aynı değerler
    // İleride sadece bir yerde tutulacak
    'roles' => [
        'ROLE_ID_1' => 'Fellas',
        'ROLE_ID_2' => 'Community',
        'ROLE_ID_3' => 'Developer',
        'ROLE_ID_4' => 'Whitelist'
    ],
    
    // Bot API ayarları
    'api' => [
        'endpoint' => 'http://localhost:3000',
        'dm_endpoint' => 'http://localhost:3000/send-dm'
    ]
];
