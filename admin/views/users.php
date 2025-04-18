<!-- Kullanıcılar Tab -->
<div class="card bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-8">
    <div class="p-5 border-b border-gray-700">
        <h3 class="text-xl font-bold text-primary">Forum Kullanıcıları</h3>
    </div>
    <div class="p-5">
        <div class="mb-6 flex items-center justify-between">
            <div class="text-gray-400">Toplam <span class="text-white font-medium"><?php echo $users_data['pagination']['total_users']; ?></span> kullanıcı bulundu</div>
            
            <form class="flex items-center" action="" method="get">
                <input type="hidden" name="tab" value="users">
                <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Kullanıcı ara..." class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-64 p-2.5 mr-2">
                <button type="submit" class="bg-primary hover:bg-primary-light text-white py-2.5 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-750">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kullanıcı</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Discord ID</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Konular</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Yorumlar</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Son Giriş</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Durum</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($users_data['users'])): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                <i class="far fa-user text-4xl mb-3"></i>
                                <p>Kullanıcı bulunamadı.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users_data['users'] as $user): ?>
                            <tr class="hover:bg-gray-750 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php if (!empty($user['avatar'])): ?>
                                                <img class="h-10 w-10 rounded-full" src="<?php echo get_discord_avatar_url($user['avatar'], $user['discord_id']); ?>" alt="Avatar">
                                            <?php else: ?>
                                                <div class="h-10 w-10 rounded-full bg-gray-600 flex items-center justify-center text-gray-300">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-base font-medium text-white"><?php echo htmlspecialchars($user['username']); ?></div>
                                            <?php if (isset($user['role']) && !empty($user['role'])): ?>
                                                <div class="text-sm text-gray-400">
                                                    <?php if ($user['role'] == 'admin'): ?>
                                                        <span class="text-red-400">Admin</span>
                                                    <?php elseif ($user['role'] == 'moderator'): ?>
                                                        <span class="text-yellow-400">Moderatör</span>
                                                    <?php else: ?>
                                                        <span>Kullanıcı</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-400">
                                    <span class="font-mono"><?php echo htmlspecialchars($user['discord_id']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-300">
                                    <?php echo $user['post_count'] ?? 0; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-300">
                                    <?php echo $user['comment_count'] ?? 0; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-400">
                                    <?php echo $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Hiç'; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if (isset($user['is_banned']) && $user['is_banned']): ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-900 text-red-200">
                                            <i class="fas fa-ban mr-1"></i> Yasaklı
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-900 text-green-200">
                                            <i class="fas fa-check-circle mr-1"></i> Aktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="#" onclick="viewUserDetails('<?php echo $user['discord_id']; ?>')" class="text-blue-400 hover:text-blue-300" title="Detaylar">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                        <?php if (isset($user['is_banned']) && $user['is_banned']): ?>
                                            <a href="#" onclick="confirmUserAction('unban', '<?php echo $user['discord_id']; ?>', '<?php echo addslashes($user['username']); ?>')" class="text-green-400 hover:text-green-300" title="Yasağı Kaldır">
                                                <i class="fas fa-user-check"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="#" onclick="confirmUserAction('ban', '<?php echo $user['discord_id']; ?>', '<?php echo addslashes($user['username']); ?>')" class="text-yellow-400 hover:text-yellow-300" title="Yasakla">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="#" onclick="openUserRoleModal('<?php echo $user['discord_id']; ?>', '<?php echo addslashes($user['username']); ?>', '<?php echo $user['role'] ?? 'user'; ?>')" class="text-purple-400 hover:text-purple-300" title="Rol Değiştir">
                                            <i class="fas fa-user-tag"></i>
                                        </a>
                                        <a href="#" onclick="confirmDeleteUser('<?php echo $user['discord_id']; ?>', '<?php echo addslashes($user['username']); ?>')" class="text-red-400 hover:text-red-300" title="Sil">
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
        <?php if ($users_data['pagination']['total_pages'] > 1): ?>
            <div class="flex justify-center mt-6">
                <nav class="flex items-center space-x-2">
                    <?php
                    $current_page = $users_data['pagination']['current_page'];
                    $total_pages = $users_data['pagination']['total_pages'];
                    
                    // Önceki sayfa bağlantısı
                    if ($current_page > 1):
                    ?>
                        <a href="?tab=users&page=<?php echo $current_page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="px-3 py-2 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
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
                        <a href="?tab=users&page=1<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="px-3 py-2 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
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
                            <a href="?tab=users&page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="px-3 py-2 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
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
                        <a href="?tab=users&page=<?php echo $total_pages; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="px-3 py-2 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    // Sonraki sayfa bağlantısı
                    if ($current_page < $total_pages):
                    ?>
                        <a href="?tab=users&page=<?php echo $current_page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="px-3 py-2 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
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

<!-- Kullanıcı Rol Değiştirme Modal -->
<div id="userRoleModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-75 transition-opacity"></div>
        
        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4 text-purple-500">
                    <i class="fas fa-user-tag text-5xl"></i>
                </div>
                
                <h3 class="text-2xl font-bold text-center mb-2 text-white" id="userRoleModalTitle">Kullanıcı Rolünü Değiştir</h3>
                
                <p class="text-center text-gray-300 mb-6" id="userRoleModalText">
                    Bu kullanıcının rolünü değiştir:
                </p>
                
                <form id="userRoleForm" method="post" action="">
                    <input type="hidden" id="role_user_id" name="user_id">
                    <input type="hidden" name="update_user_role" value="1">
                    
                    <div class="mb-4 grid grid-cols-1 gap-3">
                        <button type="submit" name="user_role" value="admin" class="role-button bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-user-shield mr-2"></i> Admin
                        </button>
                        
                        <button type="submit" name="user_role" value="moderator" class="role-button bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-3 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-user-cog mr-2"></i> Moderatör
                        </button>
                        
                        <button type="submit" name="user_role" value="user" class="role-button bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-user mr-2"></i> Normal Kullanıcı
                        </button>
                    </div>
                    
                    <div class="flex justify-center mt-4">
                        <button type="button" onclick="closeUserRoleModal()" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                            İptal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Kullanıcı İşlem Onay Modal -->
<div id="userActionModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-75 transition-opacity"></div>
        
        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4 text-yellow-500" id="userActionIcon">
                    <i class="fas fa-exclamation-triangle text-5xl"></i>
                </div>
                
                <h3 class="text-2xl font-bold text-center mb-2 text-white" id="userActionModalTitle">Kullanıcı İşlemi</h3>
                
                <p class="text-center text-gray-300 mb-6" id="userActionModalText">
                    Bu işlemi yapmak istediğinizden emin misiniz?
                </p>
                
                <form id="userActionForm" method="post" action="">
                    <input type="hidden" id="action_user_id" name="user_id">
                    <input type="hidden" id="action_type" name="action_type" value="">
                    
                    <div class="flex justify-center space-x-4">
                        <button type="button" onclick="closeUserActionModal()" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                            İptal
                        </button>
                        
                        <button type="submit" id="confirmActionButton" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                            Onayla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Kullanıcı Detay Modal -->
<div id="userDetailsModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-75 transition-opacity"></div>
        
        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all max-w-2xl w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-700">
                    <h3 class="text-2xl font-bold text-white">Kullanıcı Detayları</h3>
                    <button type="button" onclick="closeUserDetailsModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="userDetailsContent" class="py-2">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/3 flex flex-col items-center mb-4 md:mb-0">
                            <div id="userAvatar" class="h-24 w-24 rounded-full bg-gray-600 flex items-center justify-center text-gray-300 mb-2">
                                <i class="fas fa-user text-4xl"></i>
                            </div>
                            <h4 id="userName" class="text-lg font-bold text-white mb-1">Kullanıcı Adı</h4>
                            <p id="userRole" class="text-primary mb-2">Rol</p>
                            <p id="userStatus" class="px-2 py-1 text-xs font-medium rounded-full bg-green-900 text-green-200">
                                <i class="fas fa-check-circle mr-1"></i> Aktif
                            </p>
                        </div>
                        
                        <div class="md:w-2/3 md:pl-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Discord ID</p>
                                    <p id="userDiscordId" class="mb-3 text-white font-mono text-sm"></p>
                                </div>
                                
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Kayıt Tarihi</p>
                                    <p id="userCreatedAt" class="mb-3 text-white"></p>
                                </div>
                                
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Son Giriş</p>
                                    <p id="userLastLogin" class="mb-3 text-white"></p>
                                </div>
                                
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Son Güncelleme</p>
                                    <p id="userUpdatedAt" class="mb-3 text-white"></p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 mt-2">
                                <div class="bg-gray-750 rounded-lg p-3 text-center">
                                    <p class="text-xs text-gray-400 mb-1">Konular</p>
                                    <p id="userTopicCount" class="text-xl font-bold text-primary">0</p>
                                </div>
                                
                                <div class="bg-gray-750 rounded-lg p-3 text-center">
                                    <p class="text-xs text-gray-400 mb-1">Yorumlar</p>
                                    <p id="userCommentCount" class="text-xl font-bold text-primary">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-gray-700">
                        <h4 class="text-lg font-bold text-white mb-3">Kullanıcı İşlemleri</h4>
                        
                        <div class="flex flex-wrap gap-2">
                            <button type="button" id="detailBanButton" onclick="" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition duration-300">
                                <i class="fas fa-ban mr-2"></i> Yasakla
                            </button>
                            
                            <button type="button" id="detailUnbanButton" onclick="" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-300 hidden">
                                <i class="fas fa-user-check mr-2"></i> Yasağı Kaldır
                            </button>
                            
                            <button type="button" id="detailRoleButton" onclick="" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition duration-300">
                                <i class="fas fa-user-tag mr-2"></i> Rol Değiştir
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="userDetailsLoading" class="py-8 text-center hidden">
                    <i class="fas fa-circle-notch fa-spin text-4xl text-primary mb-3"></i>
                    <p class="text-gray-400">Kullanıcı bilgileri yükleniyor...</p>
                </div>
                
                <div id="userDetailsError" class="py-8 text-center hidden">
                    <i class="fas fa-exclamation-circle text-4xl text-red-500 mb-3"></i>
                    <p class="text-gray-400">Kullanıcı bilgileri yüklenirken bir hata oluştu.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Kullanıcı Detayları Modal
function viewUserDetails(userId) {
    document.getElementById('userDetailsContent').classList.add('hidden');
    document.getElementById('userDetailsLoading').classList.remove('hidden');
    document.getElementById('userDetailsError').classList.add('hidden');
    document.getElementById('userDetailsModal').classList.remove('hidden');
    
    // Normalde AJAX ile kullanıcı detayları alınır
    // Ancak örnek için simüle ediyoruz
    setTimeout(() => {
        document.getElementById('userDetailsLoading').classList.add('hidden');
        
        // Kullanıcı bilgilerini doldur - bu değerler gerçek uygulamada AJAX'dan gelir
        document.getElementById('userDetailsContent').classList.remove('hidden');
        
        // Butonlara click handler ekle
        document.getElementById('detailBanButton').onclick = function() {
            closeUserDetailsModal();
            confirmUserAction('ban', userId, 'Kullanıcı');
        };
        
        document.getElementById('detailUnbanButton').onclick = function() {
            closeUserDetailsModal();
            confirmUserAction('unban', userId, 'Kullanıcı');
        };
        
        document.getElementById('detailRoleButton').onclick = function() {
            closeUserDetailsModal();
            openUserRoleModal(userId, 'Kullanıcı', 'user');
        };
        
    }, 500);
}

function closeUserDetailsModal() {
    document.getElementById('userDetailsModal').classList.add('hidden');
}

// Kullanıcı Rol Modal
function openUserRoleModal(userId, userName, currentRole) {
    document.getElementById('userRoleModalTitle').innerText = 'Kullanıcı Rolünü Değiştir';
    document.getElementById('userRoleModalText').innerText = 
        '"' + userName + '" kullanıcısının rolünü değiştir:';
    document.getElementById('role_user_id').value = userId;
    
    // Highlight current role
    const roleButtons = document.querySelectorAll('.role-button');
    roleButtons.forEach(button => {
        button.classList.remove('ring-2', 'ring-white');
        const buttonRole = button.value || button.getAttribute('value');
        if (buttonRole === currentRole) {
            button.classList.add('ring-2', 'ring-white');
        }
    });
    
    document.getElementById('userRoleModal').classList.remove('hidden');
}

function closeUserRoleModal() {
    document.getElementById('userRoleModal').classList.add('hidden');
}

// Kullanıcı Eylem Modal
function confirmUserAction(action, userId, userName) {
    const actionTypesConfig = {
        ban: {
            title: 'Kullanıcıyı Yasakla',
            text: '"' + userName + '" kullanıcısını yasaklamak istediğinizden emin misiniz?',
            buttonText: 'Yasakla',
            buttonClass: 'bg-yellow-600 hover:bg-yellow-700',
            iconClass: 'fas fa-ban text-5xl text-yellow-500'
        },
        unban: {
            title: 'Kullanıcı Yasağını Kaldır',
            text: '"' + userName + '" kullanıcısının yasağını kaldırmak istediğinizden emin misiniz?',
            buttonText: 'Yasağı Kaldır',
            buttonClass: 'bg-green-600 hover:bg-green-700',
            iconClass: 'fas fa-user-check text-5xl text-green-500'
        },
        delete: {
            title: 'Kullanıcıyı Sil',
            text: '"' + userName + '" kullanıcısını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',
            buttonText: 'Sil',
            buttonClass: 'bg-red-600 hover:bg-red-700',
            iconClass: 'fas fa-trash-alt text-5xl text-red-500'
        }
    };
    
    const config = actionTypesConfig[action];
    
    document.getElementById('userActionModalTitle').innerText = config.title;
    document.getElementById('userActionModalText').innerText = config.text;
    document.getElementById('action_user_id').value = userId;
    document.getElementById('action_type').value = action;
    
    // Icon
    document.getElementById('userActionIcon').innerHTML = '<i class="' + config.iconClass + '"></i>';
    
    // Button
    const button = document.getElementById('confirmActionButton');
    button.innerText = config.buttonText;
    
    // Remove all bg classes
    button.className = button.className
        .replace(/bg-\w+-\d+/g, '')
        .replace(/hover:bg-\w+-\d+/g, '')
        .trim();
    
    // Add new bg class
    button.className += ' ' + config.buttonClass;
    
    document.getElementById('userActionModal').classList.remove('hidden');
}

function closeUserActionModal() {
    document.getElementById('userActionModal').classList.add('hidden');
}

function confirmDeleteUser(userId, userName) {
    confirmUserAction('delete', userId, userName);
}

// ESC tuşuyla modalları kapatma
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeUserRoleModal();
        closeUserActionModal();
        closeUserDetailsModal();
    }
});
</script>
