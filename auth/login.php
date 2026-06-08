<?php
session_start();
include '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Check users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = 'user';
        header("Location: ../user/dashboard.php");
        exit();
    } else {
        // 2. Check admins table
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_name'] = $admin['name'];
            $_SESSION['role'] = 'admin';
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CraftyGifts</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-bg {
            background: linear-gradient(135deg, var(--background) 0%, var(--pink-50) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
        }
        .auth-bg::before {
            content: '♡';
            position: absolute;
            top: 10%;
            left: 8%;
            font-size: 2rem;
            color: var(--primary-light);
            opacity: 0.3;
            animation: float 6s ease-in-out infinite;
        }
        .auth-bg::after {
            content: '♡';
            position: absolute;
            bottom: 15%;
            right: 10%;
            font-size: 1.5rem;
            color: var(--primary-light);
            opacity: 0.25;
            animation: float 5s ease-in-out infinite 1s;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            border: 1.5px solid rgba(232, 98, 140, 0.12);
            border-radius: 30px;
            box-shadow: var(--shadow-soft);
            padding: 3rem;
        }
        .form-control {
            border-radius: 15px;
            padding: 0.8rem 1.2rem;
            border: 1.5px solid var(--pink-200);
            background: var(--cream);
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(232, 98, 140, 0.1);
            border-color: var(--primary);
            background: #fff;
        }
        .form-label {
            font-weight: 500;
            color: var(--text);
        }
    </style>
</head>
<body class="auth-bg">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-4 fade-up visible">
                    <a href="../index.php" class="font-serif fs-2 fw-bold text-gradient text-decoration-none">CraftyGifts</a>
                </div>
                <div class="auth-card fade-up visible" style="transition-delay: 0.1s;">
                    <h2 class="font-serif fw-bold text-center mb-2">Welcome Back</h2>
                    <p class="text-secondary text-center mb-4">Log in to access your custom orders and wishlist.</p>

                    <?php if($error): ?>
                        <div class="alert alert-danger rounded-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required placeholder="jane@example.com">
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">Password</label>
                                <a href="#" class="text-secondary small text-decoration-none hover-primary">Forgot Password?</a>
                            </div>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label text-secondary small" for="rememberMe">
                                Remember me for 30 days
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 py-2 fs-5">Log In</button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-secondary mb-0">Don't have an account? <a href="register.php" class="text-primary fw-bold text-decoration-none">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
