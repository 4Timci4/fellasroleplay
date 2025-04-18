<!-- Roleplay Deneyimi -->
<div class="form-section" data-section="3" data-title="Roleplay Deneyimi">
    <div class="bg-bg-light/5 p-6 rounded-xl border border-primary/10 mb-8 transform transition-all duration-300">
        <div class="flex items-center mb-4">
            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center mr-3">
                <i class="fas fa-gamepad text-primary"></i>
            </div>
            <h3 class="text-xl font-semibold text-primary">Roleplay Deneyimi</h3>
        </div>
        
        <!-- Önceden Oynanan Sunucular -->
        <div class="form-group relative mb-6 group">
            <div class="flex items-center mb-2">
                <i class="fas fa-server text-primary mr-2"></i>
                <label for="previous_servers" class="text-text-light">Önceden oynamış olduğunuz sunucu isimlerini yazınız. <span class="text-red-500">*</span></label>
            </div>
            <textarea id="previous_servers" name="previous_servers" required rows="3" placeholder="Daha önce oynadığınız sunucuları listeleyin..." 
                class="w-full bg-[#1a1a1a] text-text-light px-4 py-3 rounded-lg border border-primary-dark/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all"></textarea>
        </div>
        
        <!-- FiveM Oynama Süresi -->
        <div class="form-group relative mb-6 group">
            <div class="flex items-center mb-2">
                <i class="fas fa-hourglass-half text-primary mr-2"></i>
                <label for="fivem_experience" class="text-text-light">FiveM oynama sürenizi yazınız (saat). <span class="text-red-500">*</span></label>
            </div>
            <div class="relative w-full md:w-1/2">
                <input type="number" id="fivem_experience" name="fivem_experience" required placeholder="Toplam saat" step="1" min="0" pattern="\d+" 
                    class="w-full bg-[#1a1a1a] text-text-light px-4 py-3 rounded-lg border border-primary-dark/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all">
                <div id="fivem_experience_error" class="hidden mt-2 text-red-500 text-sm">Lütfen sadece tam sayı giriniz.</div>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <span class="text-gray-400">saat</span>
                </div>
            </div>
        </div>
    </div>
</div>
