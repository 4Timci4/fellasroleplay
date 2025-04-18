<?php
require_once 'includes/security_check.php'; // Güvenlik kontrolü
require_once 'includes/functions.php';
include 'includes/header.php';

// Sayfalama için parametreleri al
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Reddedilen başvuruları al
$applications = getApplications('rejected', $limit, $offset);
$total_applications = countApplications('rejected');
$total_pages = ceil($total_applications / $limit);
?>

<div class="container mx-auto">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-2">
        <h1 class="text-3xl font-bold text-text-light">Reddedilen <span class="text-primary">Başvurular</span></h1>
        <span class="bg-[#2a0000] text-red-300 text-xs font-semibold px-3 py-1 rounded-lg border border-red-500">
            Toplam: <?php echo $total_applications; ?>
        </span>
    </div>
    
    <?php if (count($applications) > 0): ?>
    <div class="card-bg rounded-lg shadow-lg overflow-hidden border border-primary-dark">
        <table class="min-w-full divide-y divide-primary-dark">
            <thead>
                <tr>
                    <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">ID</th>
                    <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">Karakter Adı</th>
                    <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">Discord ID</th>
                    <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">Başvuru Tarihi</th>
                    <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">Red Tarihi</th>
                    <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">Reddeden</th>
                    <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-primary-dark">
                <?php foreach ($applications as $application): ?>
                <tr class="hover:bg-[#0a0a0a] transition-colors">
                    <td class="py-3 px-4"><?php echo $application['id']; ?></td>
                    <td class="py-3 px-4 text-text-light"><?php echo sanitizeOutput($application['character_name']); ?></td>
                    <td class="py-3 px-4 text-white"><?php echo sanitizeOutput($application['discord_id']); ?></td>
                    <td class="py-3 px-4 text-white"><?php echo formatDate($application['created_at']); ?></td>
                    <td class="py-3 px-4 text-white"><?php echo formatDate($application['updated_at']); ?></td>
                    <td class="py-3 px-4 text-primary">
                        <?php 
                        // İşlemi yapan admin kullanıcısının bilgilerini getir
                        $admin = !empty($application['admin_id']) ? \Services\UserService::getInstance()->getUserByDiscordId($application['admin_id']) : false;
                        if ($admin) {
                            echo sanitizeOutput($admin['username']);
                        } else {
                            echo '<span class="text-gray-500">Bilinmiyor</span>';
                        }
                        ?>
                    </td>
                    <td class="py-3 px-4">
                        <a href="view_application?id=<?php echo $application['id']; ?>" class="text-primary hover:text-primary-light transition-colors">
                            <i class="fas fa-eye"></i> Görüntüle
                        </a>
                        <?php if (isAdmin()): ?>
                        <a href="delete_application?id=<?php echo $application['id']; ?>&redirect=rejected" 
                           onclick="return confirm('Bu başvuruyu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');" 
                           class="text-red-500 hover:text-red-700 ml-3" title="Sil">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Sayfalama -->
    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center mt-6">
        <nav class="inline-flex flex-wrap justify-center gap-2 rounded-lg shadow-lg overflow-hidden">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="px-3 sm:px-4 py-2 text-sm font-medium text-text-light bg-[#0a0a0a] border border-primary-dark hover:bg-[#141414] transition-colors">
                <i class="fas fa-chevron-left mr-1"></i><span class="hidden sm:inline"> Önceki</span>
            </a>
            <?php endif; ?>
            
            <?php 
            // Sayfa numaralarını sınırla
            $startPage = max(1, $page - 2);
            $endPage = min($total_pages, $startPage + 4);
            if ($endPage - $startPage < 4) {
                $startPage = max(1, $endPage - 4);
            }
            ?>
            
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="px-3 py-2 text-sm font-medium <?php echo $i === $page ? 'text-primary bg-[#141414]' : 'text-text-light bg-[#0a0a0a] hover:bg-[#141414]'; ?> border border-primary-dark transition-colors">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="px-3 sm:px-4 py-2 text-sm font-medium text-text-light bg-[#0a0a0a] border border-primary-dark hover:bg-[#141414] transition-colors">
                <span class="hidden sm:inline">Sonraki </span><i class="fas fa-chevron-right ml-1"></i>
            </a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>
    
    <?php else: ?>
    <div class="card-bg rounded-lg shadow-lg p-8 text-center border border-primary-dark">
        <i class="fas fa-times-circle text-primary text-4xl mb-4"></i>
        <p class="text-white">Reddedilen başvuru bulunmuyor.</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
