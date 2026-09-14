<?php
// c:\Users\louis\Documents\rehab\rehab_rkz\index.php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Handle Logout
if ($page === 'logout') {
    session_destroy();
    header("Location: index.php?page=login");
    exit;
}


// Handle Login POST
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = $_POST['username'];
    $password = $_POST['password']; 
    
    $stmt = $pdo->prepare("SELECT * FROM hrd.datadasar WHERE NIP = ? AND password = ?");
    $stmt->execute(array($nip, $password));
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['NIP'];
        
        $_SESSION['role'] = 'medis'; // Role tidak lagi dibedakan
        $_SESSION['nama_lengkap'] = $user['Nama'];
        
        header("Location: index.php?page=dashboard");
        exit;
    } else {
        $error = "NIP atau password salah!";
    }
}

// Routing Logic
if ($page !== 'login' && (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['nama_lengkap']))) {
    session_unset(); // Clear current script variables
    session_destroy(); // Destroy incomplete session from previous version
    session_start();
    header("Location: index.php?page=login");
    exit;
}

// Load Header
require 'includes/header.php';

// Load Main Content (Modul)
switch ($page) {
    case 'login':
        require 'views/login.php';
        break;
    case 'home':
    case 'dashboard':
        require 'views/dashboard_dokter.php';
        break;
    case 'anamnesis':
        require 'views/anamnesis.php';
        break;
    case 'pasien':
        require 'views/pasien.php';
        break;
    case 'kunjungan':
        require 'views/kunjungan.php';
        break;
    case 'laporan':
        require 'views/laporan.php';
        break;
    case 'detail_rm':
        require 'views/detail_rm.php';
        break;
    default:
        echo "<h2>Halaman tidak ditemukan</h2>";
        break;
}

// Load Footer
require 'includes/footer.php';
?>
