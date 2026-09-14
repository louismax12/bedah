<?php
// c:\Users\louis\Documents\rehab\prd\config.php

$host = '192.168.2.12';
$db   = 'askes';
$user = 'anugrah';
$pass = 'anugrah'; // Sesuaikan dengan password MySQL Anda
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Error Exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Array Assoc
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Native prepared statements
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Koneksi Database Gagal: " . $e->getMessage());
}
?>
