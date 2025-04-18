<?php if (isLoggedIn()): ?>
        </div>
    </div>
<?php endif; ?>

    <footer class="text-white py-4 mt-auto border-t border-primary shadow-lg">
        <div class="container mx-auto px-4 text-center">
            <p class="text-text-light">&copy; <?php echo date('Y'); ?> <span class="text-primary font-bold">Fellas Roleplay</span> - Admin Paneli</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mesajları otomatik olarak gizle
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }, 5000);
            });
            
            // Responsive sidebar için
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            
            if (sidebarToggle && sidebar && sidebarOverlay) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    sidebarOverlay.classList.toggle('active');
                });
                
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('active');
                });
                
                // Sidebar menü öğelerine tıklandığında sidebar'ı kapat (mobil görünümde)
                const sidebarLinks = sidebar.querySelectorAll('a');
                sidebarLinks.forEach(function(link) {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            sidebar.classList.remove('open');
                            sidebarOverlay.classList.remove('active');
                        }
                    });
                });
                
                // Ekran boyutu değiştiğinde sidebar'ı sıfırla
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 768) {
                        sidebar.classList.remove('open');
                        sidebarOverlay.classList.remove('active');
                    }
                });
            }
            
            // Tablolar için responsive sınıfı ekle
            const tables = document.querySelectorAll('table');
            tables.forEach(function(table) {
                const parent = table.parentElement;
                if (!parent.classList.contains('table-responsive') && !parent.classList.contains('overflow-x-auto')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'table-responsive';
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                }
            });
            
            // Oyuncu detayları sayfasında ham verilerin açılıp kapanması
            const toggleRawData = document.getElementById('toggle-raw-data');
            const rawDataContent = document.getElementById('raw-data-content');
            const toggleIcon = toggleRawData?.querySelector('i');
            
            if (toggleRawData && rawDataContent && toggleIcon) {
                toggleRawData.addEventListener('click', function() {
                    rawDataContent.classList.toggle('hidden');
                    toggleIcon.classList.toggle('fa-chevron-down');
                    toggleIcon.classList.toggle('fa-chevron-up');
                });
            }
        });
    </script>
</body>
</html>
