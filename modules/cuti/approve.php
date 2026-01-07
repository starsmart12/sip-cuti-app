<?php 
// 1. Tambahkan ob_start agar tidak error "headers already sent"
ob_start(); 
require '../../includes/auth_check.php'; 
// 2. Sertakan role_check.php untuk fungsi allow_roles() dan can_approve()
require '../../includes/role_check.php'; 
require '../../config/database.php';

// --- START LOGIC ARSIP ---
if (isset($_POST['aksi_selesai'])) {
    $id_pengajuan = mysqli_real_escape_string($conn, $_POST['id_pengajuan']);
    $update = mysqli_query($conn, "UPDATE pengajuan_cuti SET status = 'Selesai' WHERE id_pengajuan = '$id_pengajuan'");
    if ($update) {
        $id_u = $_SESSION['id_user'];
        mysqli_query($conn, "INSERT INTO log_aktivitas (id_user, aktivitas) VALUES ('$id_u', 'Mengarsipkan pengajuan ID #$id_pengajuan')");
        header("Location: approve.php?pesan=Pengajuan berhasil diarsipkan");
        exit;
    }
}
// --- END LOGIC ARSIP ---

// 3. Batasi akses halaman: Admin dan Manager/Pejabat
allow_roles(['admin', 'manager', 'pejabat']);

include '../../includes/header.php';
include '../../includes/sidebar.php';

$id_user_login = $_SESSION['id_user'];
$role_login = $_SESSION['role'];
$jabatan_user = $_SESSION['jabatan'] ?? ''; 
$level_akses_tampil = $_SESSION['level_akses'] ?? 'Staff';

/**
 * LOGIKA FILTER DATA BERDASARKAN JABATAN & ROLE
 */

if ($role_login === 'admin') {
    $query = "SELECT pc.*, u.nama_lengkap, u.nip, u.level_akses, jc.nama_jenis, j.nama_jabatan 
              FROM pengajuan_cuti pc 
              JOIN users u ON pc.id_user = u.id_user 
              JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis 
              LEFT JOIN jabatan j ON u.id_jabatan = j.id_jabatan
              WHERE (pc.status = 'pending_admin' OR (pc.status = 'approved' AND pc.is_printed = 0))
              AND pc.status != 'Selesai'
              ORDER BY CASE WHEN pc.status = 'pending_admin' THEN 1 ELSE 2 END ASC, pc.created_at ASC";
} else {
    $filters = [];

    // 1. ATASAN LANGSUNG (General - Termasuk Panitera untuk stafnya)
    $filters[] = "(pc.status = 'pending_manager' AND u.manager_id = $id_user_login)";

    // 2. KASUBAG KEPEGAWAIAN
    if ($jabatan_user === 'Kasubag Kepegawaian') {
        $filters[] = "pc.status = 'pending_kasub'";
    } 
    
    // 3. KABAG
    if (strpos($jabatan_user, 'Kabag') !== false) {
        $filters[] = "pc.status = 'pending_kabag'";
    } 
    
    // 4. SEKRETARIS
    if ($jabatan_user === 'Sekretaris') {
        $filters[] = "pc.status = 'pending_sekretaris'"; // Jalur admin wajib
    } 

    // 5. KETUA & WAKIL KETUA (Pimpinan)
        if (in_array($jabatan_user, ['Ketua', 'Wakil Ketua Pengadilan', 'Ketua Pengadilan'])) {
            
            // Cek apakah user saat ini adalah WAKIL
            $is_wakil = (strpos($jabatan_user, 'Wakil') !== false);

            if ($is_wakil) {
                // Wakil hanya melihat pengajuan Staff Biasa (pending_pimpinan)
                $filters[] = "pc.status = 'pending_pimpinan'";
            } else {
                // Ketua melihat jalur umum (pending_pimpinan) 
                // DAN jalur khusus Hakim/Panitera/Sekretaris (pending_ketua)
                $filters[] = "pc.status = 'pending_pimpinan'";
                $filters[] = "pc.status = 'pending_ketua'";
            }
        }

// ... (Bagian atas tetap sama)

    $where_clause = !empty($filters) ? "(" . implode(" OR ", $filters) . ")" : "1=0";

    /**
     * LOGIKA KHUSUS:
     * 1. Secara umum, user tidak bisa melihat/acc pengajuannya sendiri (pc.id_user != $id_user_login).
     * 2. PENGECUALIAN: Jika user yang login adalah Kasubag, Kabag, atau Sekretaris, 
     * maka mereka BOLEH melihat pengajuan mereka sendiri untuk di-acc sesuai alur pusat.
     */
    
    $jabatan_boleh_self_acc = ['Kasubag Kepegawaian', 'Sekretaris'];
    $is_pejabat_mandiri = false;

    // Cek apakah jabatan mengandung kata 'Kabag', 'Kasubag', atau 'Sekretaris'
    if (strpos($jabatan_user, 'Kasubag Kepegawaian') !== false || 
        strpos($jabatan_user, 'Kepala Bagian (Kabag)') !== false || 
        $jabatan_user === 'Sekretaris') {
        $is_pejabat_mandiri = true;
    }

    if ($is_pejabat_mandiri) {
        // Pejabat khusus ini bisa melihat pengajuan miliknya sendiri
        $final_where = "$where_clause";
    } else {
        // User lain (Staf, Hakim, Panitera, dll) tetap dilarang acc diri sendiri
        $final_where = "$where_clause AND pc.id_user != $id_user_login";
    }

    // Query untuk menampilkan data
    $query = "SELECT pc.*, u.nama_lengkap, u.nip, u.level_akses, jc.nama_jenis, j.nama_jabatan 
              FROM pengajuan_cuti pc 
              JOIN users u ON pc.id_user = u.id_user 
              JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis 
              LEFT JOIN jabatan j ON u.id_jabatan = j.id_jabatan
              WHERE $final_where
              ORDER BY pc.created_at ASC";
}
$result = mysqli_query($conn, $query);
?>

<div class="container mx-auto">
    <div class="bg-white p-6 rounded-xl shadow-md border border-emerald-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-clipboard-check mr-3 text-emerald-600"></i> Persetujuan Cuti Karyawan
            </h2>
            <span class="text-[10px] bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full font-mono uppercase tracking-widest border border-emerald-100">
                Akses: <?= htmlspecialchars($level_akses_tampil); ?>
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-emerald-50 text-left text-emerald-700 uppercase text-xs tracking-wider">
                        <th class="p-4 border-b border-emerald-100">Karyawan</th>
                        <th class="p-4 border-b border-emerald-100">Jenis Cuti</th>
                        <th class="p-4 border-b border-emerald-100 text-center">Periode Tanggal</th>
                        <th class="p-4 border-b border-emerald-100 text-center">Durasi</th>
                        <th class="p-4 border-b border-emerald-100">Tahapan Status</th>
                        <th class="p-4 border-b border-emerald-100 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php while($row = mysqli_fetch_assoc($result)) : 
                            $status_info = get_status_label($row['status']); 
                            $final_color = str_replace('blue', 'emerald', $status_info['color']);
                        ?>
                        <tr class="hover:bg-emerald-50/50 transition-colors duration-200 text-sm">
                            <td class="p-4 border-b border-gray-50">
                                <div class="font-medium"><?= $row['nama_lengkap']; ?></div>
                                <div class="text-[10px] text-gray-500 font-mono"><?= $row['nip']; ?></div>
                                <div class="text-[10px] text-emerald-600 italic"><?= $row['nama_jabatan'] ?? '-'; ?> (<?= $row['level_akses']; ?>)</div>
                            </td>
                            <td class="p-4 border-b border-gray-50">
                                <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-[10px] font-bold uppercase">
                                    <?= $row['nama_jenis']; ?>
                                </span>
                            </td>
                            <td class="p-4 border-b border-gray-50 text-center">
                                <div class="text-[11px] font-semibold text-gray-600 bg-gray-50 px-2 py-1 rounded-md inline-block border border-gray-100">
                                    <i class="far fa-calendar-alt text-emerald-500 mr-1"></i>
                                    <?= date('d/m/Y', strtotime($row['tanggal_mulai'])); ?> 
                                    <span class="mx-1 text-gray-400">s/d</span>
                                    <?= date('d/m/Y', strtotime($row['tanggal_selesai'])); ?>
                                </div>
                            </td>
                            <td class="p-4 border-b border-gray-50 text-center font-bold text-emerald-600"><?= $row['jumlah_hari']; ?> Hari</td>
                            <td class="p-4 border-b border-gray-50">
                                <span class="<?= $final_color; ?> px-2 py-1 rounded text-[10px] font-bold border flex items-center w-max">
                                    <span class="relative flex h-2 w-2 mr-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-current opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-current"></span>
                                    </span>
                                    <?= $status_info['text']; ?>
                                </span>
                            </td>
                           <td class="p-4 border-b border-gray-50 text-center whitespace-nowrap">
                                <?php if ($row['status'] === 'approved') : ?>
                                    <div class="flex gap-2 justify-center">
                                        <a href="cetak_word.php?id=<?= $row['id_pengajuan']; ?>" target="_blank"
                                        class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-emerald-700 transition shadow-sm inline-block">
                                        <i class="fas fa-print mr-1"></i> Cetak
                                        </a>
                                        
                                        <?php if ($role_login === 'admin') : ?>
                                       <button onclick="openFinishModal(<?= $row['id_pengajuan']; ?>, '<?= addslashes($row['nama_lengkap']); ?>')" 
                                            class="inline-flex items-center bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-700 transition shadow-sm shadow-indigo-100 active:scale-95">
                                            <i class="fas fa-check-double mr-1.5"></i> Selesai
                                        </button>
                                        <?php endif; ?>
                                    </div>

                                <?php elseif (can_approve()) : ?>
                                    <button onclick="openApproveModal(<?= $row['id_pengajuan']; ?>, '<?= addslashes($row['nama_lengkap']); ?>', '<?= $row['status']; ?>')" 
                                            class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-emerald-700 transition shadow-sm mr-2">
                                    <i class="fas fa-check mr-1"></i> Setujui
                                    </button>
                                    
                                    <button onclick="openRejectModal(<?= $row['id_pengajuan']; ?>, '<?= $row['status']; ?>')" 
                                            class="bg-rose-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-rose-700 transition shadow-sm">
                                    <i class="fas fa-times mr-1"></i> Tolak
                                    </button>
                                <?php else : ?>
                                    <span class="text-gray-400 italic text-xs">Hanya Lihat</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="p-12 text-center text-gray-400 border-b">
                                <i class="fas fa-inbox text-4xl mb-3 block text-gray-200"></i>
                                <span class="italic text-sm">Tidak ada antrean pengajuan saat ini.</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="approveModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity"></div>
    
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 transform transition-all text-center border border-white/20">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-emerald-50 mb-6">
                <i class="fas fa-check text-emerald-600 text-3xl"></i>
            </div>
            <h3 class="text-2xl font-extrabold text-slate-800 mb-2">Teruskan Proses</h3>
            <p class="text-slate-500 mb-8 text-sm px-4">Setujui pengajuan <span id="approve_nama" class="font-bold text-slate-900"></span> dan kirim ke tahap selanjutnya?</p>

            <div class="flex flex-col gap-3">
                <a id="approve_link" href="#" class="w-full py-4 bg-emerald-600 text-white rounded-2xl hover:bg-emerald-700 transition-all font-bold shadow-lg shadow-emerald-200 active:scale-95">
                    Ya, Setujui
                </a>
                <button onclick="closeApproveModal()" class="w-full py-4 bg-slate-100 text-slate-500 rounded-2xl hover:bg-slate-200 transition font-bold">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Tolak Pengajuan</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="proses_approve.php" method="GET">
                <input type="hidden" name="id" id="reject_id">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="current_status" id="reject_status">
                
                <div class="mb-6">
                    <label for="komentar" class="block text-sm font-bold text-gray-700 mb-2">Alasan Penolakan</label>
                    <textarea id="komentar" name="komentar" class="w-full border border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-rose-500 focus:border-transparent outline-none transition duration-200 text-sm" rows="4" placeholder="Tuliskan alasan penolakan..." required></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeRejectModal()" class="flex-1 py-3 bg-gray-100 text-gray-500 rounded-xl hover:bg-gray-200 transition font-semibold">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-rose-600 text-white rounded-xl hover:bg-rose-700 transition shadow-lg shadow-rose-200 font-bold">
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="finishModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 transform transition-all text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-slate-100 mb-6">
                <i class="fas fa-archive text-slate-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Arsip Pengajuan</h3>
            <p class="text-gray-500 mb-8 text-sm px-4">Selesaikan pengajuan <span id="finish_nama" class="font-bold text-gray-700"></span>? Data akan dipindahkan ke laporan arsip.</p>

            <form action="" method="POST">
                <input type="hidden" name="id_pengajuan" id="finish_id">
                <div class="flex flex-col gap-2">
                    <button type="submit" name="aksi_selesai" class="w-full py-3 bg-slate-800 text-white rounded-xl hover:bg-black transition font-bold shadow-lg">
                        Ya, Selesaikan
                    </button>
                    <button type="button" onclick="closeFinishModal()" class="w-full py-3 bg-gray-100 text-gray-500 rounded-xl hover:bg-gray-200 transition font-semibold">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if(isset($_GET['pesan'])) : ?>
<div id="toast" class="fixed bottom-5 right-5 z-[60] transform transition-all duration-500 translate-y-0">
    <div class="bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl border-l-4 border-emerald-500 flex items-center">
        <span class="font-medium"><?= htmlspecialchars($_GET['pesan']); ?></span>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-6 text-gray-400 hover:text-white">&times;</button>
    </div>
</div>
<script>setTimeout(() => { document.getElementById('toast')?.remove(); }, 4000);</script>
<?php endif; ?>

<script>
if (window.history.replaceState) {
    const url = new URL(window.location.href);
    if (url.searchParams.has('pesan')) {
        url.searchParams.delete('pesan');
        window.history.replaceState({}, document.title, url.pathname + url.search);
    }
}

function openApproveModal(id, nama, status) {
    document.getElementById('approve_nama').innerText = nama;
    document.getElementById('approve_link').href = 'proses_approve.php?id=' + id + '&action=approve&current_status=' + status;
    document.getElementById('approveModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openRejectModal(id, status) {
    document.getElementById('reject_id').value = id;
    document.getElementById('reject_status').value = status;
    document.getElementById('rejectModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openFinishModal(id, nama) {
    document.getElementById('finish_id').value = id;
    document.getElementById('finish_nama').innerText = nama;
    document.getElementById('finishModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFinishModal() {
    document.getElementById('finishModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

window.onclick = function(e) {
    if (e.target.id === 'approveModal') closeApproveModal();
    if (e.target.id === 'rejectModal') closeRejectModal();
    if (e.target.id === 'finishModal') closeFinishModal();
}

</script>

<?php include '../../includes/footer.php'; ?>