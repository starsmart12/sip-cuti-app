<?php 
require '../../includes/auth_check.php';
require '../../config/database.php';

// Proteksi halaman: pastikan hanya admin yang bisa akses
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

$save_success = false;
// Ambil data session untuk pengecekan otoritas
$role_login = $_SESSION['role'];
$lvl_login  = $_SESSION['level_akses']; 

if(isset($_POST['save'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    
    $check_user = mysqli_query($conn, "SELECT username FROM users WHERE username = '$user'");
    
    if(mysqli_num_rows($check_user) > 0) {
        $error = "Username '$user' sudah terdaftar!";
    } else {
        $nama       = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $pass       = password_hash($_POST['password'], PASSWORD_DEFAULT); 
        $nip        = mysqli_real_escape_string($conn, $_POST['nip']);
        $golongan   = mysqli_real_escape_string($conn, $_POST['golongan']);
        $masa_kerja = mysqli_real_escape_string($conn, $_POST['masa_kerja']);
        $telp       = mysqli_real_escape_string($conn, $_POST['no_telp']);
        $role       = $_POST['role'];
        $lvl_akses  = $_POST['level_akses'];
        $jab        = $_POST['id_jabatan'];
        $mgr        = !empty($_POST['manager_id']) ? $_POST['manager_id'] : "NULL";
        $jatah      = $_POST['jatah_cuti_tahunan'];

        // --- PROTEKSI KEAMANAN (SERVER-SIDE) ---
        // Hanya Super Admin (Role: admin & Level: admin) yang boleh membuat akun admin
        if (!($role_login === 'admin' && $lvl_login === 'admin')) {
            if ($lvl_akses === 'admin') $lvl_akses = 'staff';
            if ($role === 'admin') $role = 'karyawan';
        }

        // --- UPLOAD TANDA TANGAN ---
        $ttd_name = $_FILES['tanda_tangan']['name'];
        $ttd_tmp  = $_FILES['tanda_tangan']['tmp_name'];
        if(!empty($ttd_name)) {
            $target_dir = "../../assets/img/ttd/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $ext = strtolower(pathinfo($ttd_name, PATHINFO_EXTENSION));
            $newName = $nip . "_ttd." . $ext; 
            move_uploaded_file($ttd_tmp, $target_dir . $newName);
        } else {
            $newName = "default_ttd.png";
        }

        if(!isset($error)) {
            $sql = "INSERT INTO users (
                        username, password, nama_lengkap, no_telp, tanda_tangan, 
                        nip, golongan, masa_kerja, role, id_jabatan, 
                        level_akses, manager_id, jatah_cuti_tahunan
                    ) VALUES (
                        '$user', '$pass', '$nama', '$telp', '$newName', 
                        '$nip', '$golongan', '$masa_kerja', '$role', $jab, 
                        '$lvl_akses', $mgr, $jatah
                    )";

            if(mysqli_query($conn, $sql)) {
                $save_success = true;
            } else {
                $error = mysqli_error($conn);
            }
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
            <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Manajemen Karyawan</h2>
            <p class="text-gray-500 mt-1">Pendaftaran pegawai baru ke dalam SIP-Cuti.</p>
        </div>
        <a href="index.php" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-600 hover:bg-gray-50 hover:text-green-600 transition shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Daftar
        </a>
    </div>

    <?php if(isset($error)): ?>
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
            <p class="font-bold">Gagal Menyimpan:</p>
            <p class="text-sm"><?php echo $error; ?></p>
        </div>
    <?php endif; ?>

    <form id="formTambah" action="" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center mb-8 border-b border-gray-50 pb-5">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Informasi Profil Pegawai</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nama Lengkap & Gelar</label>
                        <input type="text" name="nama_lengkap" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">NIP</label>
                        <input type="text" name="nip" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nomor Telepon/WA</label>
                        <input type="text" name="no_telp" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Golongan / Pangkat</label>
                        <select name="golongan" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none cursor-pointer">
                            <option value="">Pilih Golongan...</option>
                            <?php $gols = ["II/a", "III/a", "III/b", "IV/a"]; 
                            foreach($gols as $g) echo "<option value='$g'>$g</option>"; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Masa Kerja</label>
                        <input type="text" name="masa_kerja" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10 transition-all">
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
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Username</label>
                        <input type="text" name="username" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Password Login</label>
                        <input type="password" name="password" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10 transition-all">
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 rounded-full mr-4"></span>
                    Struktur & Hirarki
                </h3>
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Jabatan Satker</label>
                        <select name="id_jabatan" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none">
                            <?php 
                            $j = mysqli_query($conn, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
                            while($rj = mysqli_fetch_assoc($j)) echo "<option value='".$rj['id_jabatan']."'>".$rj['nama_jabatan']."</option>"; 
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Level Akses Cuti</label>
                        <select name="level_akses" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none">
                            <option value="staff">Staff (Pemohon)</option>
                            <option value="pejabat">Pejabat (Pemberi ACC)</option>
                            <?php if ($role_login === 'admin' && $lvl_login === 'admin') : ?>
                                <option value="admin">Admin (Kepegawaian)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Atasan Langsung</label>
                        <select name="manager_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none">
                            <option value="">-- Tanpa Atasan --</option>
                            <?php 
                            $m = mysqli_query($conn, "SELECT id_user, nama_lengkap FROM users WHERE level_akses='pejabat' ORDER BY nama_lengkap ASC"); 
                            while($rm = mysqli_fetch_assoc($m)) echo "<option value='".$rm['id_user']."'>".$rm['nama_lengkap']."</option>"; 
                            ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Role Menu</label>
                            <select name="role" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-3 text-sm outline-none">
                                <option value="karyawan">Karyawan</option>
                                <option value="manager">Manager</option>
                                <?php if ($role_login === 'admin' && $lvl_login === 'admin') : ?>
                                    <option value="admin">Admin</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Jatah Cuti</label>
                            <input type="number" name="jatah_cuti_tahunan" value="12" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-3 text-sm outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-widest">Tanda Tangan Digital</h3>
                
                <div id="preview_container" class="hidden mb-4 transition-all">
                    <div class="relative w-full h-32 bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-center overflow-hidden p-2">
                        <img id="ttd_preview" src="#" alt="Preview TTD" class="max-h-full object-contain">
                        <button type="button" onclick="resetFile()" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <div id="upload_ui" class="relative border-2 border-dashed border-gray-200 rounded-2xl p-6 hover:bg-green-50 hover:border-green-300 transition-all group">
                    <input type="file" name="tanda_tangan" id="ttd_input" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewTTD()">
                    <div id="upload_placeholder">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <p class="text-xs text-gray-400 font-medium">Klik untuk Pilih Gambar TTD</p>
                    </div>
                </div>
            </div>

            <button type="button" onclick="confirmSave()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-5 rounded-2xl shadow-lg shadow-green-200 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                SIMPAN PEGAWAI
            </button>
            <input type="hidden" name="save" value="1">
        </div>
    </form>
</div>

<script>
function previewTTD() {
    const input = document.getElementById('ttd_input');
    const preview = document.getElementById('ttd_preview');
    const container = document.getElementById('preview_container');
    const uploadUi = document.getElementById('upload_ui');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('hidden');
            uploadUi.classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function resetFile() {
    document.getElementById('ttd_input').value = "";
    document.getElementById('preview_container').classList.add('hidden');
    document.getElementById('upload_ui').classList.remove('hidden');
}

function confirmSave() {
    const form = document.getElementById('formTambah');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    Swal.fire({
        title: 'Simpan Data?',
        text: "Pastikan data pegawai yang diinput sudah benar.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        confirmButtonText: 'Ya, Simpan!',
        cancelButtonText: 'Batal',
        borderRadius: '15px'
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    })
}

<?php if ($save_success): ?>
    Swal.fire({
        title: 'Berhasil!',
        text: 'Karyawan baru telah ditambahkan ke sistem.',
        icon: 'success',
        confirmButtonColor: '#059669',
        borderRadius: '15px'
    }).then(() => {
        window.location = 'index.php';
    });
<?php endif; ?>
</script>

<?php include '../../includes/footer.php'; ?>