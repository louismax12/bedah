<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/../config/database.php';

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    die("Anda tidak memiliki akses ke halaman ini.");
}

$kunjungan_id = isset($_GET['kunjungan_id']) ? $_GET['kunjungan_id'] : null;
if (!$kunjungan_id) {
    die("Parameter kunjungan_id tidak valid.");
}

// Fetch Kunjungan, Pasien, and Rekam Medis data
$stmt = $pdo->prepare("
    SELECT k.no_register, k.tgl_kunjungan, k.status, k.dokter_id,
           r.rmunit as no_rm, b.nama, b.tanggallahir as tgl_lahir, b.telp as no_telp, b.alamat,
           rm.id as rekam_medis_id, rm.diagnosa_masuk, rm.keluhan_utama, 
           rm.riwayat_sekarang, rm.riwayat_dulu, rm.riwayat_keluarga, 
           rm.jenis_form, rm.detail_pemeriksaan, rm.created_at,
           d.Nama as nama_dokter
    FROM rehab_kunjungan k
    JOIN pasien.rmlink r ON k.no_rm = r.rmunit
    JOIN pasien.biodata b ON r.idbiodata = b.idbiodata
    LEFT JOIN hrd.datadasar d ON k.dokter_id = d.NIP
    LEFT JOIN rehab_rekam_medis rm ON k.no_register = rm.no_register
    WHERE k.no_register = ?
");
$stmt->execute([$kunjungan_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data || !$data['rekam_medis_id']) {
    echo "<div class='panel m-6 p-6 flex-col items-center justify-center text-center bg-slate-50 border border-slate-200 shadow-sm'>
            <span class='material-symbols-outlined text-red-500 icon-md mb-2'>error</span>
            <h3 class='panel-title-text'>Rekam Medis Tidak Ditemukan</h3>
            <p class='text-slate-500 mt-2 mb-4'>Data pemeriksaan untuk kunjungan ini belum diisi atau tidak valid.</p>
            <a href='index.php?page=dashboard' class='btn-primary-orange'>Kembali ke Dashboard</a>
          </div>";
    exit;
}

// Fetch Body Mapping Pins
$stmt_pins = $pdo->prepare("SELECT * FROM rehab_body_mapping WHERE rekam_medis_id = ?");
$stmt_pins->execute([$data['rekam_medis_id']]);
$pins = $stmt_pins->fetchAll(PDO::FETCH_ASSOC);

$birthDate = new DateTime($data['tgl_lahir']);
$today = new DateTime('today');
$age = $birthDate->diff($today)->y;
?>

<div class="flex flex-col gap-space-md mb-space-lg">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-space-xs text-on-surface-variant font-label-sm uppercase tracking-wider">
            <a href="index.php?page=dashboard" class="hover:text-primary transition-colors cursor-pointer">Dashboard</a>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-primary font-semibold">Detail Pemeriksaan & Asesmen Klinis</span>
        </div>
        <a href="views/cetak_rm.php?kunjungan_id=<?= htmlspecialchars($kunjungan_id) ?>" target="_blank" class="inline-flex items-center gap-space-xs px-space-md py-2 bg-surface-container-high text-on-surface hover:bg-surface-container-highest rounded-lg transition-colors font-label-md">
            <span class="material-symbols-outlined text-[18px]">print</span>
            Cetak Rekam Medis
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-space-xl">
    <!-- LEFT PANEL: Data Demografi & Teks Anamnesis (Col-span 2) -->
    <div class="lg:col-span-2 flex flex-col gap-space-xl">
        <!-- Patient Info Card -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="p-space-xl bg-primary-container/20 border-b border-outline-variant/30 flex flex-col sm:flex-row sm:items-center justify-between gap-space-md">
                <div class="flex items-center gap-space-md">
                    <div class="w-16 h-16 rounded-full bg-primary text-on-primary flex items-center justify-center font-display-md font-bold shadow-md">
                        <?= strtoupper(substr($data['nama'], 0, 1)) ?>
                    </div>
                    <div>
                        <h2 class="font-headline-lg text-on-surface font-bold"><?= htmlspecialchars($data['nama']) ?></h2>
                        <div class="flex items-center gap-2 text-on-surface-variant font-body-sm mt-1">
                            <span class="px-2 py-0.5 bg-surface-container rounded font-mono font-bold text-primary">REG-<?= htmlspecialchars($data['no_register']) ?></span>
                            <span><?= htmlspecialchars($data['no_telp']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-label-sm uppercase tracking-wider text-outline">Tanggal Kunjungan</p>
                    <p class="font-headline-sm text-on-surface font-semibold"><?= date('d F Y', strtotime($data['tgl_kunjungan'])) ?></p>
                    <p class="font-body-sm text-on-surface-variant mt-1">RM-<?= htmlspecialchars($data['no_rm']) ?></p>
                </div>
            </div>
            
            <div class="p-space-xl grid grid-cols-1 sm:grid-cols-3 gap-space-lg bg-surface-container-lowest">
                <div>
                    <span class="block font-label-sm uppercase tracking-wider text-outline mb-1">Usia / Tgl Lahir</span>
                    <span class="font-body-md text-on-surface font-medium"><?= $age ?> Tahun (<?= date('d/m/Y', strtotime($data['tgl_lahir'])) ?>)</span>
                </div>
                <div>
                    <span class="block font-label-sm uppercase tracking-wider text-outline mb-1">Dokter Pemeriksa</span>
                    <span class="font-body-md text-on-surface font-medium"><?= htmlspecialchars($data['nama_dokter'] ?: 'Dokter Tidak Ditemukan') ?></span>
                </div>
                <div>
                    <span class="block font-label-sm uppercase tracking-wider text-outline mb-1">Status Kunjungan</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary font-label-sm font-bold">
                        <span class="material-symbols-outlined text-[14px]">check_circle</span> Selesai
                    </span>
                </div>
            </div>
        </div>

        <!-- Anamnesis Card -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 p-space-xl">
            <h3 class="font-headline-md text-on-surface font-bold border-b border-surface-container pb-space-sm mb-space-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">description</span>
                Hasil Anamnesis & Pemeriksaan
            </h3>
            
            <div class="flex flex-col gap-space-lg">
                <div class="bg-surface-container-low rounded-xl p-space-md">
                    <span class="block font-label-sm uppercase tracking-wider text-primary font-bold mb-2">Diagnosa Masuk (Admission Diagnosis)</span>
                    <p class="font-body-md text-on-surface"><?= nl2br(htmlspecialchars($data['diagnosa_masuk'])) ?: '-' ?></p>
                </div>
                
                <div class="bg-surface-container-low rounded-xl p-space-md">
                    <span class="block font-label-sm uppercase tracking-wider text-primary font-bold mb-2">Keluhan Utama</span>
                    <p class="font-body-md text-on-surface"><?= nl2br(htmlspecialchars($data['keluhan_utama'])) ?: '-' ?></p>
                </div>
                
                <div class="bg-surface-container-low rounded-xl p-space-md">
                    <span class="block font-label-sm uppercase tracking-wider text-primary font-bold mb-2">Riwayat Penyakit Sekarang (RPS)</span>
                    <p class="font-body-md text-on-surface"><?= nl2br(htmlspecialchars($data['riwayat_sekarang'])) ?: '-' ?></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-space-lg">
                    <div class="bg-surface-container-low rounded-xl p-space-md">
                        <span class="block font-label-sm uppercase tracking-wider text-primary font-bold mb-2">Riwayat Penyakit Dahulu (RPD)</span>
                        <p class="font-body-md text-on-surface"><?= htmlspecialchars($data['riwayat_dulu']) ?: '-' ?></p>
                    </div>
                    <div class="bg-surface-container-low rounded-xl p-space-md">
                        <span class="block font-label-sm uppercase tracking-wider text-primary font-bold mb-2">Riwayat Penyakit Keluarga (RPK)</span>
                        <p class="font-body-md text-on-surface"><?= htmlspecialchars($data['riwayat_keluarga']) ?: '-' ?></p>
                    </div>
                </div>

                <div class="bg-surface-container-low rounded-xl p-space-md border border-primary/20">
                    <span class="block font-label-sm uppercase tracking-wider text-primary font-bold mb-2">Detail Pemeriksaan / Instruksi Fisioterapi</span>
                    <p class="font-body-md text-on-surface"><?= nl2br(htmlspecialchars($data['detail_pemeriksaan'])) ?: '-' ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: Visual Body Mapping (Col-span 1) -->
    <div class="lg:col-span-1 flex flex-col gap-space-xl">
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 flex flex-col h-full">
            <div class="p-space-lg border-b border-surface-container bg-surface-container-low/30">
                <h3 class="font-headline-sm text-on-surface font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">accessibility_new</span>
                    Peta Nyeri (Body Mapping)
                </h3>
            </div>
            
            <div class="p-space-md flex-1 flex flex-col">
                <!-- Visual Map -->
                <div class="relative w-full bg-surface-container-low/50 rounded-xl overflow-hidden p-space-md flex items-center justify-center min-h-[460px] select-none">
                    <div style="position: relative; width: 360px; height: 430px; display: flex; justify-content: space-around; align-items: center;" id="bodyCanvasContainer">
                        
                        <!-- ANTERIOR -->
                        <div class="flex flex-col items-center">
                            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-semibold mb-1">Tampak Depan</span>
                            <svg style="width: 130px; height: auto; stroke: currentColor; fill: none; pointer-events: none;" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 100 240" class="text-outline-variant drop-shadow-sm">
                                <ellipse cx="50" cy="22" fill="#ffffff" rx="12" ry="15"></ellipse>
                                <circle cx="46" cy="20" fill="currentColor" r="0.8"></circle>
                                <circle cx="54" cy="20" fill="currentColor" r="0.8"></circle>
                                <path d="M50 25 C48 30 52 30 50 25" stroke-width="1"></path>
                                <path d="M44 36 L43 44 L25 49 L18 88 L14 135 L20 138 L25 95 L28 65" fill="#ffffff"></path>
                                <path d="M56 36 L57 44 L75 49 L82 88 L86 135 L80 138 L75 95 L72 65" fill="#ffffff"></path>
                                <path d="M28 65 L27 105 C35 110 65 110 73 105 L72 65" fill="#ffffff"></path>
                                <path d="M35 65 C40 85 60 85 65 65" stroke-width="1.2"></path>
                                <path d="M50 132 L58 135 L62 175 L59 215 L66 235 L53 235 L48 215 L50 175 L50 132" fill="#ffffff"></path>
                                <path d="M50 132 L42 135 L38 175 L41 215 L34 235 L47 235 L52 215 L50 175 L50 132" fill="#ffffff"></path>
                            </svg>
                        </div>
                        
                        <!-- POSTERIOR -->
                        <div class="flex flex-col items-center">
                            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-semibold mb-1">Tampak Belakang</span>
                            <svg style="width: 130px; height: auto; stroke: currentColor; fill: none; pointer-events: none;" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 100 240" class="text-outline-variant drop-shadow-sm">
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
                        
                        <!-- PINS LAYER -->
                        <div class="absolute inset-0 pointer-events-none" id="pinsContainer">
                            <?php foreach ($pins as $idx => $pin): 
                                $bgClass = 'bg-error'; // Default Nyeri
                                $ket = $pin['keterangan'];
                                if (stripos($ket, 'Kesemutan') !== false) { $bgClass = 'bg-primary'; }
                                elseif (stripos($ket, 'Baal') !== false || stripos($ket, 'Kebas') !== false) { $bgClass = 'bg-[#f59e0b]'; }
                                elseif (stripos($ket, 'Bengkak') !== false || stripos($ket, 'Luka') !== false) { $bgClass = 'bg-[#9333ea]'; }
                            ?>
                                <div class="absolute pointer-events-auto group" style="left: <?= $pin['x_coord'] ?>px; top: <?= $pin['y_coord'] ?>px; transform: translate(-50%, -50%);">
                                    <span class="relative flex h-5 w-5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full <?= $bgClass ?> opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-5 w-5 <?= $bgClass ?> text-white font-label-sm text-[10px] items-center justify-center font-bold shadow-md"><?= $idx + 1 ?></span>
                                    </span>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:flex flex-col bg-inverse-surface text-inverse-on-surface px-2 py-1 rounded text-xs whitespace-nowrap z-30 shadow-lg">
                                        <span class="font-bold">Titik #<?= $idx + 1 ?></span>
                                        <span><?= htmlspecialchars($ket) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if(empty($pins)): ?>
                                <div class="absolute inset-0 flex items-center justify-center text-outline text-sm font-semibold italic bg-surface-container-low/40 rounded-xl">
                                    Tidak ada catatan Body Mapping
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Pins List -->
                <?php if(!empty($pins)): ?>
                <div class="mt-space-md border border-outline-variant/30 rounded-xl overflow-hidden bg-surface-container-lowest">
                    <table class="w-full text-left font-body-sm text-body-sm">
                        <thead class="bg-surface-container-low text-on-surface-variant">
                            <tr>
                                <th class="py-2 px-3 w-16 text-center">Titik</th>
                                <th class="py-2 px-3">Keterangan Gejala</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container">
                            <?php foreach ($pins as $idx => $pin): 
                                $bgClass = 'bg-error'; // Default Nyeri
                                $ket = $pin['keterangan'];
                                if (stripos($ket, 'Kesemutan') !== false) { $bgClass = 'bg-primary'; }
                                elseif (stripos($ket, 'Baal') !== false || stripos($ket, 'Kebas') !== false) { $bgClass = 'bg-[#f59e0b]'; }
                                elseif (stripos($ket, 'Bengkak') !== false || stripos($ket, 'Luka') !== false) { $bgClass = 'bg-[#9333ea]'; }
                            ?>
                            <tr class="hover:bg-surface-container-low/50">
                                <td class="py-2 px-3 text-center">
                                    <span class="inline-flex w-6 h-6 rounded-full <?= $bgClass ?> text-white items-center justify-center font-bold text-xs">
                                        <?= $idx + 1 ?>
                                    </span>
                                </td>
                                <td class="py-2 px-3 font-medium text-on-surface">
                                    <?= htmlspecialchars($ket) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .flex-col, .grid, .grid-cols-1, .lg\:grid-cols-3, .lg\:col-span-2, .lg\:col-span-1 {
        display: block !important;
        width: 100% !important;
    }
    .grid > div {
        page-break-inside: avoid;
        margin-bottom: 20px;
    }
    .bg-surface-container-lowest, .bg-surface-container-low, .bg-primary-container\/20 {
        background-color: transparent !important;
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    .material-symbols-outlined {
        display: none !important;
    }
    button, a {
        display: none !important;
    }
    .lg\:col-span-2, .lg\:col-span-1 {
        visibility: visible;
    }
    .lg\:col-span-2 *, .lg\:col-span-1 * {
        visibility: visible;
    }
}
</style>
