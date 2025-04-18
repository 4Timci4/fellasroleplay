<?php
/**
 * Forum ayarları ve bakım modu yönetimi için görünüm dosyası.
 * GET parametreleriyle doğrudan işlem yapma özelliği içerir.
 */

// GET parametreleri ile bakım modu işlemleri
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$message_type = 'success';

// Eğer GET parametresi ile bir işlem yapılıyorsa
if (!empty($action)) {
    // Bakım modunu etkinleştir - Yetkililer her zaman erişebilir
    if ($action === 'enable') {
        if (enable_maintenance_mode()) {
            // Yetkililerin her zaman erişebilmesi için ayarla
            update_maintenance_settings(['allow_staff_access' => true]);
            $message = 'Bakım modu başarıyla etkinleştirildi. Yetkililer ve yöneticiler erişebilir.';
        } else {
            $message = 'Bakım modu etkinleştirilirken bir hata oluştu.';
            $message_type = 'error';
        }
    } 
    // Bakım modunu devre dışı bırak
    elseif ($action === 'disable') {
        if (disable_maintenance_mode()) {
            $message = 'Bakım modu başarıyla devre dışı bırakıldı.';
        } else {
            $message = 'Bakım modu devre dışı bırakılırken bir hata oluştu.';
            $message_type = 'error';
        }
    }
}

// Bakım modu ayarlarını getir
$maintenance_settings = get_maintenance_settings();
$allow_staff_access = isset($maintenance_settings['allow_staff_access']) ? $maintenance_settings['allow_staff_access'] : true;

// Bakım modunun mevcut durumunu kontrol et (değişken tanımlanmamışsa)
if (!isset($maintenance_active)) {
    $maintenance_active = function_exists('is_maintenance_mode') ? is_maintenance_mode() : false;
}

// Bakım dosyasını kontrol et (bilgi amaçlı)
$maintenance_file = __DIR__ . '/../../maintenance.flag';
$file_status = file_exists($maintenance_file) ? 'Dosya mevcut' : 'Dosya yok';
?>

<div class="bg-gray-800 rounded-lg p-6 mb-8">
    <h2 class="text-2xl font-bold mb-6 text-white">
        <i class="fas fa-tools mr-3"></i>Bakım Modu Yönetimi
    </h2>
    
    <?php if ($message): ?>
        <div class="mb-6 bg-<?php echo $message_type === 'success' ? 'green-800' : 'red-700'; ?> text-white p-4 rounded-lg flex items-center">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> text-xl mr-3"></i>
            <span><?php echo $message; ?></span>
        </div>
    <?php endif; ?>
    
    <div class="mb-6 p-4 <?php echo $maintenance_active ? 'bg-amber-700/20' : 'bg-green-700/20'; ?> border-l-4 <?php echo $maintenance_active ? 'border-amber-500' : 'border-green-500'; ?> rounded">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas <?php echo $maintenance_active ? 'fa-exclamation-triangle text-amber-500' : 'fa-check-circle text-green-500'; ?> text-2xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-white">
                    <?php echo $maintenance_active ? 'Bakım Modu Aktif' : 'Bakım Modu Devre Dışı'; ?>
                </h3>
                <p class="mt-1 text-gray-300">
                    <?php 
                    if ($maintenance_active) {
                        echo 'Forum şu anda bakım modunda. Sadece yöneticiler ve yetkililer erişebilir.';
                    } else {
                        echo 'Forum normal şekilde çalışıyor ve tüm kullanıcılar tarafından erişilebilir.';
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Bakım Modu Düğmeleri -->
    <div class="flex flex-col items-center">
        <?php if ($maintenance_active): ?>
            <!-- Bakım Modu Aktif - Kapat -->
            <a href="forum-admin.php?tab=settings&action=disable" class="mb-4 px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors text-center">
                <i class="fas fa-power-off mr-2"></i>Bakım Modunu Kapat
            </a>
            <p class="text-gray-400 text-sm">Bakım modunu kapatarak foruma normal erişimi sağla</p>
        <?php else: ?>
            <!-- Bakım Modu Kapalı - Aç -->
            <a href="forum-admin.php?tab=settings&action=enable" class="mb-4 px-8 py-3 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition-colors text-center">
                <i class="fas fa-tools mr-2"></i>Bakım Modunu Aç
            </a>
            <p class="text-gray-400 text-sm">Bakım modunu açarak sadece yetkili ve yöneticilerin erişimine izin ver</p>
        <?php endif; ?>
    </div>
    
    <!-- Bakım Dosyası Durumu -->
    <div class="mt-8 bg-gray-700 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-white mb-2">
            <i class="fas fa-info-circle mr-1"></i>Bakım Dosyası Bilgisi
        </h3>
        <p class="text-gray-300">
            Durum: <?php echo $file_status; ?><br>
            <?php if (file_exists($maintenance_file)): ?>
                İçerik: <?php echo file_get_contents($maintenance_file); ?><br>
                İzinler: <?php echo substr(sprintf('%o', fileperms($maintenance_file)), -4); ?>
            <?php endif; ?>
        </p>
    </div>
</div>

<div class="bg-gray-800 rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6 text-white">
        <i class="fas fa-cogs mr-3"></i>Diğer Ayarlar
    </h2>
    
    <div class="text-gray-400 text-center py-6">
        <i class="fas fa-code text-4xl mb-3 text-gray-500"></i>
        <p>Diğer forum ayarları ilerleyen sürümlerde eklenecektir.</p>
    </div>
</div>
