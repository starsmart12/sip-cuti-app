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

    // 2. CEK DATA TARGET
    $check_target = mysqli_query($conn, "SELECT tanda_tangan, level_akses FROM users WHERE id_user='$id'");
    $data_karyawan = mysqli_fetch_assoc($check_target);

    if (!$data_karyawan) {
        header("Location: index.php?status=error");
        exit;
    }

    // 3. PROTEKSI HIERARKI
    if ($_SESSION['level_akses'] !== 'admin' && $data_karyawan['level_akses'] === 'admin') {
        header("Location: index.php?status=forbidden");
        exit;
    }

    // 4. MULAI TRANSACTION
    mysqli_begin_transaction($conn);

    try {

        // Hapus semua data cuti milik user
        mysqli_query($conn, "DELETE FROM pengajuan_cuti WHERE id_user='$id'");

        // Hapus user
        mysqli_query($conn, "DELETE FROM users WHERE id_user='$id'");

        // Commit jika semua berhasil
        mysqli_commit($conn);

        // 5. Hapus file tanda tangan jika bukan default
        if ($data_karyawan['tanda_tangan'] != 'default_ttd.png') {
            $path = "../../assets/img/ttd/" . $data_karyawan['tanda_tangan'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        header("Location: index.php?status=success");
        exit;

    } catch (Exception $e) {

        // Jika error rollback
        mysqli_rollback($conn);

        header("Location: index.php?status=error");
        exit;
    }

} else {

    header("Location: index.php");
    exit;
}
?>