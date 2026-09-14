<?php
session_start();
require_once dirname(__FILE__) . '/../config/database.php';

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    die("Anda tidak memiliki akses ke halaman ini.");
}

$kunjungan_id = isset($_GET['kunjungan_id']) ? $_GET['kunjungan_id'] : null;
if (!$kunjungan_id) {
    die("Parameter kunjungan_id tidak valid.");
}

// Fetch Data Kunjungan, Pasien, and Rekam Medis
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
    die("Rekam Medis Tidak Ditemukan atau Belum Diisi.");
}

// Fetch Body Mapping Pins
$stmt_pins = $pdo->prepare("SELECT * FROM rehab_body_mapping WHERE rekam_medis_id = ?");
$stmt_pins->execute([$data['rekam_medis_id']]);
$pins = $stmt_pins->fetchAll(PDO::FETCH_ASSOC);

$birthDate = new DateTime($data['tgl_lahir']);
$today = new DateTime('today');
$age = $birthDate->diff($today)->y;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekam Medis - <?= htmlspecialchars($data['no_register']) ?></title>
    <link rel="stylesheet" href="../assets/css/print.css">
</head>
<body>
    <div class="container">
        <!-- KOP SURAT -->
        <div class="kop-surat">
            <div class="kop-surat-logo">
                <img src="../img/logo_rkz.png" alt="Logo RKZ" onerror="this.src='https://via.placeholder.com/70x70?text=RKZ'">
            </div>
            <div class="kop-surat-text">
                <h1>RUMAH SAKIT KATOLIK ST. VINCENTIUS A PAULO (RKZ)</h1>
                <h2>INSTALASI REHABILITASI MEDIK</h2>
                <p>Jl. Diponegoro No.51, Darmo, Kec. Wonokromo, Surabaya, Jawa Timur 60241<br>
                Telp: (031) 5677562 | Email: info@rkzsurabaya.com</p>
            </div>
        </div>
        <div class="header-line"></div>
        <div class="header-line-thin"></div>

        <!-- TITLE -->
        <div class="document-title">
            <h3>REKAM MEDIS PASIEN REHABILITASI</h3>
            <p>NO. REG: <?= htmlspecialchars($data['no_register']) ?></p>
        </div>

        <!-- INFO SECTION -->
        <div class="info-section">
            <div class="info-col">
                <table class="info-table">
                    <tr>
                        <td>No. Rekam Medis</td>
                        <td>:</td>
                        <td>RM-<?= htmlspecialchars($data['no_rm']) ?></td>
                    </tr>
                    <tr>
                        <td>Nama Pasien</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($data['nama']) ?></td>
                    </tr>
                    <tr>
                        <td>Usia / Tgl Lahir</td>
                        <td>:</td>
                        <td><?= $age ?> Th (<?= date('d/m/Y', strtotime($data['tgl_lahir'])) ?>)</td>
                    </tr>
                    <tr>
                        <td>No. Telp</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($data['no_telp']) ?></td>
                    </tr>
                </table>
            </div>
            <div class="info-col">
                <table class="info-table">
                    <tr>
                        <td>Tanggal Periksa</td>
                        <td>:</td>
                        <td><?= date('d F Y', strtotime($data['tgl_kunjungan'])) ?></td>
                    </tr>
                    <tr>
                        <td>Dokter DPJP</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($data['nama_dokter'] ?: 'Dokter Tidak Ditemukan') ?></td>
                    </tr>
                    <tr>
                        <td>Jenis Form Asesmen</td>
                        <td>:</td>
                        <td style="text-transform: uppercase;"><?= htmlspecialchars($data['jenis_form']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- HASIL ANAMNESIS -->
        <div class="section-title">A. HASIL ANAMNESIS & PEMERIKSAAN</div>
        <div class="anamnesis-grid">
            <table style="width: 100%; table-layout: fixed; border-spacing: 5px;">
                <tr>
                    <td style="vertical-align: top; width: 50%;">
                        <div class="anamnesis-item">
                            <div class="anamnesis-label">Diagnosa Masuk</div>
                            <div class="anamnesis-value"><?= nl2br(htmlspecialchars($data['diagnosa_masuk'] ?: '-')) ?></div>
                        </div>
                    </td>
                    <td style="vertical-align: top; width: 50%;">
                        <div class="anamnesis-item">
                            <div class="anamnesis-label">Keluhan Utama</div>
                            <div class="anamnesis-value"><?= nl2br(htmlspecialchars($data['keluhan_utama'] ?: '-')) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">
                        <div class="anamnesis-item">
                            <div class="anamnesis-label">Riwayat Penyakit Sekarang (RPS)</div>
                            <div class="anamnesis-value"><?= nl2br(htmlspecialchars($data['riwayat_sekarang'] ?: '-')) ?></div>
                        </div>
                    </td>
                    <td style="vertical-align: top;">
                        <div class="anamnesis-item">
                            <div class="anamnesis-label">Detail Pemeriksaan / Instruksi Fisioterapi</div>
                            <div class="anamnesis-value"><?= nl2br(htmlspecialchars($data['detail_pemeriksaan'] ?: '-')) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">
                        <div class="anamnesis-item">
                            <div class="anamnesis-label">Riwayat Penyakit Dahulu (RPD)</div>
                            <div class="anamnesis-value"><?= nl2br(htmlspecialchars($data['riwayat_dulu'] ?: '-')) ?></div>
                        </div>
                    </td>
                    <td style="vertical-align: top;">
                        <div class="anamnesis-item">
                            <div class="anamnesis-label">Riwayat Penyakit Keluarga (RPK)</div>
                            <div class="anamnesis-value"><?= nl2br(htmlspecialchars($data['riwayat_keluarga'] ?: '-')) ?></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- BODY MAPPING -->
        <div class="section-title" style="page-break-before: auto;">B. PETA NYERI (BODY MAPPING)</div>
        
        <div class="canvas-container">
            <!-- ANTERIOR -->
            <div class="canvas-side">
                <div class="canvas-title">Tampak Depan</div>
                <svg class="svg-canvas" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 100 240">
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
            <div class="canvas-side">
                <div class="canvas-title">Tampak Belakang</div>
                <svg class="svg-canvas" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 100 240">
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
            <?php foreach ($pins as $idx => $pin): 
                $bgColor = '#ef4444'; // Default Nyeri (Merah)
                $ket = $pin['keterangan'];
                if (stripos($ket, 'Kesemutan') !== false) { $bgColor = '#3b82f6'; }
                elseif (stripos($ket, 'Baal') !== false || stripos($ket, 'Kebas') !== false) { $bgColor = '#f59e0b'; }
                elseif (stripos($ket, 'Bengkak') !== false || stripos($ket, 'Luka') !== false) { $bgColor = '#9333ea'; }
                
                // Menyesuaikan posisi dari original canvas.
                // Pada layar detail_rm, canvas diatur dalam box berukuran 320/360x430, yang ditengahkan.
                // Disini canvas-inner adalah 100% width. x_coord yang dicatat relatif pada kiri atas canvas container.
            ?>
                <div class="pin" style="left: <?= $pin['x_coord'] ?>px; top: <?= $pin['y_coord'] ?>px; background-color: <?= $bgColor ?>;">
                    <?= $idx + 1 ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if(!empty($pins)): ?>
        <table class="pins-table">
            <thead>
                <tr>
                    <th>Titik</th>
                    <th>Keterangan Gejala & Skala VAS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pins as $idx => $pin): ?>
                <tr>
                    <td><strong>#<?= $idx + 1 ?></strong></td>
                    <td><?= htmlspecialchars($pin['keterangan']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="font-size: 9pt; color: #64748b; font-style: italic;">Tidak ada titik nyeri yang dipetakan pada rekam medis ini.</p>
        <?php endif; ?>

        <div class="print-footer">
            Dokumen ini dicetak pada <?= date('d/m/Y H:i') ?> oleh Sistem Informasi Manajemen Rekam Medis (SIMRM) RKZ Surabaya.
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
