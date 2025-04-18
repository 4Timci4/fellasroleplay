<?php
// Güvenlik kontrolü
require_once 'includes/security_check.php'; // Güvenlik kontrolü

// Veritabanı bağlantısı
require_once 'includes/db.php';

// Hata mesajları
$errors = [];
$success = [];

try {
    $conn = \Core\Database::getInstance()->getConnection();
    
    // SQL dosyasını oku
    $sql = file_get_contents('setup.sql');
    
    // SQL komutlarını çalıştır
    $conn->exec($sql);
    
    $success[] = 'Veritabanı tabloları başarıyla oluşturuldu.';
    $success[] = 'Admin kullanıcısı başarıyla eklendi. (Kullanıcı adı: admin, Şifre: admin123)';
} catch (PDOException $e) {
    $errors[] = 'Veritabanı hatası: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fellas Roleplay - Admin Paneli Kurulumu</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg-dark': '#0e0e0e',
                        'primary': '#196cd9',
                        'primary-dark': '#002884',
                        'primary-light': '#4a93ff',
                        'secondary': '#002884',
                        'text-light': '#f7ffff'
                    }
                }
            }
        }
    </script>
    <style>
        .card-bg {
            @apply bg-[#141414] bg-opacity-90 backdrop-blur-sm;
        }
    </style>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-r from-primary-dark to-black text-white min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full card-bg rounded-lg shadow-lg p-8 border border-primary-dark">
            <div class="text-center mb-6">
                <i class="fas fa-shield-alt text-primary text-5xl mb-4"></i>
                <h1 class="text-2xl font-bold text-text-light">Fellas Roleplay <span class="text-primary">Admin Paneli Kurulumu</span></h1>
            </div>
            
            <?php if (!empty($errors)): ?>
            <div class="bg-[#2a0000] border border-red-500 text-red-300 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <strong class="font-bold">Hata!</strong>
                <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
            <div class="bg-[#002a00] border border-green-500 text-green-300 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <strong class="font-bold">Başarılı!</strong>
                <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($success as $message): ?>
                    <li><?php echo htmlspecialchars($message); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="mt-6">
                <a href="index.php" class="block w-full bg-primary hover:bg-primary-dark text-white font-bold py-3 px-4 rounded-lg text-center transition-colors duration-300 shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i> Admin Paneline Git
                </a>
            </div>
        </div>
    </div>
</body>
</html>
