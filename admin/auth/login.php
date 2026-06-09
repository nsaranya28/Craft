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
    $stmt = $pdo->prepare('SELECT id, password FROM admin_users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $email;
        // Regenerate session ID to prevent fixation
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm" style="width: 380px;">
        <div class="card-body p-4">
            <h4 class="card-title mb-4 text-center">Admin Login</h4>
            <?php if ($message): ?>
                <div class="alert alert-danger py-2" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="forgot_password.php" class="small text-muted">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
