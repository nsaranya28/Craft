<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/auth.php';

// Enforce admin login
if (!isAdminLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | CraftyGifts Admin' : 'CraftyGifts Admin'; ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom Admin Styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        :root {
            --primary: #E25F84;
            --primary-light: #F2A1B7;
            --primary-dark: #C44E6F;
            --text: #442E3C;
            --text-light: #9E8394;
            --white: #FFFFFF;
            --cream: #FFFBFB;
            --pink-50: #FFF3F5;
            --pink-100: #FFE4EA;
            --pink-200: #FBC5D2;
            --pink-300: #F4A2B8;
            --pink-400: #EB7A96;
            --pink-500: #E25F84;
            --bg-glass: rgba(255, 255, 255, 0.45);
            --bg-glass-card: rgba(255, 255, 255, 0.75);
            --glass-border: rgba(232, 98, 140, 0.12);
            --shadow-soft: 0 10px 30px rgba(232, 98, 140, 0.08);
            --shadow-hover: 0 15px 35px rgba(232, 98, 140, 0.15);
        }

        body {
            background: linear-gradient(135deg, #fdf4f7 0%, #fce4ee 50%, #ffe0f0 100%);
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
        }
        /* Floating hearts & bows */
        body::before {
            content: '♡';
            position: fixed;
            top: 5%; left: 3%;
            font-size: 3rem;
            color: var(--pink-200);
            opacity: 0.15;
            animation: floatHeart 8s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }
        body::after {
            content: '🎀';
            position: fixed;
            bottom: 8%; right: 5%;
            font-size: 2rem;
            color: var(--pink-200);
            opacity: 0.12;
            animation: floatBow 6s ease-in-out infinite 2s;
            pointer-events: none;
            z-index: 0;
        }
        .deco-heart-1 {
            content: '♥';
            position: fixed;
            top: 20%; right: 4%;
            font-size: 1.8rem;
            color: var(--pink-200);
            opacity: 0.1;
            animation: floatHeart 7s ease-in-out infinite 1s;
            pointer-events: none;
            z-index: 0;
        }
        .deco-heart-2 {
            content: '♡';
            position: fixed;
            bottom: 30%; left: 5%;
            font-size: 1.5rem;
            color: var(--pink-300);
            opacity: 0.08;
            animation: floatBow 9s ease-in-out infinite 3s;
            pointer-events: none;
            z-index: 0;
        }
        @keyframes floatHeart {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-18px) scale(1.05); }
        }
        @keyframes floatBow {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(5deg); }
        }
        h1, h2, h3, h4, h5, h6, .font-serif {
            font-family: 'Playfair Display', serif;
        }

        /* Navbar */
        .admin-navbar {
            backdrop-filter: blur(15px);
            background: rgba(255,255,255,0.85);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 2rem;
            z-index: 100;
            position: relative;
        }
        .admin-navbar .logo {
            font-family: 'Playfair Display', serif;
            font-weight: 800;
            font-size: 1.5rem;
            text-decoration: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        .admin-navbar .logo::before {
            content: '♥ ';
            font-size: 1rem;
        }
        .admin-navbar .logo::after {
            content: ' ♥';
            font-size: 1rem;
        }

        /* Sidebar */
        .sidebar-card {
            backdrop-filter: blur(15px);
            background: var(--bg-glass-card);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: var(--shadow-soft);
            padding: 1.5rem;
            height: calc(100vh - 120px);
            position: sticky;
            top: 100px;
            overflow: hidden;
        }
        .sidebar-card::before {
            content: '🎀';
            position: absolute;
            top: -5px; right: -5px;
            font-size: 1.2rem;
            opacity: 0.2;
            transform: rotate(15deg);
            pointer-events: none;
        }
        .sidebar-card::after {
            content: '♥';
            position: absolute;
            bottom: 10px; left: 10px;
            font-size: 0.9rem;
            color: var(--pink-200);
            opacity: 0.15;
            pointer-events: none;
        }
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-nav li { margin-bottom: 0.6rem; }
        .sidebar-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-light);
            padding: 0.5rem 0.2rem 0.2rem;
            font-weight: 600;
        }
        .sidebar-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--pink-200), transparent);
            margin: 0.8rem 0;
            opacity: 0.3;
            list-style: none;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.1rem;
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            border-radius: 14px;
            transition: all 0.3s ease;
        }
        .sidebar-nav a i { font-size: 1.05rem; color: var(--text-light); transition: color 0.3s; }
        .sidebar-nav a:hover {
            background: var(--pink-50);
            transform: translateX(4px);
            color: var(--primary);
        }
        .sidebar-nav a:hover i { color: var(--primary); }
        .sidebar-nav a.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            box-shadow: 0 4px 15px rgba(232,98,140,0.25);
        }
        .sidebar-nav a.active i { color: white; }

        /* Glass Card */
        .glass-card {
            backdrop-filter: blur(15px);
            background: var(--bg-glass-card);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: var(--shadow-soft);
            padding: 1.75rem;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        .glass-card:hover { box-shadow: var(--shadow-hover); }
        .glass-card::after {
            content: '♡';
            position: absolute;
            bottom: 8px; right: 12px;
            font-size: 0.8rem;
            color: var(--pink-200);
            opacity: 0.15;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .glass-card:hover::after {
            opacity: 0.3;
            animation: heartbeat 1s ease-in-out;
        }
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.15); }
            50% { transform: scale(1.1); }
            75% { transform: scale(1.15); }
        }

        .stat-icon-wrapper {
            width: 52px; height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        /* Buttons */
        .btn-premium {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(232,98,140,0.2);
            transition: all 0.3s;
        }
        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(232,98,140,0.35);
            color: white;
        }
        .btn-premium-outline {
            background: transparent;
            color: var(--text);
            border: 2px solid var(--pink-200);
            border-radius: 12px;
            padding: 0.5rem 1.4rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-premium-outline:hover {
            background: var(--pink-50);
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Tables */
        .custom-table {
            border-collapse: separate;
            border-spacing: 0 8px;
            width: 100%;
        }
        .custom-table th {
            border: none;
            color: var(--text-light);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0.8rem 1rem;
        }
        .custom-table tbody tr {
            background: rgba(255,255,255,0.5);
            border-radius: 14px;
            transition: all 0.2s;
        }
        .custom-table tbody tr:hover {
            transform: scale(1.005);
            background: rgba(255,255,255,0.8);
        }
        .custom-table td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
        }
        .custom-table td:first-child, .custom-table th:first-child { border-radius: 14px 0 0 14px; }
        .custom-table td:last-child, .custom-table th:last-child { border-radius: 0 14px 14px 0; }

        /* Status Pills */
        .badge-status {
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .status-ordered { background: #e0f2fe; color: #0369a1; }
        .status-processing { background: #fef3c7; color: #d97706; }
        .status-shipped { background: #f3e8ff; color: #7e22ce; }
        .status-delivered { background: #dcfce7; color: #15803d; }
        .status-cancelled { background: #fee2e2; color: #b91c1c; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-failed { background: #fee2e2; color: #b91c1c; }

        .uppercase { text-transform: uppercase; }
        .tracking-wider { letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="deco-heart-1">♥</div>
    <div class="deco-heart-2">♡</div>
    <header class="admin-navbar shadow-sm">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a href="dashboard.php" class="logo">
                CraftyGifts <span style="font-size: 0.85rem; font-weight: 500; vertical-align: middle; opacity: 0.85; margin-left: 5px;">Admin Control Panel</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-dark small d-none d-md-inline"><i class="fa-regular fa-user me-2"></i>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'Admin'); ?></strong></span>
                <a href="../auth/logout.php" class="btn btn-premium-outline btn-sm"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout</a>
            </div>
        </div>
    </header>

    <div class="container-fluid py-4 flex-grow-1">
        <div class="row">
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="sidebar-card">
                    <h5 class="mb-4 small fw-semibold text-uppercase" style="letter-spacing: 1px; color: var(--text-light);">🎀 Menu ♡</h5>
                    <ul class="sidebar-nav">
