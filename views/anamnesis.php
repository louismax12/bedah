<?php
// c:\Users\louis\Documents\rehab\rehab_rkz\views\anamnesis.php
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php?page=login';</script>";
    exit;
}

// Jika ada aksi pendaftaran pasien & kunjungan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_kunjungan') {
    try {
        $pdo->beginTransaction();
        
        $no_rm = $_POST['no_rm'];
        $no_register = $_POST['no_register'];
        $dokter_id = $_POST['dokter_id'];
        $tgl_kunjungan = $_POST['tgl_kunjungan'];
        
        // Cek apakah data kunjungan sudah ada untuk mencegah duplicate entry error
        $stmt_check = $pdo->prepare("SELECT no_register FROM rehab_kunjungan WHERE no_register = ?");
        $stmt_check->execute([$no_register]);
        
        if (!$stmt_check->fetch()) {
            $stmt_k = $pdo->prepare("INSERT INTO rehab_kunjungan (no_register, no_rm, tgl_kunjungan, dokter_id) VALUES (?, ?, ?, ?)");
            $stmt_k->execute([$no_register, $no_rm, $tgl_kunjungan, $dokter_id]);
        } else {
            // Update data jika sudah ada (barangkali user mengganti tanggal atau dokter)
            $stmt_k = $pdo->prepare("UPDATE rehab_kunjungan SET tgl_kunjungan = ?, dokter_id = ? WHERE no_register = ?");
            $stmt_k->execute([$tgl_kunjungan, $dokter_id, $no_register]);
        }
        
        $pdo->commit();
        
        // Langsung arahkan ke halaman periksa dengan JS redirect karena header.php sudah me-render HTML
        echo "<script>window.location.href='index.php?page=anamnesis&kunjungan_id=" . htmlspecialchars($no_register) . "';</script>";
        exit;
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = "Gagal mendaftarkan kunjungan: " . $e->getMessage();
    }
}

if (!isset($_GET['kunjungan_id'])) {
    // Tampilkan Form Pencarian & Pendaftaran jika belum memilih kunjungan
    
    // Fetch data 500 kunjungan terakhir dari dbold
    $opt_pasien = [];
    try {
        $stmt_opt = $pdo->query("SELECT p.fnoreg, p.frmno, b.nama AS fname, b.tanggallahir AS ftgllahir, CASE WHEN b.telphp IS NOT NULL AND b.telphp != '' AND b.telphp != '-' THEN b.telphp ELSE b.telp END AS fnotelp, b.alamat AS falamat FROM (SELECT fnoreg, frmno FROM dbold.poliumumupcust ORDER BY fdate_in DESC LIMIT 500) p JOIN pasien.rmlink r ON p.frmno = r.rmunit JOIN pasien.biodata b ON r.idbiodata = b.idbiodata");
        $raw_pasien = $stmt_opt->fetchAll();
        $seen = [];
        foreach ($raw_pasien as $row) {
            if (!isset($seen[$row['frmno']])) {
                $seen[$row['frmno']] = true;
                $opt_pasien[] = $row;
            }
        }
    } catch(Exception $e) {}
    
    // Dokter list
    $dokter_list = [];
    try {
        $dokter_list = $pdo->query("SELECT NIP as id, Nama as nama_lengkap FROM hrd.datadasar WHERE jeniskyw = 'DOKTER' ORDER BY Nama ASC")->fetchAll();
    } catch(Exception $e) {}
?>

<?php if (isset($error)): ?>
    <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bento-panel">
    <div class="bento-header" style="background-color: #2563eb; color: white;">
        <div class="bento-title-wrapper">
            <h3 class="bento-title" style="color: white;">Form Pendaftaran & Alokasi</h3>
        </div>
    </div>
    <div class="bento-body">
        <form action="index.php?page=anamnesis" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <input type="hidden" name="action" value="create_kunjungan">
            
            <div class="form-field">
                <label class="field-label" style="margin-top: 10px;margin-left: 25px;"><i data-lucide="search" class="field-label-icon"></i> Cari & Pilih Pasien (Master DB)</label>
                <div class="field-input-wrap">
                    <select id="select-pasien" name="no_register" class="field-input" required>
                        <option value="">-- Pilih Data Pasien Terdaftar --</option>
                        <?php foreach($opt_pasien as $p): ?>
                        <option value="<?= htmlspecialchars($p['fnoreg']) ?>">
                            REG-<?= htmlspecialchars($p['fnoreg']) ?> - <?= htmlspecialchars($p['fname']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <i data-lucide="chevron-down" class="field-select-icon" style="margin-right: 0px;right: 60px;"></i>
                </div>
            </div>
            
            <!-- Biodata Section (Auto filled) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                <div class="form-field">
                    <label class="field-label">No. RM</label>
                    <input name="no_rm" type="text" class="field-input" readonly required style="background: #e2e8f0;">
                </div>
                <div class="form-field">
                    <label class="field-label">Nama Lengkap</label>
                    <input name="nama" type="text" class="field-input" readonly required style="background: #e2e8f0;">
                </div>
                <div class="form-field">
                    <label class="field-label">Tanggal Lahir</label>
                    <input name="tgl_lahir" type="text" class="field-input" readonly required style="background: #e2e8f0;">
                </div>
                <div class="form-field">
                    <label class="field-label">Nomor Telp/HP</label>
                    <input name="no_telp" type="text" class="field-input" readonly style="background: #e2e8f0;">
                </div>
                <div class="form-field" style="grid-column: 1 / -1;">
                    <label class="field-label">Alamat</label>
                    <textarea name="alamat" class="field-input" readonly style="background: #e2e8f0; height: 3rem;"></textarea>
                </div>
            </div>
            
            <!-- Kunjungan Section -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-field">
                    <label class="field-label"><i data-lucide="stethoscope" class="field-label-icon" style="margin-left: 23px"></i> Dokter DPJP</label>
                    <div class="field-input-wrap">
                        <select name="dokter_id" class="field-input" required>
                            <option value="">-- Pilih Dokter --</option>
                            <?php foreach($dokter_list as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down" class="field-select-icon"></i>
                    </div>
                </div>
                <div class="form-field">
                    <label class="field-label"><i data-lucide="calendar" class="field-label-icon"></i> Tanggal Kunjungan</label>
                    <input name="tgl_kunjungan" type="date" value="<?= date('Y-m-d') ?>" class="field-input" required>
                </div>
            </div>
            
            <div class="bento-actions" style="margin-top: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; background-color: #fffbeb; border-radius: 0.75rem; border: 1px dashed #fcd34d;">
                <div style="font-size: 0.85rem; color: #b45309; display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; text-align: right; max-width: 55%;">
                    <i data-lucide="lightbulb" style="width: 1.25rem; height: 1.25rem; color: #d97706; flex-shrink: 0;"></i>
                    <span><strong>Catatan:</strong> Data registrasi akan otomatis tersimpan dan dialokasikan ke antrean.</span>
                </div>
                <button type="submit" class="btn-primary" style="width: auto; justify-content: center; margin-top: 0; padding: 0.5rem 1rem; font-size: 0.8125rem; letter-spacing: 0.025em; box-shadow: 0 1px 2px rgba(37, 99, 235, 0.15); gap: 0.375rem;">
                    <i data-lucide="arrow-right-circle" style="width: 1rem; height: 1rem;"></i>
                    <span>Lanjutkan ke Pemeriksaan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const optPasienData = <?= json_encode($opt_pasien) ?>;
    const selectRm = document.getElementById('select-pasien');
    
    if (selectRm) {
        selectRm.addEventListener('change', function() {
            const selectedNoreg = this.value;
            const patient = optPasienData.find(p => p.fnoreg === selectedNoreg);
            
            if (patient) {
                document.querySelector('input[name="no_rm"]').value = patient.frmno || '';
                document.querySelector('input[name="nama"]').value = patient.fname || '';
                if(patient.ftgllahir) document.querySelector('input[name="tgl_lahir"]').value = patient.ftgllahir.split(' ')[0];
                document.querySelector('input[name="no_telp"]').value = patient.fnotelp || '';
                document.querySelector('textarea[name="alamat"]').value = patient.falamat || '';
            }
        });
    }
});
</script>
<?php
} else {


$kunjungan_id = $_GET['kunjungan_id'];

// Ambil data kunjungan dan pasien (Langsung join ke master biodata rumah sakit)
$stmt = $pdo->prepare("SELECT k.*, b.nama, k.no_rm, b.tanggallahir as tgl_lahir, b.telp as no_telp 
                       FROM rehab_kunjungan k 
                       JOIN pasien.rmlink r ON k.no_rm = r.rmunit 
                       JOIN pasien.biodata b ON r.idbiodata = b.idbiodata 
                       WHERE k.no_register = ?");
$stmt->execute(array($kunjungan_id));
$data = $stmt->fetch();

if (!$data) {
    echo "<div style='padding: 1.5rem; text-align: center; color: #dc2626; font-weight: 700;'>Data kunjungan tidak valid.</div>";
    exit;
}

// Cek spesialisasi dokter yang sedang login
$stmt_sp = $pdo->prepare("SELECT * FROM hrd.datadasar WHERE NIP = ?");
$stmt_sp->execute(array($_SESSION['user_id']));
$dokter_info = $stmt_sp->fetch();

$spesialisasi = isset($dokter_info['spesialisasi']) ? $dokter_info['spesialisasi'] : 'KFR'; 

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $diagnosa_masuk = isset($_POST['diagnosa_masuk']) ? $_POST['diagnosa_masuk'] : '';
        $keluhan_utama = isset($_POST['keluhan_utama']) ? $_POST['keluhan_utama'] : '';
        $riwayat_sekarang = isset($_POST['riwayat_sekarang']) ? $_POST['riwayat_sekarang'] : '';
        $riwayat_dulu = isset($_POST['riwayat_dulu']) ? $_POST['riwayat_dulu'] : '';
        $riwayat_keluarga = isset($_POST['riwayat_keluarga']) ? $_POST['riwayat_keluarga'] : '';
        $detail_pemeriksaan = isset($_POST['detail_pemeriksaan']) ? $_POST['detail_pemeriksaan'] : '';
        $jenis_form = isset($_POST['jenis_form']) ? $_POST['jenis_form'] : 'umum';

        // Check if rekam medis already exists before POST
        $stmt_check_rm = $pdo->prepare("SELECT id FROM rehab_rekam_medis WHERE no_register = ?");
        $stmt_check_rm->execute(array($kunjungan_id));
        $existing_rm = $stmt_check_rm->fetch();
        
        if ($existing_rm) {
            $stmt_upd = $pdo->prepare("UPDATE rehab_rekam_medis SET diagnosa_masuk=?, keluhan_utama=?, riwayat_sekarang=?, riwayat_dulu=?, riwayat_keluarga=?, jenis_form=?, detail_pemeriksaan=? WHERE id=?");
            $stmt_upd->execute(array($diagnosa_masuk, $keluhan_utama, $riwayat_sekarang, $riwayat_dulu, $riwayat_keluarga, $jenis_form, $detail_pemeriksaan, $existing_rm['id']));
            $rm_id = $existing_rm['id'];
            
            // Delete old pins
            $pdo->prepare("DELETE FROM rehab_body_mapping WHERE rekam_medis_id = ?")->execute([$rm_id]);
        } else {
            $stmt_ins = $pdo->prepare("INSERT INTO rehab_rekam_medis (no_register, diagnosa_masuk, keluhan_utama, riwayat_sekarang, riwayat_dulu, riwayat_keluarga, jenis_form, detail_pemeriksaan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_ins->execute(array($kunjungan_id, $diagnosa_masuk, $keluhan_utama, $riwayat_sekarang, $riwayat_dulu, $riwayat_keluarga, $jenis_form, $detail_pemeriksaan));
            
            $rm_id = $pdo->lastInsertId();
        }

        // Parse dan simpan body mapping
        $pin_data = isset($_POST['body_mapping_data']) ? $_POST['body_mapping_data'] : '[]';
        $pins = json_decode($pin_data, true);
        
        if (is_array($pins)) {
            $stmt_pin = $pdo->prepare("INSERT INTO rehab_body_mapping (rekam_medis_id, x_coord, y_coord, keterangan) VALUES (?, ?, ?, ?)");
            foreach($pins as $pin) {
                // Konversi string px ke integer
                $x = isset($pin['x_coord']) ? intval(str_replace('px', '', $pin['x_coord'])) : 0;
                $y = isset($pin['y_coord']) ? intval(str_replace('px', '', $pin['y_coord'])) : 0;
                $notes = isset($pin['keterangan']) ? $pin['keterangan'] : '';
                
                $stmt_pin->execute(array($rm_id, $x, $y, $notes));
            }
        }

        // Update status antrean menjadi selesai
        $stmt_update = $pdo->prepare("UPDATE rehab_kunjungan SET status = 'selesai' WHERE no_register = ?");
        $stmt_update->execute(array($kunjungan_id));

        $pdo->commit();
        $success = "Data pemeriksaan berhasil disimpan!";
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Cek jika sudah ada rekam medis (Fetch data TERBARU setelah di-submit)
$stmt_rm = $pdo->prepare("SELECT * FROM rehab_rekam_medis WHERE no_register = ?");
$stmt_rm->execute(array($kunjungan_id));
$rekam_medis = $stmt_rm->fetch();

$body_mapping_json = '[]';
if ($rekam_medis) {
    // Ambil body mapping
    $stmt_bm = $pdo->prepare("SELECT x_coord, y_coord, keterangan FROM rehab_body_mapping WHERE rekam_medis_id = ?");
    $stmt_bm->execute(array($rekam_medis['id']));
    $pins = [];
    $pin_id = 1;
    while ($row = $stmt_bm->fetch()) {
        $pins[] = [
            'id' => $pin_id++,
            'x_coord' => floatval($row['x_coord']),
            'y_coord' => floatval($row['y_coord']),
            'keterangan' => $row['keterangan']
        ];
    }
    $body_mapping_json = json_encode($pins);
}
?>

<?php if(isset($success)): ?>
<div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 0.75rem; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; display: flex; align-items: center; gap: 0.75rem;">
    <span class="material-symbols-outlined" style="font-size: 20px;">check_circle</span>
    <span style="font-weight: 500; font-size: 0.875rem;"><?= htmlspecialchars($success) ?></span>
</div>
<?php endif; ?>

<!-- UI Form Anamnesis & Asesmen -->
<form method="POST" action="" style="width: 100%; display: flex; flex-direction: column; gap: 0.5rem;" id="anamnesisForm">
  <!-- Hidden input to store body mapping data as JSON -->
  <input type="hidden" name="body_mapping_data" id="body_mapping_data" value="<?= htmlspecialchars(isset($body_mapping_json) ? $body_mapping_json : '[]') ?>">

  <div style="display: flex; flex-direction: column; width: 100%; padding-bottom: 1rem; gap: 0.75rem;">
    <!-- PATIENT SUMMARY STRIP (Top Banner) -->
    <section class="panel m-0 patient-info-header">
        <div class="patient-avatar-wrapper">
            <div class="patient-avatar-large" style="width: 3rem; height: 3rem; font-size: 1.25rem;">
                <span class="material-symbols-outlined" style="font-size: 28px;">personal_injury</span>
            </div>
            <div class="flex-col">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="patient-name text-xl"><?= htmlspecialchars($data['nama']) ?></span>
                    <span style="padding: 0.25rem 0.5rem; background-color: #f1f5f9; color: #475569; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">54 Thn 8 Bln</span>
                    <span class="badge-status-success">
                        <span class="sidebar-role-dot admin" style="background-color: #059669;"></span>
                        Form RMF-09 Terintegrasi
                    </span>
                </div>
                <div class="patient-meta mt-1 text-slate-500">
                    <span><strong class="text-slate-800">No. RM:</strong> <?= htmlspecialchars($data['no_rm']) ?></span>
                    <span class="text-slate-300">&bull;</span>
                    <span><strong class="text-slate-800">No. Reg:</strong> <?= htmlspecialchars($data['no_register']) ?></span>
                    <span class="text-slate-300">&bull;</span>
                    <span><strong class="text-slate-800">Telp:</strong> <?= htmlspecialchars($data['no_telp']) ?></span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 pl-4 border-l border-slate-200">
            <span class="material-symbols-outlined text-blue-600 icon-sm">medical_services</span>
            <div class="flex-col">
                <span class="meta-label">Dokter DPJP</span>
                <span class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></span>
            </div>
        </div>
    </section>
    
    <!-- Quick Actions -->
    <div class="flex items-center gap-2 flex-wrap">
        <button class="btn-light text-sm px-4 py-2" type="button">
            <span class="material-symbols-outlined icon-sm">history</span>
            <span>Riwayat EMR</span>
        </button>
        <a href="views/cetak_rm.php?kunjungan_id=<?= htmlspecialchars($data['no_register']) ?>" target="_blank" class="btn-light text-sm px-4 py-2" style="text-decoration: none;">
            <span class="material-symbols-outlined icon-sm">print</span>
            <span>Cetak RMF-09</span>
        </a>
        <button class="btn-light text-sm px-4 py-2" style="background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;" type="button">
            <span class="material-symbols-outlined icon-sm">check_circle</span>
            <span>Selesaikan Sesi</span>
        </button>
    </div>

    <!-- MAIN TWO-COLUMN WORKFLOW WORKSPACE -->
    <div class="dashboard-grid" style="margin-top: 0;">
        <!-- LEFT COLUMN: MEDICAL ANAMNESIS & CLINICAL EXAMINATION (Col 2) -->
        <section class="dashboard-col-2 flex-col gap-6">
            <div class="panel m-0 p-6 flex-col gap-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600 icon-md">clinical_notes</span>
                        <h2 class="text-lg font-bold text-slate-800 m-0">Pemeriksaan &amp; Anamnesis RMF-09</h2>
                    </div>
                    <span class="text-xs text-slate-500 bg-slate-50 px-2 py-1 rounded-md border border-slate-200">Kode Form: RKZ-RM-09/24</span>
                </div>
                
                <!-- Admission Diagnosis -->
                <div class="form-field">
                    <label class="field-label flex justify-between w-full">
                        <span>Diagnosa Masuk (Admission Diagnosis) <span class="text-red-500">*</span></span>
                        <span class="text-blue-600 font-normal text-xs">ICD-10 M53.3</span>
                    </label>
                    <input name="diagnosa_masuk" class="field-input" type="text" value="<?= isset($rekam_medis['diagnosa_masuk']) ? htmlspecialchars($rekam_medis['diagnosa_masuk']) : '' ?>" required>
                </div>
                
                <!-- Main Complaint -->
                <div class="form-field">
                    <label class="field-label">Keluhan Utama (Chief Complaint) <span class="text-red-500">*</span></label>
                    <textarea name="keluhan_utama" class="field-input h-auto py-3 px-4 resize-none" rows="2" required><?= isset($rekam_medis['keluhan_utama']) ? htmlspecialchars($rekam_medis['keluhan_utama']) : '' ?></textarea>
                </div>
                
                <!-- Present Illness History (RPS) -->
                <div class="form-field">
                    <label class="field-label">Riwayat Penyakit Sekarang (RPS)</label>
                    <textarea name="riwayat_sekarang" class="field-input h-auto py-3 px-4 resize-none" rows="2"><?= isset($rekam_medis['riwayat_sekarang']) ? htmlspecialchars($rekam_medis['riwayat_sekarang']) : '' ?></textarea>
                </div>
                
                <!-- Past Medical & Family History -->
                <div class="grid-2 gap-4">
                    <div class="form-field">
                        <label class="field-label">Riwayat Penyakit Dahulu (RPD)</label>
                        <input name="riwayat_dulu" class="field-input" type="text" value="<?= isset($rekam_medis['riwayat_dulu']) ? htmlspecialchars($rekam_medis['riwayat_dulu']) : '' ?>">
                    </div>
                    <div class="form-field">
                        <label class="field-label">Riwayat Penyakit Keluarga (RPK)</label>
                        <input name="riwayat_keluarga" class="field-input" type="text" value="<?= isset($rekam_medis['riwayat_keluarga']) ? htmlspecialchars($rekam_medis['riwayat_keluarga']) : '' ?>">
                    </div>
                </div>
                
                <!-- CONDITIONAL SPECIALIZATION CONTAINER: MUSCULOSKELETAL SPECIFIC -->
                <div id="musculoSection" class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex-col gap-4 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 icon-sm">fitness_center</span>
                            <span class="text-base text-blue-600 font-bold">Pemeriksaan Range of Motion (ROM / LGS) &amp; Deformitas</span>
                        </div>
                        <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Khusus Muskuloskeletal / Umum</span>
                    </div>
                    <div class="grid-2 gap-4">
                        <!-- Pelvis & Lumbar Fleksio-Ekstensio -->
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-800 font-bold">Fleksio Lumbal / Lumbosakral</span>
                                <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded font-bold" id="romVal1">60&deg; (Normal: 60&deg;)</span>
                            </div>
                            <input class="w-full cursor-pointer" max="90" min="10" oninput="document.getElementById('romVal1').innerText = this.value + '&deg; (' + (this.value < 60 ? 'Defisit: ' + (60 - this.value) + '&deg;' : 'Normal') + ')'" type="range" value="60">
                            <span class="text-xs text-slate-500"></span>
                        </div>
                        <!-- Palpasi & Swelling -->
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 flex-col gap-2">
                            <span class="text-sm text-slate-800 font-bold">Palpasi Deformitas &amp; Nyeri Tekan</span>
                            <div class="flex items-center gap-4 pt-1">
                                <label class="inline-flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox">
                                    <span class="text-sm text-slate-800">Nyeri Tekan (+)</span>
                                </label>
                                <label class="inline-flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox">
                                    <span class="text-sm text-slate-800">Swelling / Oedem Ringan</span>
                                </label>
                            </div>
                            <input class="field-input h-9 text-xs" placeholder="Catatan palpasi regio sacrococcyx..." type="text" value="">
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 italic m-0">*Field ROM dan Swelling ini otomatis beradaptasi bila spesialisasi diubah ke Kardiorespirasi.</p>
                </div>
                
                <!-- CONDITIONAL CONTAINER: KARDIORESPIRASI ONLY (Initially Hidden) -->
                <div id="cardioSection" class="hidden p-4 bg-slate-50 rounded-xl border border-slate-200 flex-col gap-4 transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600 icon-sm">monitor_heart</span>
                        <span class="text-base text-blue-600 font-bold">Evaluasi Kapasitas Kardiorespirasi &amp; Paru</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="bg-white p-3 rounded-lg border border-slate-100">
                            <span class="text-xs text-slate-500">Kapasitas Vital Paru (FVC)</span>
                            <input class="w-full border-none bg-transparent text-base text-slate-800 font-bold outline-none" type="text" placeholder="Misal: 3.2 L..." value="">
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-slate-100">
                            <span class="text-xs text-slate-500">SpO2 Saat Aktivitas</span>
                            <input class="w-full border-none bg-transparent text-base text-slate-800 font-bold outline-none" type="text" placeholder="Misal: 98%..." value="">
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-slate-100">
                            <span class="text-xs text-slate-500">Uji Jalan 6 Menit (6MWD)</span>
                            <input class="w-full border-none bg-transparent text-base text-slate-800 font-bold outline-none" type="text" placeholder="Misal: 380m..." value="">
                        </div>
                    </div>
                </div>
                
                <!-- Problem Analysis & Action Plan -->
                <div class="form-field">
                    <label class="field-label">Rencana Tindakan / Terapi Fisik (Therapeutic Action Plan)</label>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 flex-col gap-3">
                        <div class="grid-2 gap-3">
                            <label class="flex items-center gap-2 bg-white p-3 rounded-lg border border-slate-100 cursor-pointer text-sm text-slate-800 font-medium">
                                <input type="checkbox">
                                <span>TENS (Transcutaneous Nerve Stim.)</span>
                            </label>
                            <label class="flex items-center gap-2 bg-white p-3 rounded-lg border border-slate-100 cursor-pointer text-sm text-slate-800 font-medium">
                                <input type="checkbox">
                                <span>SWD / Diatermi Regio Lumbal</span>
                            </label>
                            <label class="flex items-center gap-2 bg-white p-3 rounded-lg border border-slate-100 cursor-pointer text-sm text-slate-800 font-medium">
                                <input type="checkbox">
                                <span>Pelvic Floor Muscle Strengthening</span>
                            </label>
                            <label class="flex items-center gap-2 bg-white p-3 rounded-lg border border-slate-100 cursor-pointer text-sm text-slate-800 font-medium">
                                <input type="checkbox">
                                <span>Edukasi Ergonomis: Donut Cushion</span>
                            </label>
                        </div>
                        <textarea name="detail_pemeriksaan" class="field-input h-auto p-3 text-sm resize-none" placeholder="Instruksi spesifik untuk Fisioterapis pelaksana..." rows="2"><?= isset($rekam_medis['detail_pemeriksaan']) ? htmlspecialchars($rekam_medis['detail_pemeriksaan']) : '' ?></textarea>
                    </div>
                </div>
            </div>
        </section>

        <!-- RIGHT COLUMN: INTERACTIVE CLINICAL BODY MAPPING -->
        <section class="flex-col gap-6">
            <div class="panel m-0 p-6 flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div class="flex-col">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 icon-md">accessibility_new</span>
                            <h2 class="text-lg font-bold text-slate-800 m-0">Pemetaan Lokasi Gejala</h2>
                        </div>
                        <span class="text-sm text-slate-500 mt-1">Klik siluet tubuh untuk menandai titik nyeri.</span>
                    </div>
                    <button class="btn-light text-xs px-3 py-1 flex items-center gap-1" onclick="clearLastPin()" title="Hapus marker terakhir" type="button">
                        <span class="material-symbols-outlined icon-sm">undo</span>
                        <span>Undo</span>
                    </button>
                </div>
                
                <!-- Legend -->
                <div class="flex items-center justify-between flex-wrap gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
                    <span class="flex items-center gap-1 text-slate-600"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Nyeri (Pain)</span>
                    <span class="flex items-center gap-1 text-slate-600"><span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> Kesemutan</span>
                    <span class="flex items-center gap-1 text-slate-600"><span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span> Baal/Kebas</span>
                    <span class="flex items-center gap-1 text-slate-600"><span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span> Bengkak/Luka</span>
                </div>
                
                <!-- Interactive Canvas -->
                <div id="zoomWrapper" style="position: relative; width: 100%; background-color: rgba(241, 245, 249, 0.5); border-radius: 0.75rem; padding: 1rem; display: flex; align-items: center; justify-content: center; min-height: 460px; user-select: none; touch-action: none;">
                    <div id="bodyCanvasContainer" style="position: relative; cursor: crosshair; width: 360px; height: 430px; display: flex; justify-content: space-around; align-items: center; transform-origin: center; transition: transform 0.075s;">
                        
                        <!-- ANTERIOR -->
                        <div class="flex-col items-center">
                            <span class="text-xs text-slate-500 uppercase font-bold mb-1">Tampak Depan</span>
                            <svg style="width: 130px; height: auto; stroke: #94a3b8; fill: none; pointer-events: none;" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 100 240">
                                <ellipse cx="50" cy="22" fill="#ffffff" rx="12" ry="15"></ellipse>
                                <circle cx="46" cy="20" fill="currentColor" r="0.8"></circle>
                                <circle cx="54" cy="20" fill="currentColor" r="0.8"></circle>
                                <path d="M50 21 L50 25 L52 26"></path>
                                <path d="M44 36 L43 44 L25 49 L18 88 L14 135 L20 138 L25 95 L28 65" fill="#ffffff"></path>
                                <path d="M56 36 L57 44 L75 49 L82 88 L86 135 L80 138 L75 95 L72 65" fill="#ffffff"></path>
                                <path d="M28 65 L27 105 L32 125 L40 128 L50 128 L60 128 L68 125 L73 105 L72 65" fill="#ffffff"></path>
                                <path d="M36 68 Q50 72 64 68" stroke-dasharray="2 2" stroke-width="1"></path>
                                <path d="M50 44 L50 115" stroke-dasharray="2 2" stroke-width="1"></path>
                                <path d="M50 128 L58 135 L62 175 L59 215 L66 235 L53 235 L48 215 L50 175 L50 128" fill="#ffffff"></path>
                                <path d="M50 128 L42 135 L38 175 L41 215 L34 235 L47 235 L52 215 L50 175 L50 128" fill="#ffffff"></path>
                                <ellipse cx="40" cy="175" rx="3" ry="2" stroke-width="1"></ellipse>
                                <ellipse cx="60" cy="175" rx="3" ry="2" stroke-width="1"></ellipse>
                            </svg>
                        </div>
                        
                        <!-- POSTERIOR -->
                        <div class="flex-col items-center">
                            <span class="text-xs text-slate-500 uppercase font-bold mb-1">Tampak Belakang</span>
                            <svg style="width: 130px; height: auto; stroke: #94a3b8; fill: none; pointer-events: none;" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 100 240">
                                <ellipse cx="50" cy="22" fill="#ffffff" rx="12" ry="15"></ellipse>
                                <path d="M44 36 L43 44 L25 49 L18 88 L14 135 L20 138 L25 95 L28 65" fill="#ffffff"></path>
                                <path d="M56 36 L57 44 L75 49 L82 88 L86 135 L80 138 L75 95 L72 65" fill="#ffffff"></path>
                                <path d="M50 36 L50 125" stroke-dasharray="3 2" stroke-width="1.2"></path>
                                <path d="M28 65 L27 105 L32 125 L40 132 L50 132 L60 132 L68 125 L73 105 L72 65" fill="#ffffff"></path>
                                <path d="M50 115 C45 132 35 135 32 125" stroke-width="1.2"></path>
                                <path d="M50 115 C55 132 65 135 68 125" stroke-width="1.2"></path>
                                <path d="M50 132 L58 135 L62 175 L59 215 L66 235 L53 235 L48 215 L50 175 L50 132" fill="#ffffff"></path>
                                <path d="M50 132 L42 135 L38 175 L41 215 L34 235 L47 235 L52 215 L50 175 L50 132" fill="#ffffff"></path>
                            </svg>
                        </div>
                        
                        <!-- PIN MARKERS LAYER -->
                        <div id="pinsContainer" style="position: absolute; inset: 0; pointer-events: none;"></div>
                        
                        <!-- POPUP DIALOG -->
                        <div id="pinModal" class="hidden absolute z-40 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-lg p-4 w-72 border border-slate-200 pointer-events-auto" style="top: 50%; left: 50%;">
                            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                                <span class="text-sm font-bold text-slate-800 flex items-center gap-1">
                                    <span class="material-symbols-outlined icon-sm text-blue-600">add_location</span>
                                    Tandai Titik
                                </span>
                                <button class="text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent" onclick="closePinModal()" type="button">
                                    <span class="material-symbols-outlined icon-sm">close</span>
                                </button>
                            </div>
                            <div class="flex-col gap-3 text-left">
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>Titik: <strong class="text-slate-800" id="modalCoords">X: 0, Y: 0</strong></span>
                                    <span class="text-blue-600 font-bold" id="modalRegioHint">Regio Posterior</span>
                                </div>
                                <div class="flex-col gap-1">
                                    <label class="text-xs font-bold text-slate-800">Gejala Klinis:</label>
                                    <select class="field-input h-9 text-sm py-1 px-2" id="modalSymptomSelect">
                                        <option value="Nyeri (Pain)|#ef4444">Nyeri (Pain) - Merah</option>
                                        <option value="Kesemutan (Tingling)|#2563eb">Kesemutan (Tingling) - Biru</option>
                                        <option value="Baal / Kebas|#f59e0b">Baal / Kebas (Numbness) - Oranye</option>
                                        <option value="Bengkak / Deformitas|#9333ea">Bengkak / Deformitas - Ungu</option>
                                    </select>
                                </div>
                                <div class="flex-col gap-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-800">Skala Nyeri (VAS 1-10):</span>
                                        <span class="text-red-500 font-bold" id="modalVasVal">7/10</span>
                                    </div>
                                    <input class="w-full cursor-pointer" max="10" min="1" oninput="document.getElementById('modalVasVal').innerText = this.value + '/10'" type="range" value="7">
                                </div>
                                <div class="flex-col gap-1">
                                    <label class="text-xs font-bold text-slate-800">Catatan Lokasi:</label>
                                    <input class="field-input h-9 text-sm py-1 px-2" id="modalRegioName" placeholder="Contoh: Tulang ekor" type="text">
                                </div>
                                <div class="flex items-center justify-end gap-2 mt-1">
                                    <button class="px-3 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold cursor-pointer border-none" onclick="closePinModal()" type="button">Batal</button>
                                    <button class="px-3 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold cursor-pointer border-none" onclick="saveNewPin()" type="button">Tandai Titik</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- TABLE OF MARKED BODY PINPOINTS -->
                <div class="flex-col gap-2 mt-2">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-bold text-slate-800">Daftar Titik Teridentifikasi</span>
                        <span class="text-xs px-2 py-1 bg-slate-100 rounded-full text-slate-600 font-bold" id="pinCountBadge">0 Titik</span>
                    </div>
                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="py-2 px-3">#</th>
                                    <th class="py-2 px-3">Regio</th>
                                    <th class="py-2 px-3">Gejala</th>
                                    <th class="py-2 px-3 text-right">VAS</th>
                                    <th class="py-2 px-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="pinsTableBody" class="text-slate-800">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- FOOTER ACTION BAR -->
    <footer class="panel m-0 px-6 py-3 flex flex-wrap items-center justify-between gap-4" style="background: #fcfcfd; border-top: 1px solid #e2e8f0;">
        <div class="flex items-center gap-2 text-slate-500 text-sm">
            <span class="material-symbols-outlined text-blue-600 icon-sm">verified</span>
            <span>Tervalidasi Digital: <strong class="text-slate-800">Prof. Dr. dr. Bambang Prijambodo, Sp.OT</strong> (SIP: 449.1/1042/IPD/436.7.2)</span>
        </div>
        <button type="button" onclick="submitAnamnesis()" class="btn-primary" style="width: auto; padding: 0.3rem 0.5rem; font-size: 0.8125rem; font-weight: 700; border-radius: 0.375rem; display: flex; justify-content: center; align-items: center; gap: 0.25rem; box-shadow: 0 1px 2px rgba(37, 99, 235, 0.1);">
            <span class="material-symbols-outlined" style="font-size: 1rem;">save</span> Simpan Pemeriksaan
        </button>
    </footer>
  </div>

<!-- CLIENT INTERACTIVE BEHAVIOR SCRIPT -->
<script>
  let pinCounter = 1;
  let tempCoords = { x: 0, y: 0 };
  let pinsData = [];

  // ZOOM & PAN STATE
  let scale = 1;
  let panning = false;
  let pointX = 0;
  let pointY = 0;
  let startX = 0;
  let startY = 0;
  let draggingPinId = null;

  const wrapper = document.getElementById('zoomWrapper');
  const container = document.getElementById('bodyCanvasContainer');

  function setTransform() {
    container.style.transform = "translate(" + pointX + "px, " + pointY + "px) scale(" + scale + ")";
  }

  // Handle Zoom (Wheel)
  wrapper.addEventListener('wheel', function(e) {
    e.preventDefault();
    const xs = (e.clientX - wrapper.getBoundingClientRect().left - pointX) / scale;
    const ys = (e.clientY - wrapper.getBoundingClientRect().top - pointY) / scale;
    const delta = (e.wheelDelta ? e.wheelDelta : -e.deltaY);
    (delta > 0) ? (scale *= 1.1) : (scale /= 1.1);
    
    if (scale < 0.5) scale = 0.5;
    if (scale > 4) scale = 4;

    pointX = e.clientX - wrapper.getBoundingClientRect().left - xs * scale;
    pointY = e.clientY - wrapper.getBoundingClientRect().top - ys * scale;
    setTransform();
  }, { passive: false });

  // Handle Pan & Click
  wrapper.addEventListener('mousedown', function(e) {
    if (e.target.closest('.group') || e.target.closest('#pinModal')) return;
    e.preventDefault();
    startX = e.clientX - pointX;
    startY = e.clientY - pointY;
    panning = true;
    container.style.cursor = 'move';
  });

  document.addEventListener('mousemove', function(e) {
    if (panning) {
      e.preventDefault();
      pointX = e.clientX - startX;
      pointY = e.clientY - startY;
      setTransform();
    } else if (draggingPinId !== null) {
      e.preventDefault();
      const rect = container.getBoundingClientRect();
      const x = (e.clientX - rect.left) / scale;
      const y = (e.clientY - rect.top) / scale;
      const pinEl = document.getElementById('pin-marker-' + draggingPinId);
      if (pinEl) {
        pinEl.style.left = x + 'px';
        pinEl.style.top = y + 'px';
      }
    }
  });

  document.addEventListener('mouseup', function(e) {
    if (panning) {
      panning = false;
      container.style.cursor = 'crosshair';
      // Click detection if barely moved
      if (Math.abs(e.clientX - startX - pointX) < 5 && Math.abs(e.clientY - startY - pointY) < 5) {
        handleBodyClick(e);
      }
    } else if (draggingPinId !== null) {
      const rect = container.getBoundingClientRect();
      const x = Math.round((e.clientX - rect.left) / scale);
      const y = Math.round((e.clientY - rect.top) / scale);
      
      const pin = pinsData.find(p => p.id === draggingPinId);
      if (pin) {
        pin.x_coord = x;
        pin.y_coord = y;
        const rowIdText = document.getElementById('coord-text-' + draggingPinId);
        if (rowIdText) rowIdText.innerText = 'X: ' + x + ', Y: ' + y;
      }
      draggingPinId = null;
    }
  });

  function startDragPin(e, id) {
    e.stopPropagation();
    e.preventDefault();
    draggingPinId = id;
  }

  // SPECIALIZATION SELECTOR EVENT HANDLER
  const specSelector = document.getElementById('specSelector');
  const musculoSection = document.getElementById('musculoSection');
  const cardioSection = document.getElementById('cardioSection');
  const specNotice = document.getElementById('specNotice');
  const specNoticeText = document.getElementById('specNoticeText');

  if (specSelector) {
    specSelector.addEventListener('change', function(e) {
      const val = e.target.value;
      if (val === 'kardiorespirasi') {
        musculoSection.classList.add('hidden');
        cardioSection.classList.remove('hidden');
        cardioSection.style.display = 'flex';
        specNoticeText.innerText = 'Modul Evaluasi Kardiorespirasi & Kapasitas Paru Aktif';
      } else if (val === 'muskuloskeletal' || val === 'umum') {
        musculoSection.classList.remove('hidden');
        cardioSection.classList.add('hidden');
        cardioSection.style.display = 'none';
        specNoticeText.innerText = 'Modul ROM & Pembengkakan Otomatis Aktif';
      } else {
        musculoSection.classList.remove('hidden');
        cardioSection.classList.add('hidden');
        cardioSection.style.display = 'none';
        specNoticeText.innerText = 'Modul Neuromuskuler Aktif';
      }
    });
  }

  // BODY MAPPING INTERACTION
  function handleBodyClick(event) {
    const rect = container.getBoundingClientRect();
    const x = Math.round((event.clientX - rect.left) / scale);
    const y = Math.round((event.clientY - rect.top) / scale);

    tempCoords = { x, y };

    const modal = document.getElementById('pinModal');
    document.getElementById('modalCoords').innerText = 'X: ' + x + ', Y: ' + y;
    
    const isAnterior = x < 180;
    document.getElementById('modalRegioHint').innerText = isAnterior ? 'Regio Anterior' : 'Regio Posterior';

    const posX = Math.min(Math.max(x, 140), 220);
    const posY = Math.min(Math.max(y, 120), 300);

    modal.style.left = posX + 'px';
    modal.style.top = posY + 'px';
    modal.classList.remove('hidden');

    document.getElementById('modalRegioName').value = isAnterior ? 'Bahu/Lengan Anterior' : 'Kolumna Vertebralis/Punggung';
    document.getElementById('modalRegioName').focus();
  }

  function closePinModal() {
    document.getElementById('pinModal').classList.add('hidden');
  }

  function saveNewPin() {
    const symRaw = document.getElementById('modalSymptomSelect').value;
    const symParts = symRaw.split('|');
    const sym = symParts[0];
    const bgClassStr = 'background-color: ' + symParts[1] + ';';
    const reg = document.getElementById('modalRegioName').value;
    const pain = document.getElementById('modalVasVal').innerText;
    if (!reg) return alert('Silakan isi regio gejala.');

    let iconText = sym;

    const pinId = pinCounter++;
    pinsData.push({
      id: pinId,
      x_coord: tempCoords.x,
      y_coord: tempCoords.y,
      keterangan: reg + ' - ' + iconText + ' (VAS ' + pain + ')'
    });

    const pinsContainer = document.getElementById('pinsContainer');
    const newPinEl = document.createElement('div');
    newPinEl.id = 'pin-marker-' + pinId;
    newPinEl.className = 'group';
    newPinEl.style.position = 'absolute';
    newPinEl.style.pointerEvents = 'auto';
    newPinEl.style.cursor = 'grab';
    newPinEl.style.transform = 'translate(-50%, -50%)';
    newPinEl.style.left = tempCoords.x + 'px';
    newPinEl.style.top = tempCoords.y + 'px';
    newPinEl.setAttribute('onmousedown', 'startDragPin(event, ' + pinId + ')');
    
    // Group tooltip hover handled in CSS, but for simplicity here we just use native title
    newPinEl.innerHTML = `<span style='position: relative; display: flex; width: 1.25rem; height: 1.25rem;' title='Titik #${pinId}: ${reg}'><span style='position: relative; display: inline-flex; border-radius: 50%; width: 1.25rem; height: 1.25rem; ${bgClassStr} color: #ffffff; font-size: 10px; align-items: center; justify-content: center; font-weight: 700; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);'>${pinId}</span></span>`;
    pinsContainer.appendChild(newPinEl);

    const tableBody = document.getElementById('pinsTableBody');
    const row = document.createElement('tr');
    row.id = 'pin-row-' + pinId;
    row.style.borderBottom = '1px solid #f1f5f9';
    row.innerHTML = `<td style='padding: 0.5rem 0.75rem; font-weight: 700;'>#${pinId}</td><td style='padding: 0.5rem 0.75rem;'><span style='font-weight: 600; display: block;'>${reg}</span><span id='coord-text-${pinId}' style='font-size: 0.6875rem; color: #64748b;'>X: ${tempCoords.x}, Y: ${tempCoords.y}</span></td><td style='padding: 0.5rem 0.75rem;'><span style='display: inline-block; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 700; color: #ffffff; ${bgClassStr}'>${iconText}</span></td><td style='padding: 0.5rem 0.75rem; text-align: right; font-weight: 700;'>${pain}</td><td style='padding: 0.5rem 0.75rem; text-align: center;'><button type='button' onclick='deleteTableRow(${pinId})' style='border: none; background: none; color: #94a3b8; cursor: pointer;'><span class='material-symbols-outlined' style='font-size: 18px;'>delete</span></button></td>`;
    tableBody.appendChild(row);

    closePinModal();
    updatePinCount();
  }

  function clearLastPin() {
    if (pinsData.length > 0) {
      const lastPin = pinsData.pop();
      deletePinUI(lastPin.id);
    }
  }

  function deleteTableRow(id) {
    pinsData = pinsData.filter(function(pin) { return pin.id !== id; });
    deletePinUI(id);
  }

  function deletePinUI(id) {
    const marker = document.getElementById('pin-marker-' + id);
    if (marker) marker.remove();
    const row = document.getElementById('pin-row-' + id);
    if (row) row.remove();
    updatePinCount();
  }

  function updatePinCount() {
    document.getElementById('pinCountBadge').innerText = pinsData.length + ' Titik Terpetakan';
  }

  function submitAnamnesis() {
    document.getElementById("body_mapping_data").value = JSON.stringify(pinsData);
    document.getElementById("anamnesisForm").submit();
  }

  document.addEventListener('DOMContentLoaded', function() {
    const initDataStr = document.getElementById("body_mapping_data").value;
    if(initDataStr && initDataStr !== '[]') {
      try {
        const initPins = JSON.parse(initDataStr);
        initPins.forEach(pin => {
          pinCounter = Math.max(pinCounter, pin.id + 1);
          pinsData.push(pin);
          
          const pinsContainer = document.getElementById('pinsContainer');
          const newPinEl = document.createElement('div');
          newPinEl.id = 'pin-marker-' + pin.id;
          newPinEl.className = 'group';
          newPinEl.style.position = 'absolute';
          newPinEl.style.pointerEvents = 'auto';
          newPinEl.style.cursor = 'grab';
          newPinEl.style.transform = 'translate(-50%, -50%)';
          newPinEl.style.left = pin.x_coord + 'px';
          newPinEl.style.top = pin.y_coord + 'px';
          newPinEl.setAttribute('onmousedown', 'startDragPin(event, ' + pin.id + ')');
          newPinEl.innerHTML = `<span style='position: relative; display: flex; width: 1.25rem; height: 1.25rem;' title='Titik #${pin.id}: ${pin.keterangan}'><span style='position: relative; display: inline-flex; border-radius: 50%; width: 1.25rem; height: 1.25rem; background-color: #ef4444; color: #ffffff; font-size: 10px; align-items: center; justify-content: center; font-weight: 700; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);'>${pin.id}</span></span>`;
          pinsContainer.appendChild(newPinEl);

          const tableBody = document.getElementById('pinsTableBody');
          const row = document.createElement('tr');
          row.id = 'pin-row-' + pin.id;
          row.style.borderBottom = '1px solid #f1f5f9';
          row.innerHTML = `<td style='padding: 0.5rem 0.75rem; font-weight: 700;'>#${pin.id}</td><td style='padding: 0.5rem 0.75rem;' colspan="3"><span style='font-size: 0.8125rem;'>${pin.keterangan}</span><br><span style='font-size: 0.6875rem; color: #64748b;'>X: ${pin.x_coord}, Y: ${pin.y_coord}</span></td><td style='padding: 0.5rem 0.75rem; text-align: center;'><button type='button' onclick='deleteTableRow(${pin.id})' style='border: none; background: none; color: #94a3b8; cursor: pointer;'><span class='material-symbols-outlined' style='font-size: 18px;'>delete</span></button></td>`;
          tableBody.appendChild(row);
        });
        updatePinCount();
      } catch(e) {}
    }
  });
</script>
</form>
<?php } ?>
