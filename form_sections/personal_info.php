<!-- Kişisel Bilgiler -->
<div class="form-section active animate__animated animate__fadeIn" data-section="1" data-title="Kişisel Bilgiler">
    <div class="bg-bg-light/5 p-6 rounded-xl border border-primary/10 mb-8 transform transition-all duration-300">
        <div class="flex items-center mb-4">
            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center mr-3">
                <i class="fas fa-user text-primary"></i>
            </div>
            <h3 class="text-xl font-semibold text-primary">Kişisel Bilgiler</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Discord ID - Otomatik doldurulur ve değiştirilemez -->
            <div class="form-group relative mb-4 group">
                <div class="flex items-center mb-2">
                    <i class="fab fa-discord text-primary mr-2"></i>
                    <label for="discord_id" class="text-text-light">Discord ID <span class="text-red-500">*</span></label>
                </div>
                <input type="text" id="discord_id" name="discord_id" value="<?php echo $_SESSION['discord_user_id']; ?>" readonly required 
                    class="w-full bg-[#252525] text-gray-400 px-4 py-3 rounded-lg border border-primary-dark/50 cursor-not-allowed transition-all">
                <p class="text-xs text-gray-500 mt-1 italic">Discord hesabınızla giriş yaptığınız için bu alan otomatik doldurulmuştur.</p>
            </div>
            
            <!-- Adınız -->
            <div class="form-group relative mb-4 group">
                <div class="flex items-center mb-2">
                    <i class="fas fa-signature text-primary mr-2"></i>
                    <label for="first_name" class="text-text-light">Adınız <span class="text-red-500">*</span></label>
                </div>
                <input type="text" id="first_name" name="first_name" required placeholder="Adınızı girin" 
                    class="w-full bg-[#1a1a1a] text-text-light px-4 py-3 rounded-lg border border-primary-dark/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all">
            </div>
            
            <!-- Yaşınız -->
            <div class="form-group relative mb-4 group">
                <div class="flex items-center mb-2">
                    <i class="fas fa-birthday-cake text-primary mr-2"></i>
                    <label for="age" class="text-text-light">Yaşınız <span class="text-red-500">*</span></label>
                </div>
                <input type="number" id="age" name="age" required placeholder="Yaşınızı girin" 
                    class="w-full bg-[#1a1a1a] text-text-light px-4 py-3 rounded-lg border border-primary-dark/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all">
            </div>
        </div>

        <!-- Sunucuyu tercih etme sebebi -->
        <div class="form-group relative mb-6 group">
            <div class="flex items-center mb-2">
                <i class="fas fa-question-circle text-primary mr-2"></i>
                <label for="server_reason" class="text-text-light">Sunucuyu tercih etme sebebinizi yazınız. <span class="text-red-500">*</span></label>
            </div>
            <textarea id="server_reason" name="server_reason" required rows="3" placeholder="Sunucumuzu neden tercih ettiğinizi açıklayın..." 
                class="w-full bg-[#1a1a1a] text-text-light px-4 py-3 rounded-lg border border-primary-dark/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all"></textarea>
        </div>

        <!-- Roleplay için günlük saat -->
        <div class="form-group mb-6">
            <div class="flex items-center mb-3">
                <i class="fas fa-clock text-primary mr-2"></i>
                <label class="text-text-light">Roleplay için günde kaç saat ayırabiliyorsunuz? <span class="text-red-500">*</span></label>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <label class="radio-container bg-bg-light/10 border border-primary/20 rounded-lg p-3 cursor-pointer transition-all">
                    <input type="radio" id="rp_hours_1-3" name="rp_hours" value="1-3" required class="hidden">
                    <div class="flex items-center">
                        <div class="w-5 h-5 rounded-full border-2 border-primary/50 mr-2 flex items-center justify-center radio-circle">
                            <div class="w-3 h-3 rounded-full bg-primary scale-0 transition-transform duration-200"></div>
                        </div>
                        <span class="text-text-light">1-3 saat</span>
                    </div>
                </label>
                <label class="radio-container bg-bg-light/10 border border-primary/20 rounded-lg p-3 cursor-pointer transition-all">
                    <input type="radio" id="rp_hours_3-5" name="rp_hours" value="3-5" required class="hidden">
                    <div class="flex items-center">
                        <div class="w-5 h-5 rounded-full border-2 border-primary/50 mr-2 flex items-center justify-center radio-circle">
                            <div class="w-3 h-3 rounded-full bg-primary scale-0 transition-transform duration-200"></div>
                        </div>
                        <span class="text-text-light">3-5 saat</span>
                    </div>
                </label>
                <label class="radio-container bg-bg-light/10 border border-primary/20 rounded-lg p-3 cursor-pointer transition-all">
                    <input type="radio" id="rp_hours_5-10" name="rp_hours" value="5-10" required class="hidden">
                    <div class="flex items-center">
                        <div class="w-5 h-5 rounded-full border-2 border-primary/50 mr-2 flex items-center justify-center radio-circle">
                            <div class="w-3 h-3 rounded-full bg-primary scale-0 transition-transform duration-200"></div>
                        </div>
                        <span class="text-text-light">5-10 saat</span>
                    </div>
                </label>
                <label class="radio-container bg-bg-light/10 border border-primary/20 rounded-lg p-3 cursor-pointer transition-all">
                    <input type="radio" id="rp_hours_10+" name="rp_hours" value="10+" required class="hidden">
                    <div class="flex items-center">
                        <div class="w-5 h-5 rounded-full border-2 border-primary/50 mr-2 flex items-center justify-center radio-circle">
                            <div class="w-3 h-3 rounded-full bg-primary scale-0 transition-transform duration-200"></div>
                        </div>
                        <span class="text-text-light">10+ saat</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Yayın platformu -->
        <div class="form-group mb-6">
            <div class="flex items-center mb-3">
                <i class="fas fa-video text-primary mr-2"></i>
                <label class="text-text-light">Herhangi bir platformda yayın açıyor musunuz? <span class="text-red-500">*</span></label>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-3">
                <label class="radio-container bg-bg-light/10 border border-primary/20 rounded-lg p-3 cursor-pointer transition-all">
                    <input type="radio" id="streaming_kick" name="streaming" value="kick" required class="hidden">
                    <div class="flex items-center">
                        <div class="w-5 h-5 rounded-full border-2 border-primary/50 mr-2 flex items-center justify-center radio-circle">
                            <div class="w-3 h-3 rounded-full bg-primary scale-0 transition-transform duration-200"></div>
                        </div>
                        <span class="text-text-light">
                            <i class="fab fa-kickstarter text-green-400 mr-1"></i> Kick
                        </span>
                    </div>
                </label>
                <label class="radio-container bg-bg-light/10 border border-primary/20 rounded-lg p-3 cursor-pointer transition-all">
                    <input type="radio" id="streaming_twitch" name="streaming" value="twitch" required class="hidden">
                    <div class="flex items-center">
                        <div class="w-5 h-5 rounded-full border-2 border-primary/50 mr-2 flex items-center justify-center radio-circle">
                            <div class="w-3 h-3 rounded-full bg-primary scale-0 transition-transform duration-200"></div>
                        </div>
                        <span class="text-text-light">
                            <i class="fab fa-twitch text-purple-400 mr-1"></i> Twitch
                        </span>
                    </div>
                </label>
                <label class="radio-container bg-bg-light/10 border border-primary/20 rounded-lg p-3 cursor-pointer transition-all">
                    <input type="radio" id="streaming_youtube" name="streaming" value="youtube" required class="hidden">
                    <div class="flex items-center">
                        <div class="w-5 h-5 rounded-full border-2 border-primary/50 mr-2 flex items-center justify-center radio-circle">
                            <div class="w-3 h-3 rounded-full bg-primary scale-0 transition-transform duration-200"></div>
                        </div>
                        <span class="text-text-light">
                            <i class="fab fa-youtube text-red-500 mr-1"></i> Youtube
                        </span>
                    </div>
                </label>
                <label class="radio-container bg-bg-light/10 border border-primary/20 rounded-lg p-3 cursor-pointer transition-all">
                    <input type="radio" id="streaming_no" name="streaming" value="no" required class="hidden">
                    <div class="flex items-center">
                        <div class="w-5 h-5 rounded-full border-2 border-primary/50 mr-2 flex items-center justify-center radio-circle">
                            <div class="w-3 h-3 rounded-full bg-primary scale-0 transition-transform duration-200"></div>
                        </div>
                        <span class="text-text-light">Hayır</span>
                    </div>
                </label>
                <label class="radio-container bg-bg-light/10 border border-primary/20 rounded-lg p-3 cursor-pointer transition-all">
                    <input type="radio" id="streaming_other" name="streaming" value="other" required class="hidden">
                    <div class="flex items-center">
                        <div class="w-5 h-5 rounded-full border-2 border-primary/50 mr-2 flex items-center justify-center radio-circle">
                            <div class="w-3 h-3 rounded-full bg-primary scale-0 transition-transform duration-200"></div>
                        </div>
                        <span class="text-text-light">Diğer</span>
                    </div>
                </label>
            </div>
            <!-- Kick için metin alanı -->
            <div id="kick_streaming_container" class="hidden mt-3 p-3 bg-bg-light/5 rounded-lg border border-primary/10">
                <div class="flex items-center">
                    <i class="fab fa-kickstarter text-green-400 mr-2"></i>
                    <div class="flex items-center w-full bg-[#1a1a1a] text-text-light rounded-lg border border-primary-dark/50 overflow-hidden">
                        <span class="bg-[#252525] text-gray-400 px-3 py-2 border-r border-primary-dark/50">kick.com/</span>
                        <input type="text" id="streaming_kick_text" name="streaming_kick_text" maxlength="100" placeholder="Kullanıcı adınız" 
                            class="w-full bg-[#1a1a1a] text-text-light px-3 py-2 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all" disabled>
                    </div>
                </div>
            </div>
            
            <!-- Twitch için metin alanı -->
            <div id="twitch_streaming_container" class="hidden mt-3 p-3 bg-bg-light/5 rounded-lg border border-primary/10">
                <div class="flex items-center">
                    <i class="fab fa-twitch text-purple-400 mr-2"></i>
                    <div class="flex items-center w-full bg-[#1a1a1a] text-text-light rounded-lg border border-primary-dark/50 overflow-hidden">
                        <span class="bg-[#252525] text-gray-400 px-3 py-2 border-r border-primary-dark/50">twitch.tv/</span>
                        <input type="text" id="streaming_twitch_text" name="streaming_twitch_text" maxlength="100" placeholder="Kullanıcı adınız" 
                            class="w-full bg-[#1a1a1a] text-text-light px-3 py-2 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all" disabled>
                    </div>
                </div>
            </div>
            
            <!-- Youtube için metin alanı -->
            <div id="youtube_streaming_container" class="hidden mt-3 p-3 bg-bg-light/5 rounded-lg border border-primary/10">
                <div class="flex items-center">
                    <i class="fab fa-youtube text-red-500 mr-2"></i>
                    <div class="flex items-center w-full bg-[#1a1a1a] text-text-light rounded-lg border border-primary-dark/50 overflow-hidden">
                        <span class="bg-[#252525] text-gray-400 px-3 py-2 border-r border-primary-dark/50 whitespace-nowrap">youtube.com/</span>
                        <input type="text" id="streaming_youtube_text" name="streaming_youtube_text" maxlength="100" placeholder="Kanal adınız veya kanal URL'niz" 
                            class="w-full bg-[#1a1a1a] text-text-light px-3 py-2 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all" disabled>
                    </div>
                </div>
            </div>
            
            <!-- Diğer için metin alanı -->
            <div id="other_streaming_container" class="hidden mt-3 p-3 bg-bg-light/5 rounded-lg border border-primary/10">
                <div class="flex items-center">
                    <i class="fas fa-link text-primary mr-2"></i>
                    <div class="flex items-center w-full bg-[#1a1a1a] text-text-light rounded-lg border border-primary-dark/50 overflow-hidden">
                        <span class="bg-[#252525] text-gray-400 px-3 py-2 border-r border-primary-dark/50">platform/</span>
                        <input type="text" id="streaming_other_text" name="streaming_other_text" maxlength="100" placeholder="İçerik ürettiğiniz platformu giriniz." 
                            class="w-full bg-[#1a1a1a] text-text-light px-3 py-2 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 transition-all" disabled>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
