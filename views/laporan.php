<?php
// c:\Users\louis\Documents\rehab\rehab_rkz\views\laporan.php
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php?page=login';</script>";
    exit;
}

// Ambil bulan ini
$start_date = date('Y-m-01');
$end_date = date('Y-m-t');

if (isset($_GET['start']) && isset($_GET['end'])) {
    $start_date = $_GET['start'];
    $end_date = $_GET['end'];
}

$stmt_total = $pdo->prepare("SELECT COUNT(*) FROM rehab_kunjungan WHERE tgl_kunjungan BETWEEN ? AND ?");
$stmt_total->execute(array($start_date, $end_date));
$total_kunjungan = $stmt_total->fetchColumn();

// Distribusi Status Kunjungan
$stmt_status = $pdo->prepare("SELECT status, COUNT(*) as jumlah FROM rehab_kunjungan WHERE tgl_kunjungan BETWEEN ? AND ? GROUP BY status");
$stmt_status->execute(array($start_date, $end_date));
$distribusi_status = $stmt_status->fetchAll();

// Kunjungan per hari
$stmt_harian = $pdo->prepare("SELECT tgl_kunjungan, COUNT(*) as jumlah FROM rehab_kunjungan WHERE tgl_kunjungan BETWEEN ? AND ? GROUP BY tgl_kunjungan ORDER BY tgl_kunjungan ASC");
$stmt_harian->execute(array($start_date, $end_date));
$tren_kunjungan = $stmt_harian->fetchAll();

// History / List Pasien
$stmt_history = $pdo->prepare("
    SELECT k.no_register, k.tgl_kunjungan, k.status, k.no_rm, b.nama, d.Nama as nama_dokter
    FROM rehab_kunjungan k
    JOIN pasien.rmlink r ON k.no_rm = r.rmunit
    JOIN pasien.biodata b ON r.idbiodata = b.idbiodata
    LEFT JOIN hrd.datadasar d ON k.dokter_id = d.NIP
    WHERE k.tgl_kunjungan BETWEEN ? AND ?
    ORDER BY k.tgl_kunjungan DESC
");
$stmt_history->execute(array($start_date, $end_date));
$history_list = $stmt_history->fetchAll();
?>

<div style="display: flex; flex-direction: column; gap: 0.5rem; padding-right: 15px;padding-left: 15px;">
  <!-- Top Banner -->
  <div class="page-hero" style="margin-bottom: -1;">
    <div class="page-hero-decor" style="bottom: -4rem; top: auto; background-color: rgba(37, 99, 235, 0.05);"></div>
    <div class="page-hero-content" style="display: flex; flex-direction: row; flex-wrap: wrap; align-items: center; justify-content: space-between; width: 100%; padding-right: 15px;padding-left: 15px;">
      <div style="display: flex; flex-direction: column; gap: 0.25rem; padding-right: 15px;padding-left: 15px;">
        <h2 style="font-size: 1.875rem; font-weight: 700; color: #1e293b; margin: 0; letter-spacing: -0.025em;"></h2>
        <p style="font-size: 0.875rem; color: #64748b; margin: 0;"></p>
      </div>
      
      <form method="GET" action="index.php" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
          <input type="hidden" name="page" value="laporan">
                  <div class="search-bar" style="width: 16rem; margin: 0;">
            <i data-lucide="search" class="search-icon"></i>
            <input type="text" placeholder="Pencarian cepat..." class="search-input" style="background-color: #f8fafc;border: 1px solid #17191c;height: 2.25rem;font-size: 0.75rem;border-radius: 9999px;">
        </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
              <input type="date" name="start" value="<?= htmlspecialchars($start_date) ?>" style="padding: 0.5rem 1rem; background-color: #f8fafc; border: 1px solid transparent; border-radius: 0.5rem; font-size: 0.875rem; color: #1e293b; outline: none;">
              <span style="font-size: 0.875rem; color: #64748b;">s/d</span>
              <input type="date" name="end" value="<?= htmlspecialchars($end_date) ?>" style="padding: 0.5rem 1rem; background-color: #f8fafc; border: 1px solid transparent; border-radius: 0.5rem; font-size: 0.875rem; color: #1e293b; outline: none;">
          </div>
          <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1.25rem; background-color: #2563eb; color: #ffffff; font-size: 0.875rem; font-weight: 600; border-radius: 0.5rem; border: none; cursor: pointer; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);">
              <i data-lucide="filter" style="width: 1rem; height: 1rem;"></i> Terapkan Filter
          </button>
      </form>
    </div>
  </div>

  <!-- KPI Cards -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
    <div style="background-color: #ffffff; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #2563eb;">
      <div style="display: flex; flex-direction: column;">
        <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Total Kunjungan</span>
        <div style="display: flex; align-items: baseline; gap: 0.5rem; margin-top: 0.25rem;">
          <span style="font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1;"><?= $total_kunjungan ?></span>
        </div>
      </div>
      <div style="width: 3rem; height: 3rem; border-radius: 0.75rem; background-color: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center;">
        <i data-lucide="users" style="width: 1.5rem; height: 1.5rem;"></i>
      </div>
    </div>
    <div style="background-color: #ffffff; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #10b981;">
      <div style="display: flex; flex-direction: column;">
        <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Periode Aktif</span>
        <div style="display: flex; align-items: baseline; gap: 0.5rem; margin-top: 0.25rem;">
          <span style="font-size: 1.5rem; font-weight: 700; color: #1e293b; line-height: 1;"><?= date('M Y', strtotime($start_date)) ?></span>
        </div>
      </div>
      <div style="width: 3rem; height: 3rem; border-radius: 0.75rem; background-color: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center;">
        <i data-lucide="calendar" style="width: 1.5rem; height: 1.5rem;"></i>
      </div>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
    <!-- Chart / Data 1 -->
    <div style="background-color: #ffffff; border-radius: 0.75rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); border: 1px solid #f1f5f9; overflow: hidden;">
      <div style="padding: 1.25rem; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; background-color: #fcfcfd;">
          <h3 style="font-size: 0.875rem; font-weight: 700; color: #1e293b; margin: 0;">Tren Kunjungan Harian</h3>
      </div>
      <div style="padding: 1.25rem;">
        <table style="width: 100%; text-align: left; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="color: #64748b; text-transform: uppercase; font-size: 0.6875rem; font-weight: 700; border-bottom: 1px solid #f8fafc;">
                    <th style="padding: 0.75rem 1rem;">Tanggal</th>
                    <th style="padding: 0.75rem 1rem; text-align: right;">Jumlah Kunjungan</th>
                </tr>
            </thead>
            <tbody style="color: #1e293b;">
                <?php if (count($tren_kunjungan) > 0): foreach ($tren_kunjungan as $tk): ?>
                <tr style="transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 0.75rem 1rem;"><?= date('d M Y', strtotime($tk['tgl_kunjungan'])) ?></td>
                    <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: #2563eb;"><?= $tk['jumlah'] ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="2" style="padding: 1.5rem; text-align: center; color: #94a3b8;">Belum ada data pada periode ini.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
    </div>

    <!-- Chart / Data 2 -->
    <div style="background-color: #ffffff; border-radius: 0.75rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); border: 1px solid #f1f5f9; overflow: hidden;">
      <div style="padding: 1.25rem; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; background-color: #fcfcfd;">
          <h3 style="font-size: 0.875rem; font-weight: 700; color: #1e293b; margin: 0;">Distribusi Status</h3>
      </div>
      <div style="padding: 1.25rem;">
        <table style="width: 100%; text-align: left; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="color: #64748b; text-transform: uppercase; font-size: 0.6875rem; font-weight: 700; border-bottom: 1px solid #f8fafc;">
                    <th style="padding: 0.75rem 1rem;">Status</th>
                    <th style="padding: 0.75rem 1rem; text-align: right;">Jumlah</th>
                    <th style="padding: 0.75rem 1rem; width: 33%;">Persentase</th>
                </tr>
            </thead>
            <tbody style="color: #1e293b;">
                <?php if (count($distribusi_status) > 0): foreach ($distribusi_status as $ds): 
                    $pct = ($total_kunjungan > 0) ? round(($ds['jumlah'] / $total_kunjungan) * 100) : 0;
                ?>
                <tr style="transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 0.75rem 1rem; font-weight: 700; text-transform: uppercase; font-size: 0.75rem;"><?= htmlspecialchars($ds['status']) ?></td>
                    <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700;"><?= $ds['jumlah'] ?></td>
                    <td style="padding: 0.75rem 1rem;">
                        <div style="width: 100%; background-color: #f1f5f9; border-radius: 9999px; height: 0.375rem;">
                            <div style="background-color: #2563eb; height: 0.375rem; border-radius: 9999px; width: <?= $pct ?>%"></div>
                        </div>
                        <span style="font-size: 0.625rem; color: #64748b; margin-top: 0.25rem; display: block;"><?= $pct ?>%</span>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="3" style="padding: 1.5rem; text-align: center; color: #94a3b8;">Belum ada data pada periode ini.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- History Kunjungan Table -->
  <div style="background-color: #ffffff; border-radius: 0.75rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); border: 1px solid #f1f5f9; overflow: hidden;">
      <div style="padding: 1.25rem; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; background-color: #fcfcfd;">
          <h3 style="font-size: 0.875rem; font-weight: 700; color: #1e293b; margin: 0;">Rincian Data Kunjungan</h3>
      </div>
      <div style="overflow-x: auto;">
        <table style="width: 100%; text-align: left; border-collapse: collapse; font-size: 0.875rem; min-width: 600px;">
            <thead>
                <tr style="color: #64748b; text-transform: uppercase; font-size: 0.6875rem; font-weight: 700; border-bottom: 1px solid #f8fafc;">
                    <th style="padding: 1rem;">Tanggal</th>
                    <th style="padding: 1rem;">No. Register</th>
                    <th style="padding: 1rem;">Nama Pasien</th>
                    <th style="padding: 1rem;">No. RM</th>
                    <th style="padding: 1rem;">Dokter DPJP</th>
                    <th style="padding: 1rem; text-align: right;">Status</th>
                    <th style="padding: 1rem; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody style="color: #1e293b;">
                <?php if (count($history_list) > 0): foreach ($history_list as $h): ?>
                <tr style="transition: background-color 0.2s; border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 1rem;"><?= date('d/m/Y', strtotime($h['tgl_kunjungan'])) ?></td>
                    <td style="padding: 1rem; font-family: monospace; color: #2563eb; font-weight: 600;">REG-<?= htmlspecialchars($h['no_register']) ?></td>
                    <td style="padding: 1rem; font-weight: 600;"><?= htmlspecialchars($h['nama']) ?></td>
                    <td style="padding: 1rem;">RM-<?= htmlspecialchars($h['no_rm']) ?></td>
                    <td style="padding: 1rem;"><?= htmlspecialchars(str_replace('dr. ', '', isset($h['nama_dokter']) ? $h['nama_dokter'] : '')) ?></td>
                    <td style="padding: 1rem; text-align: right;">
                        <?php if ($h['status'] === 'selesai'): ?>
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; border-radius: 9999px; background-color: #ecfdf5; color: #059669; font-size: 0.6875rem; font-weight: 700;">Selesai</span>
                        <?php else: ?>
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; border-radius: 9999px; background-color: #fffbeb; color: #d97706; font-size: 0.6875rem; font-weight: 700;">Menunggu</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem; text-align: center;">
                        <a href="index.php?page=detail_rm&kunjungan_id=<?= $h['no_register'] ?>" style="display: inline-flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 50%; background-color: #eff6ff; color: #2563eb; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#dbeafe'" onmouseout="this.style.backgroundColor='#eff6ff'" title="Lihat Detail">
                            <span class="material-symbols-outlined" style="font-size: 1.125rem;">visibility</span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="7" style="padding: 2rem; text-align: center; color: #94a3b8;">Tidak ada catatan kunjungan pada rentang tanggal ini.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
  </div>
</div>
