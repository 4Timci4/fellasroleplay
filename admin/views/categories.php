<!-- Kategoriler Tab -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Kategori Listesi -->
    <div class="lg:col-span-2">
        <div class="card bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-5 border-b border-gray-700">
                <h3 class="text-xl font-bold text-primary">Forum Kategorileri</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-750">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kategori</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider w-32">Sıra</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider w-40">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-gray-500">
                                    <i class="far fa-folder-open text-4xl mb-3"></i>
                                    <p>Henüz kategori bulunmuyor.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            // Sadece ana kategorileri filtrele
                            $main_categories = array_filter($categories, function($cat) {
                                return !isset($cat['parent_id']) || $cat['parent_id'] === null;
                            });
                            
                            foreach ($main_categories as $category): 
                            ?>
                                <tr class="hover:bg-gray-750 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <?php if (isset($category['parent_id']) && $category['parent_id'] !== null): ?>
                                            <span class="ml-6">↳</span>
                                        <?php endif; ?>
                                        <div class="flex items-center">
                                            <div class="mr-3 flex-shrink-0">
                                                <i class="fas <?php echo htmlspecialchars($category['icon'] ?: 'fa-folder'); ?> text-xl" style="color: <?php echo htmlspecialchars($category['icon_color'] ?: '#747F8D'); ?>"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-white"><?php echo htmlspecialchars($category['name']); ?></div>
                                                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($category['description']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-300">
                                        <?php echo $category['display_order']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)" class="text-blue-400 hover:text-blue-300 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="confirmDeleteCategory(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>')" class="text-red-400 hover:text-red-300">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <?php
                                // Alt kategorileri göster
                                if (isset($category['id'])) {
                                    display_subcategories($categories, $category['id']);
                                }
                                ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Kategori Ekle/Düzenle Formu -->
    <div class="lg:col-span-1">
        <div class="card bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-5 border-b border-gray-700">
                <h3 class="text-xl font-bold text-primary">
                    <span id="categoryFormTitle">Kategori Ekle</span>
                </h3>
            </div>
            <div class="p-5">
                <form id="categoryForm" method="post" action="">
                    <input type="hidden" id="formAction" name="add_category" value="add_category">
                    <input type="hidden" id="category_id" name="category_id" value="">
                    
                    <div class="mb-4">
                        <label for="parent_id" class="block text-sm font-medium text-gray-300 mb-2">Üst Kategori (Opsiyonel)</label>
                        <select id="parent_id" name="parent_id" class="form-select bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5">
                            <option value="">-- Ana Kategori --</option>
                            <?php 
                            // Tüm kategorileri hiyerarşik olarak göster
                            function display_category_options($categories, $edit_category_id = 0, $parent_id = null, $level = 0) {
                                // Bu parent_id'ye sahip tüm kategorileri filtrele
                                $filtered_categories = array_filter($categories, function($cat) use ($parent_id) {
                                    return (!isset($cat['parent_id']) && $parent_id === null) || 
                                           (isset($cat['parent_id']) && $cat['parent_id'] == $parent_id);
                                });
                                
                                foreach ($filtered_categories as $cat) {
                                    // Eğer bu kategori, düzenlenen kategori ise gösterme (kendisini üst kategori olarak seçemez)
                                    if ($cat['id'] == $edit_category_id) {
                                        continue;
                                    }
                                    
                                    // Kategori ismini oluştur, seviyeye göre girintileme ekle
                                    $prefix = '';
                                    for ($i = 0; $i < $level; $i++) {
                                        $prefix .= '&nbsp;&nbsp;&nbsp;↳ ';
                                    }
                                    
                                    echo '<option value="' . $cat['id'] . '">' . $prefix . htmlspecialchars($cat['name']) . '</option>';
                                    
                                    // Bu kategorinin alt kategorilerini göster (recursive)
                                    // Eğer düzenlenen kategori ise, alt kategorilerini gösterme (döngüsel bağımlılık oluşmaması için)
                                    if ($cat['id'] != $edit_category_id) {
                                        display_category_options($categories, $edit_category_id, $cat['id'], $level + 1);
                                    }
                                }
                            }
                            
                            // Kategori seçeneklerini göster
                            display_category_options($categories, (isset($_GET['edit']) ? intval($_GET['edit']) : 0));
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Kategori Adı</label>
                        <input type="text" id="name" name="name" required class="form-input bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" placeholder="Kategori adı">
                    </div>
                    
                    <div class="mb-4">
                        <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">Slug (Opsiyonel)</label>
                        <input type="text" id="slug" name="slug" class="form-input bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" placeholder="kategori-slug (boş bırakılırsa otomatik oluşturulur)">
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Açıklama</label>
                        <textarea id="description" name="description" rows="3" class="form-textarea bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" placeholder="Kategori açıklaması"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="icon" class="block text-sm font-medium text-gray-300 mb-2">İkon</label>
                            <input type="text" id="icon" name="icon" class="form-input bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" placeholder="fa-folder">
                            <p class="text-xs text-gray-400 mt-1">FontAwesome ikonu (fa-folder, fa-users, vb.)</p>
                        </div>
                        
                        <div>
                            <label for="icon_color" class="block text-sm font-medium text-gray-300 mb-2">İkon Rengi</label>
                            <input type="text" id="icon_color" name="icon_color" class="form-input bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" placeholder="#747F8D">
                            <p class="text-xs text-gray-400 mt-1">HEX kod olarak (#747F8D, #FF5733, vb.)</p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="display_order" class="block text-sm font-medium text-gray-300 mb-2">Görüntüleme Sırası</label>
                        <input type="number" id="display_order" name="display_order" class="form-input bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" value="0" min="0">
                    </div>
                    
                    <div class="flex justify-between mt-6">
                        <button type="button" onclick="resetCategoryForm()" class="bg-gray-600 hover:bg-gray-500 text-white font-medium py-2 px-4 rounded-lg transition duration-300">
                            <i class="fas fa-undo mr-2"></i> Sıfırla
                        </button>
                        
                        <button type="submit" class="bg-primary hover:bg-primary-light text-white font-medium py-2 px-6 rounded-lg transition duration-300">
                            <i class="fas fa-save mr-2"></i> Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Kategori Silme Modal -->
<div id="deleteCategoryModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-75 transition-opacity"></div>
        
        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4 text-red-500">
                    <i class="fas fa-exclamation-triangle text-5xl"></i>
                </div>
                
                <h3 class="text-2xl font-bold text-center mb-2 text-white" id="deleteCategoryModalTitle">Kategori Silinecek</h3>
                
                <p class="text-center text-gray-300 mb-6" id="deleteCategoryModalText">
                    Bu kategoriyi silmek istediğinizden emin misiniz?
                </p>
                
                <form id="deleteCategoryForm" method="post" action="">
                    <input type="hidden" id="delete_category_id" name="category_id">
                    <input type="hidden" name="delete_category" value="1">
                    
                    <div class="flex justify-center space-x-4">
                        <button type="button" onclick="closeDeleteCategoryModal()" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
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
// Kategori formunu sıfırla
function resetCategoryForm() {
    document.getElementById('categoryFormTitle').innerText = 'Kategori Ekle';
    document.getElementById('formAction').name = 'add_category';
    document.getElementById('formAction').value = 'add_category';
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';
}

// Kategori düzenleme
function editCategory(category) {
    document.getElementById('categoryFormTitle').innerText = 'Kategori Düzenle';
    document.getElementById('formAction').name = 'update_category';
    document.getElementById('formAction').value = 'update_category';
    
    document.getElementById('category_id').value = category.id;
    document.getElementById('parent_id').value = category.parent_id || '';
    document.getElementById('name').value = category.name;
    document.getElementById('slug').value = category.slug;
    document.getElementById('description').value = category.description;
    document.getElementById('icon').value = category.icon;
    document.getElementById('icon_color').value = category.icon_color;
    document.getElementById('display_order').value = category.display_order;
    
    // Forma scroll
    document.getElementById('categoryForm').scrollIntoView({ behavior: 'smooth' });
}

// Kategori silme modalını aç
function confirmDeleteCategory(categoryId, categoryName) {
    document.getElementById('deleteCategoryModalTitle').innerText = 'Kategori Silinecek';
    document.getElementById('deleteCategoryModalText').innerText = 
        '"' + categoryName + '" kategorisini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.';
    document.getElementById('delete_category_id').value = categoryId;
    
    document.getElementById('deleteCategoryModal').classList.remove('hidden');
}

// Kategori silme modalını kapat
function closeDeleteCategoryModal() {
    document.getElementById('deleteCategoryModal').classList.add('hidden');
}

// ESC tuşuyla modalı kapatma
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteCategoryModal();
    }
});
</script>
