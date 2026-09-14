<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=login");
        exit;
    }
}

function check_role($allowed_roles) {
    if (!isset($_SESSION['role'])) {
        die('Akses ditolak.');
    }
    
    if (is_array($allowed_roles)) {
        if (!in_array($_SESSION['role'], $allowed_roles)) {
            die('Akses ditolak.');
        }
    } else {
        if ($_SESSION['role'] !== $allowed_roles) {
            die('Akses ditolak.');
        }
    }
}
?>
