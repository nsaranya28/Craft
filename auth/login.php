<?php
session_start();
include '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] == 'seller') {
            header("Location: ../seller/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CraftyGifts</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: radial-gradient(circle at top right, rgba(236, 72, 153, 0.1), transparent);
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
            padding: 3rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            font-family: inherit;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .error { color: #ef4444; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="card auth-card animate-fade-in">
            <h2 style="margin-bottom: 0.5rem;">Welcome Back</h2>
            <p style="color: var(--text-light); margin-bottom: 2rem;">Login to access your orders and custom requests.</p>

            <?php if($error) echo "<p class='error'>$error</p>"; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Login</button>
            </form>

            <p style="text-align: center; margin-top: 2rem; color: var(--text-light);">
                Don't have an account? <a href="register.php" class="text-gradient" style="font-weight: 600;">Sign Up</a>
            </p>
        </div>
    </div>
</body>
</html>
