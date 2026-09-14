<?php
$base_url = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_url = rtrim($base_url, '/');
if ($base_url === '/' || $base_url === '') $base_url = '';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$page_title = 'Rehab Medik';
if($page == 'dashboard') $page_title = 'Dashboard Admin';
if($page == 'dashboard_dokter') $page_title = 'Antrean Dokter';
if($page == 'pasien') $page_title = 'Master Pasien';
if($page == 'kunjungan') $page_title = 'Registrasi Kunjungan';
if($page == 'anamnesis') $page_title = 'Form Pemeriksaan';
if($page == 'laporan') $page_title = 'Laporan & Statistik';

$user_nama = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Tamu';
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$name_parts = explode(' ', $user_nama);
$initials = '';
if (count($name_parts) > 0) { $initials .= strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name_parts[0]), 0, 1)); }
if (count($name_parts) > 1) { $initials .= strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name_parts[1]), 0, 1)); }
if ($initials === '') $initials = 'U';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?= $page_title ?> - Sistem Rehabilitasi Medik RS RKZ</title>
    <!-- Custom Semantic CSS -->
    <link href="<?= $base_url ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= $base_url ?>/assets/css/semantic.css" rel="stylesheet">
    <link href="<?= $base_url ?>/assets/css/material_supplement.css" rel="stylesheet">
    <!-- Google Fonts & Icons Lokal -->
    <link href="<?= $base_url ?>/assets/css/fonts.css" rel="stylesheet">
    <link href="<?= $base_url ?>/assets/css/material-symbols.css" rel="stylesheet">
</head>
<body>

<?php if(isset($_SESSION['user_id'])): ?>
    <div class="app-layout">
        <!-- Collapsible Dark Sidebar -->
        <aside class="sidebar" id="main-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo-wrapper">
                    <img src="<?= $base_url ?>/img/logo_rkz.png" alt="Logo RKZ" class="sidebar-logo">
                    <div>
                        <div class="sidebar-brand-text">Rehab Medis</div>
                        <p class="sidebar-brand-subtext">Klinik & Rehabilitasi</p>
                    </div>
                </div>
                <button class="sidebar-toggle-btn" id="toggle-sidebar">
                    <i data-lucide="panel-left-close"></i>
                </button>
            </div>
            
            <div class="sidebar-menu custom-scroll">
                <div class="sidebar-menu-title">Menu Utama</div>
                
                <a class="sidebar-menu-item <?= ($page == 'dashboard') ? 'active' : '' ?>" href="index.php?page=dashboard">
                    <i class="sidebar-menu-icon" data-lucide="layout-dashboard"></i>
                    <span class="sidebar-menu-text">Dashboard</span>
                </a>
                
                <a class="sidebar-menu-item <?= ($page == 'anamnesis') ? 'active' : '' ?>" href="index.php?page=anamnesis">
                    <i class="sidebar-menu-icon" data-lucide="clipboard-list"></i>
                    <span class="sidebar-menu-text">Anamnesis & Fisik</span>
                </a>
                
                <a class="sidebar-menu-item <?= ($page == 'laporan') ? 'active' : '' ?>" href="index.php?page=laporan">
                    <i class="sidebar-menu-icon" data-lucide="bar-chart-3"></i>
                    <span class="sidebar-menu-text">Laporan & Statistik</span>
                </a>

                <div class="sidebar-menu-title" style="margin-top: 1rem;">Pengaturan</div>
                
                <!-- <a class="sidebar-menu-item btn-trigger-role" style="color: #fbbf24;" href="#">
                    <i class="sidebar-menu-icon" data-lucide="users"></i>
                    <span class="sidebar-menu-text">Ganti Role (Simulasi)</span>
                </a> -->

                <a class="sidebar-menu-item" style="color: #ef4444;" href="index.php?page=logout">
                    <i class="sidebar-menu-icon" data-lucide="log-out"></i>
                    <span class="sidebar-menu-text">Keluar Sistem</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Navbar -->
            <header class="top-header">
                <div class="header-left">
                    <button id="mobile-menu-btn" class="mobile-sidebar-toggle">
                        <i data-lucide="menu"></i>
                    </button>
                    <h1 class="page-title"><?= $page_title ?></h1>
                </div>
                
                <div class="header-right">
                    <div class="header-time">
                        <i data-lucide="clock"></i>
                        <span id="live-time"><?= date('l, d M Y H:i') ?></span>
                    </div>
                    
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?= $initials ?>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($user_nama) ?></span>
                            <span class="user-role"><?= htmlspecialchars($user_role) ?></span>
                        </div>
                        <a href="index.php?page=logout" class="logout-btn">
                            <i data-lucide="log-out"></i>
                            <span>Keluar</span>
                        </a>
                    </div>
                </div>
            </header>
            
            <div class="main-scroll-area custom-scroll">
<?php else: ?>
    <!-- Mode Login Container -->
    <div style="background-color: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem;">
<?php endif; ?>
