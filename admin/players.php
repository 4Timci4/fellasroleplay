<?php
/**
 * Oyuncular Sayfası
 * 
 * FellasRP oyun veritabanındaki oyuncuların bilgilerini gösterir
 */
require_once 'includes/security_check.php'; // Güvenlik kontrolü
require_once 'includes/header.php';
require_once 'includes/db.php';

// Oyun veritabanına bağlan
/** @var \PDO $gameDb */
$gameDb = \Core\Database::getInstance('game')->getConnection();

// Arama parametrelerini al
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchField = isset($_GET['field']) ? $_GET['field'] : 'name';
$validFields = ['name', 'citizenid', 'discord', 'job'];
if (!in_array($searchField, $validFields)) {
    $searchField = 'name';
}

// Sayfalama için değişkenler
$limit = 15; // Sayfa başına gösterilecek oyuncu sayısı
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// WHERE koşulunu hazırla
$whereClause = "";
$searchParams = [];

if (!empty($search)) {
    switch ($searchField) {
        case 'name':
            $whereClause = "WHERE p.name LIKE :search OR JSON_EXTRACT(p.charinfo, '$.firstname') LIKE :search OR JSON_EXTRACT(p.charinfo, '$.lastname') LIKE :search";
            $searchParams[':search'] = "%$search%";
            break;
        case 'citizenid':
            $whereClause = "WHERE p.citizenid LIKE :search";
            $searchParams[':search'] = "%$search%";
            break;
        case 'discord':
            $whereClause = "WHERE w.discord LIKE :search";
            $searchParams[':search'] = "%$search%";
            break;
        case 'job':
            $whereClause = "WHERE JSON_EXTRACT(p.job, '$.name') LIKE :search";
            $searchParams[':search'] = "%$search%";
            break;
    }
}

// Toplam oyuncu sayısını al
$totalPlayers = 0;
if ($gameDb) {
    try {
        if (empty($whereClause)) {
            $stmt = $gameDb->query("SELECT COUNT(*) FROM players");
            $totalPlayers = $stmt->fetchColumn();
        } else {
            $countSql = "SELECT COUNT(*) FROM players p LEFT JOIN whitelist w ON p.citizenid = w.citizenid COLLATE utf8mb4_general_ci {$whereClause}";
            $stmt = $gameDb->prepare($countSql);
            foreach ($searchParams as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            $stmt->execute();
            $totalPlayers = $stmt->fetchColumn();
        }
    } catch (PDOException $e) {
        $error = "Sorgu hatası: " . $e->getMessage();
    }
}

// Toplam sayfa sayısını hesapla
$totalPages = ceil($totalPlayers / $limit);

// Oyuncuları al
$players = [];
if ($gameDb) {
    try {
        $sql = "
            SELECT p.id, p.citizenid, p.name, p.license, p.money, p.charinfo, p.job, p.last_updated, w.discord 
            FROM players p
            LEFT JOIN whitelist w ON p.citizenid = w.citizenid COLLATE utf8mb4_general_ci
            {$whereClause}
            ORDER BY p.last_updated DESC 
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $gameDb->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($searchParams as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        $stmt->execute();
        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Sorgu hatası: " . $e->getMessage();
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <h1 class="text-2xl font-bold text-white mb-2 sm:mb-0">
            <i class="fas fa-users text-primary mr-2"></i> Oyuncular
        </h1>
        <div class="text-sm text-gray-400">
            Toplam Oyuncu: <span class="text-primary font-semibold"><?php echo $totalPlayers; ?></span>
        </div>
    </div>
    
    <!-- Arama Formu -->
    <div class="bg-[#1E2021] rounded-lg shadow-lg p-4 mb-6">
        <form action="" method="GET" class="space-y-4">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-2/3">
                    <label for="search" class="block text-gray-400 text-sm mb-1">Arama</label>
                    <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Arama yapmak için yazın..." class="w-full bg-[#121314] border border-gray-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-primary">
                </div>
                <div class="w-full md:w-1/3">
                    <label for="field" class="block text-gray-400 text-sm mb-1">Arama Kriteri</label>
                    <select name="field" id="field" class="w-full bg-[#121314] border border-gray-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-primary">
                        <option value="name" <?php echo $searchField === 'name' ? 'selected' : ''; ?>>İsim (Oyuncu/Karakter)</option>
                        <option value="citizenid" <?php echo $searchField === 'citizenid' ? 'selected' : ''; ?>>Citizen ID</option>
                        <option value="discord" <?php echo $searchField === 'discord' ? 'selected' : ''; ?>>Discord ID</option>
                        <option value="job" <?php echo $searchField === 'job' ? 'selected' : ''; ?>>Meslek</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <?php if (!empty($search)): ?>
                <a href="players.php" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    <i class="fas fa-times mr-1"></i> Temizle
                </a>
                <?php endif; ?>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                    <i class="fas fa-search mr-1"></i> Ara
                </button>
            </div>
        </form>
    </div>

    <?php if (isset($error)): ?>
    <div class="bg-red-500 text-white p-4 rounded-lg mb-6">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <?php if ($gameDb === null): ?>
    <div class="bg-yellow-600 text-white p-4 rounded-lg mb-6">
        Oyun veritabanına bağlanılamadı. Lütfen veritabanı ayarlarını kontrol edin.
    </div>
    <?php endif; ?>
    
    <?php if (!empty($search)): ?>
    <div class="bg-[#181A1B] text-white p-4 rounded-lg mb-6">
        <p>
            <i class="fas fa-filter mr-1"></i> 
            <span class="font-semibold">"<?php echo htmlspecialchars($search); ?>"</span> için 
            <span class="font-semibold"><?php echo $totalPlayers; ?></span> sonuç bulundu. 
            <span class="text-xs text-gray-400">(Kriter: 
            <?php
                switch($searchField) {
                    case 'name': echo 'İsim'; break;
                    case 'citizenid': echo 'Citizen ID'; break;
                    case 'discord': echo 'Discord ID'; break;
                    case 'job': echo 'Meslek'; break;
                }
            ?>)
            </span>
        </p>
    </div>
    <?php endif; ?>

    <?php if (!empty($players)): ?>
    <div class="bg-[#1E2021] rounded-lg shadow-lg overflow-hidden mb-6">
        <!-- Mobil Görünüm (Kartlar) -->
        <div class="md:hidden">
            <?php foreach ($players as $player): ?>
            <?php 
                // JSON verileri decode et
                $moneyData = json_decode($player['money'], true) ?: [];
                $charInfo = json_decode($player['charinfo'], true) ?: [];
                $jobInfo = json_decode($player['job'], true) ?: [];
                
                // Para bilgilerini al
                $cash = isset($moneyData['cash']) ? number_format($moneyData['cash']) : '0';
                $bank = isset($moneyData['bank']) ? number_format($moneyData['bank']) : '0';
                
                // Karakter bilgilerini al
                $firstName = isset($charInfo['firstname']) ? $charInfo['firstname'] : '';
                $lastName = isset($charInfo['lastname']) ? $charInfo['lastname'] : '';
                $fullName = trim("$firstName $lastName");
                
                // Meslek bilgilerini al
                $jobName = isset($jobInfo['name']) ? $jobInfo['name'] : 'Yok';
                $jobGrade = isset($jobInfo['grade']['name']) ? $jobInfo['grade']['name'] : '';
                
                // Zaman hesaplama
                $lastUpdated = new DateTime($player['last_updated']);
                $now = new DateTime();
                $interval = $now->diff($lastUpdated);
                
                if ($interval->days > 0) {
                    $timeAgo = $interval->days . ' gün önce';
                } elseif ($interval->h > 0) {
                    $timeAgo = $interval->h . ' saat önce';
                } elseif ($interval->i > 0) {
                    $timeAgo = $interval->i . ' dakika önce';
                } else {
                    $timeAgo = 'Az önce';
                }
            ?>
            <div class="border-b border-gray-800 p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <?php if (!empty($fullName)): ?>
                            <div class="font-semibold text-white"><?php echo htmlspecialchars($fullName); ?></div>
                            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($player['name']); ?></div>
                        <?php else: ?>
                            <div class="font-semibold text-white"><?php echo htmlspecialchars($player['name']); ?></div>
                        <?php endif; ?>
                    </div>
                    <a href="player_details.php?id=<?php echo $player['id']; ?>" class="text-primary hover:text-primary-light" title="Detaylar">
                        <i class="fas fa-info-circle"></i>
                    </a>
                </div>
                
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <span class="text-gray-400">ID:</span>
                        <span class="text-white"><?php echo htmlspecialchars($player['id']); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400">Citizen ID:</span>
                        <span class="text-white font-mono text-xs"><?php echo htmlspecialchars($player['citizenid']); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400">Discord:</span>
                        <span class="text-white">
                            <?php if (!empty($player['discord'])): ?>
                                <span class="font-mono text-xs"><?php echo htmlspecialchars($player['discord']); ?></span>
                            <?php else: ?>
                                <span class="text-gray-500">Yok</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400">Son Giriş:</span>
                        <span class="text-white"><?php echo $timeAgo; ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400">Meslek:</span>
                        <span class="text-white"><?php echo htmlspecialchars($jobName); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400">Para:</span>
                        <span class="text-green-500">$<?php echo $cash; ?> / $<?php echo $bank; ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Masaüstü Görünüm (Tablo) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-[#181A1B] text-gray-300 border-b border-primary-dark">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Citizen ID</th>
                        <th class="px-4 py-3">İsim</th>
                        <th class="px-4 py-3">Discord ID</th>
                        <th class="px-4 py-3">Para Durumu</th>
                        <th class="px-4 py-3">Meslek</th>
                        <th class="px-4 py-3">Son Giriş</th>
                        <th class="px-4 py-3">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($players as $player): ?>
                    <?php 
                        // JSON verileri decode et
                        $moneyData = json_decode($player['money'], true) ?: [];
                        $charInfo = json_decode($player['charinfo'], true) ?: [];
                        $jobInfo = json_decode($player['job'], true) ?: [];
                        
                        // Para bilgilerini al
                        $cash = isset($moneyData['cash']) ? number_format($moneyData['cash']) : '0';
                        $bank = isset($moneyData['bank']) ? number_format($moneyData['bank']) : '0';
                        
                        // Karakter bilgilerini al
                        $firstName = isset($charInfo['firstname']) ? $charInfo['firstname'] : '';
                        $lastName = isset($charInfo['lastname']) ? $charInfo['lastname'] : '';
                        $fullName = trim("$firstName $lastName");
                        
                        // Meslek bilgilerini al
                        $jobName = isset($jobInfo['name']) ? $jobInfo['name'] : 'Yok';
                        $jobGrade = isset($jobInfo['grade']['name']) ? $jobInfo['grade']['name'] : '';
                        
                    ?>
                    <tr class="border-b border-gray-800 hover:bg-[#1A1C1D]">
                        <td class="px-4 py-3"><?php echo htmlspecialchars($player['id']); ?></td>
                        <td class="px-4 py-3 font-mono"><?php echo htmlspecialchars($player['citizenid']); ?></td>
                        <td class="px-4 py-3">
                            <?php if (!empty($fullName)): ?>
                                <div class="font-semibold"><?php echo htmlspecialchars($fullName); ?></div>
                                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($player['name']); ?></div>
                            <?php else: ?>
                                <?php echo htmlspecialchars($player['name']); ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if (!empty($player['discord'])): ?>
                                <div class="font-mono"><?php echo htmlspecialchars($player['discord']); ?></div>
                            <?php else: ?>
                                <span class="text-gray-500">Yok</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="text-green-500">Nakit: $<?php echo $cash; ?></span>
                                <span class="text-blue-500">Banka: $<?php echo $bank; ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span><?php echo htmlspecialchars($jobName); ?></span>
                                <?php if (!empty($jobGrade)): ?>
                                <span class="text-xs text-gray-400"><?php echo htmlspecialchars($jobGrade); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <?php 
                                $lastUpdated = new DateTime($player['last_updated']);
                                $now = new DateTime();
                                $interval = $now->diff($lastUpdated);
                                
                                if ($interval->days > 0) {
                                    $timeAgo = $interval->days . ' gün önce';
                                } elseif ($interval->h > 0) {
                                    $timeAgo = $interval->h . ' saat önce';
                                } elseif ($interval->i > 0) {
                                    $timeAgo = $interval->i . ' dakika önce';
                                } else {
                                    $timeAgo = 'Az önce';
                                }
                            ?>
                            <div class="flex flex-col">
                                <span><?php echo $timeAgo; ?></span>
                                <span class="text-xs text-gray-400"><?php echo $lastUpdated->format('d.m.Y H:i'); ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <a href="player_details.php?id=<?php echo $player['id']; ?>" class="text-primary hover:text-primary-light mr-2" title="Detaylar">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sayfalama -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6">
        <div class="flex flex-wrap justify-center gap-2">
            <?php 
            // Arama parametrelerini URL'ye ekle
            $searchParams = [];
            if (!empty($search)) {
                $searchParams[] = 'search=' . urlencode($search);
                $searchParams[] = 'field=' . urlencode($searchField);
            }
            $searchQueryString = !empty($searchParams) ? '&' . implode('&', $searchParams) : '';
            ?>
            
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1 . $searchQueryString; ?>" class="px-3 sm:px-4 py-2 bg-[#1E2021] text-white rounded-lg hover:bg-primary transition-colors text-sm">
                <i class="fas fa-chevron-left mr-1"></i><span class="hidden sm:inline"> Önceki</span>
            </a>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $startPage + 4);
            if ($endPage - $startPage < 4) {
                $startPage = max(1, $endPage - 4);
            }
            ?>
            
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?page=<?php echo $i . $searchQueryString; ?>" class="px-3 py-2 rounded-lg text-sm <?php echo $i === $page ? 'bg-primary text-white' : 'bg-[#1E2021] text-white hover:bg-primary-dark transition-colors'; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1 . $searchQueryString; ?>" class="px-3 sm:px-4 py-2 bg-[#1E2021] text-white rounded-lg hover:bg-primary transition-colors text-sm">
                <span class="hidden sm:inline">Sonraki </span><i class="fas fa-chevron-right ml-1"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <div class="bg-[#1E2021] rounded-lg shadow-lg p-8 text-center">
        <p class="text-gray-400">Hiç oyuncu bulunamadı.</p>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
