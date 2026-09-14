<?php
// c:\Users\louis\Documents\rehab\rehab_rkz\views\pasien.php
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'dokter'])) {
    echo "<script>window.location.href='index.php?page=login';</script>";
    exit;
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['no_rm'])) {
    try {
        $stmt_del = $pdo->prepare("DELETE FROM rehab_pasien WHERE no_rm = ?");
        $stmt_del->execute(array($_GET['no_rm']));
        $success = "Data pasien beserta seluruh rekam medis terkait berhasil dihapus.";
    } catch(Exception $e) {
        $error = "Gagal menghapus data: " . $e->getMessage();
    }
}

// Handle Insert & Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['old_no_rm']) && !empty($_POST['old_no_rm'])) {
            // Update
            $stmt = $pdo->prepare("UPDATE rehab_pasien SET no_rm = ?, nama = ?, tgl_lahir = ?, alamat = ?, no_telp = ? WHERE no_rm = ?");
            $stmt->execute(array($_POST['no_rm'], $_POST['nama'], $_POST['tgl_lahir'], $_POST['alamat'], $_POST['no_telp'], $_POST['old_no_rm']));
            $success = "Data pasien berhasil diperbarui.";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO rehab_pasien (no_rm, nama, tgl_lahir, alamat, no_telp) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(array($_POST['no_rm'], $_POST['nama'], $_POST['tgl_lahir'], $_POST['alamat'], $_POST['no_telp']));
            $success = "Data pasien berhasil ditambahkan.";
        }
    } catch(Exception $e) {
        $error = "Gagal menyimpan data: " . $e->getMessage();
    }
}

// Fetch Poliumum for Dropdown
$opt_pasien = [];
try {
    $stmt_opt = $pdo->query("SELECT 
        p.fnoreg, 
        p.frmno, 
        b.nama AS fname, 
        b.tanggallahir AS ftgllahir, 
        CASE WHEN b.telphp IS NOT NULL AND b.telphp != '' AND b.telphp != '-' THEN b.telphp ELSE b.telp END AS fnotelp,
        b.alamat AS falamat
    FROM (
        SELECT fnoreg, frmno 
        FROM dbold.poliumumupcust 
        ORDER BY fdate_in DESC 
        LIMIT 500
    ) p
    JOIN pasien.rmlink r ON p.frmno = r.rmunit
    JOIN pasien.biodata b ON r.idbiodata = b.idbiodata");
    
    $raw_pasien = $stmt_opt->fetchAll();
    
    // Filter duplicates via PHP to avoid expensive SQL GROUP BY
    $seen = [];
    foreach ($raw_pasien as $row) {
        if (!isset($seen[$row['frmno']])) {
            $seen[$row['frmno']] = true;
            $opt_pasien[] = $row;
        }
    }
    
} catch(Exception $e) {
    // Ignore if table doesn't exist yet
}

// Handle Fetch for Edit
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['no_rm'])) {
    $stmt_edit = $pdo->prepare("SELECT * FROM rehab_pasien WHERE no_rm = ?");
    $stmt_edit->execute(array($_GET['no_rm']));
    $edit_data = $stmt_edit->fetch();
}

?>
<!-- Modal Preview Pasien -->
<div id="modal-preview" class="modal-backdrop hidden">
    <div class="modal-content">
        <div style="background-color: #eff6ff; padding: 1.5rem; text-align: center; position: relative;">
            <button onclick="document.getElementById('modal-preview').classList.add('hidden')" style="position: absolute; top: 1rem; right: 1rem; color: #64748b; background: none; border: none; cursor: pointer;">
                <i data-lucide="x"></i>
            </button>
            <div id="preview-initials" style="width: 5rem; height: 5rem; background-color: #2563eb; color: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; margin: 0 auto 1rem;">
                A
            </div>
            <h2 id="preview-nama" style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">Nama Pasien</h2>
            <div style="margin-top: 0.5rem;">
                <span style="background-color: rgba(37, 99, 235, 0.1); color: #2563eb; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 700; letter-spacing: 0.05em;">
                    No REG: <span id="preview-norm">0000</span>
                </span>
            </div>
        </div>
        
        <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; gap: 0.75rem; color: #475569;">
                <i data-lucide="calendar" style="color: #2563eb; flex-shrink: 0;"></i>
                <div>
                    <p style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin: 0;">Tgl Lahir / Usia</p>
                    <p style="font-weight: 600; color: #1e293b; margin: 0;"><span id="preview-tgllahir"></span> (<span id="preview-usia"></span> Tahun)</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 0.75rem; color: #475569;">
                <i data-lucide="phone" style="color: #2563eb; flex-shrink: 0;"></i>
                <div>
                    <p style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin: 0;">Nomor Kontak</p>
                    <p id="preview-telp" style="font-weight: 600; color: #1e293b; margin: 0;"></p>
                </div>
            </div>
            
            <div style="display: flex; gap: 0.75rem; color: #475569;">
                <i data-lucide="map-pin" style="color: #2563eb; flex-shrink: 0;"></i>
                <div>
                    <p style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin: 0;">Alamat Domisili</p>
                    <p id="preview-alamat" style="font-weight: 600; color: #1e293b; margin: 0;"></p>
                </div>
            </div>
        </div>
        
        <div style="padding: 1rem; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right;">
            <button onclick="document.getElementById('modal-preview').classList.add('hidden')" style="padding: 0.5rem 1.5rem; background-color: #e2e8f0; color: #475569; border-radius: 0.5rem; font-weight: 600; border: none; cursor: pointer;">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
function previewPasien(no_rm, nama, tgl_lahir, usia, telp, alamat) {
    document.getElementById('preview-norm').textContent = no_rm;
    document.getElementById('preview-nama').textContent = nama;
    
    const nameParts = nama.split(' ');
    let initials = nameParts[0].charAt(0);
    if(nameParts.length > 1) {
        initials += nameParts[1].charAt(0);
    }
    document.getElementById('preview-initials').textContent = initials.toUpperCase();
    
    const d = new Date(tgl_lahir);
    const formattedDate = d.getDate().toString().padStart(2, '0') + '/' + 
                          (d.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                          d.getFullYear();
                          
    document.getElementById('preview-tgllahir').textContent = formattedDate;
    document.getElementById('preview-usia').textContent = usia;
    document.getElementById('preview-telp').textContent = telp;
    document.getElementById('preview-alamat').textContent = alamat;
    
    document.getElementById('modal-preview').classList.remove('hidden');
}
</script>

<?php if(isset($success)): ?>
    <div style="background-color: #ecfdf5; color: #047857; padding: 1rem; border-radius: 0.75rem; border: 1px solid #a7f3d0; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 600;">
        <i data-lucide="check-circle" style="width: 1.25rem; height: 1.25rem;"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div style="background-color: #fef2f2; color: #b91c1c; padding: 1rem; border-radius: 0.75rem; border: 1px solid #fecaca; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 600;">
        <i data-lucide="alert-circle" style="width: 1.25rem; height: 1.25rem;"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<!-- Form Registrasi / Edit -->
<div id="form-pasien" class="panel <?= $edit_data ? '' : 'hidden' ?>">
    <div class="panel-header" style="display: flex; align-items: center; gap: 1rem; background-color: #f8fafc; margin-bottom: -0.625rem; position: relative; z-index: 0;">
        <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; <?= $edit_data ? 'background-color: #fef3c7; color: #d97706;' : 'background-color: #eff6ff; color: #2563eb;' ?>">
            <i data-lucide="<?= $edit_data ? 'edit-2' : 'user-plus' ?>"></i>
        </div>
        <div>
            <h3 class="panel-title"><?= $edit_data ? 'Formulir Edit Pasien' : 'Formulir Registrasi Pasien Baru' ?></h3>
            <p class="panel-desc">Pastikan data sesuai identitas KTP/KK.</p>
        </div>
    </div>
    
    <div class="panel-body" style="position: relative; z-index: 10;">
        <form action="index.php?page=pasien" method="POST" style="display: flex; flex-direction: column; gap: 0.75rem;">
            <?php if($edit_data): ?>
                <input type="hidden" name="old_no_rm" value="<?= htmlspecialchars($edit_data['no_rm']) ?>">
            <?php endif; ?>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nomor Rekam Medis *</label>
                    <select name="no_rm" class="form-input" required>
                        <option value="">-- Pilih No Rekam Medis / Noreg --</option>
                        <?php foreach($opt_pasien as $opt): ?>
                            <option value="<?= htmlspecialchars($opt['fnoreg']) ?>" <?= ($edit_data && $edit_data['no_rm'] == $opt['fnoreg']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($opt['fnoreg']) ?> - <?= htmlspecialchars($opt['frmno']) ?> - <?= htmlspecialchars($opt['fname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group form-col-2">
                    <label class="form-label">Nama Lengkap Pasien *</label>
                    <input type="text" name="nama" value="<?= $edit_data ? htmlspecialchars($edit_data['nama']) : '' ?>" class="form-input" required placeholder="Sesuai KTP/Identitas resmi">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tanggal Lahir *</label>
                    <input type="date" name="tgl_lahir" value="<?= $edit_data ? htmlspecialchars($edit_data['tgl_lahir']) : '' ?>" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">No. HP / WhatsApp *</label>
                    <input type="tel" name="no_telp" value="<?= $edit_data ? htmlspecialchars($edit_data['no_telp']) : '' ?>" class="form-input" required placeholder="0812-XXXX-XXXX">
                </div>
                
                <div class="form-group form-col-3">
                    <label class="form-label">Alamat Domisili *</label>
                    <textarea name="alamat" rows="2" class="form-input" required placeholder="Alamat lengkap tempat tinggal sekarang"><?= $edit_data ? htmlspecialchars($edit_data['alamat']) : '' ?></textarea>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <a href="index.php?page=pasien" onclick="document.getElementById('form-pasien').classList.add('hidden'); if(event.cancelable) event.preventDefault();" style="padding: 0.625rem 1rem; color: #475569; font-weight: 600; text-decoration: none; border-radius: 0.5rem; transition: background-color 0.2s;">
                    Batal
                </a>
                <button type="submit" class="btn-primary" style="margin-top: 0; width: auto; <?= $edit_data ? 'background-color: #d97706;' : '' ?>">
                    <i data-lucide="save"></i>
                    <span><?= $edit_data ? 'Simpan Perubahan' : 'Simpan Pasien' ?></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const optPasienData = <?= json_encode($opt_pasien) ?>;
    const selectRm = document.querySelector('select[name="no_rm"]');
    
    if (selectRm) {
        selectRm.addEventListener('change', function() {
            const selectedNoreg = this.value;
            const patient = optPasienData.find(p => p.fnoreg === selectedNoreg);
            
            if (patient) {
                const namaInput = document.querySelector('input[name="nama"]');
                const tglLahirInput = document.querySelector('input[name="tgl_lahir"]');
                const noTelpInput = document.querySelector('input[name="no_telp"]');
                const alamatInput = document.querySelector('textarea[name="alamat"]');
                
                if (namaInput && patient.fname) {
                    namaInput.value = patient.fname;
                }
                
                if (tglLahirInput && patient.ftgllahir) {
                    // Check valid date and format if needed, though usually SQL returns YYYY-MM-DD
                    tglLahirInput.value = patient.ftgllahir.split(' ')[0]; // Ensure it drops time if any
                }
                
                if (noTelpInput && patient.fnotelp) {
                    noTelpInput.value = patient.fnotelp;
                }
                
                if (alamatInput && patient.falamat) {
                    alamatInput.value = patient.falamat;
                }
                
                // Optional: Highlight that they were auto-filled
                [namaInput, tglLahirInput, noTelpInput, alamatInput].forEach(el => {
                    if (el) {
                        el.style.backgroundColor = '#f0fdf4';
                        setTimeout(() => el.style.backgroundColor = '', 1500);
                    }
                });
            }
        });
    }
});
</script>

<!-- Data Table Section -->
<div class="panel">
    <div class="panel-header" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; background-color: #f8fafc;">
        <h3 class="panel-title">Daftar Pasien Terdaftar</h3>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: nowrap; justify-content: flex-end;">
            <div class="search-bar" style="width: 250px; margin-bottom: 0; position: relative;">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" class="search-input" style="height: 2.25rem; background-color: #ffffff; border: 1px solid #e2e8f0; width: 100%;" placeholder="Pencarian cepat...">
            </div>
            <button onclick="document.getElementById('form-pasien').classList.toggle('hidden')" class="btn-primary" style="margin-top: 0; border-radius: 0.5rem; height: 2.25rem; padding: 0 1rem; display: flex; align-items: center; gap: 0.5rem; white-space: nowrap;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="user-plus" aria-hidden="true" class="lucide lucide-user-plus"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" x2="19" y1="8" y2="14"></line><line x1="22" x2="16" y1="11" y2="11"></line></svg>
                <span>Registrasi Pasien Baru</span>
            </button>
        </div>
    </div>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. REG</th>
                    <th>Nama Pasien</th>
                    <th>Tgl Lahir / Usia</th>
                    <th>Kontak</th>
                    <th>Alamat</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Pagination settings
                $limit = 5;
                $page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
                if ($page_num < 1) $page_num = 1;
                $offset = ($page_num - 1) * $limit;
                
                $stmt_total = $pdo->query("SELECT COUNT(*) FROM rehab_pasien");
                $total_pasien = $stmt_total->fetchColumn();
                $total_pages = ceil($total_pasien / $limit);
                
                $stmt = $pdo->query("SELECT * FROM rehab_pasien ORDER BY no_rm DESC LIMIT $limit OFFSET $offset");
                while ($row = $stmt->fetch()):
                    // Hitung umur
                    $birthDate = new DateTime($row['tgl_lahir']);
                    $today = new DateTime('today');
                    $age = $birthDate->diff($today)->y;
                ?>
                <tr>
                    <td>
                        <span style="background-color: #eff6ff; color: #2563eb; padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-family: monospace; font-weight: 700; font-size: 0.75rem;">
                            <?= htmlspecialchars($row['no_rm']) ?>
                        </span>
                    </td>
                    <td style="font-weight: 600;">
                        <?= htmlspecialchars($row['nama']) ?>
                    </td>
                    <td>
                        <?= date('d/m/Y', strtotime($row['tgl_lahir'])) ?> <br>
                        <span style="font-size: 0.6875rem; color: #64748b;"><?= $age ?> Tahun</span>
                    </td>
                    <td>
                        <?= htmlspecialchars($row['no_telp']) ?>
                    </td>
                    <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['alamat']) ?>">
                        <?= htmlspecialchars($row['alamat']) ?>
                    </td>
                    <td style="text-align: center;">
                        <button onclick="previewPasien('<?= htmlspecialchars($row['no_rm']) ?>', '<?= htmlspecialchars($row['nama']) ?>', '<?= htmlspecialchars($row['tgl_lahir']) ?>', '<?= $age ?>', '<?= htmlspecialchars($row['no_telp']) ?>', '<?= htmlspecialchars($row['alamat']) ?>')" style="padding: 0.25rem; color: #3b82f6; border-radius: 0.375rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#eff6ff'" onmouseout="this.style.backgroundColor='transparent'" title="Preview Profil">
                            <i data-lucide="eye" style="width: 1.25rem; height: 1.25rem;"></i>
                        </button>
                        <a href="index.php?page=pasien&action=edit&no_rm=<?= htmlspecialchars($row['no_rm']) ?>" style="padding: 0.25rem; color: #f59e0b; border-radius: 0.375rem; transition: background-color 0.2s; display: inline-flex;" onmouseover="this.style.backgroundColor='#fffbeb'" onmouseout="this.style.backgroundColor='transparent'" title="Edit Data">
                            <i data-lucide="edit" style="width: 1.25rem; height: 1.25rem;"></i>
                        </a>
                        <a href="index.php?page=pasien&action=delete&no_rm=<?= htmlspecialchars($row['no_rm']) ?>" onclick="return confirm('Peringatan: Menghapus pasien ini juga akan menghapus seluruh data Kunjungan & Rekam Medis (Anamnesis & Body Mapping) yang terkait dengan pasien ini secara permanen!\n\nApakah Anda yakin ingin melanjutkan?')" style="padding: 0.25rem; color: #ef4444; border-radius: 0.375rem; transition: background-color 0.2s; display: inline-flex;" onmouseover="this.style.backgroundColor='#fef2f2'" onmouseout="this.style.backgroundColor='transparent'" title="Hapus Data">
                            <i data-lucide="trash-2" style="width: 1.25rem; height: 1.25rem;"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination Controls -->
    <?php if (isset($total_pages) && $total_pages > 1): ?>
    <div class="panel-body" style="border-top: 1px solid #e2e8f0; background-color: #f8fafc; padding: 0.75rem 1.25rem;">
        <div class="pagination-container" style="margin-top: 0;">
            <div class="pagination-info">
                Menampilkan halaman <?= $page_num ?> dari <?= $total_pages ?> (Total <?= $total_pasien ?> pasien)
            </div>
            <div class="pagination-controls">
                <?php if ($page_num > 1): ?>
                    <a href="index.php?page=pasien&p=<?= $page_num - 1 ?>" class="page-btn">Sebelumnya</a>
                <?php else: ?>
                    <span class="page-btn disabled">Sebelumnya</span>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="index.php?page=pasien&p=<?= $i ?>" class="page-btn <?= $i === $page_num ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page_num < $total_pages): ?>
                    <a href="index.php?page=pasien&p=<?= $page_num + 1 ?>" class="page-btn">Berikutnya</a>
                <?php else: ?>
                    <span class="page-btn disabled">Berikutnya</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
