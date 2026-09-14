<div class="login-container">
    <!-- Left Side: Image and Branding -->
    <div class="login-image-side" <?php if (file_exists(__DIR__ . '/../img/login_page.png')): ?>style="background-image: url('<?= $base_url ?>/img/login_page.png');"><?php else: ?>style="background-image: url('<?= $base_url ?>/img/login_page.png');"><?php endif; ?>
        <!-- Overlay -->
        <div class="login-image-overlay"></div>
        
        <div class="login-branding">
            <!-- Logo Top Left -->
            <div class="login-logo-wrapper">
                <img src="img/logo_rkz.png" alt="Logo RKZ" class="login-logo">
                <span class="login-brand-text">Rehab RKZ</span>
            </div>
            
            <!-- Text Bottom Left -->
            <div class="login-description">
                <p>Sistem Manajemen Rehabilitasi Medis Terintegrasi.</p>
            </div>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="login-form-side">
        <!-- Mobile Logo (shown only on small screens) -->
        <div class="login-mobile-logo">
            <img src="img/logo_rkz.png" alt="Logo RKZ">
            <span>Rehab RKZ</span>
        </div>
        
        <div class="login-title-wrapper">
            <h2 class="login-title">Selamat Datang</h2>
            <p class="login-subtitle">Masuk menggunakan NIP Karyawan Anda</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="error-alert">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <form action="index.php?page=login" method="POST" class="login-form">
            <!-- NIP Field -->
            <div class="form-group">
                <label class="form-label">ID Karyawan (NIP)</label>
                <div class="input-wrapper">
                    <i data-lucide="user" class="input-icon"></i>
                    <input type="text" name="username" class="form-input" required placeholder="Masukkan NIP Anda...">
                </div>
            </div>
            
            <!-- Password Field -->
            <div class="form-group">
                <label class="form-label">Kata Sandi</label>
                <div class="input-wrapper">
                    <i data-lucide="lock" class="input-icon"></i>
                    <input type="password" name="password" id="password_input" class="form-input" required placeholder="••••••••">
                    <button type="button" id="toggle_password" class="password-toggle">
                        <i data-lucide="eye-off" class="w-4 h-4" id="eye_icon"></i>
                    </button>
                </div>
            </div>
            
            <div>
                <button type="submit" class="btn-primary">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggle_password');
        const passInput = document.getElementById('password_input');
        const eyeIcon = document.getElementById('eye_icon');
        
        toggleBtn.addEventListener('click', function() {
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye');
            } else {
                passInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            }
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    });
</script>
