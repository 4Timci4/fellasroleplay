<!-- Konular Tab -->
<div class="card bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-8">
    <div class="p-5 border-b border-gray-700">
        <h3 class="text-xl font-bold text-primary">Forum Konuları</h3>
    </div>
    <div class="p-5">
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="text-gray-400 mb-4 md:mb-0">
                Toplam <span class="text-white font-medium"><?php echo $topics_data['pagination']['total_topics']; ?></span> konu bulundu
            </div>
            
            <div class="flex flex-col md:flex-row gap-4">
                <form class="flex" action="" method="get">
                    <input type="hidden" name="tab" value="topics">
                    <?php if (isset($_GET['category_id'])): ?>
                        <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($_GET['category_id']); ?>">
                    <?php endif; ?>
                    <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Konu ara..." class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full md:w-64 p-2.5 mr-2">
                    <button type="submit" class="bg-primary hover:bg-primary-light text-white py-2.5 px-4 rounded-lg transition duration-300">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <form class="flex" action="" method="get">
                    <input type="hidden" name="tab" value="topics">
                    <?php if (isset($_GET['search'])): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                    <?php endif; ?>
                    <select name="category_id" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5 mr-2" onchange="this.form.submit()">
                        <option value="">Tüm Kategoriler</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-750">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Başlık</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Yazar</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Durum</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Tarih</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($topics_data['topics'])): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <i class="far fa-comment-dots text-4xl mb-3"></i>
                                <p>Konu bulunamadı.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($topics_data['topics'] as $topic): ?>
                            <tr class="hover:bg-gray-750 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <a href="../forum-topic.php?id=<?php echo $topic['id']; ?>" class="text-primary hover:text-primary-light font-medium">
                                        <?php echo htmlspecialchars($topic['title']); ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-300">
                                    <?php echo htmlspecialchars($topic['category_name']); ?>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-300">
                                    <?php echo htmlspecialchars($topic['creator_username'] ?? 'Bilinmeyen'); ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($topic['status'] == 'sticky'): ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-900 text-blue-200">
                                            <i class="fas fa-thumbtack mr-1"></i> Sabit
                                        </span>
                                    <?php elseif ($topic['status'] == 'locked'): ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-900 text-yellow-200">
                                            <i class="fas fa-lock mr-1"></i> Kilitli
                                        </span>
                                    <?php elseif ($topic['status'] == 'deleted'): ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-900 text-red-200">
                                            <i class="fas fa-trash-alt mr-1"></i> Silinmiş
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-900 text-green-200">
                                            <i class="fas fa-check-circle mr-1"></i> Normal
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500">
                                    <?php echo date('d.m.Y', strtotime($topic['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="../forum-topic.php?id=<?php echo $topic['id']; ?>" class="text-blue-400 hover:text-blue-300" title="Görüntüle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" onclick="openTopicStatusModal(<?php echo $topic['id']; ?>, '<?php echo addslashes($topic['title']); ?>', '<?php echo $topic['status']; ?>')" class="text-yellow-400 hover:text-yellow-300" title="Durumu Değiştir">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        <a href="../edit-topic.php?id=<?php echo $topic['id']; ?>" class="text-green-400 hover:text-green-300" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" onclick="confirmDeleteTopic(<?php echo $topic['id']; ?>, '<?php echo addslashes($topic['title']); ?>')" class="text-red-400 hover:text-red-300" title="Sil">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Sayfalama -->
        <?php if ($topics_data['pagination']['total_pages'] > 1): ?>
            <div class="flex justify-center mt-6">
                <nav class="flex items-center space-x-2">
                    <?php
                    $current_page = $topics_data['pagination']['current_page'];
                    $total_pages = $topics_data['pagination']['total_pages'];
                    
                    // Önceki sayfa bağlantısı
                    if ($current_page > 1):
                    ?>
                        <a href="?tab=topics&page=<?php echo $current_page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category_id']) ? '&category_id=' . urlencode($_GET['category_id']) : ''; ?>" class="px-3 py-2 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-2 rounded-md bg-gray-700 text-gray-500 cursor-not-allowed">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    <?php endif; ?>
                    
                    <?php
                    // Sayfa numaraları
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    // İlk sayfa
                    if ($start_page > 1):
                    ?>
                        <a href="?tab=topics&page=1<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category_id']) ? '&category_id=' . urlencode($_GET['category_id']) : ''; ?>" class="px-3 py-2 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
                            1
                        </a>
                        <?php if ($start_page > 2): ?>
                            <span class="px-3 py-2 text-gray-500">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="px-3 py-2 rounded-md bg-primary text-white">
                                <?php echo $i; ?>
                            </span>
                        <?php else: ?>
                            <a href="?tab=topics&page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category_id']) ? '&category_id=' . urlencode($_GET['category_id']) : ''; ?>" class="px-3 py-2 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php
                    // Son sayfa
                    if ($end_page < $total_pages):
                    ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <span class="px-3 py-2 text-gray-500">...</span>
                        <?php endif; ?>
                        <a href="?tab=topics&page=<?php echo $total_pages; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category_id']) ? '&category_id=' . urlencode($_GET['category_id']) : ''; ?>" class="px-3 py-2 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    // Sonraki sayfa bağlantısı
                    if ($current_page < $total_pages):
                    ?>
                        <a href="?tab=topics&page=<?php echo $current_page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category_id']) ? '&category_id=' . urlencode($_GET['category_id']) : ''; ?>" class="px-3 py-2 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-2 rounded-md bg-gray-700 text-gray-500 cursor-not-allowed">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Konu Durum Değiştirme Modal -->
<div id="topicStatusModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-75 transition-opacity"></div>
        
        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4 text-blue-500">
                    <i class="fas fa-cog text-5xl"></i>
                </div>
                
                <h3 class="text-2xl font-bold text-center mb-2 text-white" id="topicStatusModalTitle">Konu Durumunu Değiştir</h3>
                
                <p class="text-center text-gray-300 mb-6" id="topicStatusModalText">
                    Bu konunun durumunu değiştir:
                </p>
                
                <form id="topicStatusForm" method="post" action="">
                    <input type="hidden" id="status_topic_id" name="topic_id">
                    <input type="hidden" name="update_topic_status" value="1">
                    
                    <div class="mb-4 grid grid-cols-2 gap-3">
                        <button type="submit" name="topic_status" value="normal" class="status-button bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-check-circle mr-2"></i> Normal
                        </button>
                        
                        <button type="submit" name="topic_status" value="sticky" class="status-button bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-thumbtack mr-2"></i> Sabit
                        </button>
                        
                        <button type="submit" name="topic_status" value="locked" class="status-button bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-3 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-lock mr-2"></i> Kilitli
                        </button>
                        
                        <button type="submit" name="topic_status" value="deleted" class="status-button bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-trash-alt mr-2"></i> Sil
                        </button>
                    </div>
                    
                    <div class="flex justify-center mt-4">
                        <button type="button" onclick="closeTopicStatusModal()" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                            İptal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Konu Silme Modal -->
<div id="deleteTopicModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-75 transition-opacity"></div>
        
        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4 text-red-500">
                    <i class="fas fa-exclamation-triangle text-5xl"></i>
                </div>
                
                <h3 class="text-2xl font-bold text-center mb-2 text-white" id="deleteTopicModalTitle">Konu Silinecek</h3>
                
                <p class="text-center text-gray-300 mb-6" id="deleteTopicModalText">
                    Bu konuyu silmek istediğinizden emin misiniz?
                </p>
                
                <form id="deleteTopicForm" method="post" action="">
                    <input type="hidden" id="delete_topic_id" name="topic_id">
                    <input type="hidden" name="delete_topic" value="1">
                    
                    <div class="flex justify-center space-x-4">
                        <button type="button" onclick="closeDeleteTopicModal()" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                            İptal
                        </button>
                        
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                            Evet, Sil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Topic Status Modal
function openTopicStatusModal(topicId, topicTitle, currentStatus) {
    document.getElementById('topicStatusModalTitle').innerText = 'Konu Durumunu Değiştir';
    document.getElementById('topicStatusModalText').innerText = 
        '"' + topicTitle + '" konusunun durumunu değiştir:';
    document.getElementById('status_topic_id').value = topicId;
    
    // Highlight current status
    const statusButtons = document.querySelectorAll('.status-button');
    statusButtons.forEach(button => {
        button.classList.remove('ring-2', 'ring-white');
        const buttonStatus = button.value || button.getAttribute('value');
        if (buttonStatus === currentStatus) {
            button.classList.add('ring-2', 'ring-white');
        }
    });
    
    document.getElementById('topicStatusModal').classList.remove('hidden');
}

function closeTopicStatusModal() {
    document.getElementById('topicStatusModal').classList.add('hidden');
}

// Topic Delete Modal
function confirmDeleteTopic(topicId, topicTitle) {
    document.getElementById('deleteTopicModalTitle').innerText = 'Konu Silinecek';
    document.getElementById('deleteTopicModalText').innerText = 
        '"' + topicTitle + '" konusunu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.';
    document.getElementById('delete_topic_id').value = topicId;
    
    document.getElementById('deleteTopicModal').classList.remove('hidden');
}

function closeDeleteTopicModal() {
    document.getElementById('deleteTopicModal').classList.add('hidden');
}

// ESC tuşuyla modalları kapatma
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeTopicStatusModal();
        closeDeleteTopicModal();
    }
});
</script>
