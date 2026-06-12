<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #fdf4f7 0%, #fce4ee 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .glass-card {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(24px);
            border: 1.5px solid rgba(232,98,140,0.12);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(232,98,140,0.08);
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 520px;
            text-align: center;
            animation: fadeUp 0.6s ease;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .brand {
            font-size: 1.8rem;
            font-weight: 800;
            font-family: Georgia, serif;
            background: linear-gradient(135deg, #e8628c, #f4a0bd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e8628c, #f4a0bd);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #fff;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(232,98,140,0.2);
        }
        .info-label { color: #999; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
        .info-value { font-weight: 600; color: #2d2d2d; }
        .btn-logout {
            background: #fff;
            border: 2px solid #e8628c;
            border-radius: 14px;
            padding: 0.7rem 2rem;
            font-weight: 600;
            color: #e8628c;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-logout:hover {
            background: #e8628c;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232,98,140,0.2);
        }
        @media (max-width: 500px) {
            .glass-card { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="glass-card">
        <div class="brand mb-3">CraftyGifts</div>
        <div class="avatar">
            <?php echo strtoupper($user['name'][0]); ?>
        </div>
        <h3 class="fw-bold mb-1">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h3>
        <p class="text-secondary small mb-4">Your account is verified and active.</p>

        <div class="row mb-4">
            <div class="col-6">
                <div class="info-label">Email</div>
                <div class="info-value small"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="col-6">
                <div class="info-label">Member Since</div>
                <div class="info-value small"><?php echo date('M Y', strtotime($user['created_at'])); ?></div>
            </div>
        </div>

        <a href="auth/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
    </div>
</body>
</html>
