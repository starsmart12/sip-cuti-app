<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_path = $_SERVER['PHP_SELF'];
?>

<aside id="mainSidebar" class="fixed inset-y-0 left-0 w-64 bg-emerald-900 text-white flex-shrink-0 flex flex-col shadow-xl z-30 transform -translate-x-full lg:relative lg:translate-x-0 transition-all duration-300">
    <div class="lg:hidden absolute right-4 top-4">
        <button onclick="toggleSidebar()" class="text-emerald-300 focus:outline-none">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <div class="flex flex-col p-6 border-b border-emerald-800 items-center justify-center bg-emerald-950/30">
        <div class="text-2xl font-bold text-yellow-400 tracking-tight uppercase">SIP-CUTI</div>
        <span class="text-[9px] text-emerald-300 uppercase tracking-widest font-semibold mt-1 text-center leading-tight">Pengadilan Negeri Makassar</span>
    </div>
    
    <nav class="mt-4 flex-1 overflow-y-auto custom-scroll">
        <a href="../dashboard/index.php" 
           class="flex items-center py-3 px-6 transition-all duration-200 border-l-4 group 
           <?= ($current_page == 'index.php' && strpos($current_path, 'dashboard') !== false && strpos($current_path, 'profile.php') === false) ? 'border-yellow-400 bg-emerald-800/50 text-yellow-400 font-bold' : 'border-transparent text-emerald-100 hover:bg-emerald-800 hover:text-white' ?>">
            <i class="fas fa-chart-line w-6 group-hover:scale-110 transition-transform"></i> 
            <span>Dashboard</span>
        </a>
        
        <?php if ($_SESSION['role'] == 'admin') : ?>
        <a href="../karyawan/index.php" 
           class="flex items-center py-3 px-6 transition-all duration-200 border-l-4 group 
           <?= (strpos($current_path, 'karyawan') !== false) ? 'border-yellow-400 bg-emerald-800/50 text-yellow-400 font-bold' : 'border-transparent text-emerald-100 hover:bg-emerald-800 hover:text-white' ?>">
            <i class="fas fa-users w-6 group-hover:scale-110 transition-transform"></i> 
            <span>Data Karyawan</span>
        </a>
        <?php endif; ?>

        <?php if (($_SESSION['jabatan'] ?? '') !== 'Ketua Pengadilan') : ?>
            <a href="../cuti/ajukan.php" 
            class="flex items-center py-3 px-6 transition-all duration-200 border-l-4 group 
            <?= ($current_page == 'ajukan.php') ? 'border-yellow-400 bg-emerald-800/50 text-yellow-400 font-bold' : 'border-transparent text-emerald-100 hover:bg-emerald-800 hover:text-white' ?>">
                <i class="fas fa-paper-plane w-6 group-hover:scale-110 transition-transform"></i> 
                <span>Ajukan Cuti</span>
            </a>
        <?php endif; ?>

        <?php if (($_SESSION['jabatan'] ?? '') !== 'Ketua Pengadilan') : ?>
            <a href="../cuti/history.php" 
            class="flex items-center py-3 px-6 transition-all duration-200 border-l-4 group 
            <?= ($current_page == 'history.php' || $current_page == 'cetak_cuti.php') ? 'border-yellow-400 bg-emerald-800/50 text-yellow-400 font-bold' : 'border-transparent text-emerald-100 hover:bg-emerald-800 hover:text-white' ?>">
                <i class="fas fa-history w-6 group-hover:scale-110 transition-transform"></i> 
                <span>Riwayat Cuti</span>
            </a>
        <?php endif; ?>

        <?php if ($_SESSION['level_akses'] == 'pejabat' || $_SESSION['level_akses'] == 'admin') : ?>
        <a href="../cuti/approve.php" 
           class="flex items-center py-3 px-6 transition-all duration-200 border-l-4 group 
           <?= ($current_page == 'approve.php') ? 'border-yellow-400 bg-emerald-800/50 text-yellow-400 font-bold' : 'border-transparent text-emerald-100 hover:bg-emerald-800 hover:text-white' ?>">
            <i class="fas fa-check-circle w-6 group-hover:scale-110 transition-transform"></i> 
            <span>Approval</span>
        </a>
        <?php endif; ?>

        <?php if ($_SESSION['level_akses'] == 'admin') : ?>
        <a href="../laporan/index.php" 
            class="flex items-center py-3 px-6 transition-all duration-200 border-l-4 group 
            <?= (strpos($current_path, 'laporan') !== false) ? 'border-yellow-400 bg-emerald-800/50 text-yellow-400 font-bold' : 'border-transparent text-emerald-100 hover:bg-emerald-800 hover:text-white' ?>">
                <i class="fas fa-file-alt w-6 group-hover:scale-110 transition-transform"></i> 
                <span>Laporan & Arsip</span>
        </a>
        <?php endif; ?>
        
        <a href="../dashboard/profile.php" 
           class="flex items-center py-3 px-6 transition-all duration-200 border-l-4 group 
           <?= ($current_page == 'profile.php') ? 'border-yellow-400 bg-emerald-800/50 text-yellow-400 font-bold' : 'border-transparent text-emerald-100 hover:bg-emerald-800 hover:text-white' ?>">
            <i class="fas fa-user-circle w-6 group-hover:scale-110 transition-transform"></i> 
            <span>Profil Saya</span>
        </a>

    </nav>

    <div class="p-4 border-t border-emerald-800">
        <button onclick="openLogoutModal()" class="w-full flex items-center py-3 px-6 bg-emerald-950/50 hover:bg-red-700 transition-all duration-300 rounded-xl group">
            <i class="fas fa-sign-out-alt w-6 group-hover:translate-x-1 transition-transform text-red-400 group-hover:text-white"></i> 
            <span class="font-bold tracking-wide text-sm">LOGOUT</span>
        </button>
    </div>
</aside>

<div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
    <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b-2 border-emerald-50 z-10">
        <div class="flex items-center gap-2 md:gap-4">
            <button onclick="toggleSidebar()" class="p-2 text-emerald-900 focus:outline-none hover:bg-emerald-50 rounded-lg transition-colors">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <img src="<?= BASE_URL ?>assets/img/logo_pn.png" alt="Logo PN" class="h-8 md:h-10 w-auto">
            <div class="flex flex-col">
                <span class="text-emerald-900 font-black leading-none uppercase text-[10px] md:text-xs tracking-tight">SIP-CUTI DIGITAL</span>
                <span class="text-gray-400 text-[8px] md:text-[9px] font-bold uppercase tracking-tighter">PN Makassar Kelas I A Khusus</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="../dashboard/profile.php" class="text-[9px] md:text-[10px] bg-emerald-50 text-emerald-800 px-3 md:px-4 py-2 rounded-full uppercase font-black border border-emerald-100 shadow-sm flex items-center gap-2 hover:bg-emerald-100 transition-colors">
                <i class="fas fa-user-circle text-emerald-600 text-sm"></i> 
                <span class="hidden sm:inline"><?= $_SESSION['nama']; ?></span> 
                <span class="text-emerald-400 font-normal hidden sm:inline">|</span> 
                <?= $_SESSION['role']; ?>
            </a>
        </div>
    </header>
    <main class="p-4 md:p-8 flex-1 overflow-y-auto custom-scroll bg-gray-50/30">