<?php 
ob_start(); 
require '../../includes/auth_check.php'; 
require '../../includes/role_check.php'; 
require '../../config/database.php';

// Hanya Admin dan Pejabat yang bisa melihat laporan keseluruhan
allow_roles(['admin', 'manager', 'pejabat']);

include '../../includes/header.php';
include '../../includes/sidebar.php';

// 1. Ambil filter status & pencarian
$filter_status = $_GET['status'] ?? 'Selesai';
$search = $_GET['search'] ?? '';

// 2. Query dasar (Tanpa klausa WHERE di awal)
$query_text = "SELECT pc.*, u.nama_lengkap, u.nip, jc.nama_jenis, j.nama_jabatan 
                FROM pengajuan_cuti pc 
                JOIN users u ON pc.id_user = u.id_user 
                JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis 
                LEFT JOIN jabatan j ON u.id_jabatan = j.id_jabatan";

// 3. Kumpulkan semua kondisi ke dalam array agar tidak terjadi penulisan WHERE ganda
$conditions = [];

// Tambahkan filter status
$conditions[] = "pc.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";

// Tambahkan logika pencarian jika ada input search
if (!empty($search)) {
    $s = mysqli_real_escape_string($conn, $search);
    $conditions[] = "(u.nama_lengkap LIKE '%$s%' OR jc.nama_jenis LIKE '%$s%')";
}

// 4. Gabungkan array kondisi menjadi satu string SQL yang valid
if (count($conditions) > 0) {
    $query_text .= " WHERE " . implode(" AND ", $conditions);
}

// 5. Tambahkan pengurutan
$query_text .= " ORDER BY pc.created_at DESC";

$result = mysqli_query($conn, $query_text);

// Cek error untuk memudahkan debugging
if (!$result) {
    die("Error pada query: " . mysqli_error($conn));
}
?>

<div class="container mx-auto">
    <div class="bg-white p-6 rounded-xl shadow-md border border-emerald-100">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 border-b border-gray-100 pb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-file-invoice mr-3 text-emerald-600"></i> Laporan & Arsip Cuti
                </h2>
                <p class="text-sm text-gray-500 mt-1">Daftar pengajuan yang telah selesai diproses atau ditolak.</p>
            </div>
            
            <button onclick="openReportModal()" 
                class="w-full md:w-auto bg-gradient-to-r from-emerald-600 to-emerald-700 text-white px-6 py-2.5 rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all duration-300 font-bold text-sm flex items-center justify-center shadow-lg shadow-emerald-200/50 active:scale-95">
                <i class="fas fa-file-pdf mr-2 text-emerald-100"></i> 
                Rekap Laporan
            </button>
        </div>

        <div class="flex flex-col lg:flex-row justify-between items-center mb-8 gap-6">
            
            <form method="GET" class="flex w-full lg:max-w-md gap-2">
                <input type="hidden" name="status" value="<?= $filter_status; ?>">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-search text-gray-400"></i>
                    </span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" 
                        placeholder="Cari Nama atau Jenis Cuti..." 
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-sm transition bg-gray-50/50">
                </div>
                <button type="submit" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl hover:bg-emerald-700 transition font-bold text-sm shadow-sm">
                    Cari
                </button>
            </form>

            <div class="flex bg-gray-100 p-1.5 rounded-2xl w-full lg:w-auto shadow-inner">
                <a href="?status=Selesai" 
                    class="flex-1 lg:flex-none text-center px-8 py-2 rounded-xl text-sm font-bold transition-all duration-200 <?= $filter_status === 'Selesai' ? 'bg-white text-emerald-600 shadow-md' : 'text-gray-500 hover:text-gray-700' ?>">
                    <i class="fas fa-archive mr-2"></i>Diarsipkan
                </a>
                <a href="?status=rejected" 
                    class="flex-1 lg:flex-none text-center px-8 py-2 rounded-xl text-sm font-bold transition-all duration-200 <?= $filter_status === 'rejected' ? 'bg-white text-rose-600 shadow-md' : 'text-gray-500 hover:text-gray-700' ?>">
                    <i class="fas fa-times-circle mr-2"></i>Ditolak
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-emerald-50 text-left text-emerald-700 uppercase text-xs tracking-wider">
                        <th class="p-4 border-b border-emerald-100">Data Karyawan</th>
                        <th class="p-4 border-b border-emerald-100">Jenis Cuti</th>
                        <th class="p-4 border-b border-emerald-100 text-center">Periode</th>
                        <th class="p-4 border-b border-emerald-100 text-center">Durasi</th>
                        <th class="p-4 border-b border-emerald-100">Keterangan</th>
                        <th class="p-4 border-b border-emerald-100 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php while($row = mysqli_fetch_assoc($result)) : ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200 text-sm">
                            <td class="p-4 border-b border-gray-50">
                                <div class="font-bold text-gray-800"><?= $row['nama_lengkap']; ?></div>
                                <div class="text-[10px] text-gray-500 font-mono"><?= $row['nip']; ?></div>
                                <div class="text-[10px] text-emerald-600 italic"><?= $row['nama_jabatan'] ?? '-'; ?></div>
                            </td>
                            <td class="p-4 border-b border-gray-50">
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-[10px] font-bold uppercase border border-gray-200">
                                    <?= $row['nama_jenis']; ?>
                                </span>
                            </td>
                            <td class="p-4 border-b border-gray-50 text-center">
                                <div class="text-[11px] font-medium text-gray-600">
                                    <?= date('d/m/Y', strtotime($row['tanggal_mulai'])); ?>
                                    <span class="text-gray-400 mx-1">-</span>
                                    <?= date('d/m/Y', strtotime($row['tanggal_selesai'])); ?>
                                </div>
                            </td>
                            <td class="p-4 border-b border-gray-50 text-center font-bold text-emerald-700">
                                <?= $row['jumlah_hari']; ?> Hari
                            </td>
                            <td class="p-4 border-b border-gray-50">
                                <div class="text-xs text-gray-600 max-w-[200px] truncate" title="<?= $row['alasan']; ?>">
                                    <?= $row['alasan']; ?>
                                </div>
                                <?php if($row['status'] === 'rejected' && !empty($row['komentar_reject'])) : ?>
                                    <div class="text-[10px] text-rose-500 mt-1 italic font-medium">
                                        Note: <?= $row['komentar_reject']; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 border-b border-gray-50 text-center">
                                <div class="flex justify-center gap-2">
                                    <button onclick="viewDetail(<?= htmlspecialchars(json_encode($row)); ?>)" class="text-emerald-600 hover:text-emerald-800 p-2" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <?php if ($row['status'] !== 'rejected') : ?>
                                        <a href="../cuti/cetak_word.php?id=<?= $row['id_pengajuan']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 p-2" title="Cetak Ulang">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="p-20 text-center text-gray-400">
                                <i class="fas fa-folder-open text-5xl mb-4 block opacity-20"></i>
                                <span class="italic">Belum ada data laporan untuk kategori ini.</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800">Detail Pengajuan</h3>
                <button onclick="closeDetail()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div id="detailContent" class="space-y-3 text-sm"></div>
            <div class="mt-6 text-right">
                <button onclick="closeDetail()" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg font-bold hover:bg-gray-200 transition">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div id="reportModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Cetak Rekap Cuti</h3>
                <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>

            <form action="cetak_rekap.php" method="GET" target="_blank">
                <div class="space-y-4">
                    <div>
                        <label for="pilih_bulan" class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Pilih Bulan</label>
                        
                        <select id="pilih_bulan" name="bulan" class="w-full border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                            <?php 
                            $bulan_indo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                            foreach($bulan_indo as $num => $nama): 
                                $selected = ($num == date('n')) ? 'selected' : '';
                                echo "<option value='$num' $selected>$nama</option>";
                            endforeach; 
                            ?>
                        </select>
                    </div>

                    <div>
                        <label for="pilih_tahun" class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-wider">Pilih Tahun</label>
                        
                        <select id="pilih_tahun" name="tahun" class="w-full border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                            <?php 
                            $tahun_skrg = date('Y');
                            for($i = $tahun_skrg; $i >= $tahun_skrg-5; $i--):
                                echo "<option value='$i'>$i</option>";
                            endfor;
                            ?>
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="closeReportModal()" class="flex-1 py-3 bg-gray-100 text-gray-500 rounded-xl hover:bg-gray-200 transition font-semibold text-sm">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 font-bold text-sm">
                        <i class="fas fa-print mr-2"></i>Cetak Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewDetail(data) {
    let detailKeterangan = '';
    
    if (data.status === 'rejected') {
        detailKeterangan = `
            <div class="text-rose-500 text-xs uppercase font-bold mt-2">Alasan Penolakan (Admin)</div>
            <div class="p-3 bg-rose-50 border border-rose-100 rounded-lg text-rose-700 font-medium">
                : ${data.komentar_reject || 'Tidak ada alasan spesifik'}
            </div>
        `;
    }

    const content = `
        <div class="grid grid-cols-1 gap-4">
            <div class="grid grid-cols-2 gap-2 border-b pb-2">
                <div class="text-gray-500 text-xs uppercase">Nama Karyawan</div>
                <div class="font-bold text-gray-800">: ${data.nama_lengkap}</div>
                <div class="text-gray-500 text-xs uppercase">Jenis Cuti</div>
                <div class="font-bold text-gray-800">: ${data.nama_jenis}</div>
                <div class="text-gray-500 text-xs uppercase">Alasan Cuti</div>
                <div class="font-bold text-gray-800">: ${data.alasan}</div>
                <div class="text-gray-500 text-xs uppercase">Status Akhir</div>
                <div>: <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase ${data.status === 'Selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'}">${data.status}</span></div>
            </div>
            ${detailKeterangan}
        </div>
    `;
    document.getElementById('detailContent').innerHTML = content;
    document.getElementById('detailModal').classList.remove('hidden');
}

function closeDetail() {
    document.getElementById('detailModal').classList.add('hidden');
}

function openReportModal() {
    document.getElementById('reportModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}
</script>

<?php include '../../includes/footer.php'; ?>