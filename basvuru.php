<?php
// Oturum kontrolü
include_once 'includes/auth_check.php';

include 'includes/header.php';
require_once 'admin/includes/db.php';
require_once 'includes/functions.php';

// Discord rol kontrolü
$discord_id = $_SESSION['discord_user_id'] ?? '';
$has_whitelist_role = false;
$has_application_pending_role = false;

if (!empty($discord_id)) {
    $has_whitelist_role = userHasDiscordRole($discord_id, '1267646750789861537');
    $has_application_pending_role = userHasDiscordRole($discord_id, '1292884946662457425');
}
?>

<!-- Başvuru Section -->
<section class="min-h-[80vh] pt-16 bg-gradient-to-b from-bg-dark to-bg-light/10">
    <div class="container mx-auto px-6">
        <!-- Page Header -->
        <div class="relative text-center mb-12 animate__animated animate__fadeIn">
            <h1 class="text-4xl md:text-5xl font-bold mb-6 text-primary">
                Başvuru
            </h1>
            
            <!-- Dekoratif ayırıcı -->
            <div class="h-1 w-24 bg-primary mx-auto mb-8"></div>
            
            <p class="text-xl text-text-light max-w-3xl mx-auto mb-4">
                Fellas Roleplay sunucusuna katılmak için başvuru formunu doldurabilirsiniz. Başvurunuz yönetim ekibi
                tarafından incelenecektir.
            </p>
        </div>
        <?php
        // Başarı mesajı
        $show_success = isset($_GET['success']);
        
        // Okunmamış başvuru kontrolü - Başarı mesajı gösteriliyorsa bu kontrolü atla
        $has_unread_application = false;
        
        if (!$show_success) {
            $discord_id = $_SESSION['discord_user_id'] ?? '';
            
            if (!empty($discord_id)) {
                try {
                    $conn = \Core\Database::getInstance()->getConnection();
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE discord_id = :discord_id AND status = 'unread'");
                    $stmt->bindParam(':discord_id', $discord_id);
                    $stmt->execute();
                    $count = $stmt->fetchColumn();
                    
                    if ($count > 0) {
                        $has_unread_application = true;
                        echo '<div class="max-w-3xl mx-auto mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative animate__animated animate__fadeIn" role="alert">';
                        echo '<strong class="font-bold">Dikkat!</strong>';
                        echo '<span class="block sm:inline"> Zaten okunmamış bir başvurunuz bulunmaktadır. Lütfen başvurunuzun değerlendirilmesini bekleyin.</span>';
                        echo '</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="max-w-3xl mx-auto mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
                    echo '<strong class="font-bold">Hata!</strong>';
                    echo '<span class="block sm:inline"> Veritabanı hatası: ' . $e->getMessage() . '</span>';
                    echo '</div>';
                }
            }
        }
        
        // Başarı mesajı
        if (isset($_GET['success'])) {
            echo '<div class="max-w-3xl mx-auto mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative animate__animated animate__fadeIn" role="alert">';
            echo '<strong class="font-bold">Başarılı!</strong>';
            echo '<span class="block sm:inline"> Başvurunuz başarıyla alınmıştır. Başvurunuz incelendikten sonra sizinle iletişime geçilecektir.</span>';
            echo '<div class="mt-2 text-center">';
            echo '<span id="countdown" class="font-bold">2</span> saniye sonra ana sayfaya yönlendirileceksiniz.';
            echo '</div>';
            echo '</div>';
            
            // 2 saniye sonra anasayfaya yönlendirme yapan JavaScript kodu
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    let seconds = 2;
                    const countdownElement = document.getElementById("countdown");
                    
                    const interval = setInterval(function() {
                        seconds--;
                        countdownElement.textContent = seconds;
                        
                        if (seconds <= 0) {
                            clearInterval(interval);
                            window.location.href = "anasayfa.php";
                        }
                    }, 1000);
                });
            </script>';
        }

        // Hata mesajları
        if (isset($_GET['error'])) {
            $errors = explode('|', urldecode($_GET['error']));
            echo '<div class="max-w-3xl mx-auto mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
            echo '<strong class="font-bold">Hata!</strong>';
            echo '<ul class="mt-2 list-disc list-inside">';
            foreach ($errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        ?>

        <?php if ($has_whitelist_role): ?>
        <div class="max-w-3xl mx-auto text-center mb-12">
            <div class="p-8 rounded-2xl shadow-[0_10px_50px_rgba(0,0,0,0.3)] bg-bg-dark/80 backdrop-blur-sm border border-primary/20">
                <div class="flex items-center justify-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-primary to-primary-light flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-check text-white text-2xl"></i>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-primary mb-4">Hali Hazırda Bir Karakteriniz Var</h2>
                <p class="text-text-light mb-6">
                    Zaten whitelist rolüne sahipsiniz ve mevcut bir karakteriniz bulunmaktadır. 
                    Ekstra karakter oluşturmak için lütfen market üzerinden karakter slotu satın alınız.
                </p>
                <a href="market.php" class="btn-primary font-bold py-3 px-8 rounded-lg shadow-lg inline-block">
                    <i class="fas fa-shopping-cart mr-2"></i> Markete Git
                </a>
            </div>
        </div>
        <?php elseif ($has_application_pending_role): ?>
        <div class="max-w-3xl mx-auto text-center mb-12">
            <div class="p-8 rounded-2xl shadow-[0_10px_50px_rgba(0,0,0,0.3)] bg-bg-dark/80 backdrop-blur-sm border border-yellow-500/20">
                <div class="flex items-center justify-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-yellow-500 to-yellow-600 flex items-center justify-center shadow-lg">
                        <i class="fas fa-hourglass-half text-white text-2xl"></i>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-yellow-500 mb-4">Başvurunuz İnceleniyor</h2>
                <p class="text-text-light mb-6">
                    Başvurunuz halihazırda yönetim ekibi tarafından incelenmektedir. 
                    Lütfen sonucu bekleyiniz. İnceleme tamamlandığında Discord üzerinden bilgilendirileceksiniz.
                </p>
                <a href="anasayfa.php" class="btn-primary font-bold py-3 px-8 rounded-lg shadow-lg inline-block">
                    <i class="fas fa-home mr-2"></i> Ana Sayfaya Dön
                </a>
            </div>
        </div>
        <?php elseif (!$has_unread_application): ?>
        <div class="max-w-4xl mx-auto form-container p-8 rounded-2xl shadow-[0_10px_50px_rgba(0,0,0,0.3)] bg-bg-dark/80 backdrop-blur-sm border border-primary/20 transition-all duration-300">
            <div class="flex items-center mb-8">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-primary-light flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-file-alt text-white"></i>
                </div>
                <h2 class="text-2xl font-bold text-primary">Başvuru Formu</h2>
            </div>
            
            <!-- İlerleme çubuğu -->
            <div class="w-full bg-bg-light/20 rounded-full h-2 mb-8">
                <div id="progress-bar" class="bg-gradient-to-r from-primary to-primary-light h-2 rounded-full transition-all duration-500" style="width: 25%;"></div>
            </div>

            <form method="POST" action="process_application" id="application-form">
                <!-- Form Bölümleri -->
                <div class="form-sections">
                    <?php 
                    // Form bölümlerini include et
                    include 'form_sections/personal_info.php';
                    include 'form_sections/character_info.php';
                    include 'form_sections/roleplay_experience.php';
                    include 'form_sections/terms.php';
                    ?>
                </div>

                <!-- Form Navigasyon Butonları -->
                <div class="flex justify-between mb-8">
                    <button type="button" id="prev-btn" class="btn-secondary font-bold py-2 px-6 rounded-lg shadow-lg opacity-0 pointer-events-none transition-all duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Önceki
                    </button>
                    <button type="button" id="next-btn" class="btn-primary font-bold py-2 px-6 rounded-lg shadow-lg transition-all duration-300">
                        Sonraki <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <button type="submit" id="submit-btn" class="btn-primary font-bold py-3 px-8 rounded-lg shadow-lg hidden transition-all duration-300 bg-gradient-to-r from-primary to-primary-light">
                        <i class="fas fa-paper-plane mr-2"></i> Başvuruyu Gönder
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="max-w-3xl mx-auto text-center mb-12">
            <div class="p-8 rounded-2xl shadow-[0_10px_50px_rgba(0,0,0,0.3)] bg-bg-dark/80 backdrop-blur-sm border border-primary/20">
                <div class="flex items-center justify-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-primary to-primary-light flex items-center justify-center shadow-lg">
                        <i class="fas fa-hourglass-half text-white text-2xl"></i>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-primary mb-4">Başvurunuz Değerlendiriliyor</h2>
                <p class="text-text-light mb-6">
                    Zaten okunmamış bir başvurunuz bulunmaktadır. Başvurunuz yönetim ekibi tarafından değerlendirilmektedir. 
                    Lütfen başvurunuzun sonucunu bekleyin.
                </p>
                <a href="anasayfa.php" class="btn-primary font-bold py-3 px-8 rounded-lg shadow-lg inline-block">
                    <i class="fas fa-home mr-2"></i> Ana Sayfaya Dön
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<!-- Form Stilleri -->
<?php include 'form_sections/form_styles.php'; ?>

<!-- Form JavaScript -->
<script src="assets/js/form_scripts.js"></script>

<?php include 'includes/footer.php'; ?>
