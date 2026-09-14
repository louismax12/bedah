<?php
// Script otomatis untuk membuat tabel database
require_once 'config.php';

echo "<h2>Proses Import Database...</h2>";

$sql_file = file_get_contents('schema.sql');

// Memecah query berdasarkan titik koma (;)
$queries = explode(';', $sql_file);

try {
    $berhasil = 0;
    foreach($queries as $query) {
        $query = trim($query);
        if(!empty($query)) {
            $pdo->exec($query);
            $berhasil++;
        }
    }
    echo "<h1 style='color:green;'>Tabel Berhasil Dibuat! ($berhasil query dijalankan)</h1>";
    echo "<p>Semua tabel (termasuk `askes.pasien`) sekarang sudah ada di database Anda.</p>";
    echo "<p><a href='index.php' style='padding: 10px 20px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 8px;'>Kembali ke Aplikasi</a></p>";
} catch (PDOException $e) {
    echo "<h1 style='color:red;'>Gagal mengimpor database:</h1>";
    echo "<code>" . $e->getMessage() . "</code>";
}
?>
