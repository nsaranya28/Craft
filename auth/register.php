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
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $country = $_POST['country'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, city, state, zip_code, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $phone, $address, $city, $state, $zip_code, $country]);
        $success = "Registration successful! You can now <a href='login.php' class='alert-link'>Login here</a>.";
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
    <title>Create Account | CraftyGifts</title>
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
            <div class="col-md-8 col-lg-6">
                <div class="text-center mb-4 fade-up visible">
                    <a href="../index.php" class="font-serif fs-2 fw-bold text-gradient text-decoration-none">CraftyGifts</a>
                </div>
                <div class="auth-card fade-up visible" style="transition-delay: 0.1s;">
                    <h2 class="font-serif fw-bold text-center mb-2">Create an Account</h2>
                    <p class="text-secondary text-center mb-4">Join our community to start personalizing your gifts.</p>

                    <?php if($error): ?>
                        <div class="alert alert-danger rounded-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success rounded-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="Jane Doe">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" required placeholder="jane@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required placeholder="••••••••">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Street Address</label>
                                <input type="text" name="address" class="form-control" placeholder="123 Main St">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" placeholder="New York">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">State/Province</label>
                                <input type="text" name="state" class="form-control" placeholder="NY">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zip Code</label>
                                <input type="text" name="zip_code" class="form-control" placeholder="10001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" placeholder="United States">
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary-custom w-100 py-2 fs-5">Sign Up</button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-secondary mb-0">Already have an account? <a href="login.php" class="text-primary fw-bold text-decoration-none">Log In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
