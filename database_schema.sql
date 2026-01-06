-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Jan 2026 pada 03.19
-- Versi server: 10.4.20-MariaDB
-- Versi PHP: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_cuti_karyawan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `jabatan`
--

CREATE TABLE `jabatan` (
  `id_jabatan` int(11) NOT NULL,
  `nama_jabatan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_cuti`
--

CREATE TABLE `jenis_cuti` (
  `id_jenis` int(11) NOT NULL,
  `nama_jenis` varchar(50) NOT NULL,
  `potong_jatah` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `aktivitas` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajuan_cuti`
--

CREATE TABLE `pengajuan_cuti` (
  `id_pengajuan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_jenis` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jumlah_hari` int(11) NOT NULL,
  `alasan` text DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status` varchar(60) DEFAULT 'pending_admin',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `keterangan_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `acc_admin_at` timestamp NULL DEFAULT NULL,
  `acc_manager_at` timestamp NULL DEFAULT NULL,
  `acc_verifikasi_at` timestamp NULL DEFAULT NULL,
  `acc_pimpinan_at` timestamp NULL DEFAULT NULL,
  `is_printed` tinyint(1) DEFAULT 0,
  `tgl_cetak` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `tanda_tangan` varchar(255) DEFAULT NULL,
  `nip` varchar(25) DEFAULT NULL,
  `golongan` varchar(50) DEFAULT NULL,
  `masa_kerja` varchar(50) DEFAULT NULL,
  `role` enum('admin','manager','karyawan') NOT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `level_akses` enum('staff','pejabat','admin') DEFAULT 'staff',
  `manager_id` int(11) DEFAULT NULL,
  `jatah_cuti_tahunan` int(11) DEFAULT 12,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  ADD PRIMARY KEY (`id_jabatan`);

--
-- Indeks untuk tabel `jenis_cuti`
--
ALTER TABLE `jenis_cuti`
  ADD PRIMARY KEY (`id_jenis`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  ADD PRIMARY KEY (`id_pengajuan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_jenis` (`id_jenis`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_jabatan` (`id_jabatan`),
  ADD KEY `manager_id` (`manager_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  MODIFY `id_jabatan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jenis_cuti`
--
ALTER TABLE `jenis_cuti`
  MODIFY `id_jenis` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  MODIFY `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Ketidakleluasaan untuk tabel `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  ADD CONSTRAINT `pengajuan_cuti_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `pengajuan_cuti_ibfk_2` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_cuti` (`id_jenis`),
  ADD CONSTRAINT `pengajuan_cuti_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id_user`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id_jabatan`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
