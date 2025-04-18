<?php
// Admin bootstrap dosyasını dahil et - tüm güvenlik kontrolleri ve function dosyası dahil ediliyor
require_once 'includes/bootstrap_admin.php';

// Header'ı dahil et
include 'includes/header.php';

// İstatistikleri al
$total_applications = countApplications('all');
$unread_applications = countApplications('unread');
$approved_applications = countApplications('approved');
$rejected_applications = countApplications('rejected');

// Son 5 başvuruyu al
$recent_applications = getApplications('all', 5);
?>

<div class="container mx-auto">
    <?php if (isset($_GET['message']) && $_GET['message'] === 'success'): ?>
    <div class="mb-6 bg-[#002a00] border border-green-500 text-green-300 px-4 py-3 rounded-lg relative alert-dismissible" role="alert">
        <span class="block sm:inline">Başvuru başarıyla silindi.</span>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
    <div class="mb-6 bg-[#2a0000] border border-red-500 text-red-300 px-4 py-3 rounded-lg relative alert-dismissible" role="alert">
        <span class="block sm:inline">
            <?php 
            $error = $_GET['error'];
            if ($error === 'delete_failed') {
                echo 'Başvuru silinirken bir hata oluştu.';
            } elseif ($error === 'invalid_id') {
                echo 'Geçersiz başvuru ID\'si.';
            } else {
                echo 'Bir hata oluştu.';
            }
            ?>
        </span>
    </div>
    <?php endif; ?>
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-text-light">Genel <span class="text-primary">Bakış</span></h1>
        <div class="text-sm text-white">
            <i class="fas fa-calendar-alt text-primary mr-2"></i> <?php echo date('d.m.Y'); ?>
        </div>
    </div>
    
    <!-- İstatistikler -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card-bg rounded-lg shadow-lg p-6 border border-primary-dark">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-[#0a0a0a] text-primary mr-4 border border-primary-dark">
                    <i class="fas fa-file-alt text-xl"></i>
                </div>
                <div>
                    <p class="text-white text-sm">Toplam Başvuru</p>
                    <p class="text-2xl font-bold text-text-light"><?php echo $total_applications; ?></p>
                </div>
            </div>
        </div>
        
        <div class="card-bg rounded-lg shadow-lg p-6 border border-primary-dark">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-[#0a0a0a] text-primary mr-4 border border-primary-dark">
                    <i class="fas fa-envelope text-xl"></i>
                </div>
                <div>
                    <p class="text-white text-sm">Okunmamış</p>
                    <p class="text-2xl font-bold text-text-light"><?php echo $unread_applications; ?></p>
                </div>
            </div>
        </div>
        
        <div class="card-bg rounded-lg shadow-lg p-6 border border-primary-dark">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-[#0a0a0a] text-primary mr-4 border border-primary-dark">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-white text-sm">Onaylanan</p>
                    <p class="text-2xl font-bold text-text-light"><?php echo $approved_applications; ?></p>
                </div>
            </div>
        </div>
        
        <div class="card-bg rounded-lg shadow-lg p-6 border border-primary-dark">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-[#0a0a0a] text-primary mr-4 border border-primary-dark">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-white text-sm">Reddedilen</p>
                    <p class="text-2xl font-bold text-text-light"><?php echo $rejected_applications; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Son Başvurular -->
    <div class="card-bg rounded-lg shadow-lg p-6 border border-primary-dark">
        <div class="flex justify-between items-center mb-6 border-b border-primary-dark pb-3">
            <h2 class="text-xl font-bold text-primary">Son Başvurular</h2>
            <?php if ($unread_applications > 0): ?>
            <a href="unread" class="text-primary hover:text-primary-light transition-colors">
                Tüm okunmamış başvuruları görüntüle <i class="fas fa-arrow-right ml-1"></i>
            </a>
            <?php endif; ?>
        </div>
        
        <?php if (count($recent_applications) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">ID</th>
                        <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">Karakter Adı</th>
                        <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">Discord ID</th>
                        <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">Tarih</th>
                        <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">Durum</th>
                        <th class="py-3 px-4 border-b border-primary-dark bg-[#0a0a0a] text-left text-xs font-medium text-white uppercase tracking-wider">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-dark">
                    <?php foreach ($recent_applications as $application): ?>
                    <tr class="hover:bg-[#0a0a0a] transition-colors">
                        <td class="py-3 px-4"><?php echo $application['id']; ?></td>
                        <td class="py-3 px-4 text-text-light"><?php echo sanitizeOutput($application['character_name']); ?></td>
                        <td class="py-3 px-4 text-white"><?php echo sanitizeOutput($application['discord_id']); ?></td>
                        <td class="py-3 px-4 text-white"><?php echo formatDate($application['created_at']); ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusClass($application['status']); ?>">
                                <?php echo getStatusText($application['status']); ?>
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="view_application?id=<?php echo $application['id']; ?>" class="text-primary hover:text-primary-light transition-colors">
                                <i class="fas fa-eye"></i> Görüntüle
                            </a>
                            <?php if (isAdmin()): ?>
                            <a href="delete_application?id=<?php echo $application['id']; ?>&redirect=index" 
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
        <?php else: ?>
        <div class="text-center py-8">
            <i class="fas fa-inbox text-primary text-4xl mb-4"></i>
            <p class="text-white">Henüz başvuru bulunmuyor.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
