<footer class="py-6 border-t border-gray-100 bg-white w-full mt-10">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500 font-medium">
                <div class="flex items-center space-x-2">
                    <i class="far fa-copyright text-emerald-600"></i>
                    <span><?= date('Y'); ?> <span class="text-emerald-900 font-bold underline decoration-emerald-200">Pengadilan Negeri Makassar</span>. Seluruh Hak Cipta Dilindungi.</span>
                </div>
                <div class="flex items-center space-x-4 mt-3 md:mt-0">
                    <div class="flex items-center bg-gray-50 border border-emerald-100 px-3 py-1.5 rounded-xl shadow-sm">
                        <span class="relative flex h-2 w-2 mr-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-tighter">System Version 2.4.0</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</main> 
</div> 

<div id="logoutModal" class="fixed inset-0 z-[2000] hidden">
    <div class="fixed inset-0 bg-emerald-950/60 backdrop-blur-sm transition-opacity" onclick="closeLogoutModal()"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 transform transition-all border-t-8 border-yellow-400 text-center">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6">
                <i class="fas fa-sign-out-alt text-red-600 text-3xl animate-pulse"></i>
            </div>
            <h3 class="text-2xl font-black text-emerald-900 mb-2 font-sans">Logout Sistem?</h3>
            <p class="text-gray-500 mb-8 text-sm leading-relaxed px-4 font-medium">Sesi Anda akan diakhiri. Pastikan semua tugas Anda telah selesai.</p>
            <div class="space-y-3">
                <a href="<?= BASE_URL ?>logout.php" class="block w-full py-4 bg-red-600 text-white rounded-2xl hover:bg-red-700 transition-all font-bold shadow-lg shadow-red-200 tracking-wide text-sm">YA, KELUAR</a>
                <button onclick="closeLogoutModal()" class="w-full py-4 bg-gray-100 text-gray-600 rounded-2xl hover:bg-gray-200 transition-all font-bold uppercase text-[10px] tracking-[0.2em]">KEMBALI</button>
            </div>
        </div>
    </div>
</div>

<script>
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        const isMobile = window.innerWidth < 1024;
        if (isMobile) {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        } else {
            sidebar.classList.toggle('sidebar-closed');
        }
    }

    function openLogoutModal() { document.getElementById('logoutModal').classList.remove('hidden'); }
    function closeLogoutModal() { document.getElementById('logoutModal').classList.add('hidden'); }

    window.onkeydown = function(e) {
        if (e.key === "Escape") {
            closeLogoutModal();
            if (window.innerWidth < 1024 && sidebar && !sidebar.classList.contains('-translate-x-full')) toggleSidebar();
        }
    }
</script>
</body>
</html>