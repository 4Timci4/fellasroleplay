<?php
// Oturum kontrolü
include_once 'includes/auth_check.php';

// Fonksiyonları dahil et
include_once 'includes/functions.php';

// Header'ı dahil et
include 'includes/header.php';

// NOT: Bu sayfa şu anda devre dışı bırakılmıştır.
// Tekrar aktif etmek için includes/config.php dosyasındaki $showTeamPage değişkenini true olarak değiştirin.
?>

<!-- Devre Dışı Bildirim -->
<section class="relative pt-16 bg-bg-dark from-[#0f0f1a] to-bg-dark overflow-hidden">
    <div class="container relative mx-auto px-6 z-10">
        <div class="relative text-center mb-12 animate__animated animate__fadeIn">
            <h1 class="text-4xl md:text-5xl font-bold mb-6 text-primary">
                Yönetim Ekibi
            </h1>
            
            <!-- Dekoratif ayırıcı -->
            <div class="h-1 w-24 bg-primary mx-auto mb-8"></div>
            
            <div class="bg-[#141414] border border-gray-800 rounded-lg p-8 max-w-3xl mx-auto">
                <div class="text-yellow-500 text-5xl mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="text-2xl font-bold text-white mb-4">Bu Sayfa Geçici Olarak Kullanım Dışı</h2>
                <p class="text-gray-300 mb-4">
                    Yönetim ekibi sayfası şu anda güncellenmektedir ve geçici olarak kullanım dışıdır. 
                    Lütfen daha sonra tekrar ziyaret edin.
                </p>
                <a href="anasayfa.php" class="inline-block bg-primary hover:bg-primary-dark text-white font-medium py-2 px-6 rounded-lg transition-colors">
                    <i class="fas fa-home mr-2"></i>Ana Sayfaya Dön
                </a>
            </div>
        </div>
    </div>
</section>

<?php if ($showTeamPage) { ?>
<!-- Page Header -->
<section class="relative pt-16 bg-bg-dark from-[#0f0f1a] to-bg-dark overflow-hidden">
    <!-- Dekoratif arka plan öğeleri -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-5">
        <div class="absolute top-0 left-0 w-32 h-32 bg-primary rounded-full filter blur-xl opacity-20 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute top-0 right-0 w-48 h-48 bg-primary-light rounded-full filter blur-xl opacity-20 translate-x-1/2 -translate-y-1/2"></div>
    </div>
    
    <div class="container relative mx-auto px-6 z-10">
        <!-- Page Header -->
        <div class="relative text-center mb-12 animate__animated animate__fadeIn">
            <h1 class="text-4xl md:text-5xl font-bold mb-6 text-primary">
                Yönetim Ekibimiz
            </h1>
            
            <!-- Dekoratif ayırıcı -->
            <div class="h-1 w-24 bg-primary mx-auto mb-8"></div>
            
            <p class="text-xl text-text-light max-w-3xl mx-auto mb-4">
                Fellas Roleplay'in değerli ekip üyeleri
            </p>
        </div>
    </div>
</section>

<!-- Discord Roles Section -->
<section class="py-8 bg-bg-dark">
    <div class="container mx-auto px-4">
        <?php
        // Diğer roller için tek bir bölüm
        $otherRoleIds = [
            '1353795720716746884', // Yönetici
            '1285694535766245408', // Moderatör
        ];
        
        $allMembers = [];
        
        // Tüm üyeleri topla
        foreach ($otherRoleIds as $roleId) {
            $members = getDiscordMembersWithRole($roleId, 20);
            foreach ($members as $member) {
                // Üyeleri ID'lerine göre birleştir (tekrarları önle)
                $allMembers[$member['id']] = $member;
            }
        }
        
        if (!empty($allMembers)):
        ?>
        <div class="mb-12 team-section animate__animated animate__fadeIn" data-role="Ekip Üyeleri">
            <!-- Rol başlığı -->
            <div class="relative mb-6 bg-bg-dark from-blue-500/20 to-blue-700/10 p-4 rounded-lg border border-gray-800">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="flex items-center mb-2 md:mb-0">
                        <div class="w-8 h-8 rounded-full bg-[#141414] flex items-center justify-center mr-3 shadow-md">
                            <i class="fas fa-users text-primary text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Ekip Üyeleri</h3>
                            <p class="text-gray-300 text-sm">Sunucumuzun değerli ekip üyeleri</p>
                        </div>
                    </div>
                    <div class="text-gray-400">
                        <span class="bg-[#141414] px-3 py-1 rounded-full text-xs">
                            <i class="fas fa-users mr-1"></i><?php echo count($allMembers); ?> üye
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Üye kartları -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                <?php foreach ($allMembers as $member): ?>
                <div class="team-card" data-name="<?php echo sanitizeOutput($member['nick']); ?> <?php echo sanitizeOutput($member['username']); ?>">
                    <div class="group relative bg-[#141414] rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 border border-gray-800 hover:border-primary/30">
                        <!-- Üst kısım - Avatar -->
                        <div class="p-3 pb-2 flex flex-col items-center relative">
                            <!-- Arka plan dekoratif öğe -->
                            <div class="absolute top-0 left-0 right-0 h-12 bg-gradient-to-b from-blue-500/20 to-blue-700/10 opacity-30"></div>
                            
                            <!-- Avatar -->
                            <div class="relative z-10 w-16 h-16 rounded-full overflow-hidden mb-2 border-2 border-[#0a0a0a] group-hover:border-primary transition-all duration-300 shadow-md">
                                <?php if (!empty($member['avatar'])): ?>
                                <img src="https://cdn.discordapp.com/avatars/<?php echo $member['id']; ?>/<?php echo $member['avatar']; ?>.png" 
                                     alt="<?php echo sanitizeOutput($member['username']); ?>" 
                                     class="w-full h-full object-cover">
                                <?php else: ?>
                                <div class="w-full h-full bg-primary flex items-center justify-center text-white text-lg">
                                    <?php echo strtoupper(substr($member['username'], 0, 1)); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- İsim ve kullanıcı adı -->
                            <div class="text-center z-10">
                                <div class="font-medium text-white text-sm mb-0.5 truncate max-w-full" title="<?php echo sanitizeOutput($member['nick']); ?>">
                                    <?php echo sanitizeOutput($member['nick']); ?>
                                </div>
                                <a href="https://discord.com/users/<?php echo $member['id']; ?>" target="_blank" 
                                   class="text-xs text-gray-400 hover:text-primary transition-colors truncate max-w-full inline-flex items-center" 
                                   title="<?php echo sanitizeOutput($member['username']); ?>">
                                    <i class="fab fa-discord mr-0.5 text-xs"></i>
                                    <?php echo sanitizeOutput($member['username']); ?>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Alt kısım - Rol bilgisi kaldırıldı -->
                        <div class="px-3 py-1 bg-[#0a0a0a] border-t border-gray-800 text-center">
                            <span class="text-xs font-medium text-primary">
                                <i class="fas fa-users mr-0.5 text-xs"></i>
                                Ekip Üyesi
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php 
        endif;
        
        // Developer rolü için ayrı bölüm
        $developerRoleId = '1267751951307903017'; // Developer rolü
        $developerConfig = [
            'color' => 'from-purple-500/20 to-purple-700/10',
            'icon' => 'fa-code',
            'description' => 'Sunucunun yazılım geliştirme ekibi'
        ];
        
        $developers = getDiscordMembersWithRole($developerRoleId, 20);
        
        if (!empty($developers)):
        ?>
        <div class="mb-12 team-section animate__animated animate__fadeIn" data-role="Developer">
            <!-- Rol başlığı -->
            <div class="relative mb-6 bg-bg-dark <?php echo $developerConfig['color']; ?> p-4 rounded-lg border border-gray-800">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="flex items-center mb-2 md:mb-0">
                        <div class="w-8 h-8 rounded-full bg-[#141414] flex items-center justify-center mr-3 shadow-md">
                            <i class="fas <?php echo $developerConfig['icon']; ?> text-primary text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Developer</h3>
                            <p class="text-gray-300 text-sm"><?php echo $developerConfig['description']; ?></p>
                        </div>
                    </div>
                    <div class="text-gray-400">
                        <span class="bg-[#141414] px-3 py-1 rounded-full text-xs">
                            <i class="fas fa-users mr-1"></i><?php echo count($developers); ?> üye
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Üye kartları -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                <?php foreach ($developers as $member): ?>
                <div class="team-card" data-name="<?php echo sanitizeOutput($member['nick']); ?> <?php echo sanitizeOutput($member['username']); ?>">
                    <div class="group relative bg-[#141414] rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 border border-gray-800 hover:border-primary/30">
                        <!-- Üst kısım - Avatar -->
                        <div class="p-3 pb-2 flex flex-col items-center relative">
                            <!-- Arka plan dekoratif öğe -->
                            <div class="absolute top-0 left-0 right-0 h-12 bg-gradient-to-b <?php echo $developerConfig['color']; ?> opacity-30"></div>
                            
                            <!-- Avatar -->
                            <div class="relative z-10 w-16 h-16 rounded-full overflow-hidden mb-2 border-2 border-[#0a0a0a] group-hover:border-primary transition-all duration-300 shadow-md">
                                <?php if (!empty($member['avatar'])): ?>
                                <img src="https://cdn.discordapp.com/avatars/<?php echo $member['id']; ?>/<?php echo $member['avatar']; ?>.png" 
                                     alt="<?php echo sanitizeOutput($member['username']); ?>" 
                                     class="w-full h-full object-cover">
                                <?php else: ?>
                                <div class="w-full h-full bg-primary flex items-center justify-center text-white text-lg">
                                    <?php echo strtoupper(substr($member['username'], 0, 1)); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- İsim ve kullanıcı adı -->
                            <div class="text-center z-10">
                                <div class="font-medium text-white text-sm mb-0.5 truncate max-w-full" title="<?php echo sanitizeOutput($member['nick']); ?>">
                                    <?php echo sanitizeOutput($member['nick']); ?>
                                </div>
                                <a href="https://discord.com/users/<?php echo $member['id']; ?>" target="_blank" 
                                   class="text-xs text-gray-400 hover:text-primary transition-colors truncate max-w-full inline-flex items-center" 
                                   title="<?php echo sanitizeOutput($member['username']); ?>">
                                    <i class="fab fa-discord mr-0.5 text-xs"></i>
                                    <?php echo sanitizeOutput($member['username']); ?>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Alt kısım - Rol bilgisi -->
                        <div class="px-3 py-1 bg-[#0a0a0a] border-t border-gray-800 text-center">
                            <span class="text-xs font-medium text-primary">
                                <i class="fas <?php echo $developerConfig['icon']; ?> mr-0.5 text-xs"></i>
                                Developer
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php 
        endif;
        ?>
    </div>
</section>


<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<?php } // if (false) bloğunun sonu

include 'includes/footer.php'; 
?>
