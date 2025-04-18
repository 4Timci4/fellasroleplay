<?php
// Oturum kontrolü
include_once 'includes/auth_check.php';

// Veritabanı bağlantısı
require_once 'admin/includes/db.php';
use Core\Database;

// Form gönderilmişse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al
    $form_data = [];
    $errors = [];

    // Discord ID'nin oturumdaki ID ile aynı olduğunu kontrol et
    if (!isset($_POST['discord_id']) || $_POST['discord_id'] != $_SESSION['discord_user_id']) {
        $errors[] = 'Discord ID değiştirilemez. Lütfen sayfayı yenileyip tekrar deneyin.';
    }
    
    // Aynı Discord ID'sine sahip okunmamış başvuru kontrolü
    $discord_id = $_POST['discord_id'] ?? '';
    if (!empty($discord_id) && empty($errors)) {
        try {
            $conn = Database::getInstance('default')->getConnection();
            $stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE discord_id = :discord_id AND status = 'unread'");
            $stmt->bindParam(':discord_id', $discord_id);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $errors[] = 'Zaten okunmamış bir başvurunuz bulunmaktadır. Lütfen başvurunuzun değerlendirilmesini bekleyin.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }

    // Kuralları kabul etme kontrolü
    $terms_agreement = isset($_POST['terms_agreement']);
    if (!$terms_agreement) {
        $errors[] = 'Kuralları kabul etmeniz gereklidir.';
    }

    // Zorunlu alanları kontrol et
    $required_fields = [
        'discord_id' => 'Discord ID',
        'first_name' => 'Adınız',
        'age' => 'Yaşınız',
        'server_reason' => 'Sunucuyu tercih etme sebebinizi yazınız',
        'rp_hours' => 'Roleplay için günde kaç saat ayırabiliyorsunuz',
        'streaming' => 'Herhangi bir platformda yayın açıyor musunuz',
        'character_name' => 'Karakterinizin Adı ve Soyadı',
        'character_story' => 'Karakterinizin Hikayesi',
        'character_summary' => 'Karakterinizi 3 kelime ile özetler misiniz',
        'previous_servers' => 'Önceden oynamış olduğunuz sunucu isimlerini yazınız',
        'fivem_experience' => 'FiveM oynama sürenizi yazınız'
    ];

    foreach ($required_fields as $field => $label) {
        $field_value = $_POST[$field] ?? '';
        
        if (empty($field_value)) {
            $errors[] = $label . ' gereklidir.';
        }
        
        // Veriyi kaydet
        $form_data[$field] = $field_value;
    }
    
    // Özel alan işlemleri
    if (isset($_POST['streaming'])) {
        switch ($_POST['streaming']) {
            case 'kick':
                $streaming_kick_text = $_POST['streaming_kick_text'] ?? '';
                if (empty($streaming_kick_text)) {
                    $errors[] = 'Kick kullanıcı adınızı belirtmelisiniz.';
                } elseif (strlen($streaming_kick_text) > 100) {
                    $errors[] = 'Kick kullanıcı adı maksimum 100 karakter olmalıdır.';
                }
                $form_data['streaming_kick_text'] = $streaming_kick_text;
                break;
                
            case 'twitch':
                $streaming_twitch_text = $_POST['streaming_twitch_text'] ?? '';
                if (empty($streaming_twitch_text)) {
                    $errors[] = 'Twitch kullanıcı adınızı belirtmelisiniz.';
                } elseif (strlen($streaming_twitch_text) > 100) {
                    $errors[] = 'Twitch kullanıcı adı maksimum 100 karakter olmalıdır.';
                }
                $form_data['streaming_twitch_text'] = $streaming_twitch_text;
                break;
                
            case 'youtube':
                $streaming_youtube_text = $_POST['streaming_youtube_text'] ?? '';
                if (empty($streaming_youtube_text)) {
                    $errors[] = 'Youtube kanal adınızı belirtmelisiniz.';
                } elseif (strlen($streaming_youtube_text) > 100) {
                    $errors[] = 'Youtube kanal adı maksimum 100 karakter olmalıdır.';
                }
                $form_data['streaming_youtube_text'] = $streaming_youtube_text;
                break;
                
            case 'other':
                $stream_platform = $_POST['streaming_other_text'] ?? '';
                if (empty($stream_platform)) {
                    $errors[] = 'Diğer yayın platformu belirtilmelidir.';
                } elseif (strlen($stream_platform) > 100) {
                    $errors[] = 'Diğer yayın platformu maksimum 100 karakter olmalıdır.';
                }
                $form_data['stream_platform'] = $stream_platform;
                break;
        }
    }
    
    // Doğrulama kontrolleri
    if (!empty($form_data['discord_id']) && !is_numeric($form_data['discord_id'])) {
        $errors[] = 'Discord ID sadece sayı olmalıdır.';
    }
    
    if (!empty($form_data['age']) && (!is_numeric($form_data['age']) || $form_data['age'] < 0 || $form_data['age'] > 100)) {
        $errors[] = 'Yaşınız 0-100 arasında bir sayı olmalıdır.';
    }
    
    if (!empty($form_data['character_summary']) && strlen($form_data['character_summary']) > 100) {
        $errors[] = 'Karakter özeti maksimum 100 karakter olmalıdır.';
    }
    
    if (!empty($form_data['fivem_experience']) && (!is_numeric($form_data['fivem_experience']) || strlen($form_data['fivem_experience']) > 5)) {
        $errors[] = 'FiveM oynama süresi sadece sayı olmalıdır ve maksimum 5 karakter olmalıdır.';
    }

    // Hata yoksa veritabanına kaydet
    if (empty($errors)) {
        try {
            $conn = Database::getInstance('default')->getConnection();
            
            // SQL sorgusu için alanları ve değerleri hazırla
            $fields = array_keys($form_data);
            $placeholders = array_map(function ($field) {
                return ':' . $field; }, $fields);

            $sql = "INSERT INTO applications (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $conn->prepare($sql);

            // Parametreleri bağla
            foreach ($form_data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();

            // Başarılı mesajı ile başvuru sayfasına yönlendir, JavaScript ile 10 saniye sonra anasayfaya yönlendirilecek
            header("Location: basvuru?success=1");
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }

    // Hata varsa başvuru sayfasına yönlendir
    if (!empty($errors)) {
        $error_string = implode('|', $errors);
        header("Location: basvuru?error=" . urlencode($error_string));
        exit;
    }
} else {
    // Form gönderilmemişse ana sayfaya yönlendir
    header("Location: anasayfa");
    exit;
}
