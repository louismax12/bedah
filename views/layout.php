<?php
// c:\Users\louis\Documents\rehab\rehab_rkz\views\layout.php
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
    <link href="<?= $base_url ?>/assets/css/style.css" rel="stylesheet">
    <!-- Google Fonts & Icons Lokal -->
    <link href="<?= $base_url ?>/assets/css/fonts.css" rel="stylesheet">
    <link href="<?= $base_url ?>/assets/css/material-symbols.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .sidebar-transition { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white">

<?php if(isset($_SESSION['user_id'])): ?>
    <div class="flex h-screen overflow-hidden w-full">
        <!-- Collapsible Dark Sidebar -->
        <aside class="sidebar-transition w-72 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0 z-30 shadow-2xl relative border-r border-slate-800" id="main-sidebar">
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800/80 bg-slate-950/40">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-blue-500/20 flex-shrink-0">
                        <i data-lucide="activity" class="w-6 h-6"></i>
                    </div>
                    <div class="sidebar-text truncate">
                        <div class="font-bold text-base text-white tracking-wide flex items-center gap-1.5">
                            <span>REHAB MEDIK</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-blue-500/20 text-blue-400 font-semibold border border-blue-500/30">RKZ</span>
                        </div>
                        <p class="text-xs text-slate-400 font-medium truncate">Klinik & Rehabilitasi</p>
                    </div>
                </div>
                <button class="text-slate-300 hover:text-white p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 border border-slate-700/80 transition-all focus:outline-none" id="toggle-sidebar">
                    <i class="w-5 h-5" data-lucide="panel-left-close" id="toggle-icon"></i>
                </button>
            </div>
            
            <a href="#" id="btnSwitchRole" class="px-5 py-3.5 bg-slate-800/40 border-b border-slate-800/60 sidebar-text block hover:bg-slate-800/70 cursor-pointer transition-colors" style="text-decoration: none;">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Peran Aktif</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold <?= $user_role == 'admin' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?= $user_role == 'admin' ? 'bg-emerald-400' : 'bg-amber-400' ?> animate-pulse"></span>
                        <?= ucfirst($user_role) ?>
                    </span>
                </div>
            </a>
            
            <div class="flex-1 overflow-y-auto custom-scroll py-4 px-3 space-y-1.5">
                <div class="sidebar-text px-3 pb-1 text-[11px] font-bold text-slate-400 tracking-wider uppercase">Menu Utama</div>
                
                <?php if($user_role === 'admin'): ?>
                <a class="flex items-center gap-3 px-3.5 py-3 rounded-xl <?= ($page == 'dashboard') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' ?> font-medium transition-all" href="index.php?page=dashboard">
                    <i class="w-5 h-5 flex-shrink-0 <?= ($page == 'dashboard') ? 'text-white' : 'text-slate-400' ?>" data-lucide="layout-dashboard"></i>
                    <span class="sidebar-text text-sm">Dashboard Admin</span>
                </a>
                <a class="flex items-center gap-3 px-3.5 py-3 rounded-xl <?= ($page == 'pasien') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' ?> font-medium transition-all" href="index.php?page=pasien">
                    <i class="w-5 h-5 flex-shrink-0 <?= ($page == 'pasien') ? 'text-white' : 'text-slate-400' ?>" data-lucide="users"></i>
                    <span class="sidebar-text text-sm">Master Pasien</span>
                </a>
                <a class="flex items-center gap-3 px-3.5 py-3 rounded-xl <?= ($page == 'kunjungan') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' ?> font-medium transition-all" href="index.php?page=kunjungan">
                    <i class="w-5 h-5 flex-shrink-0 <?= ($page == 'kunjungan') ? 'text-white' : 'text-slate-400' ?>" data-lucide="clipboard-pen-line"></i>
                    <span class="sidebar-text text-sm">Registrasi Kunjungan</span>
                </a>
                <?php endif; ?>

                <?php if($user_role === 'dokter'): ?>
                <a class="flex items-center gap-3 px-3.5 py-3 rounded-xl <?= ($page == 'dashboard') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' ?> font-medium transition-all" href="index.php?page=dashboard">
                    <i class="w-5 h-5 flex-shrink-0 <?= ($page == 'dashboard') ? 'text-white' : 'text-slate-400' ?>" data-lucide="stethoscope"></i>
                    <span class="sidebar-text text-sm">Antrean Pemeriksaan</span>
                </a>
                <?php endif; ?>
                
                <a class="flex items-center gap-3 px-3.5 py-3 rounded-xl <?= ($page == 'laporan') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' ?> font-medium transition-all" href="index.php?page=laporan">
                    <i class="w-5 h-5 flex-shrink-0 <?= ($page == 'laporan') ? 'text-white' : 'text-slate-400' ?>" data-lucide="bar-chart-3"></i>
                    <span class="sidebar-text text-sm">Laporan & Statistik</span>
                </a>

                <div class="sidebar-text pt-4 px-3 pb-1 text-[11px] font-bold text-slate-400 tracking-wider uppercase">Pengaturan</div>
                <a class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-red-400 hover:text-white hover:bg-red-500/20 font-medium transition-all" href="index.php?page=logout">
                    <i class="w-5 h-5 flex-shrink-0" data-lucide="log-out"></i>
                    <span class="sidebar-text text-sm">Keluar Sistem</span>
                </a>
            </div>
            
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-700 ring-2 ring-blue-500/30 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                        <?= $initials ?>
                    </div>
                    <div class="sidebar-text min-w-0 flex-1">
                        <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($user_nama) ?></p>
                        <p class="text-xs text-slate-400 truncate"><?= htmlspecialchars(ucfirst($user_role)) ?></p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50/80">
            <!-- Top Navbar -->
            <header class="h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-8 z-20 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.04)]">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="hidden md:flex px-3 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span> Instalasi Rehabilitasi Medik
                        </span>
                        <span class="text-slate-300 hidden md:inline">/</span>
                        <h1 class="text-lg font-bold text-slate-800 tracking-tight font-heading"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative hidden lg:block w-72">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input class="w-full pl-10 pr-4 py-2 text-xs bg-slate-100/90 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600/30 transition-all" placeholder="Pencarian cepat..." type="text">
                    </div>
                    <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-bold text-slate-700"><?= date('l, d M Y') ?></span>
                        <span class="text-[11px] text-slate-400 font-mono"><?= date('H:i') ?> WIB</span>
                    </div>
                </div>
            </header>
            
            <main class="flex-1 overflow-y-auto custom-scroll p-4 md:p-6 lg:p-8 space-y-6">
                <?= isset($content) ? $content : '' ?>
            </main>
        </div>
    </div>

<?php else: ?>
    <!-- Mode Login Container -->
    <div class="w-full min-h-screen bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center p-4">
        <?= isset($content) ? $content : '' ?>
    </div>
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
    const textElements = document.querySelectorAll('.sidebar-text');
    let isCollapsed = false;

    toggleBtn.addEventListener('click', () => {
        isCollapsed = !isCollapsed;
        if (isCollapsed) {
            sidebar.classList.remove('w-72');
            sidebar.classList.add('w-20');
            textElements.forEach(el => el.classList.add('hidden'));
            toggleIcon.setAttribute('data-lucide', 'panel-left-open');
        } else {
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-72');
            textElements.forEach(el => el.classList.remove('hidden'));
            toggleIcon.setAttribute('data-lucide', 'panel-left-close');
        }
        lucide.createIcons();
    });

    // Switch Role Logic (SweetAlert2)
    const btnSwitches = document.querySelectorAll('.btn-trigger-role, #btnSwitchRole');
    if(btnSwitches.length > 0) {
        btnSwitches.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const roles = [
                    { id: 'admin', name: 'Admin Rehab', icon: 'shield', color: '#f59e0b', active: '<?= $user_role === "admin" ? "true" : "false" ?>' },
                    { id: 'dokter', name: 'Dokter Medis', icon: 'stethoscope', color: '#2563eb', active: '<?= $user_role === "dokter" ? "true" : "false" ?>' },
                    { id: 'it', name: 'Admin IT', icon: 'laptop', color: '#10b981', active: '<?= $user_role === "it" ? "true" : "false" ?>' }
                ];
                
                let htmlContent = '<div class="role-select-list">';
                roles.forEach(function(r) {
                    const isActive = r.active === 'true';
                    const border = isActive ? `2px solid ${r.color}` : '2px solid #e2e8f0';
                    const bg = isActive ? '#f8fafc' : '#ffffff';
                    const opacity = isActive ? '0.7' : '1';
                    
                    htmlContent += `
                    <button type="button" class="btn-role-select" data-id="${r.id}" ${isActive ? 'disabled' : ''} style="border: ${border}; background: ${bg}; opacity: ${opacity};">
                        <div class="role-select-content">
                            <div class="role-select-icon" style="background: ${r.color};">
                                <i data-lucide="${r.icon}" style="width:1.25rem; height:1.25rem;"></i>
                            </div>
                            <div class="role-select-text">
                                <div class="role-select-name">${r.name}</div>
                                <div class="role-select-desc">${isActive ? 'Sedang aktif' : 'Beralih ke ' + r.name}</div>
                            </div>
                        </div>
                        ${isActive ? `<span class="role-select-badge" style="background:${r.color};">AKTIF</span>` : `<i data-lucide="chevron-right" style="width:1.25rem; height:1.25rem; color:#cbd5e1;"></i>`}
                    </button>`;
                });
                htmlContent += '</div>';

                Swal.fire({
                    title: '<div style="display:flex; align-items:center; gap:0.5rem; justify-content:center;"><i data-lucide="users" style="color:#f59e0b; width:1.5rem; height:1.5rem;"></i> Ganti Hak Akses</div>',
                    html: htmlContent,
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    heightAuto: false,
                    didOpen: () => {
                        lucide.createIcons();
                        const btns = document.querySelectorAll('.btn-role-select:not([disabled])');
                        btns.forEach(btn => {
                            btn.addEventListener('click', function() {
                                const roleId = this.getAttribute('data-id');
                                window.location.href = `index.php?page=switch_role&role=${roleId}`;
                            });
                            btn.addEventListener('mouseenter', function() { this.style.borderColor = '#94a3b8'; this.style.background = '#f1f5f9'; });
                            btn.addEventListener('mouseleave', function() { this.style.borderColor = '#e2e8f0'; this.style.background = '#ffffff'; });
                        });
                    }
                });
            });
        });
    }
    <?php endif; ?>
</script>
<?= isset($extra_js) ? $extra_js : '' ?>
</body>
</html>
