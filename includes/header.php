<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIP-CUTI</title>
    <link href="/sip_cuti/assets/css/style.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo_pn.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="flex h-screen overflow-hidden bg-gray-50/50 text-gray-800 font-sans">
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-emerald-950/50 backdrop-blur-sm z-20 hidden transition-opacity duration-300"></div>
        