<?php 
require '../../includes/auth_check.php';
require '../../includes/role_check.php';
require '../../config/database.php';

allow_roles(['admin']);

include '../../includes/header.php';
include '../../includes/sidebar.php';

$current_user_id = $_SESSION['id_user'];
// Memastikan pembandingan string aman dengan strtolower
$lvl_login = strtolower($_SESSION['level_akses']); 

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$query = "SELECT u.*, j.nama_jabatan FROM users u 
          LEFT JOIN jabatan j ON u.id_jabatan = j.id_jabatan 
          WHERE (u.nama_lengkap LIKE '%$search%' 
          OR u.nip LIKE '%$search%' 
          OR u.level_akses LIKE '%$search%')
          ORDER BY u.nama_lengkap ASC";
$result = mysqli_query($conn, $query); 
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Daftar Karyawan</h2>
            <p class="text-gray-500 mt-1">Kelola data profil dan hak akses seluruh pegawai.</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <form action="" method="GET" class="relative group">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Cari nama, NIP, atau akses..." 
                       class="w-full md:w-72 pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none text-sm shadow-sm">
                <div class="absolute left-3 top-3.5 text-gray-400 group-focus-within:text-green-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </form>

            <a href="tambah.php" class="inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition shadow-lg shadow-green-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah
            </a>
        </div>
    </div>

    <?php if($search && mysqli_num_rows($result) == 0): ?>
        <div class="bg-white rounded-2xl p-12 text-center border border-dashed border-gray-200 shadow-sm">
            <h3 class="text-lg font-bold text-gray-800">Tidak ada hasil untuk "<?= htmlspecialchars($search) ?>"</h3>
            <a href="index.php" class="text-green-600 font-semibold hover:underline mt-2 inline-block">Reset Pencarian</a>
        </div>
    <?php else: ?>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="p-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Pegawai</th>
                        <th class="p-5 text-xs font-bold text-gray-400 uppercase tracking-widest text-center">Akses</th>
                        <th class="p-5 text-xs font-bold text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php while($row = mysqli_fetch_assoc($result)) : 
                        $is_self = ($row['id_user'] == $current_user_id);
                        $target_lvl = strtolower($row['level_akses']);
                        
                        // LOGIKA HIRARKI SESUAI PERMINTAAN:
                        // 1. Super Admin ($lvl_login == 'admin') bisa edit semua.
                        // 2. Admin Staff ($lvl_login == 'staff') hanya bisa edit level 'staff' & 'pejabat'.
                        $can_manage = false;
                        if ($lvl_login === 'admin') {
                            $can_manage = true;
                        } elseif ($lvl_login === 'staff' && ($target_lvl === 'staff' || $target_lvl === 'pejabat')) {
                            $can_manage = true;
                        }
                        
                        // Warna Badge Berdasarkan Level
                        $badge_color = "bg-gray-100 text-gray-700 border-gray-200";
                        if ($row['level_akses'] == 'admin') {
                            $badge_color = "bg-purple-100 text-purple-700 border-purple-200";
                        } elseif ($row['level_akses'] == 'pejabat') {
                            $badge_color = "bg-emerald-100 text-emerald-700 border-emerald-200";
                        } elseif ($row['level_akses'] == 'staff') {
                            $badge_color = "bg-amber-100 text-amber-700 border-amber-200";
                        }
                    ?>
                    <tr class="<?= $is_self ? 'bg-blue-50/40' : 'hover:bg-green-50/30' ?> transition-colors group">
                        <td class="p-5">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold mr-3 uppercase border border-green-200">
                                    <?= substr($row['nama_lengkap'], 0, 1); ?>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800">
                                        <?= $row['nama_lengkap']; ?>
                                        <?php if($is_self): ?>
                                            <span class="ml-1 text-[10px] bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded border border-blue-200 italic font-medium">Anda</span>
                                        <?php endif; ?>
                                        <?php if($row['level_akses'] === 'admin'): ?>
                                            <span class="ml-1 text-[10px] bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded border border-purple-200">Super Admin</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[11px] text-gray-400"><?= $row['nip']; ?> • <?= $row['nama_jabatan']; ?></div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="p-5 text-center">
                            <span class="px-3 py-1 border rounded-full text-[10px] font-bold uppercase <?= $badge_color ?>">
                                <?= $row['level_akses']; ?>
                            </span>
                        </td>

                        <td class="p-5 text-center">
                            <div class="flex justify-center items-center gap-2">
                                <?php if ($can_manage): ?>
                                    <a href="edit.php?id=<?= $row['id_user']; ?>" class="p-2 text-green-600 hover:bg-green-600 hover:text-white rounded-lg border border-green-100 transition-all shadow-sm" title="Edit Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    
                                    <?php if (!$is_self): ?>
                                        <button type="button" onclick="confirmDelete('<?= $row['id_user']; ?>', '<?= addslashes($row['nama_lengkap']); ?>')" class="p-2 text-red-500 hover:bg-red-500 hover:text-white rounded-lg border border-red-100 transition-all shadow-sm" title="Hapus Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="p-2 text-gray-300 bg-gray-50 border border-gray-100 rounded-lg cursor-not-allowed shadow-sm" title="Anda tidak dapat menghapus akun sendiri">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <span class="p-2 text-gray-300 bg-gray-50 border border-gray-100 rounded-lg cursor-not-allowed" title="Akses Terbatas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>


<script>
function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Hapus Karyawan?',
        html: `Anda akan menghapus data <b>${nama}</b> secara permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444', 
        cancelButtonColor: '#9ca3af', 
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        borderRadius: '15px',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `hapus.php?id=${id}`;
        }
    })
}

// Handler Status Notifikasi
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('status')) {
    const status = urlParams.get('status');
    const config = { confirmButtonColor: '#059669', borderRadius: '15px' };
    
    if (status === 'success') {
        Swal.fire({ ...config, title: 'Berhasil!', text: 'Data karyawan telah diproses.', icon: 'success' });
    } else if (status === 'error') {
        Swal.fire({ ...config, title: 'Gagal!', text: 'Terjadi kesalahan database.', icon: 'error' });
    } else if (status === 'forbidden') {
        Swal.fire({ ...config, title: 'Ditolak!', text: 'Anda tidak diizinkan mengubah akun ini.', icon: 'error' });
    }
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>

<?php include '../../includes/footer.php'; ?>