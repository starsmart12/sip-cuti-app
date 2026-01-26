<?php
/**
 * Fungsi untuk menghitung masa kerja secara dinamis
 * @param string $tanggal_masuk Format: YYYY-MM-DD
 * @return string Contoh: "5 Tahun 2 Bulan"
 */
function hitungMasaKerja($tanggal_masuk) {
    if (!$tanggal_masuk || $tanggal_masuk == '0000-00-00') return "-";

    $tgl_awal  = new DateTime($tanggal_masuk);
    $tgl_skrg  = new DateTime(); // Otomatis mengambil tanggal hari ini
    $diff      = $tgl_awal->diff($tgl_skrg);

    $hasil = [];
    if ($diff->y > 0) $hasil[] = $diff->y . " Tahun";
    if ($diff->m > 0) $hasil[] = $diff->m . " Bulan";
    
    // Jika masa kerja baru hitungan hari
    if (empty($hasil)) {
        return "Baru Bergabung";
    }

    return implode(" ", $hasil);
}