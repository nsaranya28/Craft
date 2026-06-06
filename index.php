<?php
session_start();
include 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CraftyGifts | Personalized Gifts & Handmade Crafts</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="glass">
        <nav>
            <a href="index.php" class="logo">CraftyGifts</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Shop</a></li>
                <li><a href="#custom">Custom Order</a></li>
                <li><a href="#about">About</a></li>
            </ul>
            <div class="nav-btns">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="user/dashboard.php" class="btn btn-outline">Dashboard</a>
                    <a href="auth/logout.php" class="btn btn-primary">Logout</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn btn-outline">Login</a>
                    <a href="auth/register.php" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div id="canvas-container"></div>
            <div class="floating-badge">
                <span style="font-size: 2rem;">✨</span>
                <span class="text-gradient" style="font-weight: 700; font-size: 0.8rem;">NEW CRAFT</span>
            </div>
            <div class="hero-content animate-fade-in">
                <h1 class="text-glow">Thoughtful Gifts, <br><span class="text-gradient">Handcrafted</span> with Love</h1>
                <p>Customize your favorite crafts or order unique handmade gifts for your loved ones. Quality you can feel, designs you will love.</p>
                <div class="nav-btns" style="justify-content: center;">
                    <a href="products.php" class="btn btn-primary">Browse Shop</a>
                    <a href="#custom" class="btn btn-outline">Request Custom Design</a>
                </div>
            </div>
        </section>

        <section id="categories">
            <div class="section-title">
                <h2>Our Categories</h2>
                <p>Explore our curated collections of handcrafted items.</p>
            </div>
            <div class="grid">
                <?php
                $stmt = $pdo->query("SELECT * FROM categories LIMIT 3");
                while($cat = $stmt->fetch()):
                ?>
                <div class="card">
                    <h3><?php echo $cat['name']; ?></h3>
                    <p><?php echo $cat['description']; ?></p>
                    <a href="products.php?category=<?php echo $cat['id']; ?>" class="text-gradient" style="font-weight: 600; margin-top: 1rem; display: inline-block;">View All →</a>
                </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section id="featured" style="background-color: white;">
            <div class="section-title">
                <h2>Featured Products</h2>
                <p>Wait-listed items and trending crafts.</p>
            </div>
            <div class="grid">
                <!-- Placeholder for products - I'll add real ones via DB later -->
                <div class="card product-card">
                    <img src="https://images.unsplash.com/photo-1574633944818-a8325b48d516?auto=format&fit=crop&q=80&w=500" alt="Mug">
                    <div class="product-info">
                        <h3>Personalized Ceramic Mug</h3>
                        <p class="text-light">Custom image & text engraving</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                            <span class="price">$19.99</span>
                            <a href="product-details.php?id=1" class="btn btn-primary btn-sm">Customize</a>
                        </div>
                    </div>
                </div>
                <div class="card product-card">
                    <img src="https://images.unsplash.com/photo-1515562141207-7a88bb7ce338?auto=format&fit=crop&q=80&w=500" alt="Jewelry">
                    <div class="product-info">
                        <h3>Silver Name Necklace</h3>
                        <p class="text-light">Handmade sterling silver jewelry</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                            <span class="price">$45.00</span>
                            <a href="#" class="btn btn-primary btn-sm">Customize</a>
                        </div>
                    </div>
                </div>
                <div class="card product-card">
                    <img src="https://images.unsplash.com/photo-1549490349-8643362247b5?auto=format&fit=crop&q=80&w=500" alt="Wall Art">
                    <div class="product-info">
                        <h3>Wooden Wall Art</h3>
                        <p class="text-light">Laser cut geometric designs</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                            <span class="price">$59.00</span>
                            <a href="#" class="btn btn-primary btn-sm">Customize</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="custom" class="glass" style="margin: 4rem 10%; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
            <div style="text-align: center; padding: 2rem;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Have a Specific Idea?</h2>
                <p style="font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.9;">Tell us your vision and our craftsmen will bring it to life.</p>
                <a href="custom-request.php" class="btn" style="background: white; color: var(--primary);">Start Custom Request</a>
            </div>
        </section>
    </main>

    <footer>
        <div class="logo" style="margin-bottom: 1rem;">CraftyGifts</div>
        <p>&copy; 2026 CraftyGifts. All rights reserved.</p>
        <div style="margin-top: 1rem;">
            <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
        </div>
    </footer>
    <script type="module" src="assets/js/three-scene.js"></script>
    <script src="assets/js/tilt.js"></script>
</body>
</html>
