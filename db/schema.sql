CREATE DATABASE IF NOT EXISTS askes;
USE askes;

-- 1. Table rehab_pasien
CREATE TABLE IF NOT EXISTS rehab_pasien (
    no_rm VARCHAR(20) PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    alamat TEXT,
    tgl_lahir DATE,
    no_telp VARCHAR(20),
    no_ktp VARCHAR(30),
    INDEX idx_nama (nama)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 2. Table rehab_kunjungan
CREATE TABLE IF NOT EXISTS rehab_kunjungan (
    no_register VARCHAR(30) PRIMARY KEY,
    no_rm VARCHAR(20) NOT NULL,
    tgl_kunjungan DATE NOT NULL,
    dokter_id VARCHAR(5) NOT NULL, -- Merujuk ke NIP di hrd.datadasar
    status ENUM('baru', 'ulang', 'selesai') DEFAULT 'baru',
    FOREIGN KEY (no_rm) REFERENCES rehab_pasien(no_rm) ON DELETE CASCADE,
    INDEX idx_norm (no_rm),
    INDEX idx_dokter (dokter_id),
    INDEX idx_tgl (tgl_kunjungan)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 3. Table rehab_rekam_medis
CREATE TABLE IF NOT EXISTS rehab_rekam_medis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_register VARCHAR(30) NOT NULL,
    diagnosa_masuk TEXT,
    keluhan_utama TEXT,
    riwayat_sekarang TEXT,
    riwayat_dulu TEXT,
    riwayat_keluarga TEXT,
    jenis_form ENUM('umum', 'muskuloskeletal', 'kardiorespiratori', 'neuromuskuler') DEFAULT 'umum',
    detail_pemeriksaan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (no_register) REFERENCES rehab_kunjungan(no_register) ON DELETE CASCADE,
    INDEX idx_noregister (no_register)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 4. Table rehab_body_mapping
CREATE TABLE IF NOT EXISTS rehab_body_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rekam_medis_id INT NOT NULL,
    x_coord INT NOT NULL,
    y_coord INT NOT NULL,
    keterangan VARCHAR(255) NOT NULL,
    FOREIGN KEY (rekam_medis_id) REFERENCES rehab_rekam_medis(id) ON DELETE CASCADE,
    INDEX idx_rmid (rekam_medis_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 5. Database HRD & Table datadasar (Untuk Data Dokter)
CREATE DATABASE IF NOT EXISTS hrd;
USE hrd;

CREATE TABLE IF NOT EXISTS datadasar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    NIP VARCHAR(20) NOT NULL,
    nama_lengkap VARCHAR(255) NOT NULL,
    Nama VARCHAR(255),
    role VARCHAR(50) DEFAULT 'karyawan',
    password VARCHAR(255),
    jeniskyw VARCHAR(50)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Insert Data Dokter (Abaikan jika sudah ada)
INSERT IGNORE INTO datadasar (NIP, nama_lengkap, Nama, role, password, jeniskyw) VALUES 
('DOK01', 'Prof. Dr. dr. Muhammad Arifin, Sp.BS.,Subsp.N.Ped. (K)', 'Prof. Arifin', 'dokter', '123456', 'Medis'),
('DOK02', 'Prof. Dr. dr. Joni Wahyuhadi, Sp.BS., Subsp., N.Onk. (K), MARS', 'Prof. Joni', 'dokter', '123456', 'Medis'),
('DOK03', 'dr. I Gusti Made Aswin Rahmadi Ranuh, Sp.BS-FIS, M.Ked.Klin.', 'dr. Aswin', 'dokter', '123456', 'Medis'),
('DOK04', 'Dr. dr. Yulia Primitasari, Sp.M.,Subsp.,G. (K)', 'Dr. Yulia', 'dokter', '123456', 'Medis');
