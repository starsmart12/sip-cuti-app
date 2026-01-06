<?php
/**
 * File: config/database.php.example
 * Salin file ini menjadi 'database.php' dan sesuaikan konfigurasinya
 */
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_cuti_karyawan"; // Nama database Anda

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Fungsi helper tetap disertakan agar aplikasi tidak error saat dicoba orang lain
if (!function_exists('query')) {
    function query($query) {
        global $conn;
        $result = mysqli_query($conn, $query);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}
?>