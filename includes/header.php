<!DOCTYPE html>
<html lang="tr">
<?php
// Bootstrap dahil edilerek tüm yardımcı sınıflar ve fonksiyonlar yüklenir
require_once __DIR__ . '/bootstrap.php';

// Gerekli diğer dosyaları dahil et
include_once 'includes/maintenance.php';
include_once 'includes/session.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    refresh_discord_roles();
}

// Config değerlerini al
$site_name = \Core\Config::get('site.name', 'Fellas Roleplay');
$showTeamPage = \Core\Config::get('show_team_page', false);

// Forum admin kontrolü - bir kez çalıştır ve sonucu sakla
$is_admin = false;
$has_admin_access = false;
$show_admin_menu = false;

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Admin panel erişim kontrolü
    if (function_exists('has_admin_access')) {
        $has_admin_access = has_admin_access();
    }
    
    // Forum admin kontrolü
    if (function_exists('is_forum_admin') && isset($_SESSION['discord_user_id'])) {
        $is_admin = is_forum_admin($_SESSION['discord_user_id']);
    }
    
    // Geliştirme aşamasında kontrol
    $show_admin_menu = ($has_admin_access || $is_admin);
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_name; ?></title>
    <!-- Favicon -->
    <link rel="icon" href="assets/images/logo.png" type="image/png">
    <link rel="shortcut icon" href="assets/images/logo.png" type="image/png">
    <link rel="apple-touch-icon" href="assets/images/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg-dark': 'var(--bg-dark)',
                        'primary': 'var(--primary)',
                        'primary-dark': 'var(--primary-dark)',
                        'primary-light': 'var(--primary-light)',
                        'secondary': 'var(--secondary)',
                        'text-light': 'var(--text-light)'
                    },
                    boxShadow: {
                        'nav': '0 4px 20px -2px rgba(0, 0, 0, 0.2)',
                        'glow': '0 0 15px var(--primary)'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>

<body class="text-white min-h-screen bg-bg-dark">
    <header class="py-3 fixed top-0 left-0 right-0 z-50 bg-bg-dark backdrop-blur-md border-b border-primary">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <!-- Logo -->
            <div class="text-2xl font-bold flex-shrink-0">
                <a href="anasayfa" class="flex items-center group">
                    <div class="relative overflow-hidden rounded-full bg-black/40 p-1 mr-3">
                        <img src="assets/images/logo.png" alt="Fellas Roleplay Logo" class="h-10 md:h-12 w-auto transform group-hover:scale-110 transition-transform duration-300">
                        <div class="absolute inset-0 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-gradient-to-r from-primary-dark/30 to-primary-light/30"></div>
                    </div>
                    <span class="text-text-light group-hover:text-primary transition-all duration-300 font-semibold hidden sm:inline-block">Fellas 
                        <span class="relative text-primary">
                            Roleplay
                            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-primary group-hover:w-full transition-all duration-300"></span>
                        </span>
                    </span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex items-center space-x-1">
                <a href="anasayfa" class="nav-link relative px-4 py-2 rounded-lg text-gray-300 hover:text-primary transition-all duration-200 flex items-center overflow-hidden group">
                    <span class="absolute inset-0 bg-gradient-to-r from-primary/5 to-primary/10 opacity-0 group-hover:opacity-100 transform scale-x-0 group-hover:scale-x-100 transition-all duration-300 origin-left rounded-lg"></span>
                    <i class="fas fa-home text-primary mr-2 relative z-10"></i>
                    <span class="relative z-10">Ana Sayfa</span>
                </a>
                <a href="basvuru" class="nav-link relative px-4 py-2 rounded-lg text-gray-300 hover:text-primary transition-all duration-200 flex items-center overflow-hidden group">
                    <span class="absolute inset-0 bg-gradient-to-r from-primary/5 to-primary/10 opacity-0 group-hover:opacity-100 transform scale-x-0 group-hover:scale-x-100 transition-all duration-300 origin-left rounded-lg"></span>
                    <i class="fas fa-file-alt text-primary mr-2 relative z-10"></i>
                    <span class="relative z-10">Başvuru</span>
                </a>
                <a href="market" class="nav-link relative px-4 py-2 rounded-lg text-gray-300 hover:text-primary transition-all duration-200 flex items-center overflow-hidden group">
                    <span class="absolute inset-0 bg-gradient-to-r from-primary/5 to-primary/10 opacity-0 group-hover:opacity-100 transform scale-x-0 group-hover:scale-x-100 transition-all duration-300 origin-left rounded-lg"></span>
                    <i class="fas fa-shopping-cart text-primary mr-2 relative z-10"></i>
                    <span class="relative z-10">Market</span>
                </a>
                <a href="kurallar" class="nav-link relative px-4 py-2 rounded-lg text-gray-300 hover:text-primary transition-all duration-200 flex items-center overflow-hidden group">
                    <span class="absolute inset-0 bg-gradient-to-r from-primary/5 to-primary/10 opacity-0 group-hover:opacity-100 transform scale-x-0 group-hover:scale-x-100 transition-all duration-300 origin-left rounded-lg"></span>
                    <i class="fas fa-gavel text-primary mr-2 relative z-10"></i>
                    <span class="relative z-10">Kurallar</span>
                </a>
                <a href="forum" class="nav-link relative px-4 py-2 rounded-lg text-gray-300 hover:text-primary transition-all duration-200 flex items-center overflow-hidden group">
                    <span class="absolute inset-0 bg-gradient-to-r from-primary/5 to-primary/10 opacity-0 group-hover:opacity-100 transform scale-x-0 group-hover:scale-x-100 transition-all duration-300 origin-left rounded-lg"></span>
                    <i class="fas fa-comments text-primary mr-2 relative z-10"></i>
                    <span class="relative z-10">Forum</span>
                </a>
                <?php if ($showTeamPage): ?>
                <a href="yonetim-ekibi" class="nav-link relative px-4 py-2 rounded-lg text-gray-300 hover:text-primary transition-all duration-200 flex items-center overflow-hidden group">
                    <span class="absolute inset-0 bg-gradient-to-r from-primary/5 to-primary/10 opacity-0 group-hover:opacity-100 transform scale-x-0 group-hover:scale-x-100 transition-all duration-300 origin-left rounded-lg"></span>
                    <i class="fas fa-users text-primary mr-2 relative z-10"></i>
                    <span class="relative z-10">Yönetim Ekibi</span>
                </a>
                <?php endif; ?>
            </nav>

            <!-- Mobile Menu Button -->
            <div class="lg:hidden flex items-center">
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <a href="logout.php" class="mr-3 px-3 py-1.5 rounded-lg bg-red-600/80 hover:bg-red-700 text-white transition-all duration-200 flex items-center text-sm shadow-lg hover:shadow-red-900/30">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                </a>
                <?php endif; ?>
                
                <button id="mobile-menu-button" class="text-text-light p-2 rounded-lg bg-gray-800/80 hover:bg-gray-700/80 border border-gray-700/50 focus:outline-none transition-colors shadow-lg hover:shadow-primary/10">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </div>
            
            <!-- User Actions (Desktop) -->
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <div class="hidden lg:flex items-center ml-4 space-x-2">
                    <?php if ($show_admin_menu): ?>
                    <div class="relative" id="yonetim-dropdown">
                        <button class="px-4 py-2 rounded-lg bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white transition-all duration-300 flex items-center shadow-lg hover:shadow-primary/10 border border-gray-700/50">
                            <i class="fas fa-user-shield text-primary mr-2"></i>
                            <span>Yönetim</span>
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-gray-800/95 backdrop-blur-lg rounded-lg shadow-lg border border-primary/30 hidden z-50" id="yonetim-menu">
                            <?php if ($has_admin_access): ?>
                            <a href="admin/index.php" class="block px-4 py-3 text-sm hover:bg-gray-700/80 rounded-t-lg flex items-center text-purple-400 hover:text-white group transition-all duration-200">
                                <i class="fas fa-shield-alt mr-2 text-purple-500 group-hover:text-primary"></i>
                                <span>Admin Paneli</span>
                            </a>
                            <?php endif; ?>
                            <?php if ($is_admin): ?>
                            <a href="forum-admin.php" class="block px-4 py-3 text-sm hover:bg-gray-700/80 <?php echo !$has_admin_access ? 'rounded-t-lg' : ''; ?> <?php echo !$is_admin ? 'rounded-b-lg' : ''; ?> flex items-center text-blue-400 hover:text-white group transition-all duration-200">
                                <i class="fas fa-cog mr-2 text-blue-500 group-hover:text-primary"></i>
                                <span>Forum Yönetimi</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <a href="logout.php" class="px-4 py-2 rounded-lg bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white transition-all duration-200 flex items-center shadow-lg hover:shadow-red-900/30">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        <span>Çıkış Yap</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="lg:hidden hidden fixed inset-0 z-40 bg-black/90 backdrop-blur-md overflow-auto">
            <div class="min-h-screen flex flex-col">
                <div class="flex justify-between items-center p-4 border-b border-gray-800">
                    <div class="text-xl font-bold text-primary">Menü</div>
                    <button id="close-mobile-menu" class="text-white p-2 hover:text-primary">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="flex-grow p-4 space-y-1">
                    <a href="anasayfa" class="block py-3 px-4 hover:bg-gray-800/50 rounded-lg transition-colors flex items-center group">
                        <i class="fas fa-home mr-3 text-primary group-hover:scale-110 transition-transform"></i>
                        <span class="group-hover:translate-x-1 transition-transform">Ana Sayfa</span>
                    </a>
                    <a href="basvuru" class="block py-3 px-4 hover:bg-gray-800/50 rounded-lg transition-colors flex items-center group">
                        <i class="fas fa-file-alt mr-3 text-primary group-hover:scale-110 transition-transform"></i>
                        <span class="group-hover:translate-x-1 transition-transform">Başvuru</span>
                    </a>
                    <a href="market" class="block py-3 px-4 hover:bg-gray-800/50 rounded-lg transition-colors flex items-center group">
                        <i class="fas fa-shopping-cart mr-3 text-primary group-hover:scale-110 transition-transform"></i>
                        <span class="group-hover:translate-x-1 transition-transform">Market</span>
                    </a>
                    <a href="kurallar" class="block py-3 px-4 hover:bg-gray-800/50 rounded-lg transition-colors flex items-center group">
                        <i class="fas fa-gavel mr-3 text-primary group-hover:scale-110 transition-transform"></i>
                        <span class="group-hover:translate-x-1 transition-transform">Kurallar</span>
                    </a>
                    <a href="forum" class="block py-3 px-4 hover:bg-gray-800/50 rounded-lg transition-colors flex items-center group">
                        <i class="fas fa-comments mr-3 text-primary group-hover:scale-110 transition-transform"></i>
                        <span class="group-hover:translate-x-1 transition-transform">Forum</span>
                    </a>
                    <?php if ($showTeamPage): ?>
                    <a href="yonetim-ekibi" class="block py-3 px-4 hover:bg-gray-800/50 rounded-lg transition-colors flex items-center group">
                        <i class="fas fa-users mr-3 text-primary group-hover:scale-110 transition-transform"></i>
                        <span class="group-hover:translate-x-1 transition-transform">Yönetim Ekibi</span>
                    </a>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <div class="p-4 border-t border-gray-800 space-y-1">
                        <?php if ($show_admin_menu): ?>
                        <div class="mb-3">
                            <div class="text-sm text-gray-400 mb-1 px-4">Yönetim</div>
                            <?php if ($has_admin_access): ?>
                            <a href="admin/index.php" class="block py-2 px-4 hover:bg-gray-800/50 rounded-lg transition-colors flex items-center text-purple-400 hover:text-white group">
                                <i class="fas fa-shield-alt mr-3 text-purple-500 group-hover:scale-110 transition-transform"></i>
                                <span class="group-hover:translate-x-1 transition-transform">Admin Paneli</span>
                            </a>
                            <?php endif; ?>
                            <?php if ($is_admin): ?>
                            <a href="forum-admin.php" class="block py-2 px-4 hover:bg-gray-800/50 rounded-lg transition-colors flex items-center text-blue-400 hover:text-white group">
                                <i class="fas fa-cog mr-3 text-blue-500 group-hover:scale-110 transition-transform"></i>
                                <span class="group-hover:translate-x-1 transition-transform">Forum Yönetimi</span>
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <a href="logout.php" class="block py-3 px-4 bg-red-600/20 hover:bg-red-600/30 rounded-lg transition-colors flex items-center text-white group">
                            <i class="fas fa-sign-out-alt mr-3 text-red-500 group-hover:scale-110 transition-transform"></i>
                            <span class="group-hover:translate-x-1 transition-transform">Çıkış Yap</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content Spacer -->
    <div class="pt-20"></div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
        
        // Close mobile menu
        document.getElementById('close-mobile-menu').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.add('hidden');
            document.body.style.overflow = '';
        });
        
        // Yönetim dropdown toggle
        const dropdown = document.getElementById('yonetim-dropdown');
        if (dropdown) {
            dropdown.addEventListener('click', function(e) {
                const menu = document.getElementById('yonetim-menu');
                menu.classList.toggle('hidden');
                e.stopPropagation();
            });
            
            // Dışarı tıklandığında dropdown'ı kapat
            document.addEventListener('click', function() {
                const menu = document.getElementById('yonetim-menu');
                if (menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                }
            });
        }

        // Aktif sayfayı vurgula
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname.split('/').pop().split('.')[0];
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href === currentPath || (currentPath === '' && href === 'anasayfa')) {
                    link.classList.add('text-primary');
                    link.classList.add('bg-primary/5');
                }
            });
        });
    </script>
</body>
</html>
