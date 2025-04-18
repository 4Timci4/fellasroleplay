document.addEventListener('DOMContentLoaded', function () {
    // Formun var olup olmadığını kontrol et
    const applicationForm = document.getElementById('application-form');
    if (!applicationForm) {
        // Form yoksa, JavaScript işlemlerini gerçekleştirme
        return;
    }
    
    // Form bölümleri için değişkenler
    const formSections = document.querySelectorAll('.form-section');
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');
    const submitBtn = document.getElementById('submit-btn');
    const progressBar = document.getElementById('progress-bar');
    let currentSection = 0;
    
    // İlerleme çubuğunu güncelle
    function updateProgressBar() {
        const progress = ((currentSection + 1) / formSections.length) * 100;
        progressBar.style.width = `${progress}%`;
    }
    
    // Bölümleri göster/gizle
    function showSection(index) {
        formSections.forEach((section, i) => {
            section.classList.remove('active');
            if (i === index) {
                section.classList.add('active');
                section.classList.add('animate__animated', 'animate__fadeIn');
            }
        });
        
        // Butonları güncelle
        if (index === 0) {
            prevBtn.classList.add('opacity-0', 'pointer-events-none');
        } else {
            prevBtn.classList.remove('opacity-0', 'pointer-events-none');
        }
        
        if (index === formSections.length - 1) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }
        
        updateProgressBar();
    }
    
    // Form doğrulama fonksiyonu
    function validateForm() {
        // Tüm zorunlu alanları kontrol et
        const requiredInputs = applicationForm.querySelectorAll('input[required], textarea[required], select[required]');
        let isValid = true;
        let firstInvalidElement = null;
        
        // Hata mesajlarını temizle
        const errorMessages = document.querySelectorAll('.form-error-message');
        errorMessages.forEach(message => message.remove());
        
        // Her zorunlu alanı kontrol et
        requiredInputs.forEach(input => {
            // Radio butonlar için özel kontrol
            if (input.type === 'radio') {
                const radioGroup = applicationForm.querySelectorAll(`input[name="${input.name}"]`);
                const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                
                if (!isChecked) {
                    // Sadece bir kez hata mesajı göster
                    const radioContainer = input.closest('.form-group');
                    if (radioContainer && !radioContainer.querySelector('.form-error-message')) {
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'form-error-message text-red-500 text-sm mt-1 animate__animated animate__fadeIn';
                        errorMessage.textContent = 'Bu alan zorunludur';
                        radioContainer.appendChild(errorMessage);
                        
                        if (!firstInvalidElement) {
                            firstInvalidElement = input;
                        }
                    }
                    isValid = false;
                }
                return;
            }
            
            // Checkbox için özel kontrol
            if (input.type === 'checkbox' && !input.checked) {
                const checkboxContainer = input.closest('.checkbox-container');
                if (checkboxContainer) {
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'form-error-message text-red-500 text-sm mt-1 animate__animated animate__fadeIn';
                    errorMessage.textContent = 'Bu alan zorunludur';
                    checkboxContainer.appendChild(errorMessage);
                    
                    if (!firstInvalidElement) {
                        firstInvalidElement = input;
                    }
                }
                isValid = false;
                return;
            }
            
            // Diğer input tipleri için kontrol
            if (input.value.trim() === '') {
                const inputContainer = input.closest('.form-group');
                if (inputContainer) {
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'form-error-message text-red-500 text-sm mt-1 animate__animated animate__fadeIn';
                    errorMessage.textContent = 'Bu alan zorunludur';
                    inputContainer.appendChild(errorMessage);
                    
                    // Input'u kırmızı kenarlıkla işaretle
                    input.classList.add('border-red-500');
                    
                    if (!firstInvalidElement) {
                        firstInvalidElement = input;
                    }
                }
                isValid = false;
            } else {
                // Geçerli input için kırmızı kenarlığı kaldır
                input.classList.remove('border-red-500');
            }
        });
        
        // Eğer form geçerli değilse, ilk hatalı alana git
        if (!isValid && firstInvalidElement) {
            // Hangi bölümde olduğunu bul
            const section = firstInvalidElement.closest('.form-section');
            if (section) {
                const sectionIndex = Array.from(formSections).indexOf(section);
                if (sectionIndex !== -1) {
                    currentSection = sectionIndex;
                    showSection(currentSection);
                }
            }
            
            // Hatalı alana odaklan
            firstInvalidElement.focus();
            
            // Sayfayı hatalı alana kaydır
            firstInvalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        return isValid;
    }
    
    // Form gönderilmeden önce doğrulama yap
    applicationForm.addEventListener('submit', function(event) {
        // FiveM deneyimi kontrolü
        const fivemInput = document.getElementById('fivem_experience');
        const fivemErrorEl = document.getElementById('fivem_experience_error');
        
        let isFormValid = true;
        
        // FiveM süresinin tam sayı olup olmadığını kontrol et
        if (fivemInput && fivemInput.value) {
            // Ondalıklı sayı kontrolü
            if (fivemInput.value.includes('.') || fivemInput.value.includes(',')) {
                if (fivemErrorEl) {
                    fivemErrorEl.classList.remove('hidden');
                    fivemInput.classList.add('border-red-500');
                }
                isFormValid = false;
                
                // Roleplay Deneyimi bölümüne git
                const section = fivemInput.closest('.form-section');
                if (section) {
                    const sectionIndex = Array.from(formSections).indexOf(section);
                    if (sectionIndex !== -1) {
                        currentSection = sectionIndex;
                        showSection(currentSection);
                    }
                }
                
                // Odaklan ve görünür olmasını sağla
                fivemInput.focus();
                fivemInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        // Diğer form elemanlarını kontrol et
        if (!validateForm() || !isFormValid) {
            event.preventDefault();
        }
    });
    
    // İleri butonu
    nextBtn.addEventListener('click', function() {
        // Mevcut bölümdeki alanları doğrula
        const currentSectionElement = formSections[currentSection];
        const requiredInputs = currentSectionElement.querySelectorAll('input[required], textarea[required], select[required]');
        let isValid = true;
        
        // Hata mesajlarını temizle
        const errorMessages = currentSectionElement.querySelectorAll('.form-error-message');
        errorMessages.forEach(message => message.remove());
        
        // FiveM deneyimi özel kontrolü (eğer bu bölümdeyse)
        const fivemInput = currentSectionElement.querySelector('#fivem_experience');
        const fivemErrorEl = document.getElementById('fivem_experience_error');
        
        if (fivemInput && fivemInput.value) {
            // Ondalıklı sayı kontrolü
            if (fivemInput.value.includes('.') || fivemInput.value.includes(',')) {
                if (fivemErrorEl) {
                    fivemErrorEl.classList.remove('hidden');
                    fivemInput.classList.add('border-red-500');
                }
                isValid = false;
                
                // Odaklan ve görünür olmasını sağla
                fivemInput.focus();
                fivemInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Doğrulama işlemini durdur
                return;
            }
        }
        
        // Her zorunlu alanı kontrol et
        requiredInputs.forEach(input => {
            // Radio butonlar için özel kontrol
            if (input.type === 'radio') {
                const radioGroup = applicationForm.querySelectorAll(`input[name="${input.name}"]`);
                const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                
                if (!isChecked) {
                    // Sadece bir kez hata mesajı göster
                    const radioContainer = input.closest('.form-group');
                    if (radioContainer && !radioContainer.querySelector('.form-error-message')) {
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'form-error-message text-red-500 text-sm mt-1 animate__animated animate__fadeIn';
                        errorMessage.textContent = 'Bu alan zorunludur';
                        radioContainer.appendChild(errorMessage);
                    }
                    isValid = false;
                }
                return;
            }
            
            // Diğer input tipleri için kontrol
            if (input.value.trim() === '') {
                const inputContainer = input.closest('.form-group');
                if (inputContainer) {
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'form-error-message text-red-500 text-sm mt-1 animate__animated animate__fadeIn';
                    errorMessage.textContent = 'Bu alan zorunludur';
                    inputContainer.appendChild(errorMessage);
                    
                    // Input'u kırmızı kenarlıkla işaretle
                    input.classList.add('border-red-500');
                }
                isValid = false;
            } else {
                // Geçerli input için kırmızı kenarlığı kaldır
                input.classList.remove('border-red-500');
            }
        });
        
        // Eğer mevcut bölüm geçerliyse, sonraki bölüme geç
        if (isValid && currentSection < formSections.length - 1) {
            currentSection++;
            showSection(currentSection);
        }
    });
    
    // Geri butonu
    prevBtn.addEventListener('click', function() {
        if (currentSection > 0) {
            currentSection--;
            showSection(currentSection);
        }
    });
    
    // Sayısal giriş alanları için maksimum karakter sınırlaması
    const ageInput = document.getElementById('age');
    if (ageInput) {
        ageInput.addEventListener('input', function () {
            if (this.value.length > 3) {
                this.value = this.value.slice(0, 3);
            }
        });
    }

    // FiveM oynama süresi için maksimum 5 karakter ve tam sayı kontrolü
    const fivemInput = document.getElementById('fivem_experience');
    const fivemErrorEl = document.getElementById('fivem_experience_error');
    
    if (fivemInput) {
        fivemInput.addEventListener('input', function () {
            // Maksimum 5 karakter
            if (this.value.length > 5) {
                this.value = this.value.slice(0, 5);
            }
            
            // Ondalık sayı kontrolü
            if (this.value.includes('.') || this.value.includes(',')) {
                if (fivemErrorEl) {
                    fivemErrorEl.classList.remove('hidden');
                    this.classList.add('border-red-500');
                }
            } else {
                if (fivemErrorEl) {
                    fivemErrorEl.classList.add('hidden');
                    this.classList.remove('border-red-500');
                }
            }
        });
    }
    
    // Other streaming platformu için input kontrolü
    const streamingOtherRadio = document.getElementById('streaming_other');
    const streamingOtherText = document.getElementById('streaming_other_text');
    const otherStreamingContainer = document.getElementById('other_streaming_container');
    
    // Diğer radyo butonları
    const streamingRadios = document.querySelectorAll('input[name="streaming"]');
    
    // Kick, Twitch ve Youtube için metin alanları
    const streamingKickRadio = document.getElementById('streaming_kick');
    const streamingTwitchRadio = document.getElementById('streaming_twitch');
    const streamingYoutubeRadio = document.getElementById('streaming_youtube');
    const streamingNoRadio = document.getElementById('streaming_no');
    
    const kickStreamingContainer = document.getElementById('kick_streaming_container');
    const twitchStreamingContainer = document.getElementById('twitch_streaming_container');
    const youtubeStreamingContainer = document.getElementById('youtube_streaming_container');
    
    const streamingKickText = document.getElementById('streaming_kick_text');
    const streamingTwitchText = document.getElementById('streaming_twitch_text');
    const streamingYoutubeText = document.getElementById('streaming_youtube_text');
    
    // Eğer streaming elementleri yoksa işlemi sonlandır
    if (!streamingRadios || streamingRadios.length === 0) {
        return;
    }
    
    // Radyo butonlarının değişimini dinle
    streamingRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            // Tüm metin alanlarını gizle ve devre dışı bırak
            if (otherStreamingContainer) otherStreamingContainer.classList.add('hidden');
            if (kickStreamingContainer) kickStreamingContainer.classList.add('hidden');
            if (twitchStreamingContainer) twitchStreamingContainer.classList.add('hidden');
            if (youtubeStreamingContainer) youtubeStreamingContainer.classList.add('hidden');
            
            if (streamingOtherText) {
                streamingOtherText.disabled = true;
                streamingOtherText.required = false;
            }
            if (streamingKickText) {
                streamingKickText.disabled = true;
                streamingKickText.required = false;
            }
            if (streamingTwitchText) {
                streamingTwitchText.disabled = true;
                streamingTwitchText.required = false;
            }
            if (streamingYoutubeText) {
                streamingYoutubeText.disabled = true;
                streamingYoutubeText.required = false;
            }
            
            // Seçilen radyo butonuna göre ilgili metin alanını göster (null kontrolü ile)
            if (streamingOtherRadio && streamingOtherRadio.checked) {
                if (otherStreamingContainer) otherStreamingContainer.classList.remove('hidden');
                if (streamingOtherText) {
                    streamingOtherText.disabled = false;
                    streamingOtherText.required = true;
                    streamingOtherText.focus();
                }
            } else if (streamingKickRadio && streamingKickRadio.checked) {
                if (kickStreamingContainer) kickStreamingContainer.classList.remove('hidden');
                if (streamingKickText) {
                    streamingKickText.disabled = false;
                    streamingKickText.required = true;
                    streamingKickText.focus();
                }
            } else if (streamingTwitchRadio && streamingTwitchRadio.checked) {
                if (twitchStreamingContainer) twitchStreamingContainer.classList.remove('hidden');
                if (streamingTwitchText) {
                    streamingTwitchText.disabled = false;
                    streamingTwitchText.required = true;
                    streamingTwitchText.focus();
                }
            } else if (streamingYoutubeRadio && streamingYoutubeRadio.checked) {
                if (youtubeStreamingContainer) youtubeStreamingContainer.classList.remove('hidden');
                if (streamingYoutubeText) {
                    streamingYoutubeText.disabled = false;
                    streamingYoutubeText.required = true;
                    streamingYoutubeText.focus();
                }
            }
            // streamingNoRadio seçiliyse hiçbir metin alanı gösterilmez
        });
    });
    
    // Sayfa yüklendiğinde de kontrol et (null kontrolü ile)
    if (streamingOtherRadio && streamingOtherRadio.checked) {
        if (otherStreamingContainer) otherStreamingContainer.classList.remove('hidden');
        if (streamingOtherText) {
            streamingOtherText.disabled = false;
            streamingOtherText.required = true;
        }
    } else if (streamingKickRadio && streamingKickRadio.checked) {
        if (kickStreamingContainer) kickStreamingContainer.classList.remove('hidden');
        if (streamingKickText) {
            streamingKickText.disabled = false;
            streamingKickText.required = true;
        }
    } else if (streamingTwitchRadio && streamingTwitchRadio.checked) {
        if (twitchStreamingContainer) twitchStreamingContainer.classList.remove('hidden');
        if (streamingTwitchText) {
            streamingTwitchText.disabled = false;
            streamingTwitchText.required = true;
        }
    } else if (streamingYoutubeRadio && streamingYoutubeRadio.checked) {
        if (youtubeStreamingContainer) youtubeStreamingContainer.classList.remove('hidden');
        if (streamingYoutubeText) {
            streamingYoutubeText.disabled = false;
            streamingYoutubeText.required = true;
        }
    } else {
        // Hiçbir radyo butonu seçili değilse veya "Hayır" seçiliyse
        if (otherStreamingContainer) otherStreamingContainer.classList.add('hidden');
        if (kickStreamingContainer) kickStreamingContainer.classList.add('hidden');
        if (twitchStreamingContainer) twitchStreamingContainer.classList.add('hidden');
        if (youtubeStreamingContainer) youtubeStreamingContainer.classList.add('hidden');
        
        if (streamingOtherText) {
            streamingOtherText.disabled = true;
            streamingOtherText.required = false;
        }
        if (streamingKickText) {
            streamingKickText.disabled = true;
            streamingKickText.required = false;
        }
        if (streamingTwitchText) {
            streamingTwitchText.disabled = true;
            streamingTwitchText.required = false;
        }
        if (streamingYoutubeText) {
            streamingYoutubeText.disabled = true;
            streamingYoutubeText.required = false;
        }
    }
    
    // İlk bölümü göster
    showSection(currentSection);
});
