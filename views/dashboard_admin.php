<?php
// c:\Users\louis\Documents\rehab\rehab_rkz\views\dashboard_admin.php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}
$today = date('Y-m-d');
$stmt_pasien = $pdo->query("SELECT COUNT(*) FROM rehab_pasien");
$total_pasien = $stmt_pasien->fetchColumn();

$stmt_kunj = $pdo->prepare("SELECT COUNT(*) FROM rehab_kunjungan WHERE tgl_kunjungan = ?");
$stmt_kunj->execute(array($today));
$kunjungan_hari_ini = $stmt_kunj->fetchColumn();
?>
<!-- Welcome Banner with Blue Gradient & Glassmorphism Details -->
<div class="welcome-banner">
    <div class="welcome-decor-1"></div>
    <div class="welcome-decor-2"></div>
    
    <div class="welcome-content">
        <div class="welcome-badge">
            <i data-lucide="sparkles" style="width: 0.875rem; height: 0.875rem; color: #fde047;"></i>
            <span>Sistem Pelayanan Terintegrasi RS RKZ Surabaya</span>
        </div>
        <h2 class="welcome-title">
            Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!
        </h2>
        <p class="welcome-desc">
            Klinik Spesialis Kedokteran Fisik dan Rehabilitasi (Sp.KFR) hari ini siap melayani pasien. Pastikan kelengkapan berkas dan alur registrasi rekam medik terverifikasi.
        </p>
    </div>
    
    <div class="welcome-actions">
        <a href="index.php?page=pasien" class="btn-light">
            <i data-lucide="user-plus" style="width: 1rem; height: 1rem;"></i>
            <span>Pasien Baru</span>
        </a>
        <a href="index.php?page=kunjungan" class="btn-glass">
            <i data-lucide="clipboard-list" style="width: 1rem; height: 1rem;"></i>
            <span>Daftar Kunjungan</span>
        </a>
    </div>
</div>

<!-- Summary Stat Cards -->
<!-- Summary Stat Cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Total Pasien</span>
            <div class="stat-icon blue">
                <i data-lucide="folder-heart"></i>
            </div>
        </div>
        <div class="stat-body">
            <span class="stat-value"><?= $total_pasien ?></span>
        </div>
        <p class="stat-desc">Master database pasien</p>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Kunjungan Hari Ini</span>
            <div class="stat-icon indigo">
                <i data-lucide="calendar-check"></i>
            </div>
        </div>
        <div class="stat-body">
            <span class="stat-value"><?= $kunjungan_hari_ini ?></span>
        </div>
        <p class="stat-desc">Total antrean terdaftar</p>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Antrean Poli</span>
            <div class="stat-icon amber">
                <i data-lucide="hourglass"></i>
            </div>
        </div>
        <div class="stat-body">
            <span class="stat-value">0</span>
            <span class="stat-badge amber">Menunggu</span>
        </div>
        <p class="stat-desc">Belum dipanggil dokter</p>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Selesai</span>
            <div class="stat-icon emerald">
                <i data-lucide="check-circle-2"></i>
            </div>
        </div>
        <div class="stat-body">
            <span class="stat-value">0</span>
        </div>
        <p class="stat-desc">RMF-09 terisi</p>
    </div>
</div>

<div class="dashboard-grid">
    <div class="panel dashboard-col-2">
        <div class="panel-header">
            <h3 class="panel-title">Antrean Kunjungan Pasien</h3>
            <p class="panel-desc">Daftar kunjungan yang didaftarkan</p>
        </div>
        <div class="panel-body empty-state">
            <i data-lucide="clipboard-list" class="empty-icon"></i>

            
            
            <p>Silakan menuju halaman <a href="index.php?page=kunjungan" class="panel-link">Registrasi Kunjungan</a> untuk mengelola antrean.</p>
        </div>
    </div>
    
    <div>
        <div class="panel">
            <div class="panel-header" style="border-bottom: none; padding-bottom: 0;">
                <h3 class="panel-title">
                    <i data-lucide="zap" style="color: #f59e0b; width: 1rem; height: 1rem;"></i>
                    Aksi Cepat Registrasi
                </h3>
            </div>
            <div class="panel-body">
                <div class="quick-action-list">
                    <a href="index.php?page=pasien" class="quick-action-item">
                        <div class="quick-action-content">
                            <div class="quick-action-icon">
                                <i data-lucide="user-plus" style="width: 1rem; height: 1rem;"></i>
                            </div>
                            <div class="quick-action-text">
                                <div class="quick-action-title">Registrasi Pasien Baru</div>
                                <div class="quick-action-desc">Input rekam medis</div>
                            </div>
                        </div>
                        <i data-lucide="arrow-right" class="quick-action-arrow"></i>
                    </a>
                    
                    <a href="index.php?page=kunjungan" class="quick-action-item emerald">
                        <div class="quick-action-content">
                            <div class="quick-action-icon">
                                <i data-lucide="calendar-plus" style="width: 1rem; height: 1rem;"></i>
                            </div>
                            <div class="quick-action-text">
                                <div class="quick-action-title">Kunjungan Pasien Lama</div>
                                <div class="quick-action-desc">Pilih poli & dokter</div>
                            </div>
                        </div>
                        <i data-lucide="arrow-right" class="quick-action-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
