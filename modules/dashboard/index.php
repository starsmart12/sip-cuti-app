<?php 
require '../../includes/auth_check.php'; 
require '../../config/database.php';
include '../../includes/header.php';
include '../../includes/sidebar.php';

$id_user = $_SESSION['id_user'];
$role = $_SESSION['role'];
$jabatan_user = $_SESSION['jabatan'] ?? ''; 

// Ambil Data User
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user = $id_user"));
?>

<div class="container mx-auto px-2 sm:px-0">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-emerald-900 tracking-tight">Dashboard Utama</h1>
            <p class="text-sm text-gray-500">Selamat datang kembali, <span class="font-semibold text-emerald-700"><?= $_SESSION['nama']; ?></span></p>
        </div>
        <div class="text-[11px] sm:text-sm text-gray-400 mt-2 md:mt-0 italic font-medium bg-white px-4 py-2 rounded-full shadow-sm border border-gray-50 self-start">
            <i class="fas fa-calendar-alt mr-2 text-emerald-500"></i> <?= date('d F Y'); ?>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <?php if($role == 'admin') : ?>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-100 flex items-center gap-4 transition-all hover:shadow-md">
                <div class="h-14 w-14 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 text-2xl">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Karyawan</h3>
                    <?php $tk = mysqli_num_rows(mysqli_query($conn, "SELECT id_user FROM users")); ?>
                    <p class="text-2xl font-black text-gray-800"><?= $tk; ?> <span class="text-sm font-normal text-gray-400 italic font-medium ml-1">Orang</span></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if($role == 'manager' || $role == 'admin') : ?>
            <div class="bg-white p-6 rounded-2xl shadow-sm border-t-4 border-yellow-400 flex items-center gap-4 relative overflow-hidden transition-all hover:shadow-md">
                <div class="absolute -right-4 -bottom-4 text-yellow-100 text-6xl opacity-50 rotate-12">
                    <i class="fas fa-file-signature"></i>
                </div>
                
                <div class="h-14 w-14 bg-yellow-50 rounded-xl flex items-center justify-center text-yellow-600 text-2xl z-10 shadow-inner">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="z-10">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Menunggu Approval</h3>
                    <?php 
                    $sql_p = "";
                    
                    if ($role == 'admin') {
                        $sql_p = "SELECT id_pengajuan FROM pengajuan_cuti WHERE status = 'pending_admin'";
                    } else {
                        // LOGIKA UNTUK PEJABAT STRUKTURAL YANG JUGA JADI ATASAN LANGSUNG
                        if (stripos($jabatan_user, 'Kasubag') !== false) {
                            $sql_p = "SELECT pc.id_pengajuan FROM pengajuan_cuti pc 
                                      JOIN users u ON pc.id_user = u.id_user 
                                      WHERE (pc.status = 'pending_kasub') 
                                      OR (pc.status = 'pending_manager' AND u.manager_id = '$id_user')";
                        } elseif (stripos($jabatan_user, 'Kabag') !== false) {
                            $sql_p = "SELECT pc.id_pengajuan FROM pengajuan_cuti pc 
                                      JOIN users u ON pc.id_user = u.id_user 
                                      WHERE (pc.status = 'pending_kabag') 
                                      OR (pc.status = 'pending_manager' AND u.manager_id = '$id_user')";
                        } elseif (stripos($jabatan_user, 'Sekretaris') !== false) {
                            $sql_p = "SELECT pc.id_pengajuan FROM pengajuan_cuti pc 
                                      JOIN users u ON pc.id_user = u.id_user 
                                      WHERE (pc.status = 'pending_sekretaris') 
                                      OR (pc.status = 'pending_manager' AND u.manager_id = '$id_user')";
                        } elseif (stripos($jabatan_user, 'Wakil Ketua') !== false) {
                            $sql_p = "SELECT pc.id_pengajuan FROM pengajuan_cuti pc 
                                      JOIN users u ON pc.id_user = u.id_user 
                                      JOIN jabatan j ON u.id_jabatan = j.id_jabatan
                                      WHERE (pc.status = 'pending_pimpinan' AND j.nama_jabatan NOT LIKE '%Hakim%') 
                                      OR (pc.status = 'pending_manager' AND u.manager_id = '$id_user')";
                        } elseif (stripos($jabatan_user, 'Ketua') !== false) {
                            $sql_p = "SELECT id_pengajuan FROM pengajuan_cuti WHERE status = 'pending_pimpinan' OR status = 'pending_ketua'";
                        } else {
                            // Manager Murni (bukan pejabat struktural di alur)
                            $sql_p = "SELECT pc.id_pengajuan FROM pengajuan_cuti pc 
                                      JOIN users u ON pc.id_user = u.id_user 
                                      WHERE pc.status = 'pending_manager' AND u.manager_id = '$id_user'";
                        }
                    }

                    $res_p = mysqli_query($conn, $sql_p);
                    $pending = ($res_p) ? mysqli_num_rows($res_p) : 0; 
                    ?>
                    <p class="text-2xl font-black <?= $pending > 0 ? 'text-red-600' : 'text-gray-800' ?>">
                        <?= $pending; ?> 
                        <span class="text-sm font-normal text-gray-400 italic font-medium ml-1">Permohonan</span>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (($_SESSION['jabatan'] ?? '') !== 'Ketua Pengadilan') : ?>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-100 flex items-center gap-4 transition-all hover:shadow-md">
                <div class="h-14 w-14 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 text-2xl">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pengajuan Saya</h3>
                    <?php 
                    $rc = mysqli_num_rows(mysqli_query($conn, "SELECT id_pengajuan FROM pengajuan_cuti WHERE id_user = $id_user")); 
                    ?>
                    <p class="text-2xl font-black text-emerald-900"><?= $rc; ?> <span class="text-sm font-normal text-gray-400 italic font-medium ml-1">Data</span></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-8 p-6 sm:p-8 bg-emerald-900 rounded-[2rem] text-white shadow-xl shadow-emerald-900/10 flex flex-col lg:flex-row items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-800 rounded-full translate-x-16 -translate-y-16 opacity-50"></div>
        <div class="flex items-center gap-5 z-10">
            <div class="h-14 w-14 bg-emerald-800/50 backdrop-blur-sm rounded-2xl flex items-center justify-center text-yellow-400 shadow-xl border border-emerald-700">
                <i class="fas fa-lightbulb text-xl"></i>
            </div>
            <div>
                <p class="text-lg font-bold tracking-tight">Butuh waktu istirahat?</p>
                <p class="text-xs text-emerald-200/80 font-medium px-1">Pastikan Anda mengajukan cuti minimal 3 hari sebelum tanggal mulai agar proses approval lebih cepat.</p>
            </div>
        </div>
        <div class="z-10 w-full lg:w-auto">
            <a href="../cuti/ajukan.php" class="bg-yellow-400 hover:bg-yellow-500 hover:scale-105 active:scale-95 text-emerald-900 px-8 py-3.5 rounded-2xl font-bold transition-all duration-300 flex items-center justify-center gap-3 shadow-lg shadow-yellow-600/20 text-sm uppercase tracking-wider text-center">
                <i class="fas fa-plus-circle"></i> Buat Pengajuan Baru
            </a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>