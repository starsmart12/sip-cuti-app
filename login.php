<?php
session_start();
require 'config/database.php';

// Logic Login dengan Verifikasi Hash
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 

    $sql = "SELECT users.*, jabatan.nama_jabatan 
            FROM users 
            LEFT JOIN jabatan ON users.id_jabatan = jabatan.id_jabatan 
            WHERE users.username = '$username'";
            
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // --- PERUBAHAN LOGIKA DI SINI ---
        // Menggunakan password_verify untuk mencocokkan input dengan hash di database
        if (password_verify($password, $row['password'])) { 
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['nama'] = $row['nama_lengkap'];
            $_SESSION['level_akses'] = $row['level_akses']; 
            $_SESSION['jabatan'] = $row['nama_jabatan']; 
            
            header("Location: modules/dashboard/index.php");
            exit;
        }
    }
    // Jika tidak ditemukan atau password_verify mengembalikan false
    $error = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - SIP Cuti PN Makassar</title>
    
    <link rel="icon" type="image/png" href="assets/img/logo_pn.png">
    
    <link href="/sip_cuti/assets/css/style.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="bg-emerald-900 min-h-screen flex items-center justify-center p-4 sm:p-6 relative overflow-hidden">
    
    <div class="bg-blob absolute top-0 left-0 w-64 h-64 sm:w-96 sm:h-96 bg-emerald-800 rounded-full -translate-x-1/2 -translate-y-1/2 opacity-50"></div>
    <div class="bg-blob absolute bottom-0 right-0 w-64 h-64 sm:w-96 sm:h-96 bg-yellow-500 rounded-full translate-x-1/2 translate-y-1/2 opacity-20"></div>

    <div class="w-full max-w-[400px] relative z-10 transition-all duration-500">
        <div class="glass-effect rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl overflow-hidden border border-emerald-100/20">
            
            <div class="bg-emerald-950/5 p-6 sm:p-8 text-center border-b border-gray-100">
                <img src="assets/img/logo_pn.png" alt="Logo PN" class="h-16 sm:h-20 mx-auto mb-4 drop-shadow-md">
                <h2 class="text-2xl sm:text-3xl font-black text-emerald-900 tracking-tight leading-none">SIP-CUTI</h2>
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600 uppercase tracking-[0.2em] mt-2 px-4 leading-tight">
                    Pengadilan Negeri Makassar
                </p>
            </div>

            <div class="p-6 sm:p-8 md:p-10">
                <?php if(isset($error)) : ?>
                    <div class="mb-6 flex items-center bg-red-50 border-l-4 border-red-500 p-4 rounded-xl animate-pulse">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3 text-sm"></i>
                        <p class="text-red-700 text-[11px] sm:text-xs font-bold uppercase tracking-wide">Username / Password Salah</p>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-5 sm:space-y-6">
                    <div>
                        <label for="username" class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest mb-2 ml-1">Username</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-600 group-focus-within:text-yellow-500 transition-colors">
                                <i class="fas fa-user text-sm"></i>
                            </span>
                            <input id="username" type="text" name="username" autocomplete="username" required 
                                class="block w-full pl-11 pr-4 py-3.5 sm:py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-yellow-400 focus:border-transparent outline-none transition-all text-sm sm:text-base"
                                placeholder="Username">
                        </div>
                    </div>

                    <div>
                        <label for="passwordInput" class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest mb-2 ml-1">Password</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-600 group-focus-within:text-yellow-500 transition-colors">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input type="password" name="password" id="passwordInput" required
                                class="block w-full pl-11 pr-12 py-3.5 sm:py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-yellow-400 focus:border-transparent outline-none transition-all text-sm sm:text-base"
                                placeholder="••••••••">
                            
                            <button type="button" onclick="togglePassword()" 
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-emerald-600 focus:outline-none">
                                <i class="fas fa-eye text-lg" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="login" 
                        class="btn-active w-full py-3.5 sm:py-4 bg-emerald-800 hover:bg-emerald-950 text-yellow-400 font-extrabold rounded-2xl shadow-lg shadow-emerald-900/20 transition-all tracking-[0.2em] uppercase text-xs sm:text-sm flex items-center justify-center gap-3">
                        SIGN IN
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
                </form>

                <div class="mt-8 sm:mt-10 text-center">
                    <p class="text-gray-400 text-[8px] sm:text-[9px] font-bold uppercase tracking-[0.15em] leading-relaxed italic">
                        © 2026 PN Makassar <br>
                        <span class="text-emerald-800 not-italic">Sistem Informasi Permohonan Cuti Digital</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>