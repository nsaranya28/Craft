<?php
session_start();
require_once '../../includes/db.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare('SELECT id, password FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $email;
        $_SESSION['role'] = 'admin';
        $_SESSION['admin'] = true;
        session_regenerate_id(true);
        header('Location: ../dashboard.php');
        exit;
    } else {
        $message = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | CraftyGifts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fdf4f7 0%, #fce4ee 50%, #f3e8ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '♡';
            position: fixed;
            top: 8%; left: 6%;
            font-size: 4rem;
            color: #FBC5D2;
            opacity: 0.15;
            animation: floatHeart 8s ease-in-out infinite;
        }
        body::after {
            content: '🎀';
            position: fixed;
            bottom: 10%; right: 6%;
            font-size: 3rem;
            color: #FBC5D2;
            opacity: 0.12;
            animation: floatBow 6s ease-in-out infinite 2s;
        }
        .deco-heart-login {
            position: fixed;
            top: 30%; right: 8%;
            font-size: 2.5rem;
            color: #FBC5D2;
            opacity: 0.1;
            animation: floatHeart 7s ease-in-out infinite 1s;
            pointer-events: none;
        }
        .deco-heart-login2 {
            position: fixed;
            bottom: 25%; left: 8%;
            font-size: 2rem;
            color: #F4A2B8;
            opacity: 0.08;
            animation: floatBow 9s ease-in-out infinite 3s;
            pointer-events: none;
        }
        @keyframes floatHeart {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-18px) scale(1.05); }
        }
        @keyframes floatBow {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(5deg); }
        }

        .login-card {
            backdrop-filter: blur(20px);
            background: rgba(255,255,255,0.75);
            border: 1px solid rgba(232,98,140,0.12);
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(232,98,140,0.1);
            padding: 2.5rem;
            width: 400px;
            max-width: 92vw;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        .login-card::after {
            content: '♡';
            position: absolute;
            bottom: 10px; right: 15px;
            font-size: 1rem;
            color: #FBC5D2;
            opacity: 0.15;
            pointer-events: none;
        }
        .login-logo {
            font-family: 'Playfair Display', serif;
            font-weight: 800;
            font-size: 1.8rem;
            text-align: center;
            background: linear-gradient(135deg, #E25F84, #F2A1B7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .login-logo::before {
            content: '♥ ';
            font-size: 1.2rem;
        }
        .login-logo::after {
            content: ' ♥';
            font-size: 1.2rem;
        }
        .login-subtitle {
            color: #9E8394;
            font-size: 0.88rem;
            text-align: center;
            margin-bottom: 1.8rem;
        }
        .form-control {
            border-radius: 14px;
            border: 1.5px solid rgba(232,98,140,0.12);
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            background: rgba(255,255,255,0.7);
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #E25F84;
            box-shadow: 0 0 0 3px rgba(232,98,140,0.12);
        }
        .form-label {
            font-weight: 500;
            font-size: 0.85rem;
            color: #442E3C;
        }
        .btn-login {
            background: linear-gradient(135deg, #E25F84, #F2A1B7);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 6px 20px rgba(232,98,140,0.25);
            transition: all 0.3s;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(232,98,140,0.35);
            color: white;
        }
        .alert-custom {
            border-radius: 14px;
            background: #fee2e2;
            color: #b91c1c;
            border: none;
            padding: 0.7rem 1rem;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="deco-heart-login">♥</div>
    <div class="deco-heart-login2">♡</div>
    <div class="login-card">
        <div class="login-logo">CraftyGifts</div>
        <p class="login-subtitle">Admin Control Panel</p>
        <?php if ($message): ?>
            <div class="alert-custom mb-3" role="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
            <div class="text-center mt-3">
                <a href="forgot_password.php" style="color: #9E8394; font-size: 0.82rem; text-decoration: none;">Forgot password?</a>
            </div>
        </form>
    </div>
</body>
</html>
