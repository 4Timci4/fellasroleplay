<?php
/**
 * Forum formatlama ve yardımcı fonksiyonlar
 */

// Gerekli sınıfları ve yardımcı fonksiyonları içeren bootstrapper'ı kontrol et
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../bootstrap.php';
}

/**
 * Sayfalama bağlantılarını oluşturan yardımcı fonksiyon
 * 
 * @param array $pagination Sayfalama bilgileri
 * @param string $page_url Sayfa URL'si (sayfa parametresi olmadan)
 * @return string HTML sayfalama bağlantıları
 */
function get_pagination_links($pagination, $page_url) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $current_page = $pagination['current_page'];
    $total_pages = $pagination['total_pages'];
    
    $html = '<div class="pagination flex justify-center space-x-2 my-6">';
    
    // Önceki sayfa bağlantısı
    if ($current_page > 1) {
        $html .= '<a href="' . $page_url . '?page=' . ($current_page - 1) . '" class="btn-secondary px-4 py-2 rounded">Önceki</a>';
    } else {
        $html .= '<span class="btn-secondary opacity-50 px-4 py-2 rounded">Önceki</span>';
    }
    
    // Sayfa numaraları
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    if ($start_page > 1) {
        $html .= '<a href="' . $page_url . '?page=1" class="btn-secondary px-4 py-2 rounded">1</a>';
        if ($start_page > 2) {
            $html .= '<span class="px-2">...</span>';
        }
    }
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="btn-primary px-4 py-2 rounded">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $page_url . '?page=' . $i . '" class="btn-secondary px-4 py-2 rounded">' . $i . '</a>';
        }
    }
    
    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            $html .= '<span class="px-2">...</span>';
        }
        $html .= '<a href="' . $page_url . '?page=' . $total_pages . '" class="btn-secondary px-4 py-2 rounded">' . $total_pages . '</a>';
    }
    
    // Sonraki sayfa bağlantısı
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $page_url . '?page=' . ($current_page + 1) . '" class="btn-secondary px-4 py-2 rounded">Sonraki</a>';
    } else {
        $html .= '<span class="btn-secondary opacity-50 px-4 py-2 rounded">Sonraki</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Metin formatlama için yardımcı fonksiyon
 * HTML güvenliği sağlar ve basit metin formatlamayı destekler
 * 
 * @param string $text İşlenecek metin
 * @return string İşlenmiş metin
 */
function format_forum_text($text) {
    // İzin verilen HTML etiketleri ve nitelikleri (Quill çıktısına göre ayarlanabilir)
    $allowed_tags = '<p><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><blockquote><pre><ol><ul><li><sup><sub><a><img><span><br>';
    // Not: `style` niteliğine dikkatli izin verilmeli veya daha güvenli bir kütüphane kullanılmalıdır.
    // Şimdilik temel etiketlere izin veriyoruz.
    
    // Güvenli olmayan etiketleri temizle, ancak izin verilenleri koru
    $text = strip_tags($text, $allowed_tags);
    
    // TODO: Daha sağlam bir HTML temizleme için HTML Purifier gibi bir kütüphane kullanmayı düşünün.
    
    // URL'leri bağlantıya dönüştürme (Quill zaten yapabilir, isteğe bağlı)
    // $text = preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" class="text-primary">$1</a>', $text);

    return $text;
}

/**
 * Zaman damgasını insan dostu formata dönüştürür
 * 
 * @param string $timestamp Zaman damgası
 * @return string Formatlanmış zaman
 */
function format_forum_date($timestamp) {
    $date = new DateTime($timestamp);
    $now = new DateTime();
    $diff = $now->diff($date);
    
    if ($diff->y > 0) {
        return $diff->y . ' yıl önce';
    } elseif ($diff->m > 0) {
        return $diff->m . ' ay önce';
    } elseif ($diff->d > 0) {
        return $diff->d . ' gün önce';
    } elseif ($diff->h > 0) {
        return $diff->h . ' saat önce';
    } elseif ($diff->i > 0) {
        return $diff->i . ' dakika önce';
    } else {
        return 'Az önce';
    }
}
