<?php
require '../../includes/auth_check.php';
require '../../config/database.php';

$id_pengajuan = $_GET['id'];

if (isset($_POST['proses_tolak'])) {
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan_admin']);
    
    $query = "UPDATE pengajuan_cuti SET 
                status = 'rejected', 
                keterangan_admin = '$keterangan' 
              WHERE id_pengajuan = $id_pengajuan";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Pengajuan telah ditolak.'); window.location='approve.php';</script>";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="max-w-lg bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4 text-red-600">Konfirmasi Penolakan Cuti</h2>
    <form action="" method="POST">
        <label class="block mb-2 font-semibold">Alasan Penolakan:</label>
        <textarea name="keterangan_admin" class="w-full border p-2 rounded mb-4" rows="4" placeholder="Sebutkan alasan mengapa cuti tidak disetujui..." required></textarea>
        
        <div class="flex gap-2">
            <button type="submit" name="proses_tolak" class="bg-red-600 text-white px-4 py-2 rounded">Konfirmasi Tolak</button>
            <a href="approve.php" class="bg-gray-400 text-white px-4 py-2 rounded">Batal</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>