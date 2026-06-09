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
        :root {
            --primary-pink: hsl(340, 75%, 92%);
            --primary-pink-dark: hsl(340, 70%, 75%);
            --secondary-lavender: hsl(265, 60%, 93%);
            --accent-purple: #7C4DFF;
            --accent-pink: #E8628C;
            --text-dark: #2A2F35;
            --text-muted: #6C7A89;
            --bg-glass: rgba(255, 255, 255, 0.45);
            --bg-glass-card: rgba(255, 255, 255, 0.65);
            --glass-border: rgba(255, 255, 255, 0.5);
            --shadow-soft: 0 10px 30px rgba(232, 98, 140, 0.08);
            --shadow-hover: 0 15px 35px rgba(232, 98, 140, 0.15);
        }

        body {
            background: linear-gradient(135deg, var(--primary-pink), var(--secondary-lavender));
            color: var(--text-dark);
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Navbar Glassmorphism */
        .admin-navbar {
            backdrop-filter: blur(15px);
            background: var(--bg-glass);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 2rem;
            z-index: 100;
        }

        .admin-navbar .logo {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-dark);
            text-decoration: none;
            background: linear-gradient(45deg, var(--accent-pink), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        /* Sidebar Glassmorphism */
        .sidebar-card {
            backdrop-filter: blur(15px);
            background: var(--bg-glass-card);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            padding: 1.5rem;
            height: calc(100vh - 120px);
            position: sticky;
            top: 100px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin-bottom: 0.8rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1.2rem;
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-nav a i {
            font-size: 1.15rem;
            color: var(--text-muted);
            transition: color 0.3s;
        }

        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.5);
            transform: translateX(5px);
            color: var(--accent-pink);
        }

        .sidebar-nav a:hover i {
            color: var(--accent-pink);
        }

        .sidebar-nav a.active {
            background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple));
            color: white;
            box-shadow: 0 4px 15px rgba(232, 98, 140, 0.3);
        }

        .sidebar-nav a.active i {
            color: white;
        }

        /* Content Area Glassmorphism */
        .glass-card {
            backdrop-filter: blur(15px);
            background: var(--bg-glass-card);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            padding: 2rem;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .glass-card:hover {
            box-shadow: var(--shadow-hover);
        }

        .stat-icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Button styles */
        .btn-premium {
            background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(232, 98, 140, 0.2);
            transition: all 0.3s;
        }
        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(232, 98, 140, 0.35);
            color: white;
        }

        .btn-premium-outline {
            background: transparent;
            color: var(--text-dark);
            border: 2px solid var(--glass-border);
            border-radius: 12px;
            padding: 0.5rem 1.4rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-premium-outline:hover {
            background: var(--bg-glass);
            border-color: var(--accent-pink);
            color: var(--accent-pink);
        }

        /* Tables styling */
        .custom-table {
            border-collapse: separate;
            border-spacing: 0 8px;
            width: 100%;
        }

        .custom-table th {
            border: none;
            color: var(--text-muted);
            font-weight: 600;
            padding: 1rem;
        }

        .custom-table tr {
            transition: transform 0.2s;
        }

        .custom-table tbody tr {
            background: rgba(255, 255, 255, 0.4);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.01);
        }

        .custom-table tbody tr:hover {
            transform: scale(1.005);
            background: rgba(255, 255, 255, 0.7);
        }

        .custom-table td {
            border: none;
            padding: 1.2rem 1rem;
            vertical-align: middle;
        }

        .custom-table td:first-child, .custom-table th:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .custom-table td:last-child, .custom-table th:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        /* Status Pills */
        .badge-status {
            padding: 0.4rem 0.8rem;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-ordered { background-color: #e0f2fe; color: #0369a1; }
        .status-processing { background-color: #fef3c7; color: #d97706; }
        .status-shipped { background-color: #f3e8ff; color: #7e22ce; }
        .status-delivered { background-color: #dcfce7; color: #15803d; }
        .status-cancelled { background-color: #fee2e2; color: #b91c1c; }

        .status-paid { background-color: #dcfce7; color: #15803d; }
        .status-pending { background-color: #fef3c7; color: #d97706; }
        .status-failed { background-color: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
    <header class="admin-navbar shadow-sm">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a href="dashboard.php" class="logo">
                CraftyGifts <span style="font-family: 'Outfit', sans-serif; font-size: 0.85rem; font-weight: 500; vertical-align: middle; opacity: 0.85; margin-left: 5px;">Admin Control Panel</span>
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
                    <h5 class="mb-4 text-muted small uppercase fw-bold tracking-wider">Navigation</h5>
                    <ul class="sidebar-nav">
