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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .request-container {
            max-width: 800px;
            margin: 4rem auto;
            padding: 3rem;
        }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input, .form-group textarea {
            width: 100%; padding: 0.8rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; font-family: inherit;
        }
        .success-box { background: #ecfdf5; color: #065f46; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem; border: 1px solid #10b981; }
    </style>
</head>
<body>
    <header class="glass">
        <nav>
            <a href="index.php" class="logo">CraftyGifts</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Shop</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="card request-container animate-fade-in">
            <h1 style="margin-bottom: 1rem;">Custom Design Request</h1>
            <p style="color: var(--text-light); margin-bottom: 2.5rem;">Have a unique vision? Describe it below and we'll bring it to life. You'll receive a price estimate once we review your request.</p>

            <?php if ($success): ?>
                <div class="success-box"><?php echo $success; ?></div>
                <a href="user/dashboard.php" class="btn btn-primary">View My Requests</a>
            <?php else: ?>
                <?php if($error) echo "<p style='color:red; margin-bottom:1rem;'>$error</p>"; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Request Title</label>
                        <input type="text" name="title" required placeholder="e.g. Hand-carved Wooden Jewelry Box">
                    </div>
                    <div class="form-group">
                        <label>Detailed Description</label>
                        <textarea name="description" rows="6" required placeholder="Describe materials, size, colors, and any specific details..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Reference Image / Sketch</label>
                        <input type="file" name="image" class="btn btn-outline" style="width: 100%;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Submit Request</button>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer style="text-align: center; padding: 2rem;">
        <p>&copy; 2026 CraftyGifts. All rights reserved.</p>
    </footer>
</body>
</html>
