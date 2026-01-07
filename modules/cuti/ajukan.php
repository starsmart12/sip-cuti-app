<?php 
// 1. Tambahkan ob_start untuk menghindari error header
ob_start(); 
require '../../includes/auth_check.php'; 
// Tidak perlu require database.php karena sudah dipanggil di auth_check.php

// Proses Simpan Data
if (isset($_POST['submit_cuti'])) {
    $id_user = $_SESSION['id_user'];
    $id_jenis = mysqli_real_escape_string($conn, $_POST['id_jenis']);
    $tgl_mulai = $_POST['tanggal_mulai'];
    $tgl_selesai = $_POST['tanggal_selesai'];
    $alasan = mysqli_real_escape_string($conn, $_POST['alasan']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']); 

    // --- LOGIKA HITUNG HARI KERJA (Senin-Jumat) ---
    $start = new DateTime($tgl_mulai);
    $end = new DateTime($tgl_selesai);
    $end->modify('+1 day'); 

    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end);

    $jumlah_hari = 0;
    foreach ($period as $dt) {
        $curr = $dt->format('N'); 
        if ($curr < 6) { 
            $jumlah_hari++;
        }
    }

    // PERBAIKAN LOGIC: Status awal diarahkan ke pending_admin agar masuk ke meja Admin dulu
    $sql = "INSERT INTO pengajuan_cuti (id_user, id_jenis, tanggal_mulai, tanggal_selesai, jumlah_hari, alasan, alamat, status) 
            VALUES ('$id_user', '$id_jenis', '$tgl_mulai', '$tgl_selesai', '$jumlah_hari', '$alasan', '$alamat', 'pending_admin')";

    if (mysqli_query($conn, $sql)) {
        header("Location: history.php?status=success");
        exit();
    } else {
        header("Location: ajukan.php?status=error");
        exit();
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>


<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-emerald-100">
        <div class="mb-6">
            <h2 class="text-3xl font-extrabold text-emerald-900 tracking-tight flex items-center">
                <i class="fas fa-paper-plane mr-3 text-emerald-600"></i> Form Pengajuan Cuti
            </h2>
            <p class="text-gray-500 mt-1">Isi formulir di bawah ini untuk mengajukan permohonan cuti.</p>
        </div>
        
        <form id="formCuti" action="" method="POST" class="space-y-6">
            <div id="errorBanner" class="hidden bg-red-50 border-l-4 border-red-500 p-4 rounded-xl mb-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-sm text-red-700 font-bold">Harap melengkapi semua formulir sebelum mengirim!</p>
                </div>
            </div>
            <div>
                <label for ="id_jenis" class="block text-sm font-bold text-emerald-800 mb-2 uppercase tracking-wide">Jenis Cuti</label>
                <select id ="id_jenis" name="id_jenis" class="w-full border-gray-200 border-2 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition bg-gray-50/50" required>
                    <option value="" disabled selected>Pilih kategori cuti...</option>
                    <?php 
                    $jenis = mysqli_query($conn, "SELECT * FROM jenis_cuti");
                    while($j = mysqli_fetch_assoc($jenis)) {
                        echo "<option value='".$j['id_jenis']."'>".$j['nama_jenis']."</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="tgl_mulai" class="block text-sm font-bold text-emerald-800 mb-2 uppercase tracking-wide">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" id="tgl_mulai" class="w-full border-gray-200 border-2 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition bg-gray-50/50" required>
                </div>
                <div>
                    <label for="tgl_selesai" class="block text-sm font-bold text-emerald-800 mb-2 uppercase tracking-wide">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" id="tgl_selesai" class="w-full border-gray-200 border-2 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition bg-gray-50/50" required>
                </div>
            </div>
            
            <div>
                <label for="alasan" class="block text-sm font-bold text-emerald-800 mb-2 uppercase tracking-wide">Alasan Cuti</label>
                <textarea name="alasan" id="alasan" class="w-full border-gray-200 border-2 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition bg-gray-50/50" rows="3" placeholder="Contoh: Keperluan keluarga mendesak..." required></textarea>
            </div>

            <div>
                <label for="alamat" class="block text-sm font-bold text-emerald-800 mb-2 uppercase tracking-wide">Alamat Selama Cuti</label>
                <textarea name="alamat" id="alamat" class="w-full border-gray-200 border-2 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition bg-gray-50/50" rows="3" placeholder="Tuliskan alamat lengkap lokasi Anda saat cuti..." required></textarea>
            </div>

            <div class="pt-4">
                <button type="button" onclick="handleFormSubmit()" class="w-full bg-emerald-600 text-white font-extrabold py-4 rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 transform active:scale-95 flex items-center justify-center">
                    <i class="fas fa-paper-plane mr-2"></i> KIRIM PENGAJUAN SEKARANG
                </button>
            </div>
            
            <input type="hidden" name="submit_cuti" value="1">
        </form>

        <div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-2xl max-w-md w-full p-8 shadow-2xl">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-100 mb-6">
                            <i class="fas fa-question text-2xl text-emerald-600"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Kirim Pengajuan Cuti?</h3>
                        <p class="text-gray-500 mb-8">Pastikan data sudah benar. Sabtu & Minggu tidak akan dihitung sebagai hari kerja.</p>
                        
                        <div class="flex space-x-3">
                            <button type="button" onclick="closeConfirmModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition">
                                Cek Lagi
                            </button>
                            <button type="button" onclick="submitForm()" class="flex-1 px-4 py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition">
                                Ya, Kirim!
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fungsi untuk memunculkan modal konfirmasi
function handleFormSubmit() {
    // Ambil ID yang sudah kita rapikan (pakai tgl_mulai, tgl_selesai, dll)
    const tgl1 = document.getElementById('tgl_mulai').value;
    const tgl2 = document.getElementById('tgl_selesai').value;
    const alasan = document.getElementById('alasan').value;
    const alamat = document.getElementById('alamat').value;
    const jenis = document.getElementById('id_jenis').value;

    // Validasi Sederhana
    if(!tgl1 || !tgl2 || !alasan || !alamat || !jenis) {
            // Tampilkan Banner Error
            errorBanner.classList.remove('hidden');
            
            // Scroll otomatis ke atas agar banner terlihat
            errorBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Hilangkan banner otomatis setelah 3 detik
            setTimeout(() => {
                errorBanner.classList.add('hidden');
            }, 4000);
            
            return;
        }

    // Jika valid, sembunyikan banner dan buka modal konfirmasi
    errorBanner.classList.add('hidden');
    document.getElementById('confirmModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Tampilkan Modal Konfirmasi (Kita pakai modal Tailwind)
    document.getElementById('confirmModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Kunci scroll layar
}

// Fungsi untuk menutup modal
function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    document.body.style.overflow = 'auto'; // Aktifkan scroll kembali
}

// Fungsi eksekusi kirim form
function submitForm() {
    document.getElementById('formCuti').submit();
}

// Cek status error dari URL (setelah redirect)
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('status') === 'error') {
    alert("Gagal! Terjadi kesalahan sistem saat memproses pengajuan.");
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>

<?php include '../../includes/footer.php'; ?>