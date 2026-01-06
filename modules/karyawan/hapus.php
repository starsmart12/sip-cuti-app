<?php
require '../../includes/auth_check.php';
require '../../includes/role_check.php';
require '../../config/database.php';

allow_roles(['admin']);

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $current_admin_id = $_SESSION['id_user'];

    // 1. PROTEKSI: Admin tidak boleh menghapus dirinya sendiri
    if ($id == $current_admin_id) {
        header("Location: index.php?status=forbidden");
        exit;
    }

    // 2. CEK DATA TARGET & PROTEKSI HIERARKI
    $check_target = mysqli_query($conn, "SELECT tanda_tangan, level_akses FROM users WHERE id_user = '$id'");
    $data_karyawan = mysqli_fetch_assoc($check_target);

    if (!$data_karyawan) {
        header("Location: index.php?status=error");
        exit;
    }

    // Jika yang login level 'staff' tapi mencoba menghapus level 'admin'
    if ($_SESSION['level_akses'] !== 'Admin' && $data_karyawan['level_akses'] === 'admin') {
        header("Location: index.php?status=forbidden");
        exit;
    }

    // 3. JALANKAN QUERY HAPUS
    $query_delete = "DELETE FROM users WHERE id_user = '$id'";
    
    if (mysqli_query($conn, $query_delete)) {
        // Hapus file tanda tangan jika bukan default
        if ($data_karyawan['tanda_tangan'] != 'default_ttd.png') {
            $path = "../../assets/img/ttd/" . $data_karyawan['tanda_tangan'];
            if (file_exists($path)) { unlink($path); }
        }
        header("Location: index.php?status=success");
    } else {
        header("Location: index.php?status=error");
    }
} else {
    header("Location: index.php");
}
exit;