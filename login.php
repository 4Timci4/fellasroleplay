<?php
// Oturum başlat
session_start();

// Eğer kullanıcı zaten giriş yapmışsa, ana sayfaya yönlendir
// Whitelist rolü kontrolünü kaldırdık, sadece giriş yapmış olması yeterli
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: anasayfa');
    exit;
}

// Bootstrap dosyasını dahil et
require_once 'includes/bootstrap.php';

// Config dosyasını dahil et
include_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_site_config('site_name'); ?> - Giriş</title>
    <!-- Favicon -->
    <link rel="icon" href="assets/images/logo.png" type="image/png">
    <link rel="shortcut icon" href="assets/images/logo.png" type="image/png">
    <link rel="apple-touch-icon" href="assets/images/logo.png">
    <!-- Tailwind CSS CDN -->
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
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'fade-in': 'fadeIn 0.8s ease-out forwards',
                        'slide-up': 'slideUp 0.8s ease-out forwards',
                        'slide-in-right': 'slideInRight 0.8s ease-out forwards',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        slideInRight: {
                            '0%': { transform: 'translateX(20px)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' },
                        },
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Toastify JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Ana Stil Dosyası -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Temel Stiller */
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        
        /* Responsive Arka Plan */
        .login-container {
            min-height: 100vh;
            background-image: url('assets/images/login-ekran.png');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.4) 100%);
            z-index: 1;
        }
        
        /* Responsive İçerik Konteyneri */
        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            z-index: 10;
        }
        
        /* Login Kutusu Responsive Stilleri */
        .login-box {
            background-color: rgba(20, 20, 20, 0.7);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .login-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.05) 100%
            );
            transform: rotate(30deg);
            pointer-events: none;
        }
        
        .login-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }
        
        /* Discord Buton Responsive Stilleri */
        .discord-btn {
            background: linear-gradient(135deg, #5865F2 0%, #4752C4 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(88, 101, 242, 0.4);
            border-radius: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        
        .discord-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.2) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transition: all 0.6s ease;
        }
        
        .discord-btn:hover {
            background: linear-gradient(135deg, #4752C4 0%, #3b44a3 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(88, 101, 242, 0.6);
        }
        
        .discord-btn:hover::before {
            left: 100%;
        }
        
        /* Sosyal Medya İkonları Responsive Stilleri */
        .social-icon {
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .social-icon::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }
        
        .social-icon:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        
        .social-icon:hover::after {
            width: 150%;
            height: 150%;
        }
        
        /* Metin Stilleri */
        .title-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #d0d0d0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        
        .subtitle-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        
        /* Bildirim Stilleri */
        .toast-notification {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-family: 'Poppins', sans-serif;
            transition: all 0.5s ease !important;
            opacity: 1;
        }
        
        .toast-notification.hide {
            opacity: 0 !important;
            transform: translateX(100%);
        }
        
        /* Animasyon Gecikmeleri */
        .delay-100 {
            animation-delay: 100ms;
        }
        
        .delay-200 {
            animation-delay: 200ms;
        }
        
        .delay-300 {
            animation-delay: 300ms;
        }
        
        .delay-400 {
            animation-delay: 400ms;
        }
        
        .delay-500 {
            animation-delay: 500ms;
        }
        
        /* Responsive Düzenlemeler */
        @media (max-width: 640px) {
            .login-box {
                padding: 1.5rem !important;
            }
            
            .discord-btn {
                padding: 0.75rem 1rem !important;
                font-size: 1rem !important;
            }
            
            .social-icon {
                width: 2.5rem !important;
                height: 2.5rem !important;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            h1.title-gradient {
                font-size: 2.5rem !important;
                line-height: 1.2 !important;
            }
            
            .info-box {
                margin-bottom: 2rem !important;
            }
            
            .mobile-discord-btn {
                display: flex !important;
                margin-bottom: 2rem;
            }
        }
        
        @media (min-width: 1280px) {
            .container {
                max-width: 1200px;
            }
            
            .login-box {
                max-width: 450px;
            }
        }
        
        @media (min-width: 1536px) {
            .container {
                max-width: 1400px;
            }
            
            h1.title-gradient {
                font-size: 5rem !important;
            }
        }
    </style>
</head>
<body class="text-white">
    <div class="login-container flex items-center justify-center">
        <div class="container mx-auto px-4 py-8 relative z-10 flex flex-col md:flex-row items-center justify-between">
            <!-- Sol Taraf - Hoş Geldiniz Mesajı -->
            <div class="w-full md:w-3/5 lg:w-1/2 text-center md:text-left mb-10 md:mb-0 md:pr-6 lg:pr-12">
                <h2 class="text-base sm:text-lg font-medium mb-2 text-white opacity-0 animate-fade-in delay-100 subtitle-gradient">HOŞGELDİNİZ</h2>
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold mb-4 sm:mb-6 text-white opacity-0 animate-slide-up delay-200 title-gradient tracking-tight">FELLAS<br>ROLEPLAY</h1>
                <div class="bg-gray-800 bg-opacity-50 rounded-lg p-3 sm:p-4 mb-6 sm:mb-8 border border-gray-700 opacity-0 animate-fade-in delay-300 max-w-xl info-box">
                    <div class="flex items-start">
                        <div class="text-primary mr-2 sm:mr-3 mt-1">
                            <i class="fas fa-info-circle text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-medium mb-1 text-white">Discord Gerekli</h3>
                            <p class="text-sm sm:text-base text-gray-300">Siteye erişim için Discord sunucumuzda bulunmanız gerekmektedir. Gerçekçi roleplay deneyimi için doğru adrestesiniz.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Mobil için Discord butonu -->
                <div class="flex md:hidden justify-center space-x-4 opacity-0 animate-fade-in delay-400 mobile-discord-btn">
                    <?php if (get_social_link('discord')): ?>
                        <a href="<?php echo get_social_link('discord'); ?>" target="_blank" class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-all duration-300 shadow-lg w-full justify-center">
                            <i class="fab fa-discord mr-2 text-lg"></i>
                            <span>Discord'a Katıl</span>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Desktop için Discord butonu -->
                <div class="hidden md:flex space-x-4 opacity-0 animate-fade-in delay-400">
                    <?php if (get_social_link('discord')): ?>
                        <a href="<?php echo get_social_link('discord'); ?>" target="_blank" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-all duration-300 shadow-lg">
                            <i class="fab fa-discord mr-2 text-lg"></i>
                            <span>Discord'a Katıl</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sağ Taraf - Giriş Kutusu -->
            <div class="w-full md:w-2/5 lg:w-5/12 flex justify-center md:justify-end">
                <div class="login-box p-6 sm:p-8 w-full max-w-sm sm:max-w-md relative opacity-0 animate-slide-in-right delay-300">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-primary-light"></div>
                    
                    <div class="text-center mb-6 sm:mb-8">
                        <div class="flex justify-center mb-4 sm:mb-6">
                            <img src="assets/images/logo.png" alt="Fellas Logo" class="h-16 sm:h-20 animate-float">
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold mb-1 sm:mb-2 subtitle-gradient">Giriş Yap</h2>
                        <p class="text-gray-300 text-xs sm:text-sm">Devam etmek için Discord hesabınızla giriş yapın</p>
                    </div>
                    
                    <!-- Discord ile Giriş Butonu -->
                    <a href="auth" class="discord-btn flex items-center justify-center py-3 sm:py-4 px-4 sm:px-6 w-full mb-6 sm:mb-8 text-base sm:text-lg">
                        <i class="fab fa-discord mr-2 sm:mr-3 text-lg sm:text-xl"></i>
                        Discord ile giriş yap!
                    </a>
                    
                    <!-- Sosyal Medya Linkleri -->
                    <div class="text-center">
                        <p class="text-xs sm:text-sm mb-3 sm:mb-4 text-gray-400">Fellas Roleplay Sosyal Medya Hesapları</p>
                        <div class="flex justify-center space-x-3 sm:space-x-5">
                            <?php if (get_social_link('instagram')): ?>
                                <a href="<?php echo get_social_link('instagram'); ?>" target="_blank" class="social-icon bg-gradient-to-br from-purple-600 to-pink-600 w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center">
                                    <i class="fab fa-instagram text-base sm:text-lg"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (get_social_link('twitter')): ?>
                                <a href="<?php echo get_social_link('twitter'); ?>" target="_blank" class="social-icon bg-gradient-to-br from-blue-400 to-blue-600 w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center">
                                    <i class="fab fa-twitter text-base sm:text-lg"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (get_social_link('discord')): ?>
                                <a href="<?php echo get_social_link('discord'); ?>" target="_blank" class="social-icon bg-gradient-to-br from-indigo-500 to-indigo-700 w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center">
                                    <i class="fab fa-discord text-base sm:text-lg"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (get_social_link('youtube')): ?>
                                <a href="<?php echo get_social_link('youtube'); ?>" target="_blank" class="social-icon bg-gradient-to-br from-red-500 to-red-700 w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center">
                                    <i class="fab fa-youtube text-base sm:text-lg"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Modern bildirimler için Toastify kullanımı
        function showToast(message, isError = false) {
            Toastify({
                text: message,
                duration: 2000,
                close: true,
                gravity: "top",
                position: "right",
                className: "toast-notification",
                style: {
                    background: isError ? 
                        "linear-gradient(to right, #e53e3e, #c53030)" : 
                        "linear-gradient(to right, #38a169, #2f855a)",
                    transition: "all 0.5s ease",
                    opacity: 1
                }
            }).showToast();
        }
        
        // Sayfa yüklendiğinde çalışacak kodlar
        document.addEventListener('DOMContentLoaded', function() {
            // Hata mesajı varsa göster
            <?php if (isset($_GET['error'])): ?>
                showToast("<?php echo htmlspecialchars($_GET['error']); ?>", true);
            <?php endif; ?>
            
            // Bilgi mesajı varsa göster
            <?php if (isset($_GET['message'])): ?>
                showToast("<?php echo htmlspecialchars($_GET['message']); ?>");
            <?php endif; ?>
        });
    </script>
</body>
</html>
