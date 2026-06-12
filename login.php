<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$verified = isset($_GET['verified']) ? 'Your email has been verified! You can now log in.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, password, email_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'No account found with this email.';
        } elseif (!password_verify($password, $user['password'])) {
            $error = 'Incorrect password.';
        } elseif (!$user['email_verified']) {
            $error = 'Please verify your email first. A verification link was sent to your email.';
            $error .= ' <a href="resend_otp.php" style="color:#e8628c;font-weight:600;">Resend OTP</a>';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            header("Location: dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo SITE_NAME; ?></title>
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
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '♡';
            position: absolute;
            top: 6%; left: 8%;
            font-size: 4rem;
            color: #f4a0bd;
            opacity: 0.15;
            animation: float 8s ease-in-out infinite;
        }
        body::after {
            content: '🎁';
            position: absolute;
            bottom: 10%; right: 8%;
            font-size: 3rem;
            opacity: 0.08;
            animation: float 6s ease-in-out infinite 1s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-18px); }
        }
        .glass-card {
            background: rgba(255,255,255,0.90);
            backdrop-filter: blur(24px);
            border: 1.5px solid rgba(232,98,140,0.12);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(232,98,140,0.08);
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 460px;
            position: relative;
            z-index: 1;
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
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 14px;
            padding: 0.75rem 1.1rem;
            border: 1.5px solid #f0d0dc;
            background: #fdf4f7;
            transition: all 0.25s;
        }
        .form-control:focus {
            border-color: #e8628c;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(232,98,140,0.10);
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #e8628c, #f4a0bd);
            border: none;
            border-radius: 14px;
            padding: 0.8rem;
            font-weight: 600;
            color: #fff;
            transition: all 0.3s;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232,98,140,0.25);
            color: #fff;
        }
        .input-group-icon { position: relative; }
        .input-group-icon .form-control { padding-left: 2.6rem; }
        .input-group-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #e8628c;
            opacity: 0.6;
            z-index: 10;
        }
        @media (max-width: 500px) {
            .glass-card { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="glass-card">
        <div class="brand">CraftyGifts</div>
        <p class="text-center text-secondary mb-4" style="font-size:0.9rem;">Welcome back!</p>

        <?php if ($verified): ?>
            <div class="alert alert-success rounded-4 py-2 small"><?php echo $verified; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger rounded-4 py-2 small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3 input-group-icon">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" class="form-control" placeholder="Email ID" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="mb-4 input-group-icon">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100">Log In</button>
        </form>

        <p class="text-center mt-4 mb-0 small text-secondary">
            Don't have an account? <a href="register.php" style="color:#e8628c;font-weight:600;text-decoration:none;">Sign Up</a>
        </p>
    </div>
</body>
</html>
