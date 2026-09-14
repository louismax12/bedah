<?php if(isset($_SESSION['user_id'])): ?>
            </div> <!-- End main-scroll-area -->
        </main>
    </div> <!-- End h-full flex -->
<?php else: ?>
    </div> <!-- End login-container wrapper -->
<?php endif; ?>

<!-- Icons -->
<script src="<?= $base_url ?>/assets/js/lucide.min.js"></script>
<!-- SweetAlert2 (Local for alerts) -->
<script src="<?= $base_url ?>/assets/vendor/js/sweetalert2.all.min.js"></script>
<script src="<?= $base_url ?>/assets/vendor/js/jquery.min.js"></script>

<script>
    lucide.createIcons();
    <?php if(isset($_SESSION['user_id'])): ?>
    const sidebar = document.getElementById('main-sidebar');
    const toggleBtn = document.getElementById('toggle-sidebar');
    const toggleIcon = document.getElementById('toggle-icon');
    let isCollapsed = false;

    toggleBtn.addEventListener('click', () => {
        isCollapsed = !isCollapsed;
        sidebar.classList.toggle('collapsed');
        if (isCollapsed) {
            toggleIcon.setAttribute('data-lucide', 'panel-left-open');
        } else {
            toggleIcon.setAttribute('data-lucide', 'panel-left-close');
        }
        lucide.createIcons();
    });
    

    <?php endif; ?>

    function updateLiveTime() {
        const now = new Date();
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        const dayName = days[now.getDay()];
        const date = String(now.getDate()).padStart(2, '0');
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        const timeString = `${dayName}, ${date} ${monthName} ${year} ${hours}:${minutes}`;
        const timeEl = document.getElementById('live-time');
        if(timeEl) {
            timeEl.textContent = timeString;
        }
    }
    setInterval(updateLiveTime, 60000); // Update every minute
    updateLiveTime();
</script>
<?= isset($extra_js) ? $extra_js : '' ?>
</body>
</html>
