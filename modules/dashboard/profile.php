<?php 
session_start();
require '../../includes/auth_check.php';
require '../../config/database.php';

// Ambil ID dari Session (Hanya bisa edit diri sendiri)
$id_user_login = $_SESSION['id_user'];

// Ambil data user yang sedang login
$result = mysqli_query($conn, "SELECT users.*, jabatan.nama_jabatan 
                               FROM users 
                               LEFT JOIN jabatan ON users.id_jabatan = jabatan.id_jabatan 
                               WHERE id_user = $id_user_login");
$data = mysqli_fetch_assoc($result);

$update_success = false;

if (isset($_POST['update_profile'])) {
    $nama       = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username   = mysqli_real_escape_string($conn, $_POST['username']);
    $nip        = mysqli_real_escape_string($conn, $_POST['nip']);
    $no_telp    = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $masa_kerja = mysqli_real_escape_string($conn, $_POST['masa_kerja']);

    // Logika Password
    if (!empty($_POST['password'])) {
        $password_final = password_hash($_POST['password'], PASSWORD_DEFAULT);
    } else {
        $password_final = $data['password'];
    }

    // Logika Upload Tanda Tangan
    $newName = $data['tanda_tangan']; 
    if(!empty($_FILES['tanda_tangan']['name'])) {
        $ttd_name = $_FILES['tanda_tangan']['name'];
        $ttd_tmp  = $_FILES['tanda_tangan']['tmp_name'];
        $target_dir = "../../assets/img/ttd/";
        
        $ext = strtolower(pathinfo($ttd_name, PATHINFO_EXTENSION));
        if(in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $newName = $nip . "_ttd_self." . $ext;
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
                    no_telp = '$no_telp',
                    masa_kerja = '$masa_kerja',
                    tanda_tangan = '$newName'
                  WHERE id_user = $id_user_login";

        if (mysqli_query($conn, $query)) {
            // Update Session Nama jika berubah
            $_SESSION['nama'] = $nama;
            $update_success = true;
            // Refresh data terbaru
            $data['tanda_tangan'] = $newName; 
        } else {
            $error = "Gagal memperbarui profil.";
        }
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Profil Saya</h2>
        <p class="text-gray-500 mt-1">Kelola informasi personal dan keamanan akun Anda.</p>
    </div>

    <form id="formProfile" action="" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center mb-6 border-b border-gray-50 pb-5">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center mr-4 text-emerald-600">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Data Personal</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= $data['nama_lengkap']; ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">NIP</label>
                        <input type="text" name="nip" value="<?= $data['nip']; ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-500/10">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">No. Telepon</label>
                        <input type="text" name="no_telp" value="<?= $data['no_telp']; ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-500/10">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Masa Kerja</label>
                        <input type="text" name="masa_kerja" value="<?= $data['masa_kerja']; ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-500/10">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center mb-6 border-b border-gray-50 pb-5">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center mr-4 text-emerald-600">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Keamanan Akun</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Username</label>
                        <input type="text" name="username" value="<?= $data['username']; ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-500/10" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Password Baru</label>
                        <input type="text" name="password" value="" placeholder="Password" 
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-green-500/10">
                        <p class="text-[10px] text-emerald-600 mt-2 italic">*Biarkan kosong jika tetap ingin menggunakan password lama.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-emerald-900 rounded-2xl p-8 text-white shadow-xl relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-emerald-300 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Jabatan Saat Ini</p>
                    <h4 class="text-xl font-bold text-yellow-400 mb-4"><?= $data['nama_jabatan']; ?></h4>
                    <div class="flex items-center text-sm text-emerald-100">
                        <span class="px-3 py-1 bg-emerald-800 rounded-full border border-emerald-700 text-[10px] uppercase font-bold tracking-wider">
                            Level: <?= ucfirst($data['level_akses']); ?>
                        </span>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-800 rounded-full opacity-50"></div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                <h3 class="text-[10px] font-bold text-gray-400 mb-4 uppercase tracking-widest">Tanda Tangan Digital</h3>
                <div class="mb-4 bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <img id="preview_ttd" src="../../assets/img/ttd/<?= $data['tanda_tangan']; ?>?t=<?= time(); ?>" class="h-24 mx-auto object-contain" alt="TTD">
                </div>
                <div class="relative border-2 border-dashed border-emerald-100 rounded-2xl p-4 hover:bg-emerald-50 transition-all group">
                    <input type="file" name="tanda_tangan" id="ttd_input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage()">
                    <div id="upload_placeholder">
                        <i class="fas fa-cloud-upload-alt text-emerald-200 text-2xl mb-2"></i>
                        <p class="text-[10px] text-emerald-600 font-bold">Ganti Tanda Tangan</p>
                    </div>
                </div>
            </div>

            <button type="submit" name="update_profile" class="w-full bg-emerald-800 hover:bg-emerald-950 text-yellow-400 font-black py-5 rounded-2xl shadow-lg transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center tracking-widest text-xs">
                UPDATE PROFIL
            </button>
        </div>
    </form>
</div>

<script>
function previewImage() {
    const input = document.getElementById('ttd_input');
    const preview = document.getElementById('preview_ttd');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

<?php if ($update_success): ?>
    Swal.fire({
        title: 'Profil Diperbarui!',
        text: 'Data personal Anda telah berhasil disimpan.',
        icon: 'success',
        confirmButtonColor: '#065f46',
        borderRadius: '20px'
    });
<?php endif; ?>

<?php if (isset($error)): ?>
    Swal.fire({
        title: 'Error',
        text: '<?= $error; ?>',
        icon: 'error',
        confirmButtonColor: '#b91c1c'
    });
<?php endif; ?>
</script>

<?php include '../../includes/footer.php'; ?>