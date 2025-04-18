<?php
/**
 * Oyuncu Detayları Sayfası
 * 
 * FellasRP oyun veritabanındaki belirli bir oyuncunun detaylı bilgilerini gösterir
 * 
 * @package FellasRP
 * @version 2.0
 */

require_once 'includes/security_check.php'; // Güvenlik kontrolü
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Oyun veritabanına bağlan
/** @var \PDO $gameDb */
$gameDb = \Core\Database::getInstance('game')->getConnection();

// Oyuncu ID'sini al
$playerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Oyuncu bilgilerini al
$player = null;
$error = null;

if ($playerId > 0 && $gameDb) {
    try {
        $stmt = $gameDb->prepare("
            SELECT p.*, w.discord 
            FROM players p
            LEFT JOIN whitelist w ON p.citizenid = w.citizenid COLLATE utf8mb4_general_ci
            WHERE p.id = :id
        ");
        $stmt->bindParam(':id', $playerId, PDO::PARAM_INT);
        $stmt->execute();
        $player = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Sorgu hatası: " . $e->getMessage();
    }
}

// Oyuncu bulunamadıysa hata mesajı göster
if (!$player) {
    $error = "Oyuncu bulunamadı veya geçersiz ID.";
}

// JSON verilerini decode et
$moneyData = [];
$charInfo = [];
$jobInfo = [];
$metadata = [];

if ($player) {
    $moneyData = json_decode($player['money'], true) ?: [];
    $charInfo = json_decode($player['charinfo'], true) ?: [];
    $jobInfo = json_decode($player['job'], true) ?: [];
    $metadata = json_decode($player['metadata'], true) ?: [];
}

// Oyuncunun araç bilgilerini al
$vehicles = [];
if ($player && $gameDb) {
    try {
        $stmt = $gameDb->prepare("
            SELECT plate, garage, vehicle 
            FROM player_vehicles 
            WHERE citizenid = :citizenid
        ");
        $stmt->bindParam(':citizenid', $player['citizenid'], PDO::PARAM_STR);
        $stmt->execute();
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Hata durumunda sessizce devam et
    }
}

/**
 * Oyuncu bilgilerini güvenli bir şekilde göster
 * 
 * @param array $data Veri dizisi
 * @param string $key Anahtar
 * @param string $default Varsayılan değer
 * @return string Güvenli çıktı
 */
function displaySafe($data, $key, $default = 'Belirtilmemiş') {
    if (isset($data[$key]) && !empty($data[$key])) {
        return htmlspecialchars($data[$key]);
    }
    return $default;
}

/**
 * Para değerini formatlı göster
 * 
 * @param array $data Veri dizisi
 * @param string $key Anahtar
 * @param string $default Varsayılan değer
 * @return string Formatlı para değeri
 */
function displayMoney($data, $key, $default = '0') {
    if (isset($data[$key])) {
        return number_format((float)$data[$key]);
    }
    return $default;
}

/**
 * Oyuncunun tam adını döndür
 * 
 * @param array $charInfo Karakter bilgileri
 * @param string $defaultName Varsayılan isim
 * @return string Tam ad
 */
function getFullName($charInfo, $defaultName) {
    $firstName = isset($charInfo['firstname']) ? $charInfo['firstname'] : '';
    $lastName = isset($charInfo['lastname']) ? $charInfo['lastname'] : '';
    $fullName = trim("$firstName $lastName");
    
    if (!empty($fullName)) {
        return htmlspecialchars($fullName);
    }
    return htmlspecialchars($defaultName);
}

/**
 * Tarih formatını düzenle veya varsayılan değer döndür
 * 
 * @param string $dateString Tarih string'i
 * @param string $default Varsayılan değer
 * @return string Formatlanmış tarih
 */
function formatDateOrDefault($dateString, $default = 'Belirtilmemiş') {
    if (!empty($dateString)) {
        try {
            return formatDate($dateString);
        } catch (Exception $e) {
            return $default;
        }
    }
    return $default;
}
?>

<div class="container mx-auto px-4 py-6">
    <!-- Üst Başlık ve Geri Butonu -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <h1 class="text-2xl font-bold text-white mb-3 sm:mb-0">
            <i class="fas fa-user-circle text-primary mr-2"></i> Oyuncu Detayları
        </h1>
        <a href="players" class="bg-[#1E2021] hover:bg-[#2D3032] text-white px-4 py-2 rounded-lg transition-colors flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Oyunculara Dön
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="bg-red-500 text-white p-4 rounded-lg mb-6 shadow-lg animate__animated animate__fadeIn">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-3 text-xl"></i>
            <span><?php echo $error; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($gameDb === null): ?>
    <div class="bg-yellow-600 text-white p-4 rounded-lg mb-6 shadow-lg animate__animated animate__fadeIn">
        <div class="flex items-center">
            <i class="fas fa-database mr-3 text-xl"></i>
            <span>Oyun veritabanına bağlanılamadı. Lütfen veritabanı ayarlarını kontrol edin.</span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($player): ?>
    <!-- Sekme Navigasyonu -->
    <div class="mb-6 bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
        <div class="flex flex-wrap text-sm font-medium text-center border-b border-gray-700" id="playerTabs" role="tablist">
            <button class="tab-button active flex-grow p-4 text-white hover:bg-[#2D3032] transition-colors" 
                    id="overview-tab" data-target="overview-content">
                <i class="fas fa-info-circle mr-2"></i> Genel Bakış
            </button>
            <button class="tab-button flex-grow p-4 text-white hover:bg-[#2D3032] transition-colors" 
                    id="vehicles-tab" data-target="vehicles-content">
                <i class="fas fa-car mr-2"></i> Araçlar
                <span class="ml-2 bg-primary text-xs py-1 px-2 rounded-full"><?php echo count($vehicles); ?></span>
            </button>
            <button class="tab-button flex-grow p-4 text-white hover:bg-[#2D3032] transition-colors" 
                    id="details-tab" data-target="details-content">
                <i class="fas fa-list-alt mr-2"></i> Detaylar
            </button>
            <button class="tab-button flex-grow p-4 text-white hover:bg-[#2D3032] transition-colors" 
                    id="raw-tab" data-target="raw-content">
                <i class="fas fa-code mr-2"></i> Ham Veriler
            </button>
        </div>
    </div>

    <!-- Sekme İçerikleri -->
    <div class="tab-content-container">
        <!-- Genel Bakış Sekmesi -->
        <div id="overview-content" class="tab-content active animate__animated animate__fadeIn">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sol Panel - Oyuncu Profili -->
                <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-primary to-blue-700 px-6 py-8 text-center">
                        <div class="inline-block bg-white p-1 rounded-full mb-4">
                            <div class="bg-[#1E2021] rounded-full p-4">
                                <i class="fas fa-user text-5xl text-primary"></i>
                            </div>
                        </div>
                        <h2 class="text-xl font-bold text-white mb-1">
                            <?php echo getFullName($charInfo, $player['name']); ?>
                        </h2>
                        <div class="text-sm text-gray-200 mb-3">
                            <span class="bg-[#1E2021] px-3 py-1 rounded-full">
                                <?php echo displaySafe($jobInfo, 'name', 'İşsiz'); ?>
                            </span>
                        </div>
                        <div class="text-xs text-gray-300">
                            <i class="fas fa-id-card mr-1"></i> <?php echo displaySafe($player, 'citizenid'); ?>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="bg-primary bg-opacity-20 p-2 rounded-full mr-3">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">Steam Kullanıcı Adı</div>
                                    <div class="text-white"><?php echo displaySafe($player, 'name'); ?></div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="bg-primary bg-opacity-20 p-2 rounded-full mr-3">
                                    <i class="fas fa-phone text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">Telefon Numarası</div>
                                    <div class="text-white"><?php echo displaySafe($charInfo, 'phone'); ?></div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="bg-primary bg-opacity-20 p-2 rounded-full mr-3">
                                    <i class="fas fa-birthday-cake text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">Doğum Tarihi</div>
                                    <div class="text-white"><?php echo displaySafe($charInfo, 'birthdate'); ?></div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="bg-primary bg-opacity-20 p-2 rounded-full mr-3">
                                    <i class="fas fa-venus-mars text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">Cinsiyet</div>
                                    <div class="text-white">
                                        <?php 
                                        if (isset($charInfo['gender'])) {
                                            echo (int)$charInfo['gender'] === 0 ? 'Erkek' : 'Kadın';
                                        } else {
                                            echo 'Belirtilmemiş';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="bg-primary bg-opacity-20 p-2 rounded-full mr-3">
                                    <i class="fab fa-discord text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">Discord ID</div>
                                    <div class="text-white font-mono"><?php echo !empty($player['discord']) ? displaySafe($player, 'discord') : 'Yok'; ?></div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="bg-primary bg-opacity-20 p-2 rounded-full mr-3">
                                    <i class="fas fa-clock text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">Son Giriş</div>
                                    <div class="text-white"><?php echo formatDateOrDefault($player['last_updated']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orta Panel - Finans ve Meslek -->
                <div class="space-y-6">
                    <!-- Finans Bilgileri -->
                    <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-[#181A1B] px-6 py-4 border-b border-primary-dark">
                            <h2 class="text-lg font-semibold text-white flex items-center">
                                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i> Finans Bilgileri
                            </h2>
                        </div>
                        <div class="p-6">
                            <!-- Para Dağılımı Görseli -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="w-1/2 pr-2">
                                    <div class="bg-[#181A1B] rounded-lg p-4 text-center">
                                        <div class="text-xs text-gray-400 mb-1">Nakit</div>
                                        <div class="text-green-500 text-xl font-bold">
                                            $<?php echo displayMoney($moneyData, 'cash'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-1/2 pl-2">
                                    <div class="bg-[#181A1B] rounded-lg p-4 text-center">
                                        <div class="text-xs text-gray-400 mb-1">Banka</div>
                                        <div class="text-blue-500 text-xl font-bold">
                                            $<?php echo displayMoney($moneyData, 'bank'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Toplam Para -->
                            <div class="bg-gradient-to-r from-green-500 to-blue-500 rounded-lg p-4 text-center mb-4">
                                <div class="text-xs text-white mb-1">Toplam Varlık</div>
                                <div class="text-white text-2xl font-bold">
                                    $<?php 
                                        $cash = isset($moneyData['cash']) ? (float)$moneyData['cash'] : 0;
                                        $bank = isset($moneyData['bank']) ? (float)$moneyData['bank'] : 0;
                                        echo number_format($cash + $bank);
                                    ?>
                                </div>
                            </div>
                            
                            <?php if (isset($charInfo['account']) && !empty($charInfo['account'])): ?>
                            <div class="flex justify-between items-center mt-4 bg-[#181A1B] p-3 rounded-lg">
                                <span class="text-gray-400">Banka Hesabı:</span>
                                <span class="font-mono text-white"><?php echo displaySafe($charInfo, 'account'); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Meslek Bilgileri -->
                    <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-[#181A1B] px-6 py-4 border-b border-primary-dark">
                            <h2 class="text-lg font-semibold text-white flex items-center">
                                <i class="fas fa-briefcase text-yellow-500 mr-2"></i> Meslek Bilgileri
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="bg-[#181A1B] rounded-lg p-4 mb-4">
                                <div class="flex items-center">
                                    <div class="bg-yellow-500 bg-opacity-20 p-3 rounded-full mr-4">
                                        <i class="fas fa-briefcase text-yellow-500 text-xl"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-400">Meslek</div>
                                        <div class="text-white text-lg font-semibold">
                                            <?php echo displaySafe($jobInfo, 'name', 'İşsiz'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-[#181A1B] rounded-lg p-4 text-center">
                                    <div class="text-xs text-gray-400 mb-1">Rütbe</div>
                                    <div class="text-white">
                                        <?php 
                                        if (isset($jobInfo['grade']) && isset($jobInfo['grade']['name'])) {
                                            echo htmlspecialchars($jobInfo['grade']['name']);
                                        } else {
                                            echo 'Yok';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="bg-[#181A1B] rounded-lg p-4 text-center">
                                    <div class="text-xs text-gray-400 mb-1">Maaş</div>
                                    <div class="text-green-500">
                                        $<?php echo isset($jobInfo['payment']) ? number_format($jobInfo['payment']) : '0'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sağ Panel - Karakter Bilgileri -->
                <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-[#181A1B] px-6 py-4 border-b border-primary-dark">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-id-card text-blue-500 mr-2"></i> Karakter Bilgileri
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-[#181A1B] p-4 rounded-lg">
                                    <div class="text-xs text-gray-400 mb-1">İsim</div>
                                    <div class="text-white"><?php echo displaySafe($charInfo, 'firstname'); ?></div>
                                </div>
                                <div class="bg-[#181A1B] p-4 rounded-lg">
                                    <div class="text-xs text-gray-400 mb-1">Soyisim</div>
                                    <div class="text-white"><?php echo displaySafe($charInfo, 'lastname'); ?></div>
                                </div>
                            </div>
                            
                            <div class="bg-[#181A1B] p-4 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Milliyet</div>
                                <div class="text-white"><?php echo displaySafe($charInfo, 'nationality'); ?></div>
                            </div>
                            
                            <div class="bg-[#181A1B] p-4 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Lisans</div>
                                <div class="text-white font-mono text-sm break-all">
                                    <?php echo displaySafe($player, 'license'); ?>
                                </div>
                            </div>
                            
                            <?php if (isset($charInfo['backstory']) && !empty($charInfo['backstory'])): ?>
                            <div class="mt-4">
                                <div class="text-sm text-gray-400 mb-2">Karakter Hikayesi:</div>
                                <div class="bg-[#181A1B] p-4 rounded-lg text-sm text-white">
                                    <?php echo nl2br(htmlspecialchars($charInfo['backstory'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mt-4">
                                <div class="text-sm text-gray-400 mb-2">Hesap Bilgileri:</div>
                                <div class="bg-[#181A1B] p-4 rounded-lg">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-400">Oluşturulma:</span>
                                        <span class="text-white">
                                            <?php echo isset($player['created']) ? formatDateOrDefault($player['created']) : 'Bilinmiyor'; ?>
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-400">Son Güncelleme:</span>
                                        <span class="text-white"><?php echo formatDateOrDefault($player['last_updated']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Araçlar Sekmesi -->
        <div id="vehicles-content" class="tab-content hidden animate__animated animate__fadeIn">
            <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
                <div class="bg-[#181A1B] px-6 py-4 border-b border-primary-dark">
                    <h2 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-car text-blue-500 mr-2"></i> Araç Bilgileri
                        <span class="ml-2 bg-primary text-xs py-1 px-2 rounded-full"><?php echo count($vehicles); ?></span>
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (empty($vehicles)): ?>
                    <div class="bg-[#181A1B] p-8 rounded-lg text-center">
                        <i class="fas fa-car-crash text-gray-600 text-5xl mb-4"></i>
                        <div class="text-gray-400 text-lg">Bu oyuncuya ait kayıtlı araç bulunamadı.</div>
                    </div>
                    <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($vehicles as $vehicle): ?>
                        <div class="bg-gradient-to-br from-[#1E2021] to-[#181A1B] rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 border border-gray-800 transform hover:-translate-y-1">
                            <div class="bg-gradient-to-r from-blue-600 to-primary px-4 py-3 text-white">
                                <div class="flex justify-between items-center">
                                    <div class="font-bold"><?php echo htmlspecialchars(ucfirst(strtolower($vehicle['vehicle'] ?: 'Bilinmeyen Model'))); ?></div>
                                    <div class="bg-[#1E2021] px-2 py-1 rounded text-xs font-mono">
                                        <?php echo htmlspecialchars($vehicle['plate']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center justify-center mb-4">
                                    <div class="bg-[#181A1B] p-6 rounded-full">
                                        <i class="fas fa-car text-4xl text-primary"></i>
                                    </div>
                                </div>
                                
                                <div class="flex items-center mt-4 text-sm">
                                    <div class="bg-gray-800 p-2 rounded-full mr-3">
                                        <i class="fas fa-warehouse text-gray-400"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-400">Garaj</div>
                                        <div class="text-white">
                                            <?php echo htmlspecialchars($vehicle['garage'] ?: 'Bilinmeyen Garaj'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Detaylar Sekmesi -->
        <div id="details-content" class="tab-content hidden animate__animated animate__fadeIn">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Temel Bilgiler -->
                <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-[#181A1B] px-6 py-4 border-b border-primary-dark">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i> Temel Bilgiler
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between bg-[#181A1B] p-3 rounded-lg">
                                <span class="text-gray-400">ID:</span>
                                <span class="font-mono text-white"><?php echo displaySafe($player, 'id'); ?></span>
                            </div>
                            <div class="flex justify-between bg-[#181A1B] p-3 rounded-lg">
                                <span class="text-gray-400">Citizen ID:</span>
                                <span class="font-mono text-white"><?php echo displaySafe($player, 'citizenid'); ?></span>
                            </div>
                            <div class="flex justify-between bg-[#181A1B] p-3 rounded-lg">
                                <span class="text-gray-400">Steam Adı:</span>
                                <span class="text-white"><?php echo displaySafe($player, 'name'); ?></span>
                            </div>
                            <div class="flex justify-between bg-[#181A1B] p-3 rounded-lg">
                                <span class="text-gray-400">Telefon:</span>
                                <span class="text-white"><?php echo displaySafe($charInfo, 'phone'); ?></span>
                            </div>
                            <div class="flex justify-between bg-[#181A1B] p-3 rounded-lg">
                                <span class="text-gray-400">Cinsiyet:</span>
                                <span class="text-white">
                                    <?php 
                                    if (isset($charInfo['gender'])) {
                                        echo (int)$charInfo['gender'] === 0 ? 'Erkek' : 'Kadın';
                                    } else {
                                        echo 'Belirtilmemiş';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="flex justify-between bg-[#181A1B] p-3 rounded-lg">
                                <span class="text-gray-400">Doğum Tarihi:</span>
                                <span class="text-white"><?php echo displaySafe($charInfo, 'birthdate'); ?></span>
                            </div>
                            <div class="flex justify-between bg-[#181A1B] p-3 rounded-lg">
                                <span class="text-gray-400">Discord ID:</span>
                                <span class="font-mono text-white"><?php echo !empty($player['discord']) ? displaySafe($player, 'discord') : 'Yok'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lisans Bilgileri -->
                <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-[#181A1B] px-6 py-4 border-b border-primary-dark">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-id-badge text-blue-500 mr-2"></i> Lisans Bilgileri
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <div class="text-sm text-gray-400 mb-2">License:</div>
                            <div class="bg-[#181A1B] p-4 rounded-lg">
                                <span class="font-mono text-sm break-all block text-white">
                                    <?php echo displaySafe($player, 'license'); ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if (isset($metadata['licences']) && is_array($metadata['licences'])): ?>
                        <div class="mt-6">
                            <div class="text-sm text-gray-400 mb-2">Oyun İçi Lisanslar:</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <?php foreach ($metadata['licences'] as $licenseType => $hasLicense): ?>
                                <div class="bg-[#181A1B] p-3 rounded-lg flex items-center">
                                    <div class="mr-3">
                                        <?php if ($hasLicense): ?>
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        <?php else: ?>
                                        <i class="fas fa-times-circle text-red-500"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-white">
                                        <?php 
                                        $licenseName = '';
                                        switch ($licenseType) {
                                            case 'driver':
                                                $licenseName = 'Sürücü Belgesi';
                                                break;
                                            case 'weapon':
                                                $licenseName = 'Silah Ruhsatı';
                                                break;
                                            default:
                                                $licenseName = ucfirst($licenseType);
                                        }
                                        echo $licenseName;
                                        ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ham Veriler Sekmesi -->
        <div id="raw-content" class="tab-content hidden animate__animated animate__fadeIn">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Karakter Bilgileri JSON -->
                <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-[#181A1B] px-6 py-4 border-b border-primary-dark">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-user-tag text-blue-500 mr-2"></i> Karakter Bilgileri (JSON)
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="bg-[#181A1B] p-4 rounded-lg overflow-x-auto">
                            <pre class="text-xs text-gray-300"><?php echo htmlspecialchars(json_encode($charInfo, JSON_PRETTY_PRINT)); ?></pre>
                        </div>
                    </div>
                </div>
                
                <!-- Para Bilgileri JSON -->
                <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-[#181A1B] px-6 py-4 border-b border-primary-dark">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-money-bill-wave text-green-500 mr-2"></i> Para Bilgileri (JSON)
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="bg-[#181A1B] p-4 rounded-lg overflow-x-auto">
                            <pre class="text-xs text-gray-300"><?php echo htmlspecialchars(json_encode($moneyData, JSON_PRETTY_PRINT)); ?></pre>
                        </div>
                    </div>
                </div>
                
                <!-- Meslek Bilgileri JSON -->
                <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-[#181A1B] px-6 py-4 border-b border-primary-dark">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-briefcase text-yellow-500 mr-2"></i> Meslek Bilgileri (JSON)
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="bg-[#181A1B] p-4 rounded-lg overflow-x-auto">
                            <pre class="text-xs text-gray-300"><?php echo htmlspecialchars(json_encode($jobInfo, JSON_PRETTY_PRINT)); ?></pre>
                        </div>
                    </div>
                </div>
                
                <!-- Metadata JSON -->
                <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-[#181A1B] px-6 py-4 border-b border-primary-dark">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-database text-purple-500 mr-2"></i> Metadata (JSON)
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="bg-[#181A1B] p-4 rounded-lg overflow-x-auto">
                            <pre class="text-xs text-gray-300"><?php echo htmlspecialchars(json_encode($metadata, JSON_PRETTY_PRINT)); ?></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Sekme Sistemi için JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sekme değiştirme fonksiyonu
    function switchTab(targetId) {
        // Tüm sekme butonlarını ve içeriklerini gizle
        document.querySelectorAll('.tab-button').forEach(function(btn) {
            btn.classList.remove('active');
        });
        
        document.querySelectorAll('.tab-content').forEach(function(content) {
            content.classList.remove('active');
            content.classList.add('hidden');
        });
        
        // Seçilen sekme butonunu ve içeriğini göster
        document.getElementById(targetId.split('-')[0] + '-tab').classList.add('active');
        
        const contentElement = document.getElementById(targetId);
        contentElement.classList.remove('hidden');
        contentElement.classList.add('active');
    }
    
    // Sekme butonlarına tıklama olayı ekle
    document.querySelectorAll('.tab-button').forEach(function(button) {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            switchTab(targetId);
        });
    });
    
    // Mobil görünümde ham verileri açma/kapama
    const toggleRawData = document.getElementById('toggle-raw-data');
    const rawDataContent = document.getElementById('raw-data-content');
    
    if (toggleRawData && rawDataContent) {
        toggleRawData.addEventListener('click', function() {
            if (rawDataContent.classList.contains('hidden')) {
                rawDataContent.classList.remove('hidden');
                toggleRawData.querySelector('i').classList.remove('fa-chevron-down');
                toggleRawData.querySelector('i').classList.add('fa-chevron-up');
            } else {
                rawDataContent.classList.add('hidden');
                toggleRawData.querySelector('i').classList.remove('fa-chevron-up');
                toggleRawData.querySelector('i').classList.add('fa-chevron-down');
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
