<?php
/**
 * Veritabanı konfigürasyon dosyası
 * 
 * Bu dosya, veritabanı bağlantı bilgilerini içerir.
 */

return [
    // Ana site veritabanı
    'default' => [
        'host' => 'localhost',
        'name' => 'fellasrpweb',
        'username' => 'dbuser',
        'password' => 'dbpassword',
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
        'host' => 'localhost',
        'name' => 'fellasrp',
        'username' => 'dbuser',
        'password' => 'dbpassword',
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
