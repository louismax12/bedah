<?php
// c:\Users\louis\Documents\rehab\rehab_rkz\views\dashboard_dokter.php
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php?page=login';</script>";
    exit;
}
$today = isset($_GET['tgl']) ? $_GET['tgl'] : date('Y-m-d');
$tgl_param = "&tgl=" . urlencode($today);

// Pagination settings
$limit = 5;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page_num < 1) $page_num = 1;
$offset = ($page_num - 1) * $limit;

// Total antrean (Semua Dokter)
$stmt_kunj = $pdo->prepare("SELECT COUNT(*) FROM rehab_kunjungan WHERE tgl_kunjungan = ?");
$stmt_kunj->execute(array($today));
$kunjungan_hari_ini = $stmt_kunj->fetchColumn();

// Menunggu
$stmt_wait = $pdo->prepare("SELECT COUNT(*) FROM rehab_kunjungan WHERE tgl_kunjungan = ? AND status != 'selesai'");
$stmt_wait->execute(array($today));
$menunggu_hari_ini = $stmt_wait->fetchColumn();

$total_pages = ceil($kunjungan_hari_ini / $limit);

$stmt = $pdo->prepare("
    SELECT k.*, b.nama, k.no_rm, d.Nama as nama_dokter 
    FROM rehab_kunjungan k 
    JOIN pasien.rmlink r ON k.no_rm = r.rmunit 
    JOIN pasien.biodata b ON r.idbiodata = b.idbiodata 
    LEFT JOIN hrd.datadasar d ON k.dokter_id = d.NIP
    WHERE k.tgl_kunjungan = ? 
    ORDER BY k.no_register ASC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute(array($today));

$antrian = $stmt->fetchAll();
?>

<!-- Top Welcome & Doctor Clinical Context Banner -->
<div class="welcome-banner" style="background: linear-gradient(to right, #059669, #10b981, #34d399); margin-bottom: 0.25rem !important; padding: 1rem 1.5rem !important; gap: 0.5rem !important;">
    <div class="welcome-decor-1"></div>
    <div class="welcome-decor-2"></div>
    
    <div class="welcome-content">
        <div class="welcome-badge">
            <i data-lucide="sparkles" style="width: 0.875rem; height: 0.875rem; color: #fde047;"></i>
            <span>Poli Rehab Medik</span>
        </div>
        <h2 class="welcome-title">
            <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>
        </h2>
        <p class="welcome-desc">
            Dashboard Antrean Pemeriksaan Fisik & Asesmen Klinis
        </p>
    </div>
</div>

<!-- KPI Metric Summary Bar -->
<div class="stat-grid" style="margin-bottom: 0.25rem !important; gap: 0.5rem !important;">
  <!-- Stat 1: Menunggu -->
  <div class="stat-card" style="padding: 0.75rem 1rem !important;">
    <div class="stat-header">
      <span class="stat-title">Total Antrean</span>
      <div class="stat-icon indigo">
        <i data-lucide="users"></i>
      </div>
    </div>
    <div class="stat-body">
      <span class="stat-value"><?= $kunjungan_hari_ini ?></span>
    </div>
  </div>
  <!-- Stat 2: Sedang Konsultasi -->
  <div class="stat-card" style="padding: 0.75rem 1rem !important;">
    <div class="stat-header">
      <span class="stat-title">Menunggu</span>
      <div class="stat-icon amber">
        <i data-lucide="hourglass"></i>
      </div>
    </div>
    <div class="stat-body">
      <span class="stat-value"><?= $menunggu_hari_ini ?></span>
    </div>
  </div>
</div>

<!-- Filter & Search Toolbar Card -->
<div class="panel" style="margin-bottom: 0.5rem !important;">
  <div class="panel-body" style="padding: 0.5rem 1rem !important; display: flex; align-items: center; gap: 1rem; justify-content: space-between;">
    <div class="search-bar" style="flex: 1;">
      <i data-lucide="search" class="search-icon"></i>
      <input type="text" class="search-input" placeholder="Cari nama pasien, No. RM..." />
    </div>
    <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 0.5rem; margin: 0;">
        <input type="hidden" name="page" value="dashboard">
        <label style="font-size: 0.75rem; font-weight: 600; color: #64748b; white-space: nowrap;">Tgl Antrean:</label>
        <input type="date" name="tgl" value="<?= htmlspecialchars($today) ?>" class="form-input" style="padding: 0.375rem 0.625rem; font-size: 0.8125rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; width: auto;" onchange="this.form.submit()">
    </form>
  </div>
</div>

<!-- Patient Queue Cards Stack -->
<div class="queue-list" id="queue-container" style="gap: 0.25rem !important;">
  <?php if(count($antrian) > 0): foreach($antrian as $idx => $row): ?>
  <div class="queue-item" style="padding: 0.75rem 1rem !important;">
    <div class="queue-info-wrap">
      <div class="queue-number-box">
        <span class="queue-number">#<?= $offset + $idx + 1 ?></span>
      </div>
      <div class="queue-details">
        <div class="queue-meta">
          <span class="queue-rm">REG-<?= htmlspecialchars($row['no_register']) ?></span>
          <span class="queue-type-badge">Dr. <?= htmlspecialchars(str_replace('dr. ', '', isset($row['nama_dokter']) ? $row['nama_dokter'] : '')) ?></span>
          <?php if ($row['status'] === 'selesai'): ?>
            <span class="queue-type-badge" style="background-color: #dcfce7; color: #166534;">Selesai</span>
          <?php endif; ?>
        </div>
        <div class="queue-patient-name" style="white-space: normal !important; line-height: 1.25 !important; word-break: break-word !important; font-size: 0.9rem !important;">
          <?= htmlspecialchars($row['nama']) ?>
        </div>
      </div>
    </div>
    <!-- Right Actions -->
    <div class="queue-actions-wrap">
      <div class="queue-status-bar">
        <?php if ($row['status'] === 'selesai'): ?>
          <span class="badge-status success">
            <span class="dot"></span> Selesai
          </span>
        <?php else: ?>
          <span class="badge-status waiting">
            <span class="dot"></span> Menunggu
          </span>
        <?php endif; ?>
      </div>
      <div class="queue-action-buttons">
        <?php if ($row['status'] === 'selesai'): ?>
          <a href="index.php?page=detail_rm&kunjungan_id=<?= $row['no_register'] ?>" class="btn-sm btn-outline">
            <i data-lucide="visibility"></i>
            <span>Lihat Detail</span>
          </a>
          <a href="index.php?page=anamnesis&kunjungan_id=<?= $row['no_register'] ?>" class="btn-sm btn-outline-primary" title="Edit">
            <i data-lucide="edit"></i>
            <span>Edit</span>
          </a>
          <a href="index.php?page=kunjungan&action=delete&id=<?= $row['no_register'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus kunjungan beserta rekam medis ini dari sistem?');" class="btn-sm btn-outline-danger" title="Hapus">
            <i data-lucide="trash-2"></i>
            <span>Hapus</span>
          </a>
        <?php else: ?>
          <a href="index.php?page=anamnesis&kunjungan_id=<?= $row['no_register'] ?>" class="btn-primary" style="margin-top: 0; padding: 0.5rem 1rem;">
            <i data-lucide="stethoscope"></i>
            <span>Mulai Periksa</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; else: ?>
    <div class="panel-body empty-state" style="border-radius: 0.75rem; background: #fff; border: 1px solid rgba(226, 232, 240, 0.8);">
        <i data-lucide="calendar-x" class="empty-icon"></i>
        <p>Belum ada antrean saat ini.</p>
    </div>
  <?php endif; ?>
</div>

<!-- Pagination Controls -->
<?php if (isset($total_pages) && $total_pages > 1): ?>
<div class="pagination-container">
    <div class="pagination-info">
        Menampilkan halaman <?= $page_num ?> dari <?= $total_pages ?> (Total <?= $kunjungan_hari_ini ?> antrean)
    </div>
    <div class="pagination-controls">
        <?php if ($page_num > 1): ?>
            <a href="index.php?page=dashboard<?= $tgl_param ?>&p=<?= $page_num - 1 ?>" class="page-btn">Sebelumnya</a>
        <?php else: ?>
            <span class="page-btn disabled">Sebelumnya</span>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?page=dashboard<?= $tgl_param ?>&p=<?= $i ?>" class="page-btn <?= $i === $page_num ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($page_num < $total_pages): ?>
            <a href="index.php?page=dashboard<?= $tgl_param ?>&p=<?= $page_num + 1 ?>" class="page-btn">Berikutnya</a>
        <?php else: ?>
            <span class="page-btn disabled">Berikutnya</span>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
