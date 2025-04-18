<?php
// Bootstrap dosyasını dahil et (gerekli tüm yardımcı sınıfları ve fonksiyonları yükler)
require_once 'includes/bootstrap.php';

// Oturum kontrolü
include_once 'includes/auth_check.php';

// Header'ı dahil et
include 'includes/header.php';

// Config'den slider ayarlarını al
$slider_settings = \Core\Config::get('slider', [
    'transition_time' => 4000,
    'images' => ['assets/images/slider/slider1.png']
]);
?>

<!-- Hero Section with Slider -->
<section class="relative h-[600px] overflow-hidden">
    <!-- Slider Images -->
    <div class="absolute inset-0 w-full h-full">
        <!-- Slider resimleri config'den alınıyor -->
        <?php if (isset($slider_settings['images']) && is_array($slider_settings['images'])): ?>
            <?php foreach ($slider_settings['images'] as $index => $image): ?>
<div class="slider-image absolute inset-0 w-full h-full bg-center transition-opacity duration-1000 ease-in-out <?php echo $index === 0 ? 'opacity-100' : 'opacity-0'; ?>"
                    style="background-image: url('<?php echo $image; ?>'); background-position: center; background-size: 100% auto; background-repeat: no-repeat; background-color: var(--bg-dark);"></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/40"></div>

    <!-- Content -->
    <div class="relative container mx-auto px-6 h-full flex flex-col justify-center">
        <div class="max-w-lg animate__animated animate__fadeIn">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-text-light">Fellas Roleplay</h1>
            <p class="text-xl mb-8 text-text-light">Gerçekçi roleplay deneyimi için doğru adrestesiniz. Fellas Roleplay
                ile sanal dünyada gerçek bir yaşam deneyimleyin.</p>

            <!-- Social Icons -->
            <div class="flex space-x-4 mb-8">
                <?php if (get_social_link('discord')): ?>
                    <a href="<?php echo get_social_link('discord'); ?>" target="_blank"
                        class="text-white hover:text-primary-light transition">
                        <i class="fab fa-discord text-3xl"></i>
                    </a>
                <?php endif; ?>

                <?php if (get_social_link('youtube')): ?>
                    <a href="<?php echo get_social_link('youtube'); ?>" target="_blank"
                        class="text-white hover:text-primary-light transition">
                        <i class="fab fa-youtube text-3xl"></i>
                    </a>
                <?php endif; ?>

                <?php if (get_social_link('instagram')): ?>
                    <a href="<?php echo get_social_link('instagram'); ?>" target="_blank"
                        class="text-white hover:text-primary-light transition">
                        <i class="fab fa-instagram text-3xl"></i>
                    </a>
                <?php endif; ?>

                <?php if (get_social_link('twitter')): ?>
                    <a href="<?php echo get_social_link('twitter'); ?>" target="_blank"
                        class="text-white hover:text-primary-light transition">
                        <i class="fab fa-twitter text-3xl"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scroll Down Arrow -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
        <button id="scroll-down" class="text-white hover:text-primary-light animate-bounce">
            <i class="fas fa-chevron-down text-3xl"></i>
        </button>
    </div>
</section>

<!-- Content Section -->
<section id="content-section" class="py-16 bg-bg-dark">
    <div class="container mx-auto px-6">
        <!-- Sistemlerimiz -->
        <div class="flex flex-col md:flex-row items-center mb-24 card p-6 bg-[#141414] backdrop-blur-sm animate__animated animate__fadeIn">
            <div class="md:w-1/3 mb-8 md:mb-0 flex justify-center">
                <div class="text-5xl text-primary">
                    <i class="fas fa-cogs"></i>
                </div>
            </div>
            <div class="md:w-2/3">
                <h2 class="text-3xl font-bold mb-4 text-primary">Sistemlerimiz</h2>
                <p class="text-text-light mb-4">
                    Fellas Roleplay, en gelişmiş roleplay sistemlerini sunmaktadır. Gerçekçi ekonomi, detaylı karakter
                    gelişimi,
                    kapsamlı meslek sistemi ve daha fazlası ile gerçek bir yaşam deneyimi sunuyoruz. Sunucumuzda her
                    oyuncu kendi
                    hikayesini yazabilir ve şehrin bir parçası olabilir.
                </p>
                <p class="text-text-light">
                    Özel araç modifikasyonları, ev satın alma ve dekore etme, işletme sahibi olma gibi birçok özellik
                    ile
                    oyuncularımıza geniş bir özgürlük sunuyoruz. Ayrıca, düzenli etkinlikler ve güncellemeler ile oyun
                    deneyimini
                    sürekli olarak geliştiriyoruz.
                </p>
            </div>
        </div>

        <!-- Neden Burdayız -->
        <div class="flex flex-col md:flex-row-reverse items-center card p-6 bg-[#141414] backdrop-blur-sm animate__animated animate__fadeIn">
            <div class="md:w-1/3 mb-8 md:mb-0 flex justify-center">
                <div class="text-5xl text-primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="md:w-2/3">
                <h2 class="text-3xl font-bold mb-4 text-primary">Neden Burdayız</h2>
                <p class="text-text-light mb-4">
                    Fellas Roleplay, kaliteli ve gerçekçi bir roleplay deneyimi sunmak için kurulmuştur. Amacımız,
                    oyunculara
                    özgür bir ortam sağlayarak kendi hikayelerini yazmalarına olanak tanımaktır. Deneyimli yönetim
                    ekibimiz ve
                    aktif topluluğumuz ile her zaman daha iyisini hedefliyoruz.
                </p>
                <p class="text-text-light">
                    Oyuncularımızın geri bildirimleri bizim için çok önemlidir. Sürekli olarak sistemlerimizi
                    geliştiriyor ve
                    yeni özellikler ekliyoruz. Fellas Roleplay ailesine katılarak, bu gelişimin bir parçası olabilir ve
                    unutulmaz anılar biriktirebilirsiniz.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-16 bg-bg-dark">
    <div class="container mx-auto px-6">
        <h2 class="text-3xl font-bold mb-12 text-center text-primary animate__animated animate__fadeIn">İstatistikler</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Karakter Sayacı -->
            <div class="card p-6 text-center bg-[#141414] backdrop-blur-sm animate__animated animate__fadeIn">
                <div class="text-4xl font-bold mb-2 text-white"><?php echo get_statistic('active_characters'); ?></div>
                <div class="text-xl text-white">Aktif Karakter</div>
            </div>

            <!-- Whitelist Sayacı -->
            <div class="card p-6 text-center bg-[#141414] backdrop-blur-sm animate__animated animate__fadeIn">
                <div class="text-4xl font-bold mb-2 text-white"><?php echo get_statistic('whitelist_players'); ?></div>
                <div class="text-xl text-white">Whitelist Oyuncu</div>
            </div>
        </div>
    </div>
</section>

<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<!-- Slider JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Slider değişkenleri
    const sliderImages = document.querySelectorAll('.slider-image');
    const transitionTime = <?php echo isset($slider_settings['transition_time']) ? $slider_settings['transition_time'] : 4000; ?>;
    let currentSlide = 0;
    
    // Slider fonksiyonu
    function nextSlide() {
        sliderImages[currentSlide].classList.remove('opacity-100');
        sliderImages[currentSlide].classList.add('opacity-0');
        
        currentSlide = (currentSlide + 1) % sliderImages.length;
        
        sliderImages[currentSlide].classList.remove('opacity-0');
        sliderImages[currentSlide].classList.add('opacity-100');
    }
    
    // Slider'ı otomatik olarak değiştir
    if (sliderImages.length > 1) {
        setInterval(nextSlide, transitionTime);
    }
    
    // Scroll down butonu
    const scrollDownBtn = document.getElementById('scroll-down');
    const contentSection = document.getElementById('content-section');
    
    if (scrollDownBtn && contentSection) {
        scrollDownBtn.addEventListener('click', function() {
            contentSection.scrollIntoView({ behavior: 'smooth' });
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
