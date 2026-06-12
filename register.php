<?php
session_start();
require_once 'config.php';
require_once 'includes/mailer.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname  = trim($_POST['fullname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    if (empty($fullname) || empty($email) || empty($password) || empty($cpassword)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $cpassword) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiry = date('Y-m-d H:i:s', time() + OTP_EXPIRY_SECONDS);

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, otp, otp_expiry) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$fullname, $email, $hashed, $otp, $expiry]);
            $userId = $pdo->lastInsertId();

            $mailError = '';
            $sent = sendOtpEmail($email, $fullname, $otp, $mailError);

            if ($sent) {
                $_SESSION['otp_user_id'] = $userId;
                $_SESSION['otp_email']   = $email;
                header("Location: verify_otp.php");
                exit;
            } else {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
                $error = 'Failed to send OTP email. ' . htmlspecialchars($mailError);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | <?php echo SITE_NAME; ?></title>
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
            max-width: 480px;
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
        .btn-primary-custom:disabled {
            opacity: 0.6;
            transform: none;
        }
        .input-group-icon {
            position: relative;
        }
        .input-group-icon .form-control {
            padding-left: 2.6rem;
        }
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
        <p class="text-center text-secondary mb-4" style="font-size:0.9rem;">Create your account</p>

        <?php if ($error): ?>
            <div class="alert alert-danger rounded-4 py-2 small"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success rounded-4 py-2 small"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3 input-group-icon">
                <i class="fas fa-user"></i>
                <input type="text" name="fullname" class="form-control" placeholder="Full Name" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
            </div>
            <div class="mb-3 input-group-icon">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" class="form-control" placeholder="Email ID" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="mb-3 input-group-icon">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" class="form-control" placeholder="Password" minlength="6" required>
            </div>
            <div class="mb-4 input-group-icon">
                <i class="fas fa-check-circle"></i>
                <input type="password" name="cpassword" class="form-control" placeholder="Confirm Password" minlength="6" required>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100">Register & Send OTP</button>
        </form>

        <p class="text-center mt-4 mb-0 small text-secondary">
            Already have an account? <a href="login.php" style="color:#e8628c;font-weight:600;text-decoration:none;">Log In</a>
        </p>
    </div>
</body>
</html>
