<?php
require_once __DIR__ . '/../../config/database.php';
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

$id_user_login = $_SESSION['id_user']; // ID User yang sedang login

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];
    $current_status = isset($_GET['current_status']) ? mysqli_real_escape_string($conn, $_GET['current_status']) : '';
    $komentar = isset($_GET['komentar']) ? mysqli_real_escape_string($conn, $_GET['komentar']) : '';

    if ($action === 'approve') {
        // Ambil data pengaju
        $sql_pengaju = "SELECT u.level_akses, j.nama_jabatan FROM pengajuan_cuti pc 
                        JOIN users u ON pc.id_user = u.id_user 
                        LEFT JOIN jabatan j ON u.id_jabatan = j.id_jabatan 
                        WHERE pc.id_pengajuan = '$id'";
        $res_pengaju = mysqli_query($conn, $sql_pengaju);
        $data_pengaju = mysqli_fetch_assoc($res_pengaju);
        
        $jabatan = $data_pengaju['nama_jabatan'] ?? '';
        $is_pejabat_khusus = ($jabatan === 'Panitera' || $jabatan === 'Sekretaris' || $jabatan === 'Wakil Ketua Pengadilan');

        $next_status = '';
        $keterangan = '';
        $update_fields = []; // Array untuk menampung kolom waktu acc

        switch ($current_status) {
            case 'pending_admin':
                $update_fields[] = "acc_admin_at = NOW()";
                if ($is_pejabat_khusus) {
                    $next_status = 'pending_kasub';
                    $keterangan = 'Disetujui Admin, Menunggu Kasubag Kepegawaian';
                } else {
                    $next_status = 'pending_manager';
                    $keterangan = 'Disetujui Admin, Menunggu Atasan Langsung';
                }
                break;

            case 'pending_manager':
                $update_fields[] = "acc_manager_at = NOW()";
                $next_status = 'pending_kasub';
                $keterangan = 'Disetujui Atasan, Menunggu Kasubag Kepegawaian';
                break;

            case 'pending_kasub':
                $update_fields[] = "acc_verifikasi_at = NOW()"; 
                $next_status = 'pending_kabag';
                $keterangan = 'Disetujui Kasubag Kepeg, Menunggu Kabag';
                break;

            case 'pending_kabag':
                $next_status = 'pending_sekretaris';
                $keterangan = 'Disetujui Kabag, Menunggu Sekretaris';
                break;

            case 'pending_sekretaris':
                // Sekretaris memberikan ACC Pimpinan (Jika P3K) atau diteruskan
                $update_fields[] = "acc_pimpinan_at = NOW()"; 
                if (strpos($jabatan, 'P3K') !== false) {
                    $next_status = 'approved';
                    $keterangan = 'Disetujui Sekretaris (Final P3K)';
                    $update_fields[] = "approved_by = '$id_user_login'";
                    $update_fields[] = "approved_at = NOW()";
                } elseif (strpos($jabatan, 'Hakim') !== false || $is_pejabat_khusus) {
                    $next_status = 'pending_ketua';
                    $keterangan = 'Disetujui Sekretaris, Menunggu Persetujuan Ketua';
                } else {
                    $next_status = 'pending_pimpinan';
                    $keterangan = 'Menunggu Persetujuan Pimpinan';
                }
                break;

            case 'pending_pimpinan':
            case 'pending_ketua':
                $next_status = 'approved';
                $keterangan = 'Disetujui Pimpinan (Selesai)';
                $update_fields[] = "acc_pimpinan_at = NOW()";
                $update_fields[] = "approved_by = '$id_user_login'";
                $update_fields[] = "approved_at = NOW()";
                break;

            default:
                $next_status = 'approved';
                break;
        }

        // Gabungkan semua update field
        $extra_query = !empty($update_fields) ? ", " . implode(", ", $update_fields) : "";
        $query = "UPDATE pengajuan_cuti SET 
                  status = '$next_status', 
                  keterangan_admin = '$keterangan' 
                  $extra_query 
                  WHERE id_pengajuan = '$id'";

    } elseif ($action === 'reject') {
        $query = "UPDATE pengajuan_cuti SET 
                  status = 'rejected', 
                  keterangan_admin = 'Ditolak: $komentar',
                  approved_by = '$id_user_login',
                  approved_at = NOW() 
                  WHERE id_pengajuan = '$id'";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: approve.php?pesan=" . urlencode("Status berhasil diperbarui"));
    } else {
        header("Location: approve.php?pesan=" . urlencode("Gagal database error: " . mysqli_error($conn)));
    }
}
?>