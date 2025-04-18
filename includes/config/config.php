<?php
/**
 * Temel konfigürasyon dosyası
 * 
 * Bu dosya, site genelinde kullanılan ayarları ve linkleri içerir.
 */

return [
    // Site Genel Ayarları
    'site' => [
        'name' => 'Fellas Roleplay',
        'description' => 'Gerçekçi roleplay deneyimi için doğru adrestesiniz.',
        'copyright_year' => '2025',
        'copyright_text' => 'Tüm hakları saklıdır. Fellas Roleplay, gerçek bir roleplay deneyimi sunar.',
        'designed_by' => 'Designed by Timci',
    ],
    
    // Sosyal Medya Linkleri
    'social' => [
        'discord' => 'https://discord.gg/fellasrp',
        'youtube' => 'https://youtube.com'
    ],
    
    // Slider Ayarları
    'slider' => [
        'transition_time' => 4000,
        'images' => [
            'assets/images/slider/slider1.png'
        ],
    ],
    
    // Discord Rol Adları
    'discord_roles' => [
        \Core\Config::get('discord.roles.fellas_id', 'ROLE_ID_1') => 'Fellas',
        \Core\Config::get('discord.roles.community_id', 'ROLE_ID_2') => 'Community',
        \Core\Config::get('discord.roles.developer_id', 'ROLE_ID_3') => 'Developer',
        \Core\Config::get('discord.roles.whitelist_id', 'ROLE_ID_4') => 'Whitelist'
    ],
    
    // Sayfa Görünürlük Ayarları
    'show_team_page' => false,
    
    // İstatistik varsayılan değerleri
    'statistics' => [
        'active_characters' => '0',
        'whitelist_players' => '0',
        'admin_team' => '15',
        'vehicles_properties' => '300+'
    ]
];
