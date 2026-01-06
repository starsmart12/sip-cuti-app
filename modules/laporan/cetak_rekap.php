<?php
// Pastikan session dimulai sebelum pengecekan lainnya
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../../config/database.php';
require '../../includes/auth_check.php';

// Ambil data admin dari session
$admin_nama = $_SESSION['nama_lengkap'] ?? '';
$admin_nip = $_SESSION['nip'] ?? '';

// Jika session kosong (bug session), ambil ulang dari database agar tidak "TIDAK TERSEDIA"
if (empty($admin_nama) || empty($admin_nip)) {
    $id_admin = $_SESSION['id_user'];
    $get_admin = mysqli_query($conn, "SELECT nama_lengkap, nip FROM users WHERE id_user = '$id_admin'");
    $data_admin = mysqli_fetch_assoc($get_admin);
    $admin_nama = $data_admin['nama_lengkap'];
    $admin_nip = $data_admin['nip'];
}

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$bulan_indo = [
    1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL', 5 => 'MEI', 6 => 'JUNI',
    7 => 'JULI', 8 => 'AGUSTUS', 9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
];

// Query data rekap
$query = "SELECT u.nama_lengkap, u.nip, pc.alasan, pc.tanggal_mulai, pc.tanggal_selesai, 
                 pc.jumlah_hari, jc.nama_jenis, j.nama_jabatan
          FROM pengajuan_cuti pc
          JOIN users u ON pc.id_user = u.id_user
          JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis
          LEFT JOIN jabatan j ON u.id_jabatan = j.id_jabatan
          WHERE pc.status = 'Selesai' 
          AND MONTH(pc.tanggal_mulai) = '$bulan' 
          AND YEAR(pc.tanggal_mulai) = '$tahun'
          ORDER BY pc.tanggal_mulai ASC";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap_Cuti_<?= $bulan_indo[$bulan] ?>_<?= $tahun ?></title>
    
    <link rel="icon" type="image/png" href="../../assets/img/logo_pn.png">
    
    <link rel="stylesheet" href="../../assets/css/print-rekap.css">
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <h2>LAPORAN REKAPITULASI CUTI PEGAWAI</h2>
            <p>Sistem Informasi Pengajuan Cuti Pengadilan Negeri Makassar - Tahun <?= $tahun ?></p>
        </div>

        <p class="font-bold" style="margin-bottom: 10px;">PERIODE: <?= $bulan_indo[$bulan] ?> <?= $tahun ?></p>

        <table>
            <thead>
                <tr>
                    <th>NO</th>
                    <th>NAMA / NIP</th>
                    <th>JABATAN</th>
                    <th>JENIS CUTI</th>
                    <th>ALASAN</th>
                    <th>TANGGAL</th>
                    <th>DURASI</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                if(mysqli_num_rows($result) > 0):
                    while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td>
                        <strong><?= strtoupper($row['nama_lengkap']); ?></strong><br>
                        NIP. <?= $row['nip']; ?>
                    </td>
                    <td class="text-center"><?= $row['nama_jabatan'] ?? '-'; ?></td>
                    <td class="text-center"><?= $row['nama_jenis']; ?></td>
                    <td><?= $row['alasan']; ?></td>
                    <td class="text-center">
                        <?= date('d/m/y', strtotime($row['tanggal_mulai'])); ?> s/d <?= date('d/m/y', strtotime($row['tanggal_selesai'])); ?>
                    </td>
                    <td class="text-center font-bold"><?= $row['jumlah_hari']; ?> HARI</td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" class="text-center">Data tidak ditemukan pada periode ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="footer-sign">
            <p>Dicetak pada: <?= date('d/m/Y') ?></p>
            <p style="margin-top: 5px;">Admin Kepegawaian,</p>
            
            <div class="ttd-container">
                <?php 
                $ttd_file = "../../assets/img/ttd/" . $admin_nip . "_ttd.png";
                if (file_exists($ttd_file)): ?>
                    <img src="<?= $ttd_file ?>" class="img-ttd">
                <?php else: ?>
                    <div style="height: 80px;"></div> <?php endif; ?>
            </div>

            <p class="font-bold" style="text-decoration: underline;"><?= strtoupper($admin_nama) ?></p>
            <p>NIP. <?= $admin_nip ?></p>
        </div>
    </div>
</body>
</html>