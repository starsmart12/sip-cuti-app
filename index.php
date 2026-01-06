<?php
session_start();

// Jika sudah login, lempar ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: modules/dashboard/index.php");
    exit;
} else {
    // Jika belum login, lempar ke halaman login
    header("Location: login.php");
    exit;
}
?>