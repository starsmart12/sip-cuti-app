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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-emerald-100">
        <div class="mb-6">
            <h2 class="text-3xl font-extrabold text-emerald-900 tracking-tight flex items-center">
                <i class="fas fa-paper-plane mr-3 text-emerald-600"></i> Form Pengajuan Cuti
            </h2>
            <p class="text-gray-500 mt-1">Isi formulir di bawah ini untuk mengajukan permohonan cuti.</p>
        </div>
        
        <form id="formCuti" action="" method="POST" class="space-y-6">
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
                    <label for="tanggal_mulai" class="block text-sm font-bold text-emerald-800 mb-2 uppercase tracking-wide">Tanggal Mulai</label>
                    <input id="tanggal_mulai" type="date" name="tanggal_mulai" id="tgl_mulai" class="w-full border-gray-200 border-2 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition bg-gray-50/50" required>
                </div>
                <div>
                    <label for="tanggal_selesai" class="block text-sm font-bold text-emerald-800 mb-2 uppercase tracking-wide">Tanggal Selesai</label>
                    <input id="tanggal_selesai" type="date" name="tanggal_selesai" id="tgl_selesai" class="w-full border-gray-200 border-2 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition bg-gray-50/50" required>
                </div>
            </div>
            
            <div>
                <label for="alasan" class="block text-sm font-bold text-emerald-800 mb-2 uppercase tracking-wide">Alasan Cuti</label>
                <textarea id="alasan" name="alasan" id="alasan" class="w-full border-gray-200 border-2 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition bg-gray-50/50" rows="3" placeholder="Contoh: Keperluan keluarga mendesak..." required></textarea>
            </div>

            <div>
                <label for="alamat" class="block text-sm font-bold text-emerald-800 mb-2 uppercase tracking-wide">Alamat Selama Cuti</label>
                <textarea id="alamat" name="alamat" id="alamat" class="w-full border-gray-200 border-2 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition bg-gray-50/50" rows="3" placeholder="Tuliskan alamat lengkap lokasi Anda saat cuti..." required></textarea>
            </div>

            <div class="pt-4">
                <button type="button" onclick="handleFormSubmit()" class="w-full bg-emerald-600 text-white font-extrabold py-4 rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 transform active:scale-95 flex items-center justify-center">
                    <i class="fas fa-paper-plane mr-2"></i> KIRIM PENGAJUAN SEKARANG
                </button>
            </div>
            
            <input type="hidden" name="submit_cuti" value="1">
        </form>
    </div>
</div>

<script>
function handleFormSubmit() {
    const tgl1 = document.getElementById('tgl_mulai').value;
    const tgl2 = document.getElementById('tgl_selesai').value;
    const alasan = document.getElementById('alasan').value;
    const alamat = document.getElementById('alamat').value;

    if(!tgl1 || !tgl2 || !alasan || !alamat) {
        Swal.fire({
            icon: 'error',
            title: 'Data Belum Lengkap',
            text: 'Silakan isi semua bidang formulir sebelum mengirim.',
            confirmButtonColor: '#059669',
            borderRadius: '15px'
        });
        return;
    }

    Swal.fire({
        title: 'Kirim Pengajuan?',
        html: "Pastikan data sudah benar.<br><small class='text-gray-500'>Sabtu & Minggu tidak dihitung sebagai hari kerja.</small>",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669', 
        cancelButtonColor: '#9ca3af',
        confirmButtonText: 'Ya, Kirim!',
        cancelButtonText: 'Cek Lagi',
        borderRadius: '15px',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formCuti').submit();
        }
    })
}

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('status') === 'error') {
    Swal.fire({
        title: 'Gagal!',
        text: 'Terjadi kesalahan sistem saat memproses pengajuan.',
        icon: 'error',
        confirmButtonColor: '#059669',
        borderRadius: '15px'
    });
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>

<?php include '../../includes/footer.php'; ?>