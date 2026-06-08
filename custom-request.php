<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];
    
    // File upload logic
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "assets/img/custom/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $image_path = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO custom_requests (user_id, title, description, image_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $description, $image_path]);
        $success = "Your custom request has been submitted! Our craftsmen will review it and provide a quote shortly.";
    } catch (PDOException $e) {
        $error = "Error submitting request. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Request | CraftyGifts</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
    <style>
        .request-container {
            max-width: 760px;
            margin: 0 auto;
        }
        .success-box {
            background: #ecfdf5;
            color: #065f46;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 1px solid #10b981;
        }
    </style>
</head>
<body>
    <!-- Navbar brand with cute ribbon -->
    <nav class="navbar navbar-expand-lg glass-nav">
        <div class="container-fluid">
            <a class="navbar-brand font-serif fs-3 fw-bold text-gradient" href="index.php">
                CraftyGifts
                <svg class="brand-ribbon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="36" height="36" style="color: var(--primary); fill: none; stroke: currentColor; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; vertical-align: middle; margin-left: 2px;">
                    <path d="M32 32 C20 18, 10 24, 16 36 C20 44, 30 36, 32 32 Z" fill="rgba(226, 95, 132, 0.15)"/>
                    <path d="M32 32 C44 18, 54 24, 48 36 C44 44, 34 36, 32 32 Z" fill="rgba(226, 95, 132, 0.15)"/>
                    <path d="M28 34 C24 44, 18 50, 20 54 M20 54 C23 54, 25 50, 27 46"/>
                    <path d="M36 34 C40 44, 46 50, 44 54 M44 54 C41 54, 39 50, 37 46"/>
                    <circle cx="32" cy="32" r="5" fill="var(--primary)"/>
                </svg>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars" style="color: var(--primary);"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link fw-medium" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="products.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link active fw-medium" href="custom-request.php">Custom Order</a></li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Cart 
                            <span class="badge bg-primary text-white rounded-pill px-2" style="font-size: 0.75rem; vertical-align: middle;">
                                <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                            </span>
                        </a>
                    </li>
                </ul>
                <div class="d-flex gap-2">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="user/dashboard.php" class="btn btn-outline-custom">Dashboard</a>
                        <a href="auth/logout.php" class="btn btn-primary-custom">Logout</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn btn-outline-custom">Login</a>
                        <a href="auth/register.php" class="btn btn-primary-custom">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="navbar-scallop-divider"></div>

    <main class="py-5">
        <div class="container">
            <div class="card request-container p-4 p-md-5 shadow-sm border-pink rounded-cute bg-white fade-up">
                <h1 class="font-serif mb-2 text-dark">Custom Design Request</h1>
                <p class="text-secondary mb-4 pb-3 border-bottom border-pink-dashed">Have a unique vision? Describe it below and our skilled artisans will bring it to life. You'll receive a price estimate once we review your request.</p>

                <?php if ($success): ?>
                    <div class="success-box d-flex align-items-center gap-2">
                        <i class="fas fa-circle-check fs-4"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                    <div class="text-center mt-3">
                        <a href="user/dashboard.php" class="btn btn-primary-custom btn-lg">View My Requests</a>
                    </div>
                <?php else: ?>
                    <?php if($error) echo "<p class='text-danger mb-3 fw-medium'><i class='fas fa-circle-xmark me-1'></i>$error</p>"; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Request Title</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Hand-carved Wooden Jewelry Box" style="background: var(--cream);">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Detailed Description</label>
                            <textarea name="description" class="form-control" rows="6" required placeholder="Describe materials, size, colors, and any specific details you would like..." style="background: var(--cream);"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Reference Image / Sketch (Optional)</label>
                            <input type="file" name="image" class="form-control py-2" style="background: var(--cream);">
                        </div>
                        
                        <button type="submit" class="btn btn-primary-custom btn-lg w-100 py-3 mt-3">
                            <i class="fas fa-paper-plane me-1"></i> Submit Request
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container text-center">
            <p class="mb-0 text-secondary">&copy; 2026 CraftyGifts. All rights reserved. Handcrafted with <i class="fas fa-heart text-danger mx-1"></i></p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Trigger fade-up animations
        document.addEventListener("DOMContentLoaded", function() {
            const el = document.querySelector('.fade-up');
            if (el) el.style.opacity = 1;
        });
    </script>
</body>
</html>
