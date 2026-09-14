<?php
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->query("SELECT NIP, Nama, password, jeniskyw FROM hrd.datadasar");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h1>Daftar Pengguna:</h1><table border='1' cellpadding='10'>";
echo "<tr><th>NIP (Username)</th><th>Nama</th><th>Password</th><th>Jenis Karyawan</th></tr>";
foreach ($users as $u) {
    echo "<tr><td>{$u['NIP']}</td><td>{$u['Nama']}</td><td>{$u['password']}</td><td>{$u['jeniskyw']}</td></tr>";
}
echo "</table>";
?>
