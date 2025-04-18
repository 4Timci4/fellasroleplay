<?php
// Oturum kontrolü
include_once 'includes/auth_check.php';

include 'includes/header.php';
?>

<!-- Combined Hero and Character Slot Section -->
<section class="pt-16 bg-bg-dark">
    <div class="container mx-auto px-6">
        <!-- Page Header -->
        <div class="relative text-center mb-12 animate__animated animate__fadeIn">
            <h1 class="text-4xl md:text-5xl font-bold mb-6 text-primary">
                Market
            </h1>
            
            <!-- Dekoratif ayırıcı -->
            <div class="h-1 w-24 bg-primary mx-auto mb-8"></div>
            
            <p class="text-xl text-text-light max-w-3xl mx-auto mb-4">
                Fellas Roleplay sunucusunda oyun deneyiminizi geliştirecek 
                <span class="font-bold">karakter slotlarını</span> satın alın.
            </p>
        </div>

        <!-- Character Slot Card - Modern Design -->
        <div class="max-w-4xl mx-auto animate__animated animate__fadeIn">
            <div class="bg-[#141414] rounded-2xl overflow-hidden shadow-2xl border border-primary-dark">
                <div class="flex flex-col md:flex-row">
                    <!-- Left Side - Image -->
                    <div class="md:w-2/5 relative overflow-hidden">
                        <div class="h-64 md:h-full bg-center bg-cover" 
                            style="background-image: url('https://placehold.co/800x1200/002884/f7ffff?text=Karakter+Slotu')">
                            <!-- Overlay with Gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent opacity-70"></div>
                            
                            <!-- Badge -->
                            <div class="absolute top-4 left-4">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side - Content -->
                    <div class="md:w-3/5 p-8">
                        <div class="flex flex-col h-full justify-between">
                            <div>
                                <h3 class="text-2xl font-bold mb-2 text-text-light">Karakter Slotu</h3>
                                
                                <p class="text-text-light mb-6 leading-relaxed">
                                    Yeni bir karakter oluşturmak için ek slot satın alın. Her slot, yeni bir hikaye başlatmanızı sağlar. 
                                    Farklı roller, farklı hikayeler ve farklı deneyimler için ideal.
                                </p>
                                
                                <!-- Features List -->
                                <div class="mb-6">
                                    <div class="grid grid-cols-1 gap-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                                            <span class="text-text-light">Anında hesabınıza tanımlanır</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                                            <span class="text-text-light">Sınırsız kullanım süresi</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                                            <span class="text-text-light">Özel karakter oluşturma hakkı</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Price and Button -->
                            <div class="mt-auto">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                                    <div class="mb-4 sm:mb-0">
                                        <span class="text-gray-400 text-sm line-through">700₺</span>
                                        <div class="flex items-center">
                                            <span class="text-3xl font-extrabold text-text-light">500₺</span>
                                        </div>
                                    </div>
                                    <button class="bg-primary hover:bg-primary-dark text-white font-bold py-3 px-8 rounded-lg transition shadow-lg transform hover:-translate-y-1">
                                        <i class="fas fa-shopping-cart mr-2"></i> Satın Al
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Payment Info Section -->
<section class="py-16 bg-bg-dark">
    <div class="container mx-auto px-6">
        <div class="max-w-4xl mx-auto bg-[#141414] rounded-2xl p-8 border border-primary-dark shadow-2xl animate__animated animate__fadeIn">
            <h2 class="text-2xl font-bold mb-8 text-center text-primary">Ödeme Bilgileri</h2>

            <div class="mb-8">
                <p class="text-text-light mb-4 text-center">
                    Satın aldığınız karakter slotu, ödeme onayından sonra 24 saat içinde hesabınıza tanımlanacaktır.
                    Herhangi bir sorun yaşarsanız Discord sunucumuzdan destek alabilirsiniz.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-center">
                <div class="bg-bg-dark p-6 rounded-xl border border-primary-dark transform transition duration-300 hover:scale-105">
                    <div class="text-4xl text-primary mb-4 flex justify-center">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="font-bold mb-2 text-text-light">Güvenli Ödeme</h3>
                    <p class="text-gray-400 text-sm">Tüm ödemeleriniz güvenli bir şekilde işlenir.</p>
                </div>

                <div class="bg-bg-dark p-6 rounded-xl border border-primary-dark transform transition duration-300 hover:scale-105">
                    <div class="text-4xl text-primary mb-4 flex justify-center">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="font-bold mb-2 text-text-light">7/24 Destek</h3>
                    <p class="text-gray-400 text-sm">Discord üzerinden her zaman destek alabilirsiniz.</p>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="mt-10 text-center">
                <p class="text-sm text-text-light mb-4">Kabul Edilen Ödeme Yöntemleri</p>
                <div class="flex justify-center space-x-6">
                    <i class="fab fa-cc-visa text-3xl text-gray-300"></i>
                    <i class="fab fa-cc-mastercard text-3xl text-gray-300"></i>
                    <i class="fab fa-cc-paypal text-3xl text-gray-300"></i>
                    <i class="fab fa-bitcoin text-3xl text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<?php include 'includes/footer.php'; ?>
