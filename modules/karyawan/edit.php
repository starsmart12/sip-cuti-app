<?php 
require '../../includes/auth_check.php';
require '../../includes/role_check.php';
require '../../config/database.php';

// Hanya role admin yang boleh akses halaman ini
allow_roles(['admin']);

// Ambil ID dari URL
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : null;
if (!$id) {
    header("Location: index.php");
    exit;
}

// Ambil data user yang akan diedit (Target)
$result = mysqli_query($conn, "SELECT * FROM users WHERE id_user = $id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// --- LOGIKA PROTEKSI HIRARKI ---
$lvl_login = strtolower($_SESSION['level_akses']); 
$target_lvl = strtolower($data['level_akses']);

if ($lvl_login === 'staff' && $target_lvl === 'admin') {
    header("Location: index.php?status=forbidden");
    exit;
}

$update_success = false;

// Logika saat tombol simpan ditekan
if (isset($_POST['update'])) {
    $nama       = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username   = mysqli_real_escape_string($conn, $_POST['username']);
    $role       = $_POST['role'];
    $lvl_akses  = $_POST['level_akses'];
    $jabatan    = $_POST['id_jabatan'];
    $manager    = !empty($_POST['manager_id']) ? $_POST['manager_id'] : "NULL";
    $jatah      = $_POST['jatah_cuti'];
    $nip        = mysqli_real_escape_string($conn, $_POST['nip']);
    $golongan   = mysqli_real_escape_string($conn, $_POST['golongan']);
    $no_telp    = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $masa_kerja = mysqli_real_escape_string($conn, $_POST['masa_kerja']);

    // --- LOGIKA PASSWORD (HASHING SAAT SIMPAN) ---
    // Jika input password diisi, buat hash baru. Jika kosong, gunakan hash lama dari database.
    if (!empty($_POST['password'])) {
        $password_final = password_hash($_POST['password'], PASSWORD_DEFAULT);
    } else {
        $password_final = $data['password'];
    }

    if ($lvl_login === 'staff' && $lvl_akses === 'admin') {
        $error = "Anda tidak memiliki wewenang untuk memberikan akses Super Admin.";
    }

    // --- LOGIKA UPLOAD TANDA TANGAN ---
    $newName = $data['tanda_tangan']; 
    if(!empty($_FILES['tanda_tangan']['name']) && !isset($error)) {
        $ttd_name = $_FILES['tanda_tangan']['name'];
        $ttd_tmp  = $_FILES['tanda_tangan']['tmp_name'];
        $target_dir = "../../assets/img/ttd/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $ext = strtolower(pathinfo($ttd_name, PATHINFO_EXTENSION));
        if(in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $newName = $nip . "_ttd." . $ext;
            if($data['tanda_tangan'] != $newName && $data['tanda_tangan'] != 'default_ttd.png') {
                if(file_exists($target_dir . $data['tanda_tangan'])) { unlink($target_dir . $data['tanda_tangan']); }
            }
            move_uploaded_file($ttd_tmp, $target_dir . $newName);
        } else {
            $error = "Format file tidak didukung! Gunakan PNG atau JPG.";
        }
    }

    if (!isset($error)) {
        $query = "UPDATE users SET 
                    username = '$username',
                    password = '$password_final',
                    nama_lengkap = '$nama',
                    nip = '$nip',
                    golongan = '$golongan',
                    no_telp = '$no_telp',
                    masa_kerja = '$masa_kerja',
                    tanda_tangan = '$newName',
                    role = '$role',
                    level_akses = '$lvl_akses',
                    id_jabatan = $jabatan,
                    manager_id = $manager,
                    jatah_cuti_tahunan = $jatah
                  WHERE id_user = $id";

        if (mysqli_query($conn, $query)) {
            $update_success = true;
        } else {
            $error = "Gagal Update: " . mysqli_error($conn);
        }
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Edit Profil Karyawan</h2>
            <p class="text-gray-500 mt-1">Perbarui informasi data diri dan wewenang akses pegawai.</p>
        </div>
        <a href="index.php" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-600 hover:bg-gray-50 hover:text-green-600 transition shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Daftar
        </a>
    </div>

    <?php if(isset($error)): ?>
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl">
            <p class="font-bold uppercase text-xs tracking-widest mb-1">Terjadi Kesalahan</p>
            <p class="text-sm"><?php echo $error; ?></p>
        </div>
    <?php endif; ?>

    <form id="formUpdate" action="" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center mb-8 border-b border-gray-50 pb-5">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Informasi Dasar Pegawai</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="nama_lengkap" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nama Lengkap & Gelar</label>
                        <input id="nama_lengkap" type="text" name="nama_lengkap" value="<?= $data['nama_lengkap']; ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none" required>
                    </div>
                    <div>
                        <label for="nip" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">NIP</label>
                        <input id="nip" type="text" name="nip" value="<?= $data['nip']; ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10" required>
                    </div>
                    <div>
                        <label for="no_telp" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">No. Telepon / WA</label>
                        <input id="no_telp" type="text" name="no_telp" value="<?= $data['no_telp']; ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10">
                    </div>
                    <div>
                        <label for="golongan" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Golongan</label>
                        <input id="golongan" type="text" name="golongan" value="<?= $data['golongan']; ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10">
                    </div>
                    <div>
                        <label for="masa_kerja" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Masa Kerja</label>
                        <input id="masa_kerja" type="text" name="masa_kerja" value="<?= $data['masa_kerja']; ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center mb-8 border-b border-gray-50 pb-5">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Akses Sistem</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Username</label>
                        <input id="username" type="text" name="username" value="<?= $data['username']; ?>" autocomplete="username" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10" required>
                    </div>
                    <div>
                        <label for="password" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Password Login</label>
                        <input id="password" type="text" name="password" value="" placeholder="Password" 
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10">
                        <p class="text-[11px] text-emerald-600 mt-2 italic">*Biarkan kosong jika tetap ingin menggunakan password lama.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 rounded-full mr-4"></span>
                    Jabatan & Hirarki
                </h3>
                <div class="space-y-5">
                    <div>
                        <label for="id_jabatan" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Jabatan</label>
                        <select id="id_jabatan" name="id_jabatan" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none">
                            <?php 
                            $jab_q = mysqli_query($conn, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
                            while($j = mysqli_fetch_assoc($jab_q)) :
                            ?>
                                <option value="<?= $j['id_jabatan']; ?>" <?= $data['id_jabatan'] == $j['id_jabatan'] ? 'selected' : ''; ?>>
                                    <?= $j['nama_jabatan']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="level_akses" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Level Akses Cuti</label>
                        <select id="level_akses" name="level_akses" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none">
                            <option value="staff" <?= $data['level_akses'] == 'staff' ? 'selected' : ''; ?>>Staff (Pemohon)</option>
                            <option value="pejabat" <?= $data['level_akses'] == 'pejabat' ? 'selected' : ''; ?>>Pejabat (Pemberi ACC)</option>
                            <?php if ($lvl_login === 'admin'): ?>
                                <option value="admin" <?= $data['level_akses'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label for="manager_id" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Atasan Langsung</label>
                        <select id="manager_id" name="manager_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none">
                            <option value="">-- Tanpa Atasan --</option>
                            <?php 
                            $mgr_q = mysqli_query($conn, "SELECT id_user, nama_lengkap FROM users WHERE level_akses = 'pejabat' AND id_user != $id ORDER BY nama_lengkap ASC");
                            while($m = mysqli_fetch_assoc($mgr_q)) :
                            ?>
                                <option value="<?= $m['id_user']; ?>" <?= $data['manager_id'] == $m['id_user'] ? 'selected' : ''; ?>>
                                    <?= $m['nama_lengkap']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="role" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Role Menu</label>
                            <select id="role" name="role" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-3 text-sm outline-none">
                                <?php if ($lvl_login === 'admin'): ?>
                                    <option value="admin" <?= $data['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <?php endif; ?>
                                <option value="manager" <?= $data['role'] == 'manager' ? 'selected' : ''; ?>>Manager</option>
                                <option value="karyawan" <?= $data['role'] == 'karyawan' ? 'selected' : ''; ?>>Karyawan</option>
                            </select>
                        </div>
                        <div>
                            <label for="jatah_cuti" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Jatah Cuti</label>
                            <input id="jatah_cuti" type="number" name="jatah_cuti" value="<?= $data['jatah_cuti_tahunan']; ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-3 text-sm outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-widest">Tanda Tangan Digital</h3>
                <div class="mb-4">
                    <p class="text-[10px] text-gray-400 mb-2 uppercase">File Saat Ini:</p>
                    <img src="../../assets/img/ttd/<?= $data['tanda_tangan']; ?>?t=<?= time(); ?>" class="h-20 mx-auto border rounded-lg p-2 bg-gray-50" alt="TTD">
                </div>
                <div class="relative border-2 border-dashed border-gray-200 rounded-2xl p-4 hover:bg-green-50 transition-all group">
                    <input type="file" name="tanda_tangan" id="ttd_input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateFileName()">
                    <div id="upload_placeholder">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <p class="text-[10px] text-gray-400">Klik untuk ganti TTD</p>
                    </div>
                    <div id="file_info" class="hidden">
                         <p id="file_name_display" class="text-[10px] font-bold text-green-700 truncate"></p>
                    </div>
                </div>
            </div>

            <button type="button" onclick="confirmUpdate()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-5 rounded-2xl shadow-lg transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m13 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                SIMPAN PERUBAHAN
            </button>
            <input type="hidden" name="update" value="1">
        </div>
    </form>
</div>

<script>
function updateFileName() {
    const input = document.getElementById('ttd_input');
    const placeholder = document.getElementById('upload_placeholder');
    const fileInfo = document.getElementById('file_info');
    const nameDisplay = document.getElementById('file_name_display');

    if (input.files.length > 0) {
        placeholder.classList.add('hidden');
        fileInfo.classList.remove('hidden');
        nameDisplay.textContent = input.files[0].name;
    }
}

function confirmUpdate() {
    const form = document.getElementById('formUpdate');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    Swal.fire({
        title: 'Simpan Perubahan?',
        text: "Pastikan data pegawai yang diperbarui sudah benar.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: 'Ya, Update!',
        cancelButtonText: 'Batal',
        borderRadius: '15px'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    })
}

<?php if ($update_success): ?>
    Swal.fire({
        title: 'Berhasil!',
        text: 'Data pegawai telah diperbarui.',
        icon: 'success',
        confirmButtonColor: '#059669',
        borderRadius: '15px'
    }).then(() => {
        window.location = 'index.php';
    });
<?php endif; ?>
</script>

<?php include '../../includes/footer.php'; ?>