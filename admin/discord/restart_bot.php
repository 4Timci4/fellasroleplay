<?php
require_once '../includes/security_check.php';
require_once '../includes/functions.php';

// Yönetici yetkisi kontrolü
if (!isAdmin()) {
    header("Location: ../index");
    exit;
}

$botPath = realpath('../../discord-bot');
$startCommand = 'start cmd.exe /c "cd /d ' . $botPath . ' && node index.js"';

// Botun çalışıp çalışmadığını kontrol et
function isNodeProcessRunning() {
    exec('tasklist /FI "IMAGENAME eq node.exe" /FO CSV', $output);
    return count($output) > 1; // Başlık satırından fazla satır varsa, node.exe çalışıyor demektir
}

// Çalışan node süreçlerini durdur
function stopNodeProcesses() {
    exec('taskkill /IM node.exe /F', $output, $returnCode);
    return $returnCode === 0;
}

$logs = [];
$success = false;
$message = '';

// Botu yeniden başlat işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // İşlem başarılı olarak kabul et, ancak hata olursa false yap
    $success = true;
    
    // Mevcut node.exe süreçlerini kontrol et
    if (isNodeProcessRunning()) {
        $logs[] = "Node.js süreçleri tespit edildi. Durduruluyor...";
        
        // Süreçleri durdur
        if (stopNodeProcesses()) {
            $logs[] = "Node.js süreçleri başarıyla durduruldu.";
        } else {
            $logs[] = "⚠️ Node.js süreçleri durdurulamadı!";
            $success = false;
        }
    } else {
        $logs[] = "Çalışan Node.js süreci bulunamadı.";
    }
    
    // Eski log dosyasını arşivle
    $logFile = $botPath . '/bot_log.txt';
    if (file_exists($logFile)) {
        $archiveLogFile = $botPath . '/bot_log_' . date('Y-m-d_H-i-s') . '.txt';
        if (rename($logFile, $archiveLogFile)) {
            $logs[] = "Günlük dosyası arşivlendi: " . basename($archiveLogFile);
        } else {
            $logs[] = "⚠️ Günlük dosyası arşivlenemedi!";
        }
    }
    
    // Node.js sunucusunu başlat
    $logs[] = "Discord botu başlatılıyor...";
    exec($startCommand, $output, $returnCode);
    
    if ($returnCode === 0) {
        $logs[] = "Discord botu başlatma komutu gönderildi.";
        $message = "Discord bot başarıyla yeniden başlatıldı!";
    } else {
        $logs[] = "⚠️ Discord botu başlatılamadı! Hata kodu: " . $returnCode;
        $message = "Discord bot yeniden başlatılamadı! Lütfen sunucu günlüklerini kontrol edin.";
        $success = false;
    }
}

// Log dosyasını kontrol et
$logContent = '';
if (file_exists($botPath . '/bot_log.txt')) {
    $logContent = file_get_contents($botPath . '/bot_log.txt');
}

// Sayfa
include '../includes/header.php';
?>

<div class="container mx-auto">
    <div class="mb-6">
        <a href="javascript:history.back()" class="text-primary hover:text-primary-light transition-colors inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Geri Dön
        </a>
    </div>
    
    <div class="card-bg rounded-lg shadow-lg overflow-hidden border border-primary-dark mb-8">
        <div class="px-6 py-4 border-b border-primary-dark">
            <h1 class="text-2xl font-bold text-text-light">Discord <span class="text-primary">Bot Yönetimi</span></h1>
        </div>
        
        <div class="p-6">
            <?php if (!empty($message)): ?>
            <div class="mb-6 <?php echo $success ? 'bg-[#002a00] border border-green-500 text-green-300' : 'bg-[#2a0000] border border-red-500 text-red-300'; ?> px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
            <?php endif; ?>
            
            <p class="mb-6 text-gray-300">Bu sayfadan Discord botunu yeniden başlatabilirsiniz. Bu işlem, tüm Discord üzerinden yapılan kontrol ve entegrasyonları yeniden başlatır.</p>
            
            <!-- İşlem Logları -->
            <?php if (!empty($logs)): ?>
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-2">İşlem Logları</h2>
                <div class="bg-gray-900 p-3 rounded text-sm">
                    <?php foreach ($logs as $log): ?>
                    <div class="py-1"><?php echo $log; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="flex gap-4 mb-8">
                <form method="POST" onsubmit="return confirm('Discord botunu yeniden başlatmak istediğinize emin misiniz?');">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-sync-alt mr-2"></i> Botu Yeniden Başlat
                    </button>
                </form>
                
                <a href="test_role.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-vial mr-2"></i> Rol Atama Testi
                </a>
                
                <a href="check_user.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-user-check mr-2"></i> Kullanıcı Kontrolü
                </a>
            </div>
            
            <!-- Bot Günlüğü -->
            <div>
                <h2 class="text-lg font-semibold mb-2">Bot Günlüğü</h2>
                <?php if (!empty($logContent)): ?>
                <div class="bg-gray-900 p-3 rounded text-sm overflow-y-auto max-h-[400px]">
                    <pre class="whitespace-pre-wrap text-gray-300"><?php echo htmlspecialchars($logContent); ?></pre>
                </div>
                <?php else: ?>
                <div class="bg-gray-900 p-3 rounded text-sm">
                    <p class="text-gray-300">Günlük dosyası bulunamadı veya boş.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
