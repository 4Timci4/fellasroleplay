<?php
/**
 * Veritabanı konfigürasyon dosyası
 * 
 * Bu dosya, veritabanı bağlantı bilgilerini içerir.
 */

return [
    // Ana site veritabanı
    'default' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'fellasrpweb',
        'username' => getenv('DB_USER') ?: 'dbuser',
        'password' => getenv('DB_PASSWORD') ?: 'YOUR_DB_PASSWORD',
        'charset' => 'utf8mb4'
    ],

    // 'default' => [
    //     'host' => 'localhost',
    //     'name' => 'fellasrpweb',
    //     'username' => 'root',
    //     'password' => '',
    //     'charset' => 'utf8mb4'
    // ],
    
    // Oyun veritabanı
    'game' => [
        'host' => getenv('GAME_DB_HOST') ?: 'localhost',
        'name' => getenv('GAME_DB_NAME') ?: 'fellasrp',
        'username' => getenv('GAME_DB_USER') ?: 'dbuser',
        'password' => getenv('GAME_DB_PASSWORD') ?: 'YOUR_DB_PASSWORD',
        'charset' => 'utf8mb4'
    ]

    // 'game' => [
    //     'host' => 'localhost',
    //     'name' => 'fellasrp',
    //     'username' => 'root',
    //     'password' => '',
    //     'charset' => 'utf8mb4'
    // ]
];
