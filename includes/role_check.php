<?php
// includes/role_check.php

/**
 * Fungsi untuk membatasi akses halaman (Role-based)
 * Digunakan di bagian paling atas file (sebelum HTML)
 */
function allow_roles($allowed_roles) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: ../dashboard/index.php?status=forbidden");
        exit;
    }
}

/**
 * Fungsi untuk mengecek apakah user boleh menekan tombol/eksekusi (Access Level-based)
 * Mengembalikan nilai true atau false
 */
function can_approve() {
    $allowed_levels = ['admin', 'pejabat'];
    return isset($_SESSION['level_akses']) && in_array($_SESSION['level_akses'], $allowed_levels);
}

/**
 * Helper untuk label status di tabel agar scannable
 */
function get_status_label($status) {
    $labels = [
        'pending_admin'      => ['text' => 'Verifikasi Admin', 'color' => 'bg-gray-100 text-gray-700 border-gray-200'],
        'pending_manager'    => ['text' => 'Persetujuan Atasan', 'color' => 'bg-amber-100 text-amber-700 border-amber-200'],
        'pending_pimpinan'   => ['text' => 'Persetujuan Pimpinan', 'color' => 'bg-purple-100 text-purple-700 border-purple-200'],
        'approved'           => ['text' => 'Disetujui', 'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
        'rejected'           => ['text' => 'Ditolak', 'color' => 'bg-red-100 text-red-700 border-red-200']
    ];
    return $labels[$status] ?? ['text' => $status, 'color' => 'bg-gray-50 text-gray-500'];
}