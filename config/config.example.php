<?php
/**
 * File: config/database.php.example
 * Salin file ini menjadi 'database.php' dan sesuaikan konfigurasinya
 */

$host = "localhost";
$user = "root";       // Username database Anda (biasanya root)
$pass = "";           // Kosongkan saja di file example ini
$db   = "sip_cuti";    // Nama database Anda

$conn = mysqli_connect($host, $user, $pass, $db);

// Cek Koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

?>