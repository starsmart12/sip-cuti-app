<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../../index.php"); // Sesuaikan path ke halaman login/index Anda
    exit;
}

// 2. Sertakan koneksi database jika belum ada
// Menggunakan require_once agar tidak terjadi error jika database.php dipanggil dua kali
// Ganti ../../ menjadi ../ saja
require_once __DIR__ . '/../config/database.php';

// 3. SINKRONISASI REAL-TIME
$id_user_login = $_SESSION['id_user'];
$sql_sync = "SELECT role, level_akses, nama_lengkap FROM users WHERE id_user = '$id_user_login'";
$query_sync = mysqli_query($conn, $sql_sync);

if ($row_sync = mysqli_fetch_assoc($query_sync)) {
    // Timpa data session lama dengan yang terbaru dari database
    $_SESSION['role'] = $row_sync['role'];
    $_SESSION['level_akses'] = $row_sync['level_akses'];
    $_SESSION['nama'] = $row_sync['nama_lengkap'];
} else {
    // Jika user tiba-tiba tidak ada di DB (dihapus admin), tendang keluar
    session_destroy();
    header("Location: ../../index.php?pesan=akun_tidak_aktif");
    exit;
}