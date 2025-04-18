<?php
/**
 * Forum kategorileri için placeholder resimler oluşturan script
 */

// İzin verilen kategori slugları
$allowed_categories = [
    'karakterler',
    'departmanlar',
    'olusumlar',
    'isletmeler',
    'birlikler'
];

// URL'den kategori parametresini al
$category = isset($_GET['category']) && in_array($_GET['category'], $allowed_categories) 
    ? $_GET['category'] 
    : 'default';

// Her kategori için farklı bir renk
$colors = [
    'karakterler' => '#3490dc', // Mavi
    'departmanlar' => '#38c172', // Yeşil
    'olusumlar' => '#e3342f',   // Kırmızı
    'isletmeler' => '#f6993f',  // Turuncu
    'birlikler' => '#9561e2',   // Mor
    'default' => '#6c757d'      // Gri
];

// Kategori başlıkları
$titles = [
    'karakterler' => 'Karakterler',
    'departmanlar' => 'Departmanlar',
    'olusumlar' => 'Oluşumlar',
    'isletmeler' => 'İşletmeler',
    'birlikler' => 'Birlikler',
    'default' => 'Forum'
];

// Kategori için renk ve başlık
$color = $colors[$category];
$title = $titles[$category];

// SVG içeriğini oluştur
$width = 800;
$height = 400;
$svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
    <rect width="100%" height="100%" fill="{$color}" />
    <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="40" font-weight="bold" text-anchor="middle" dominant-baseline="middle" fill="white">{$title}</text>
</svg>
SVG;

// Başlık ve MIME türünü ayarla
header('Content-Type: image/svg+xml');
header('Cache-Control: max-age=86400'); // 1 gün önbellek

// SVG içeriğini gönder
echo $svg;
