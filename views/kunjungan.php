<?php
// c:\Users\louis\Documents\rehab\rehab_rkz\views\kunjungan.php
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php?page=login';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO rehab_kunjungan (no_register, no_rm, tgl_kunjungan, dokter_id) VALUES (?, ?, ?, ?)");
        $stmt->execute(array($_POST['no_register'], $_POST['no_rm'], $_POST['tgl_kunjungan'], $_POST['dokter_id']));
        $success = "Kunjungan berhasil didaftarkan.";
    } catch(Exception $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt_rm = $pdo->prepare("SELECT id FROM rehab_rekam_medis WHERE no_register = ?");
        $stmt_rm->execute([$id]);
        $rm = $stmt_rm->fetch();
        if ($rm) {
            $pdo->prepare("DELETE FROM rehab_body_mapping WHERE rekam_medis_id = ?")->execute([$rm['id']]);
            $pdo->prepare("DELETE FROM rehab_rekam_medis WHERE id = ?")->execute([$rm['id']]);
        }
        $pdo->prepare("DELETE FROM rehab_kunjungan WHERE no_register = ?")->execute([$id]);
        $success = "Kunjungan berhasil dihapus dari antrean.";
    } catch(Exception $e) {
        $error = "Gagal menghapus kunjungan: " . $e->getMessage();
    }
}
?>

<!-- Top Context / Breadcrumbs Bar -->
<div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 0 0 1rem 0; margin-bottom: -1rem;">

    <!-- <div style="display: flex;align-items: center;gap: 0.5rem;color: #64748b;font-size: 0.875rem;font-weight: 600;padding-left: 15px;">
        <span style="color: #2563eb; cursor: pointer;">Instalasi Rehabilitasi Medik</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" aria-hidden="true" style="width: 1rem; height: 1rem; color: #cbd5e1;" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"></path></svg>
        <span style="color: #2563eb; cursor: pointer;">Pelayanan Loket</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" aria-hidden="true" style="width: 1rem; height: 1rem; color: #cbd5e1;" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"></path></svg>
        <span style="color: #1e293b; font-weight: 700;">Registrasi &amp; Antrean Kunjungan</span>
    </div> -->

    <!-- Active Operational Badge -->
    <!-- <div style="display: flex; align-items: center; gap: 0.5rem; background-color: #ffffff; padding: 0.5rem 1rem; border-radius: 9999px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border: 1px solid #f1f5f9;">
        <span style="display: flex; position: relative; width: 0.625rem; height: 0.625rem;">
            <span style="position: absolute; width: 100%; height: 100%; border-radius: 50%; background-color: #10b981; animation: pulse 2s infinite;"></span>
            <span style="position: relative; display: block; width: 0.625rem; height: 0.625rem; border-radius: 50%; background-color: #10b981;"></span>
        </span>
        <span style="font-size: 0.75rem; font-weight: 700; color: #1e293b; letter-spacing: 0.025em;">LOKET 02 - ADMISI AKTIF</span>
        <span style="color: #cbd5e1; font-size: 0.75rem;">|</span>
        <span style="font-size: 0.75rem; color: #64748b; display: flex; align-items: center; gap: 0.25rem;">
            <i data-lucide="clock" style="width: 0.875rem; height: 0.875rem;"></i> <?= date('l, d M Y') ?> &bull; Shift Pagi
        </span>
    </div> -->
</div>

<?php if(isset($success)): ?>
    <div style="background-color: #eff6ff; color: #1e40af; padding: 1rem; border-radius: 0.75rem; border: 1px solid #bfdbfe; margin-bottom: -1rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);">
        <i data-lucide="check-circle" style="width: 1.25rem; height: 1.25rem;"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div style="background-color: #fef2f2; color: #991b1b; padding: 1rem; border-radius: 0.75rem; border: 1px solid #fecaca; margin-bottom: -5rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);">
        <i data-lucide="alert-circle" style="width: 1.25rem; height: 1.25rem;"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<?php
// Dynamic Hero Metrics Logic
$current_hour = (int)date('H');
$shift_name = ($current_hour < 14) ? 'Shift Pagi' : 'Shift Siang';

$max_kuota = 60;
$today_date = date('Y-m-d');
$stmt_today_count = $pdo->prepare("SELECT COUNT(*) FROM rehab_kunjungan WHERE tgl_kunjungan = ?");
$stmt_today_count->execute([$today_date]);
$terdaftar_hari_ini = (int)$stmt_today_count->fetchColumn();
$sisa_kuota = $max_kuota - $terdaftar_hari_ini;
if ($sisa_kuota < 0) $sisa_kuota = 0;
?>

<!-- Main Headline Block with Visual Telemetry -->
<div class="page-hero">
    <div class="page-hero-decor"></div>
    <div class="page-hero-content">
        <div class="page-hero-tag">
            <span class="badge">ADMISI RAWAT JALAN</span>
            <span class="dot">&bull;</span>
            <span class="text">Rehabilitasi Medik Terpadu RKZ</span>
        </div>
        <h1 class="page-hero-title">Registrasi Kunjungan Pasien Poli Rehab</h1>
        <p class="page-hero-desc">
            Pendaftaran pemeriksaan spesialis KFR, penjadwalan fisioterapi, dan alokasi kuota antrean poli klinik otomatis secara terintegrasi dengan SIMRS.
        </p>
    </div>
    
    <!-- Quick Metrics Pill Counter -->
    <div class="hero-metrics">
        <div class="metric-pill">
            <span class="metric-label">Sisa Kuota <?= $shift_name ?></span>
            <div class="metric-value-wrap">
                <span class="metric-number"><?= $sisa_kuota ?></span>
                <span class="metric-unit">/ <?= $max_kuota ?> kursi</span>
            </div>
        </div>
        <div class="metric-pill">
            <span class="metric-label">Rata-rata Waktu Tunggu</span>
            <div class="metric-value-wrap">
                <span class="metric-number tertiary">18</span>
                <span class="metric-unit">menit</span>
            </div>
        </div>
    </div>
</div>

<!-- Registration Form Card -->
<sessction class="bento-panel" style="margin-bottom: 0.5rem;">
    <div class="bento-header" style="padding: 0.5rem 1rem;">
        <div class="bento-header-left">
            <div class="bento-icon" style="width: 2rem; height: 2rem;">
                <i data-lucide="file-check-2" style="width: 1rem; height: 1rem;"></i>
            </div>
            <div>
                <h2 class="bento-title" style="font-size: 1rem;">Formulir Alokasi Antrean &amp; Pendaftaran Kunjungan</h2>
                <p class="bento-desc" style="font-size: 0.75rem;">Isi parameter kunjungan pasien untuk menerbitkan nomor antrean poli dan lembar tracer</p>
            </div>
        </div>
        <?php $reg_no = 'REG-' . date('YmdHis'); ?>
        <div class="bento-reg-pill">
            <i data-lucide="fingerprint" class="bento-reg-icon"></i>
            <div class="bento-reg-text">
                <span class="bento-reg-label">No. Registrasi Sistem</span>
                <span class="bento-reg-val"><?= $reg_no ?></span>
            </div>
        </div>
    </div>

    <!-- Interactive Registration Form -->
    <div class="bento-body" style="padding: 0.75rem 1rem;">
        <form action="index.php?page=kunjungan" method="POST" style="display: flex; flex-direction: column;">
            <input type="hidden" name="no_register" value="<?= $reg_no ?>">
            
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-start;">
                <!-- Field 1: Cari & Pilih Pasien -->
                <div class="form-field" style="flex: 2 1 300px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="field-label">
                            <i data-lucide="user-search" class="field-label-icon"></i> Pilih Pasien *
                        </label>
                        <a href="index.php?page=pasien" class="field-link">
                            <i data-lucide="plus-circle" style="width: 1rem; height: 1rem;"></i> Pasien Baru
                        </a>
                    </div>
                    <div class="field-input-wrap">
                        <select name="no_rm" class="field-input" required>
                            <option value="">-- Pilih Pasien --</option>
                            <?php
                            $pasien_list = $pdo->query("SELECT no_rm, nama FROM rehab_pasien ORDER BY nama ASC")->fetchAll();
                            foreach($pasien_list as $p):
                            ?>
                            <option value="<?= htmlspecialchars($p['no_rm']) ?>"><?= htmlspecialchars($p['no_rm']) ?> - <?= htmlspecialchars($p['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down" class="field-select-icon"></i>
                    </div>
                </div>

                <!-- Field 2: DPJP Pilihan -->
                <div class="form-field" style="flex: 1 1 200px;">
                    <label class="field-label">
                        <i data-lucide="stethoscope" class="field-label-icon"></i> Dokter DPJP *
                    </label>
                    <div class="field-input-wrap">
                        <select name="dokter_id" class="field-input" required>
                            <option value="">-- Pilih Dokter --</option>
                            <?php
                            $dokter_list = $pdo->query("SELECT NIP as id, Nama as nama_lengkap FROM hrd.datadasar WHERE jeniskyw = 'DOKTER' ORDER BY Nama ASC")->fetchAll();
                            foreach($dokter_list as $d):
                            ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down" class="field-select-icon"></i>
                    </div>
                </div>

                <!-- Field 3: Waktu Kedatangan -->
                <div class="form-field" style="flex: 1 1 150px;">
                    <label class="field-label">
                        <i data-lucide="calendar" class="field-label-icon"></i> Tanggal Kunjungan *
                    </label>
                    <input name="tgl_kunjungan" type="date" value="<?= date('Y-m-d') ?>" class="field-input" required>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="bento-actions">
                <button type="submit" class="btn-primary" style="margin-top: 0; width: auto; font-size: 0.875rem; padding: 0.75rem 1.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i data-lucide="check-square"></i>
                    <span>Daftarkan ke Antrean Poli</span>
                </button>
            </div>
        </form>
    </div>
</section>
<?php
$today = date('Y-m-d');
// Pagination settings
$limit = 5;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page_num < 1) $page_num = 1;
$offset = ($page_num - 1) * $limit;

// Get total for pagination
$stmt_total = $pdo->prepare("SELECT COUNT(*) FROM rehab_kunjungan WHERE tgl_kunjungan = ?");
$stmt_total->execute(array($today));
$total_antrean = $stmt_total->fetchColumn();
$total_pages = ceil($total_antrean / $limit);

$stmt = $pdo->prepare("SELECT k.*, p.nama AS nama_pasien, p.tgl_lahir, d.Nama AS nama_dokter FROM rehab_kunjungan k JOIN rehab_pasien p ON k.no_rm = p.no_rm LEFT JOIN hrd.datadasar d ON k.dokter_id = d.NIP WHERE k.tgl_kunjungan = ? ORDER BY k.no_register DESC LIMIT $limit OFFSET $offset");
$stmt->execute(array($today));
$antrean = $stmt->fetchAll();
?>
<section style="display: flex; flex-direction: column; gap: 0.5rem;">
    <!-- Header Controls -->
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <!-- <h3 style="font-size: 1.125rem; font-weight: 700; color: #1e293b; margin: 0;">Antrean Kunjungan Aktif Hari Ini</h3> -->
                <h3 style="font-size: 1rem;font-weight: 700;color: #1e293b;margin: 0;padding-left: 12px;">Antrean Kunjungan Aktif Hari Ini</h3>
                <span style="background-color: #2563eb;color: #ffffff;font-size: 0.625rem;font-weight: 700;padding: 0.125rem 0.375rem;">
                    <?= $total_antrean ?> Pasien Terdaftar
                </span>
            </div>
            <p style="font-size: 0.6875rem;padding-left: 12px;margin: 0;">Real-time status triage poli rehabilitasi medik.</p>
        </div>
        <div style="width: 100%; max-width: 15rem;">
            <div class="search-bar">
                <i data-lucide="search" class="search-icon" style="width: 1rem; height: 1rem;"></i>
                <input type="text" id="queue-search" onkeyup="filterQueueList()" placeholder="Cari antrean..." class="search-input" style="background-color: #ffffff; border: 1px solid #e2e8f0; height: 2rem; border-radius: 0.5rem; font-size: 0.75rem; padding-left: 2rem;">
            </div>
        </div>
    </div>

    <!-- Active Table Panel -->
    <div class="bento-panel" style="margin-bottom: 0;">
        <div class="table-container">
            <table class="data-table" id="queue-table">
                <thead>
                    <tr>
                        <th style="width: 8rem;">No. Antrean</th>
                        <th>Pasien &amp; No. Rekam Medis</th>
                        <th>Dokter DPJP / Poli</th>
                        <th>Status Kunjungan</th>
                        <th style="text-align: right;">Aksi Terpadu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($total_antrean > 0): ?>
                        <?php foreach($antrean as $idx => $kunj): 
                            $no_antrean_num = str_pad($total_antrean - $idx - $offset, 3, "0", STR_PAD_LEFT);
                            $birthDate = new DateTime($kunj['tgl_lahir']);
                            $todayDate = new DateTime('today');
                            $age = $birthDate->diff($todayDate)->y;
                            
                            $initials = '';
                            $name_parts = explode(' ', $kunj['nama_pasien']);
                            if(count($name_parts)>0) $initials .= substr($name_parts[0], 0, 1);
                            if(count($name_parts)>1) $initials .= substr($name_parts[1], 0, 1);
                        ?>
                        <tr class="queue-row">
                            <td style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 800; color: #1e293b; font-size: 0.875rem; letter-spacing: -0.025em;">NO-<?= $no_antrean_num ?></span>
                                    <span style="font-size: 0.625rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0;">Loket 01</span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 1.75rem; height: 1.75rem; border-radius: 50%; background-color: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem;">
                                        <?= htmlspecialchars($initials) ?>
                                    </div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 700; color: #1e293b; font-size: 0.875rem; text-transform: uppercase;"><?= htmlspecialchars($kunj['nama_pasien']) ?></span>
                                        <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.125rem;">NO: <?= htmlspecialchars($kunj['no_rm']) ?> &bull; <?= $age ?> Th</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 600; color: #334155; font-size: 0.875rem;"><?= htmlspecialchars($kunj['nama_dokter'] ?: 'Dokter Tidak Ditemukan') ?></span>
                                    <span style="font-size: 0.625rem; color: #94a3b8; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; margin-top: 0.125rem;">Klinik Rehabilitasi</span>
                                </div>
                            </td>
                            <td>
                                <?php if ($kunj['status'] === 'selesai'): ?>
                                    <span class="badge-status success" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">
                                        <span class="dot"></span> Sudah Diperiksa
                                    </span>
                                <?php else: ?>
                                    <span class="badge-status waiting" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">
                                        <span class="dot"></span> Menunggu Dokter
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.25rem;">
                                    <?php if ($kunj['status'] === 'selesai'): ?>
                                        <a href="index.php?page=detail_rm&kunjungan_id=<?= htmlspecialchars($kunj['no_register']) ?>" style="padding: 0.375rem; color: #3b82f6; border-radius: 0.375rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#eff6ff'" onmouseout="this.style.backgroundColor='transparent'" title="Lihat Rekam Medis">
                                            <i data-lucide="eye" style="width: 1.125rem; height: 1.125rem;"></i>
                                        </a>
                                    <?php else: ?>
                                        <button style="padding: 0.375rem; color: #3b82f6; border: none; cursor: pointer; border-radius: 0.375rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#eff6ff'" onmouseout="this.style.backgroundColor='transparent'" title="Panggil Antrean" type="button">
                                            <i data-lucide="megaphone" style="width: 1.125rem; height: 1.125rem;"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($kunj['status'] === 'selesai'): ?>
                                        <a href="views/cetak_rm.php?kunjungan_id=<?= htmlspecialchars($kunj['no_register']) ?>" target="_blank" style="padding: 0.375rem; color: #64748b; border-radius: 0.375rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'" title="Cetak Rekam Medis">
                                            <i data-lucide="printer" style="width: 1.125rem; height: 1.125rem;"></i>
                                        </a>
                                    <?php else: ?>
                                        <button style="padding: 0.375rem; color: #94a3b8; border: none; cursor: not-allowed; border-radius: 0.375rem;" title="Belum Diperiksa (Cetak Barcode)" type="button" disabled>
                                            <i data-lucide="printer" style="width: 1.125rem; height: 1.125rem;"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="index.php?page=kunjungan&action=delete&id=<?= htmlspecialchars($kunj['no_register']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus kunjungan beserta rekam medis ini dari sistem?');" style="padding: 0.375rem; color: #ef4444; border-radius: 0.375rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#fef2f2'" onmouseout="this.style.backgroundColor='transparent'" title="Hapus Kunjungan">
                                        <i data-lucide="trash-2" style="width: 1.125rem; height: 1.125rem;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: #94a3b8; font-style: italic;">Belum ada antrean terdaftar hari ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
        <div style="border-top: 1px solid #e2e8f0; background-color: #ffffff; padding: 1rem 1.5rem;">
            <div class="pagination-container" style="margin: 0;">
                <div class="pagination-info">
                    Menampilkan halaman <?= $page_num ?> dari <?= $total_pages ?> (Total <?= $total_antrean ?> data)
                </div>
                <div class="pagination-controls">
                    <?php if ($page_num > 1): ?>
                        <a href="index.php?page=kunjungan&p=<?= $page_num - 1 ?>" class="page-btn">Sebelumnya</a>
                    <?php else: ?>
                        <span class="page-btn disabled">Sebelumnya</span>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="index.php?page=kunjungan&p=<?= $i ?>" class="page-btn <?= $i === $page_num ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page_num < $total_pages): ?>
                        <a href="index.php?page=kunjungan&p=<?= $page_num + 1 ?>" class="page-btn">Berikutnya</a>
                    <?php else: ?>
                        <span class="page-btn disabled">Berikutnya</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function filterQueueList() {
    var query = document.getElementById('queue-search').value.toLowerCase();
    var rows = document.querySelectorAll('.queue-row');
    rows.forEach(function(row) {
        var text = row.innerText.toLowerCase();
        if (text.indexOf(query) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
