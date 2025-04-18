<!-- Dashboard Tab -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Kart: Kategori Sayısı -->
    <div class="card p-6 rounded-lg bg-gray-800 shadow-lg flex items-start">
        <div class="bg-blue-500 p-3 rounded-lg mr-4">
            <i class="fas fa-folder text-2xl text-white"></i>
        </div>
        <div>
            <div class="text-3xl font-bold mb-1"><?php echo number_format($stats['category_count']); ?></div>
            <div class="text-sm text-gray-400">Kategori</div>
        </div>
    </div>
    
    <!-- Kart: Konu Sayısı -->
    <div class="card p-6 rounded-lg bg-gray-800 shadow-lg flex items-start">
        <div class="bg-purple-500 p-3 rounded-lg mr-4">
            <i class="fas fa-comments text-2xl text-white"></i>
        </div>
        <div>
            <div class="text-3xl font-bold mb-1"><?php echo number_format($stats['topic_count']); ?></div>
            <div class="text-sm text-gray-400">Konu</div>
        </div>
    </div>
    
    <!-- Kart: Yorum Sayısı -->
    <div class="card p-6 rounded-lg bg-gray-800 shadow-lg flex items-start">
        <div class="bg-green-500 p-3 rounded-lg mr-4">
            <i class="fas fa-comment-dots text-2xl text-white"></i>
        </div>
        <div>
            <div class="text-3xl font-bold mb-1"><?php echo number_format($stats['comment_count']); ?></div>
            <div class="text-sm text-gray-400">Yorum</div>
        </div>
    </div>
    
    <!-- Kart: Kullanıcı Sayısı -->
    <div class="card p-6 rounded-lg bg-gray-800 shadow-lg flex items-start">
        <div class="bg-red-500 p-3 rounded-lg mr-4">
            <i class="fas fa-users text-2xl text-white"></i>
        </div>
        <div>
            <div class="text-3xl font-bold mb-1"><?php echo number_format($stats['user_count']); ?></div>
            <div class="text-sm text-gray-400">Kullanıcı</div>
        </div>
    </div>
</div>

<!-- Son Aktiviteler -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Son Konular -->
    <div class="card bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-5 border-b border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-primary">Son Konular</h3>
            <a href="?tab=topics" class="text-blue-400 hover:text-blue-300 text-sm">Tümünü Görüntüle</a>
        </div>
        <div class="divide-y divide-gray-700">
            <?php if (empty($latest_topics)): ?>
                <div class="p-5 text-center text-gray-500">
                    <i class="far fa-comment-dots text-3xl mb-2"></i>
                    <p>Henüz konu bulunmuyor.</p>
                </div>
            <?php else: ?>
                <?php foreach ($latest_topics as $topic): ?>
                    <div class="p-4 hover:bg-gray-750 transition duration-150">
                        <div class="flex justify-between mb-1">
                            <a href="../forum-topic.php?id=<?php echo $topic['id']; ?>" class="text-primary hover:text-primary-light font-medium">
                                <?php echo htmlspecialchars($topic['title']); ?>
                            </a>
                            <span class="text-xs text-gray-500"><?php echo date('d.m.Y', strtotime($topic['created_at'])); ?></span>
                        </div>
                        <div class="text-sm flex items-center text-gray-400">
                            <span class="mr-3"><i class="fas fa-user text-xs mr-1"></i> <?php echo htmlspecialchars($topic['creator_username'] ?? 'Bilinmeyen'); ?></span>
                            <span><i class="fas fa-folder text-xs mr-1"></i> <?php echo htmlspecialchars($topic['category_name']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Son Yorumlar -->
    <div class="card bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-5 border-b border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-primary">Son Yorumlar</h3>
            <a href="?tab=topics" class="text-blue-400 hover:text-blue-300 text-sm">Tümünü Görüntüle</a>
        </div>
        <div class="divide-y divide-gray-700">
            <?php if (empty($latest_comments)): ?>
                <div class="p-5 text-center text-gray-500">
                    <i class="far fa-comments text-3xl mb-2"></i>
                    <p>Henüz yorum bulunmuyor.</p>
                </div>
            <?php else: ?>
                <?php foreach ($latest_comments as $comment): ?>
                    <div class="p-4 hover:bg-gray-750 transition duration-150">
                        <div class="flex justify-between mb-1">
                            <a href="../forum-topic.php?id=<?php echo $comment['topic_id']; ?>" class="text-primary hover:text-primary-light font-medium">
                                <?php echo htmlspecialchars($comment['topic_title']); ?>
                            </a>
                            <span class="text-xs text-gray-500"><?php echo date('d.m.Y', strtotime($comment['created_at'])); ?></span>
                        </div>
                        <div class="text-sm mb-2 text-gray-300">
                            <?php 
                            $content = strip_tags($comment['content']);
                            echo strlen($content) > 100 ? substr($content, 0, 100) . '...' : $content; 
                            ?>
                        </div>
                        <div class="text-xs text-gray-400">
                            <i class="fas fa-user text-xs mr-1"></i> <?php echo htmlspecialchars($comment['creator_username'] ?? 'Bilinmeyen'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
