<?php
// Slider ayarlarını config'den al
$slider_settings = [
    'transition_time' => \Core\Config::get('slider.transition_time', 4000)
];
?>
<!-- Kompakt Footer -->
<footer class="relative overflow-hidden mt-10 border-t border-primary/20 bg-bg-dark">
    <div class="container mx-auto px-4 py-6">
        <!-- Logo ve Linkler Alanı -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-5">
            <!-- Logo ve Telif -->
            <div class="flex items-center mb-4 md:mb-0">
                <img src="assets/images/logo.png" alt="<?php echo get_site_config('site_name'); ?> Logo" class="h-10 w-auto mr-3">
                <div class="text-left">
                    <h3 class="text-lg font-bold text-white"><?php echo get_site_config('site_name'); ?></h3>
                    <p class="text-xs text-gray-400">Gerçekçi roleplay deneyimi</p>
                </div>
            </div>
            
            <!-- Sosyal Medya -->
            <div class="flex space-x-3">
                <?php if (get_social_link('discord')): ?>
                    <a href="<?php echo get_social_link('discord'); ?>" target="_blank" class="w-8 h-8 rounded-md bg-gray-800 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                        <i class="fab fa-discord"></i>
                    </a>
                <?php endif; ?>
                
                <?php if (get_social_link('youtube')): ?>
                    <a href="<?php echo get_social_link('youtube'); ?>" target="_blank" class="w-8 h-8 rounded-md bg-gray-800 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                        <i class="fab fa-youtube"></i>
                    </a>
                <?php endif; ?>
                
                <?php if (get_social_link('instagram')): ?>
                    <a href="<?php echo get_social_link('instagram'); ?>" target="_blank" class="w-8 h-8 rounded-md bg-gray-800 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                        <i class="fab fa-instagram"></i>
                    </a>
                <?php endif; ?>
                
                <?php if (get_social_link('twitter')): ?>
                    <a href="<?php echo get_social_link('twitter'); ?>" target="_blank" class="w-8 h-8 rounded-md bg-gray-800 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                        <i class="fab fa-twitter"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Linkler ve Navigasyon -->
        <div class="flex flex-wrap justify-center md:justify-between items-center py-3 border-t border-gray-800">
            <div class="flex flex-wrap justify-center space-x-4 mb-3 md:mb-0">
                <a href="anasayfa" class="text-gray-300 hover:text-primary text-sm transition-colors">Ana Sayfa</a>
                <a href="basvuru" class="text-gray-300 hover:text-primary text-sm transition-colors">Başvuru</a>
                <a href="market" class="text-gray-300 hover:text-primary text-sm transition-colors">Market</a>
                <a href="kurallar" class="text-gray-300 hover:text-primary text-sm transition-colors">Kurallar</a>
                <a href="forum" class="text-gray-300 hover:text-primary text-sm transition-colors">Forum</a>
            </div>
            
            <div class="text-xs text-gray-500">
                <span>© <?php echo get_site_config('copyright_year'); ?> <?php echo get_site_config('site_name'); ?></span>
                <span class="mx-2">•</span>
                <span><?php echo get_site_config('designed_by'); ?></span>
            </div>
        </div>
        
        <!-- Telif Alanı -->
        <div class="text-center mt-4">
            <p class="text-gray-400 text-xs">
                Bu site ve içeriğindeki tüm materyaller, Fellas Roleplay'in münhasır mülkiyetindedir. 
                İzinsiz çoğaltılması, kopyalanması ve dağıtılması yasaktır.
            </p>
            <p class="text-gray-500 text-xs mt-1">
                FiveM, Rockstar Games ve Grand Theft Auto, ilgili şirketlerin tescilli markalarıdır.
            </p>
        </div>
    </div>
</footer>

<!-- JS - Mobile menü için kodlar header.php içinde bulunuyor, burada tekrar tanımlanmasına gerek yok -->

<!-- Slider JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Slider elementleri mevcut mu kontrol et
        const sliderImages = document.querySelectorAll('.slider-image');
        if (sliderImages.length > 0) {
            let currentIndex = 0;

            function showImage(index) {
                sliderImages.forEach((img, i) => {
                    img.classList.add('opacity-0');
                    img.classList.remove('opacity-100');

                    if (i === index) {
                        img.classList.remove('opacity-0');
                        img.classList.add('opacity-100');
                    }
                });
            }

            function nextImage() {
                currentIndex = (currentIndex + 1) % sliderImages.length;
                showImage(currentIndex);
            }

            // İlk resmi göster
            showImage(0);

            // Config'den alınan süre ile resim değiştir (milisaniye)
            setInterval(nextImage, <?php echo $slider_settings['transition_time']; ?>);
        }

        // Aşağı ok tıklaması için smooth scroll - elementler varsa
        const scrollButton = document.getElementById('scroll-down');
        const contentSection = document.getElementById('content-section');
        if (scrollButton && contentSection) {
            scrollButton.addEventListener('click', function () {
                contentSection.scrollIntoView({ behavior: 'smooth' });
            });
        }
    });
</script>

<!-- Quill JS - Sadece gerektiğinde yükle -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if the Quill editor target exists
    const contentElement = document.querySelector('#content');
    if (contentElement) {
        // Eğer zaten bir editor oluşturulmuşsa tekrar oluşturma
        if (!document.querySelector('#content-editor')) {
            var quill = new Quill('#content-editor', {
                theme: 'snow', // 'snow' or 'bubble'
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'], // Toggled buttons
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'script': 'sub'}, { 'script': 'super' }], // Superscript/subscript
                        [{ 'indent': '-1'}, { 'indent': '+1' }], // Outdent/indent
                        [{ 'direction': 'rtl' }], // Text direction
                        [{ 'size': ['small', false, 'large', 'huge'] }], // Custom dropdown
                        [{ 'color': [] }, { 'background': [] }], // Dropdown with defaults from theme
                        [{ 'font': [] }],
                        [{ 'align': [] }],
                        ['link', 'image', 'video'], // Embeds
                        ['clean'] // Remove formatting button
                    ]
                }
            });

            // Get the original textarea
            var originalTextarea = contentElement;
            // Hide the original textarea but keep it for submission
            originalTextarea.style.display = 'none';
            // Create the editor container
            var editorContainer = document.createElement('div');
            editorContainer.id = 'content-editor';
            originalTextarea.parentNode.insertBefore(editorContainer, originalTextarea);

            // Set the initial content of Quill from the textarea
            quill.root.innerHTML = originalTextarea.value;

            // Update the original textarea when Quill content changes
            quill.on('text-change', function() {
                originalTextarea.value = quill.root.innerHTML;
            });
        }
    }
});
</script>

</body>
</html>
