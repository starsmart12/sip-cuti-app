<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../vendor/autoload.php'; 
require_once '../../config/database.php';

use PhpOffice\PhpWord\TemplateProcessor;

function terbilang($angka) {
    $bil = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
    if ($angka < 12) return $bil[$angka];
    if ($angka < 20) return terbilang($angka - 10) . " belas";
    if ($angka < 100) return terbilang(intval($angka / 10)) . " puluh " . terbilang($angka % 10);
    return $angka;
}

function hariIndo($tanggal) {
    $hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    return $hari[date('l', strtotime($tanggal))];
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // 1. QUERY UTAMA: Mengambil data cuti, pengaju, dan kolom approved_by
    $query = "SELECT pc.*, u.nama_lengkap, u.nip, u.golongan, u.masa_kerja, u.no_telp, j.nama_jabatan, u.level_akses, 
              m.nama_lengkap AS nama_atasan, m.nip AS nip_atasan, jm.nama_jabatan AS jabatan_atasan
              FROM pengajuan_cuti pc 
              JOIN users u ON pc.id_user = u.id_user 
              JOIN jabatan j ON u.id_jabatan = j.id_jabatan 
              LEFT JOIN users m ON u.manager_id = m.id_user 
              LEFT JOIN jabatan jm ON m.id_jabatan = jm.id_jabatan
              WHERE pc.id_pengajuan = '$id'";

    $result = mysqli_query($conn, $query);
    if (!$result) die("Query Error: " . mysqli_error($conn));
    $data = mysqli_fetch_assoc($result);

    if (!$data) die("Data tidak ditemukan.");

    $templateProcessor = new TemplateProcessor('../../assets/templates/FORM_CUTI_fixx.docx');

    // --- A. DATA TEKS PEGAWAI ---
    $bulan_indo = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];
    
    $templateProcessor->setValue('tgl_surat', strtr(date('d F Y', strtotime($data['created_at'])), $bulan_indo));
    $templateProcessor->setValue('nama_lengkap', $data['nama_lengkap']);
    $templateProcessor->setValue('nip', $data['nip']); 
    $templateProcessor->setValue('jabatan', $data['nama_jabatan']);
    $templateProcessor->setValue('gol_ruang', $data['golongan']);
    $templateProcessor->setValue('masa_kerja', $data['masa_kerja']);
    $templateProcessor->setValue('alasan', $data['alasan']);
    $templateProcessor->setValue('jml_hari', $data['jumlah_hari']);
    $templateProcessor->setValue('terbilang', terbilang($data['jumlah_hari']));
    $templateProcessor->setValue('hari_rentang', hariIndo($data['tanggal_mulai']) . " - " . hariIndo($data['tanggal_selesai']));
    $templateProcessor->setValue('bln_thn', strtr(date('F Y', strtotime($data['tanggal_mulai'])), $bulan_indo));
    $templateProcessor->setValue('tgl_start', date('d', strtotime($data['tanggal_mulai'])));
    $templateProcessor->setValue('tgl_end', strtr(date('d F Y', strtotime($data['tanggal_selesai'])), $bulan_indo));
    $templateProcessor->setValue('alamat_cuti', $data['alamat'] ?? '-'); 
    $templateProcessor->setValue('no_telp', $data['no_telp']);
    $templateProcessor->setValue('thn_lalu', date('Y') - 1);
    $templateProcessor->setValue('thn_skrg', date('Y'));

    // --- B. CENTANG STATUS ---
    for ($i = 1; $i <= 6; $i++) { 
        $templateProcessor->setValue('c' . $i, ($data['id_jenis'] == $i) ? '√' : ''); 
    }
    
    $status_lc = strtolower($data['status'] ?? '');
    $is_setuju = ($status_lc == 'approved' || $status_lc == 'disetujui') ? '√' : '';
    $templateProcessor->setValue('s1', $is_setuju);
    $templateProcessor->setValue('s2', $is_setuju);

    // --- C. LOGIKA TANDA TANGAN ---
    $ttd_path = "../../assets/img/ttd/";
    $default_ttd = $ttd_path . "default_ttd.png";

    // 0. TTD Admin (Ambil data Admin Kepegawaian langsung dari DB)
    // Mencari user dengan role 'admin' atau level_akses yang sesuai
    $sql_admin = "SELECT nip FROM users WHERE role = 'admin' OR level_akses = 'admin' LIMIT 1";
    $res_admin = mysqli_query($conn, $sql_admin);
    $data_admin = mysqli_fetch_assoc($res_admin);

    $nip_admin_fix = $data_admin['nip'] ?? 'admin'; 
    $file_admin = $ttd_path . $nip_admin_fix . "_ttd.png";

    // Validasi file fisik
    $img_admin = (file_exists($file_admin) && !empty($nip_admin_fix)) ? $file_admin : $default_ttd;

    // Masukkan ke template
    $templateProcessor->setImageValue('paraf_admin', [
        'path' => $img_admin, 
        'width' => 60,  // Sesuaikan ukuran paraf (biasanya lebih kecil dari TTD)
        'height' => 40, 
        'ratio' => true
    ]);

    // 1. TTD Pengaju
    $file_user = $ttd_path . $data['nip'] . "_ttd.png";
    $img_user = (file_exists($file_user) && !empty($data['nip'])) ? $file_user : $default_ttd;
    $templateProcessor->setImageValue('ttd_user', ['path' => $img_user, 'width' => 100, 'height' => 60, 'ratio' => true]);

    // --- 2. TTD Atasan Langsung (Bagian VII) ---
    $raw_jab_atasan = $data['jabatan_atasan'] ?? 'ATASAN LANGSUNG';
    $parts_atasan = explode('(', $raw_jab_atasan);
    $clean_jab_atasan = strtoupper(trim($parts_atasan[0]));
    $templateProcessor->setValue('atasan_langsung_jab', $clean_jab_atasan);
    $templateProcessor->setValue('atasan_langsung_nama', $data['nama_atasan'] ?? '-');
    $file_atasan = $ttd_path . ($data['nip_atasan'] ?? 'none') . "_ttd.png";
    $img_atasan = (file_exists($file_atasan) && !empty($data['nip_atasan'])) ? $file_atasan : $default_ttd;
    $templateProcessor->setImageValue('ttd_atasan', [
        'path' => $img_atasan, 
        'width' => 100, 
        'height' => 60, 
        'ratio' => true
    ]);

    // 3. LOGIKA PEJABAT BERWENANG (Bagian VIII) - PRIORITAS DARI DATABASE
    $p_nama = "-"; $p_nip = ""; $p_jab = "";

    // CEK APAKAH SUDAH ADA ID PIMPINAN DI KOLOM approved_by
    $id_pimpinan_acc = $data['approved_by'] ?? null;

    if (!empty($id_pimpinan_acc)) {
        // Ambil data pimpinan yang benar-benar melakukan klik approve
        $sql_pacc = "SELECT u.nama_lengkap, u.nip, j.nama_jabatan 
                     FROM users u 
                     JOIN jabatan j ON u.id_jabatan = j.id_jabatan 
                     WHERE u.id_user = '$id_pimpinan_acc'";
        $res_pacc = mysqli_query($conn, $sql_pacc);
        $data_pacc = mysqli_fetch_assoc($res_pacc);


        if ($data_pacc) {
            $p_nama = $data_pacc['nama_lengkap'];
            $p_nip  = $data_pacc['nip'];
            $raw_jabatan = $data_pacc['nama_jabatan']; 
            $clean_jabatan = preg_replace('/\s*\([^)]*\)/', '', $raw_jabatan);
            if (empty(trim($clean_jabatan))) {
                $clean_jabatan = $raw_jabatan;
            }
            $p_jab = strtoupper(trim($clean_jabatan));
        }

    } else {
        // FALLBACK: Hanya jika approved_by kosong (untuk data lama), gunakan logika manual
        $sql_fallback = "SELECT u.nama_lengkap, u.nip, j.nama_jabatan 
                         FROM users u 
                         JOIN jabatan j ON u.id_jabatan = j.id_jabatan 
                         WHERE j.nama_jabatan LIKE '%Ketua%' AND j.nama_jabatan NOT LIKE '%Wakil%' LIMIT 1";
        $res_fb = mysqli_query($conn, $sql_fallback);
        $data_fb = mysqli_fetch_assoc($res_fb);
        if ($data_fb) {
            $p_nama = $data_fb['nama_lengkap'];
            $p_nip  = $data_fb['nip'];
            $p_jab  = "KETUA";
        }
    }

    $templateProcessor->setValue('pimpinan_jab', $p_jab);
    $templateProcessor->setValue('pimpinan_nama', $p_nama);
    
    $file_pimpinan = $ttd_path . $p_nip . "_ttd.png";
    $img_pimpinan = (file_exists($file_pimpinan) && !empty($p_nip)) ? $file_pimpinan : $default_ttd;
    $templateProcessor->setImageValue('ttd_pimpinan', ['path' => $img_pimpinan, 'width' => 100, 'height' => 60, 'ratio' => true]);

    // --- D. DOWNLOAD ---
    if (ob_get_contents()) ob_end_clean();
    $outputName = "Form_Cuti_" . str_replace(' ', '_', $data['nama_lengkap']) . ".docx";
    header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
    header("Content-Disposition: attachment; filename=\"" . $outputName . "\"");
    $templateProcessor->saveAs('php://output');
    exit;
}