<?php 
require '../../includes/auth_check.php'; 
require '../../config/database.php';
include '../../includes/header.php';
include '../../includes/sidebar.php';

$id_user = $_SESSION['id_user'];
$query = "SELECT pc.*, jc.nama_jenis FROM pengajuan_cuti pc 
          JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis 
          WHERE pc.id_user = '$id_user' ORDER BY pc.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 overflow-hidden">
        <div class="p-6 border-b border-emerald-50 bg-emerald-50/50 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-extrabold text-emerald-900 tracking-tight flex items-center">
                    <i class="fas fa-history mr-3 text-emerald-600"></i> Riwayat Cuti Saya
                </h2>
                <p class="text-emerald-700/60 text-xs mt-1 font-medium">Pantau status pengajuan dan cetak surat cuti Anda.</p>
            </div>
            <a href="ajukan.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-lg shadow-emerald-100 flex items-center">
                <i class="fas fa-plus mr-2"></i> Ajukan Cuti
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-left text-emerald-800 uppercase text-[11px] font-bold tracking-widest bg-emerald-50/30">
                        <th class="p-5">Periode Tanggal</th>
                        <th class="p-5">Jenis Cuti</th>
                        <th class="p-5 text-center">Durasi</th>
                        <th class="p-5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 divide-y divide-emerald-50">
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php while($row = mysqli_fetch_assoc($result)) : ?>
                        <tr class="hover:bg-emerald-50/20 transition-colors">
                            <td class="p-5">
                                <div class="font-bold text-gray-800 text-sm">
                                    <?= date('d M Y', strtotime($row['tanggal_mulai'])); ?> 
                                    <span class="text-gray-300 font-normal mx-1">→</span> 
                                    <?= date('d M Y', strtotime($row['tanggal_selesai'])); ?>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1 italic">Diajukan pada: <?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></p>
                            </td>
                            <td class="p-5">
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-[10px] font-bold border border-emerald-200 uppercase tracking-tighter">
                                    <?= $row['nama_jenis']; ?>
                                </span>
                            </td>
                            <td class="p-5 text-center">
                                <div class="text-sm font-extrabold text-emerald-800"><?= $row['jumlah_hari']; ?> <span class="text-[10px] font-normal text-emerald-600 uppercase">Hari</span></div>
                            </td>
                            <td class="p-5 text-center">
                                <div class="flex flex-col items-center">
                                    <?php 
                                        $status_raw = $row['status']; // Ambil teks asli dari DB
                                        $status_clean = strtolower($status_raw);
                                        
                                        // Penentuan Warna Berdasarkan Status
                                        if (strpos($status_clean, 'pending') !== false || $status_clean == 'menunggu') {
                                            $badge_style = 'bg-amber-100 text-amber-700 border-amber-200';
                                        } elseif ($status_clean == 'selesai' || $status_clean == 'approved' || $status_clean == 'disetujui') {
                                            $badge_style = 'bg-emerald-600 text-white border-emerald-700 shadow-lg shadow-emerald-100';
                                        } elseif ($status_clean == 'rejected' || $status_clean == 'ditolak') {
                                            $badge_style = 'bg-red-100 text-red-700 border-red-200';
                                        } else {
                                            $badge_style = 'bg-gray-100 text-gray-700 border-gray-200';
                                        }
                                    ?>
                                    <span class="px-3 py-1.5 rounded-full border text-[10px] font-black uppercase tracking-widest shadow-sm <?= $badge_style; ?>">
                                        <?= $status_raw; ?>
                                    </span>

                                    <?php if(($status_clean == 'rejected' || $status_clean == 'ditolak') && !empty($row['keterangan_admin'])) : ?>
                                        <button onclick="showReason('<?= addslashes($row['keterangan_admin']); ?>')" class="mt-2 text-[10px] text-red-500 hover:text-red-700 font-bold underline decoration-dotted">
                                            Lihat Alasan
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="p-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="bg-emerald-50 p-4 rounded-full mb-4">
                                        <i class="fas fa-folder-open text-emerald-200 text-4xl"></i>
                                    </div>
                                    <p class="text-gray-400 text-sm font-medium">Anda belum memiliki riwayat pengajuan cuti.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Perbaikan SweetAlert2: borderRadius dihapus agar tidak error
    function showReason(reason) {
        Swal.fire({
            title: 'Alasan Penolakan',
            text: reason,
            icon: 'info',
            confirmButtonColor: '#059669'
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.get('status') === 'success') {
            Swal.fire({
                title: 'Berhasil Terkirim!',
                text: 'Pengajuan cuti Anda telah masuk ke sistem dan menunggu verifikasi.',
                icon: 'success',
                confirmButtonColor: '#059669'
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>