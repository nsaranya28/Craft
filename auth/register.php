<?php
session_start();
include '../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $phone, $address]);
        $success = "Registration successful! <a href='login.php'>Login here</a>";
    } catch (PDOException $e) {
        $error = "Error: Email already exists or connection failed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CraftyGifts</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.1), transparent);
        }
        .auth-card {
            width: 100%;
            max-width: 500px;
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
        .form-group input, .form-group textarea {
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
        .success { color: #10b981; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="card auth-card animate-fade-in">
            <h2 style="margin-bottom: 0.5rem;">Create an Account</h2>
            <p style="color: var(--text-light); margin-bottom: 2rem;">Join CraftyGifts to start personalizing.</p>

            <?php if($error) echo "<p class='error'>$error</p>"; ?>
            <?php if($success) echo "<p class='success'>$success</p>"; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="+1 (555) 000-0000">
                </div>
                <div class="form-group">
                    <label>Delivery Address</label>
                    <textarea name="address" rows="3" placeholder="123 Street Name, City, Country"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Sign Up</button>
            </form>

            <p style="text-align: center; margin-top: 2rem; color: var(--text-light);">
                Already have an account? <a href="login.php" class="text-gradient" style="font-weight: 600;">Login</a>
            </p>
        </div>
    </div>
</body>
</html>
