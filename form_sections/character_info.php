<!-- IC Bilgiler -->
<div class="form-section" data-section="2" data-title="IC Bilgiler">
    <div class="bg-bg-light/5 p-6 rounded-xl border border-primary/10 mb-8 transform transition-all duration-300">
        <div class="flex items-center mb-4">
            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center mr-3">
                <i class="fas fa-user-tag text-primary"></i>
            </div>
            <h3 class="text-xl font-semibold text-primary">IC Bilgiler</h3>
        </div>
        
        <!-- Karakter Adı -->
        <div class="form-group relative mb-6 group">
            <div class="flex items-center mb-2">
                <i class="fas fa-id-card text-primary mr-2"></i>
                <label for="character_name" class="text-text-light">Karakterinizin Adı ve Soyadı <span class="text-red-500">*</span></label>
            </div>
            <input type="text" id="character_name" name="character_name" required placeholder="Karakterinizin tam adını girin" 
                class="w-full bg-[#1a1a1a] text-text-light px-4 py-3 rounded-lg border border-primary-dark/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all">
        </div>
        
        <!-- Karakter Hikayesi -->
        <div class="form-group relative mb-6 group">
            <div class="flex items-center mb-2">
                <i class="fas fa-book text-primary mr-2"></i>
                <label for="character_story" class="text-text-light">Karakterinizin Hikayesi <span class="text-red-500">*</span></label>
            </div>
            <textarea id="character_story" name="character_story" required rows="4" placeholder="Karakterinizin geçmişi, motivasyonları ve hikayesini anlatın..." 
                class="w-full bg-[#1a1a1a] text-text-light px-4 py-3 rounded-lg border border-primary-dark/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all"></textarea>
            <div class="text-xs text-gray-500 mt-1 italic">Karakterinizin hikayesini detaylı bir şekilde anlatın. Bu, başvurunuzun değerlendirilmesinde önemli bir faktördür.</div>
        </div>
        
        <!-- Karakter Özeti -->
        <div class="form-group relative mb-6 group">
            <div class="flex items-center mb-2">
                <i class="fas fa-tags text-primary mr-2"></i>
                <label for="character_summary" class="text-text-light">Karakterinizi 3 kelime ile özetler misiniz? <span class="text-red-500">*</span></label>
            </div>
            <input type="text" id="character_summary" name="character_summary" required placeholder="Örn: Cesur, Hırslı, Sadık" 
                class="w-full bg-[#1a1a1a] text-text-light px-4 py-3 rounded-lg border border-primary-dark/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all">
        </div>
    </div>
</div>
