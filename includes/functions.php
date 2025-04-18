<?php
/**
 * Site genelinde kullanılan eski yardımcı fonksiyonların adaptörü
 * 
 * Bu dosya, eski functions.php dosyasındaki fonksiyonları yeni 
 * mimari ile uyumlu hale getirir. Bu sayede eski kod sorunsuz çalışır.
 */

// Bootstrap dosyasını dahil et - tüm yeni sınıfları ve legacy_compatibility'i içerir
require_once __DIR__ . '/bootstrap.php';

// Eski stil global değişkenleri sağlayan kod, geriye uyumluluk için
$discord_api_token = getenv('DISCORD_BOT_TOKEN') ?: 'MTM1MTU0NTUzNjU3Mzk5NzA4Nw.GMq93K.V25xA1ejWrRQggRapeLVZSpWGbftO5kMhFnUNo';
$discord_guild_id = getenv('DISCORD_GUILD_ID') ?: '1267610711509438576';
$discord_whitelist_role_id = getenv('DISCORD_WHITELIST_ROLE_ID') ?: '1267646750789861537';

// Global discord api değişkeni eski kod için gerekli olabilir
$discord_api = new \Services\DiscordService();

// Not: Tüm discord fonksiyonları artık legacy_compatibility.php dosyasından geliyor
