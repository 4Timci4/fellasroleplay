<?php
/**
 * Kurallar Sayfası
 * 
 * Bu sayfa, rules/ klasöründeki PDF dosyalarını görüntüler.
 * Kullanıcının tıkladığı menü öğesine göre içerik değişir.
 * Sayfaya erişim için kullanıcı girişi gereklidir.
 */

// Oturum kontrolü
include_once 'includes/auth_check.php';

// Kurallar dizisi (kolayca güncellenebilir)
$kurallar = [
    'fellas-sunucu' => [
        'id' => 'fellas-sunucu',
        'baslik' => 'Fellas Roleplay Sunucu Kuralları',
        'dosya' => 'fellas-roleplay-kurallari.pdf' // Bu dosya adı sonradan değiştirilebilir
    ],
    'discord-topluluk' => [
        'id' => 'discord-topluluk',
        'baslik' => 'Discord Topluluk Kuralları',
        'dosya' => 'discord-topluluk-kurallari.pdf' // Bu dosya adı sonradan değiştirilebilir
    ],
    'discord-hizmet' => [
        'id' => 'discord-hizmet',
        'baslik' => 'Discord Hizmet Şartları',
        'dosya' => 'discord-hizmet-sartlari.pdf' // Bu dosya adı sonradan değiştirilebilir
    ],
    'twitch-hizmet' => [
        'id' => 'twitch-hizmet',
        'baslik' => 'Twitch Hizmet Koşulları',
        'dosya' => 'twitch-hizmet-kosullari.pdf' // Bu dosya adı sonradan değiştirilebilir
    ],
    'fivem-lisans' => [
        'id' => 'fivem-lisans',
        'baslik' => 'FiveM İçerik Oluşturucu Platformu Lisans Sözleşmesi',
        'dosya' => 'fivem-lisans-sozlesmesi.pdf' // Bu dosya adı sonradan değiştirilebilir
    ]
];

// Hangi kuralın görüntüleneceğini belirleme
$secili_kural_id = isset($_GET['kural']) ? $_GET['kural'] : 'fellas-sunucu';
$secili_kural = isset($kurallar[$secili_kural_id]) ? $kurallar[$secili_kural_id] : $kurallar['fellas-sunucu'];

// PDF dosyasının tam yolu
$pdf_yolu = 'rules/' . $secili_kural['dosya'];

// Header'ı dahil et
include_once 'includes/header.php';
?>

<!-- Ana İçerik -->
<main class="container mx-auto py-8 px-4">
    <div class="bg-[#141414] backdrop-blur-sm rounded-lg border border-primary p-6">
        <h1 class="text-2xl font-bold mb-6 text-primary">Kurallar ve Hizmet Şartları</h1>
        
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Sol Menü -->
            <div class="md:w-1/4">
                <div class="rounded-lg p-4 bg-[#141414] backdrop-blur-sm border border-primary">
                    <h2 class="text-lg font-semibold mb-4 text-primary">Kural Kategorileri</h2>
                    <ul class="space-y-2">
                        <?php foreach ($kurallar as $kural): ?>
                            <li>
                                <a href="?kural=<?php echo $kural['id']; ?>" 
                                   class="block py-2 px-3 rounded-lg transition-colors <?php echo ($secili_kural_id === $kural['id']) ? 'bg-primary text-black font-medium' : 'hover:bg-[#1a1a1a]'; ?>">
                                    <?php echo $kural['baslik']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <!-- PDF Görüntüleyici -->
            <div class="md:w-3/4">
                <div class="bg-[#141414] backdrop-blur-sm rounded-lg p-4 border border-primary">
                    <h2 class="text-xl font-semibold mb-4 text-primary"><?php echo $secili_kural['baslik']; ?></h2>
                    
                    <?php if (file_exists($pdf_yolu)): ?>
                        <div class="pdf-container w-full h-[800px] rounded-lg overflow-hidden">
                            <iframe src="<?php echo $pdf_yolu; ?>#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" width="100%" height="100%" class="rounded-lg" frameborder="0"></iframe>
                        </div>
                    <?php else: ?>
                        <div class="bg-red-900/30 text-red-300 p-4 rounded-lg border border-red-700">
                            <p class="flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                PDF dosyası bulunamadı: <?php echo sanitizeOutput($secili_kural['dosya']); ?>
                            </p>
                            <p class="mt-2 text-sm">
                                Lütfen <code>rules/</code> klasörüne ilgili PDF dosyasını yükleyin veya dosya adını kontrol edin.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Footer'ı dahil et
include_once 'includes/footer.php';
?>
