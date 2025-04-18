<?php
require_once 'functions.php';

// Giriş sayfası hariç tüm sayfalarda giriş kontrolü yap
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'index.php' && $current_page !== 'index') {
    // Discord entegrasyonu için admin_id kontrolünü yumuşat
    if (!isset($_SESSION['discord_user_id']) || empty($_SESSION['discord_user_id'])) {
        header("Location: index.php");
        exit;
    }
    
    // Kullanıcının Discord rollerini güncelle (her sayfa yenilendiğinde)
    refreshDiscordRoles();
}

// Alt klasörde olup olmadığımızı kontrol et
$is_subdirectory = strpos(dirname($_SERVER['PHP_SELF']), '/discord') !== false;
$base_path = $is_subdirectory ? '../' : '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fellas Roleplay - Admin Paneli</title>
    <!-- Favicon -->
    <link rel="icon" href="<?php echo $is_subdirectory ? '../../assets/images/logo.png' : '../assets/images/logo.png'; ?>" type="image/png">
    <link rel="shortcut icon" href="<?php echo $is_subdirectory ? '../../assets/images/logo.png' : '../assets/images/logo.png'; ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo $is_subdirectory ? '../../assets/images/logo.png' : '../assets/images/logo.png'; ?>">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg-dark': '#0e0e0e',
                        'primary': '#196cd9',
                        'primary-dark': '#002884',
                        'primary-light': '#4a93ff',
                        'secondary': '#002884',
                        'text-light': '#f7ffff'
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar {
            min-height: calc(100vh - 64px);
        }
        .content-bg {
            @apply bg-[#181A1B];
        }
        .card-bg {
            @apply bg-[#181A1B] bg-opacity-90 backdrop-blur-sm;
        }
        
        /* Özel Scrollbar Stili */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #0a0a0a;
            border-radius: 4px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #196cd9;
            border-radius: 4px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #4a93ff;
        }
        
        /* Responsive Sidebar */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -100%;
                top: 64px;
                width: 250px;
                height: calc(100vh - 64px);
                z-index: 50;
                transition: left 0.3s ease;
                overflow-y: auto;
            }
            
            .sidebar.open {
                left: 0;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 64px;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
                display: none;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
        }
        
        /* Responsive Tablolar */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
    <!-- Stil Dosyaları -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $is_subdirectory ? '../../assets/css/style.css' : '../assets/css/style.css'; ?>">
</head>
<body class="bg-[#181A1B] text-white min-h-screen">
    <!-- Navbar -->
    <?php 
    // isLoggedIn yerine doğrudan Discord session kontrolü yapılıyor
    $showHeader = isset($_SESSION['discord_user_id']) || isset($_SESSION['admin_id']);
    if ($showHeader): 
    ?>
    <nav class="border-b border-primary shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="text-white mr-3 md:hidden">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <a href="<?php echo $base_path; ?>index" class="text-xl font-bold flex items-center group">
                        <i class="fas fa-shield-alt text-primary mr-2 text-2xl"></i>
                        <span class="text-text-light group-hover:text-primary transition-all duration-300 hidden sm:inline">Fellas Roleplay Admin</span>
                        <span class="text-text-light group-hover:text-primary transition-all duration-300 sm:hidden">FRP Admin</span>
                    </a>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <!-- Kullanıcı adı span'ı kaldırıldı -->
                </div>
            </div>
        </div>
    </nav>

    <div class="flex relative">
        <!-- Sidebar Overlay -->
        <div id="sidebar-overlay" class="sidebar-overlay"></div>
        
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar bg-[#181A1B] bg-opacity-90 text-white w-64 px-4 py-6 border-r border-primary-dark custom-scrollbar">
            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4 text-primary border-b-2 border-primary/50 pb-2">Başvurular</h2>
                <ul class="space-y-1">
                    <li>
                        <a href="<?php echo $base_path; ?>index"
                           class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200
                                  <?php echo $current_page === 'index.php' || $current_page === 'index'
                                      ? 'bg-[#0e0e0e] text-primary border-l-4 border-primary font-medium'
                                      : 'hover:bg-primary hover:text-white text-text-light/80 hover:text-white'; ?>">
                            <i class="fas fa-tachometer-alt mr-3 w-5 text-center <?php echo $current_page === 'index.php' || $current_page === 'index' ? 'text-primary' : ''; ?>"></i>
                            <span>Genel Bakış</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base_path; ?>unread"
                           class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200
                                  <?php echo $current_page === 'unread.php' || $current_page === 'unread'
                                      ? 'bg-[#0e0e0e] text-primary border-l-4 border-primary font-medium'
                                      : 'hover:bg-primary hover:text-white text-text-light/80 hover:text-white'; ?>">
                            <i class="fas fa-envelope mr-3 w-5 text-center <?php echo $current_page === 'unread.php' || $current_page === 'unread' ? 'text-primary' : ''; ?>"></i>
                            <span>Okunmamış Başvurular</span>
                            <?php $unread_count = countApplications('unread'); ?>
                            <?php if ($unread_count > 0): ?>
                            <span class="ml-auto bg-primary/80 text-white text-xs px-2 py-0.5 rounded-full"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base_path; ?>approved"
                           class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200
                                  <?php echo $current_page === 'approved.php' || $current_page === 'approved'
                                      ? 'bg-[#0e0e0e] text-primary border-l-4 border-primary font-medium'
                                      : 'hover:bg-primary hover:text-white text-text-light/80 hover:text-white'; ?>">
                            <i class="fas fa-check-circle mr-3 w-5 text-center <?php echo $current_page === 'approved.php' || $current_page === 'approved' ? 'text-primary' : ''; ?>"></i>
                            <span>Onaylanan Başvurular</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base_path; ?>rejected"
                           class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200
                                  <?php echo $current_page === 'rejected.php' || $current_page === 'rejected'
                                      ? 'bg-[#0e0e0e] text-primary border-l-4 border-primary font-medium'
                                      : 'hover:bg-primary hover:text-white text-text-light/80 hover:text-white'; ?>">
                            <i class="fas fa-times-circle mr-3 w-5 text-center <?php echo $current_page === 'rejected.php' || $current_page === 'rejected' ? 'text-primary' : ''; ?>"></i>
                            <span>Reddedilen Başvurular</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Şifre değiştirme menüsü kaldırıldı - Discord rol tabanlı yetkilendirme kullanılıyor -->

            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4 text-primary border-b-2 border-primary/50 pb-2">Oyun Yönetimi</h2>
                <ul class="space-y-1">
                    <li>
                         <a href="<?php echo $base_path; ?>players"
                           class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200
                                  <?php
                                  $is_player_page = ($current_page === 'players.php' || $current_page === 'players' || $current_page === 'player_details.php' || $current_page === 'player_details');
                                  echo $is_player_page
                                      ? 'bg-[#0e0e0e] text-primary border-l-4 border-primary font-medium'
                                      : 'hover:bg-primary hover:text-white text-text-light/80 hover:text-white'; ?>">
                            <i class="fas fa-users mr-3 w-5 text-center <?php echo $is_player_page ? 'text-primary' : ''; ?>"></i>
                            <span>Oyuncular</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <?php if (isAdmin()): ?>
            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4 text-primary border-b-2 border-primary/50 pb-2">Yönetim</h2>
                <ul class="space-y-1">
                    <li>
                        <a href="<?php echo $base_path; ?>users"
                           class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200
                                  <?php echo $current_page === 'users.php' || $current_page === 'users'
                                      ? 'bg-[#0e0e0e] text-primary border-l-4 border-primary font-medium'
                                      : 'hover:bg-primary hover:text-white text-text-light/80 hover:text-white'; ?>">
                            <i class="fas fa-users-cog mr-3 w-5 text-center <?php echo $current_page === 'users.php' || $current_page === 'users' ? 'text-primary' : ''; ?>"></i>
                            <span>Admin Kullanıcıları</span>
                        </a>
                    </li>
                    <li>
                         <a href="<?php echo $is_subdirectory ? 'settings' : 'discord/settings'; ?>"
                           class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200
                                  <?php
                                  // Check if the current script is in the 'discord' subdirectory AND its name is 'settings.php' OR check if the page name contains 'discord_settings'
                                  $is_discord_settings_page = (basename(dirname($_SERVER['PHP_SELF'])) === 'discord' && ($current_page === 'settings.php' || $current_page === 'settings'))
                                                          || (strpos($current_page, 'discord_settings') !== false);

                                  echo $is_discord_settings_page
                                      ? 'bg-[#0e0e0e] text-primary border-l-4 border-primary font-medium'
                                      : 'hover:bg-primary hover:text-white text-text-light/80 hover:text-white'; ?>">
                            <i class="fab fa-discord mr-3 w-5 text-center <?php echo $is_discord_settings_page ? 'text-primary' : ''; ?>"></i>
                            <span>Discord Ayarları</span>
                        </a>
                    </li>
                    <li>
                         <a href="<?php echo $is_subdirectory ? 'restart_bot' : 'discord/restart_bot'; ?>"
                           class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200
                                  <?php
                                  $is_discord_bot_page = (basename(dirname($_SERVER['PHP_SELF'])) === 'discord' && ($current_page === 'restart_bot.php' || $current_page === 'restart_bot' || $current_page === 'test_role.php' || $current_page === 'test_role' || $current_page === 'check_user.php' || $current_page === 'check_user'))
                                                       || (strpos($current_page, 'discord_bot') !== false);

                                  echo $is_discord_bot_page
                                      ? 'bg-[#0e0e0e] text-primary border-l-4 border-primary font-medium'
                                      : 'hover:bg-primary hover:text-white text-text-light/80 hover:text-white'; ?>">
                            <i class="fas fa-robot mr-3 w-5 text-center <?php echo $is_discord_bot_page ? 'text-primary' : ''; ?>"></i>
                            <span>Discord Bot Yönetimi</span>
                        </a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="mt-auto">
                <h2 class="text-lg font-semibold mb-4 text-primary border-b-2 border-primary/50 pb-2">Hızlı Erişim</h2>
                <ul class="space-y-1">
                    <li>
                        <a href="../anasayfa" class="flex items-center px-4 py-2.5 rounded-lg hover:bg-primary hover:text-white text-text-light/80 hover:text-white transition-colors duration-200">
                            <i class="fas fa-home mr-3 w-5 text-center"></i>
                            <span>Ana Sayfaya Dön</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
    <?php endif; ?>
