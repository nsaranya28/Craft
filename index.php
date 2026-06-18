<?php
session_start();
include 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CraftyGifts | Handmade with Love</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
</head>
<body>

    <!-- ─── Navbar ─────────────────────────────────────────────────────── -->
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
                    <li class="nav-item"><a class="nav-link active fw-medium" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="products.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#categories">Categories</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#custom">Custom Order</a></li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="user/wishlist.php" class="nav-link fw-medium position-relative" title="Wishlist">
                            <i class="fa-regular fa-heart" style="font-size:1.2rem;"></i>
                        </a>
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

    <!-- ─── Hero ────────────────────────────────────────────────────────── -->
    <section class="hero-section">
        <div class="floating-ornaments">
            <span class="float-item" style="top:10%;left:5%;animation-delay:0s;">✧</span>
            <span class="float-item" style="top:20%;right:8%;animation-delay:0.5s;">♡</span>
            <span class="float-item" style="top:50%;left:3%;animation-delay:1s;">✦</span>
            <span class="float-item" style="bottom:30%;right:5%;animation-delay:1.5s;">♡</span>
            <span class="float-item" style="top:35%;left:50%;animation-delay:2s;">✧</span>
            <span class="float-item" style="bottom:15%;left:10%;animation-delay:2.5s;">✦</span>
        </div>
        <div class="container">
            <div class="row align-items-center g-5 min-vh-75">
                <div class="col-lg-6 fade-up">
                    <div class="hero-badge">
                        <i class="fas fa-heart" style="color:var(--primary);font-size:0.7rem;"></i>
                        Handmade with Love
                    </div>
                    <h1 class="hero-title">
                        Thoughtful Gifts,<br>
                        <span class="text-gradient">Handcrafted</span> with
                        <span class="hero-underline">Passion</span>
                    </h1>
                    <p class="hero-subtitle">
                        Unique handcrafted gifts designed to create lasting memories. 
                        Explore our collection or request a custom design just for you.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="products.php" class="btn btn-primary-custom btn-lg px-5">
                            Shop Collection <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <a href="#categories" class="btn btn-outline-custom btn-lg px-5">
                            Explore Categories
                        </a>
                    </div>
                    <div class="hero-stats mt-5">
                        <div class="stat-item">
                            <span class="stat-number">10K+</span>
                            <span class="stat-label">Happy Customers</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">500+</span>
                            <span class="stat-label">Unique Designs</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">100%</span>
                            <span class="stat-label">Handmade</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 fade-up" style="transition-delay:0.2s;">
                    <div class="hero-visual">
                        <div class="hero-img-frame">
                            <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&q=80&w=800" alt="Handmade Gift" class="hero-img">
                            <div class="frame-ribbon">
                                <svg viewBox="0 0 64 64" width="100%" height="100%" fill="none" stroke="var(--primary)" stroke-width="2.5">
                                    <path d="M32 32 C20 18,10 24,16 36 C20 44,30 36,32 32 Z" fill="rgba(226,95,132,0.12)"/>
                                    <path d="M32 32 C44 18,54 24,48 36 C44 44,34 36,32 32 Z" fill="rgba(226,95,132,0.12)"/>
                                    <circle cx="32" cy="32" r="4" fill="var(--primary)"/>
                                </svg>
                            </div>
                        </div>
                        <div class="hero-floating-card card-1">
                            <i class="fas fa-gift"></i>
                            <span>Gift Ready</span>
                        </div>
                        <div class="hero-floating-card card-2">
                            <i class="fas fa-heart"></i>
                            <span>Handcrafted</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="hero-bottom-divider"></div>
    <div class="gingham-stripe">
        <span class="stripe-heart" style="left:10%;">♡</span>
        <span class="stripe-heart" style="left:30%;">♡</span>
        <span class="stripe-heart" style="left:50%;">♡</span>
        <span class="stripe-heart" style="left:70%;">♡</span>
        <span class="stripe-heart" style="left:90%;">♡</span>
    </div>
    <div class="hero-bottom-divider-reversed"></div>

    <!-- ─── Categories ──────────────────────────────────────────────────── -->
    <section class="section-padding" id="categories">
        <div class="container">
            <div class="section-title fade-up">
                <h2>Explore Our Collections</h2>
                <p>Find the perfect gift from our curated categories.</p>
            </div>
            <div class="row g-4">
                <?php
                $cats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as pc FROM categories c ORDER BY c.name")->fetchAll();
                $delays = [0, 0.05, 0.1, 0.15, 0.2, 0.25, 0.3, 0.35, 0.4];
                $i = 0;
                foreach ($cats as $cat):
                    $icons = ['fa-gift','fa-mug-hot','fa-ring','fa-paintbrush','fa-hands','fa-box-open','fa-couch','fa-book','fa-tree','fa-seedling'];
                    $icon = $icons[$cat['id'] % count($icons)];
                ?>
                <div class="col-lg-4 col-md-6 fade-up" style="transition-delay:<?php echo $delays[$i % count($delays)]; ?>s;">
                    <a href="products.php?category=<?php echo $cat['id']; ?>" class="category-card">
                        <div class="cat-icon"><i class="fas <?php echo $icon; ?>"></i></div>
                        <h5><?php echo htmlspecialchars($cat['name']); ?></h5>
                        <p><?php echo htmlspecialchars($cat['description'] ?? ''); ?></p>
                        <span class="cat-count"><?php echo $cat['pc']; ?> items</span>
                    </a>
                </div>
                <?php $i++; endforeach; ?>
                <?php if (empty($cats)): ?>
                <div class="col-12 text-center text-secondary py-5 fade-up">
                    <p>No categories yet. Check back soon!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ─── Featured Products ───────────────────────────────────────────── -->
    <section class="section-padding bg-white" id="featured">
        <div class="container">
            <div class="section-title fade-up">
                <h2>Featured Creations</h2>
                <p>Our most loved handcrafted pieces, made with exceptional care.</p>
            </div>
            <div class="row g-4">
                <?php
                $stmt = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 6");
                $delay = 0;
                while($product = $stmt->fetch()):
                ?>
                <div class="col-md-4 fade-up" style="transition-delay: <?php echo $delay; ?>s;">
                    <div class="product-card h-100">
                        <div class="product-img-wrap">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php if (!empty($product['cat_name'])): ?>
                                <span class="product-cat-badge"><?php echo htmlspecialchars($product['cat_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p><?php echo htmlspecialchars(substr($product['description'], 0, 70)); ?>...</p>
                            <div class="product-footer">
                                <span class="product-price">₹<?php echo number_format($product['base_price'], 2); ?></span>
                                <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary-custom btn-sm">View <i class="fas fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                $delay += 0.1;
                endwhile; 
                ?>
                <?php
                $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
                if ($count == 0):
                ?>
                <div class="col-12 text-center text-secondary py-5 fade-up">
                    <i class="fas fa-box-open fa-3x mb-3" style="color:var(--pink-200);"></i>
                    <p>No products yet. Our artisans are crafting something special!</p>
                </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-5 fade-up">
                <a href="products.php" class="btn btn-outline-custom btn-lg">View All Products <i class="fas fa-arrow-right ms-2"></i></a>
            </div>
        </div>
    </section>

    <!-- ─── Process ─────────────────────────────────────────────────────── -->
    <section class="section-padding" id="custom">
        <div class="container">
            <div class="section-title fade-up">
                <h2>How Custom Orders Work</h2>
                <p>Turn your vision into reality in three simple steps.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 fade-up">
                    <div class="process-card">
                        <div class="process-step">1</div>
                        <div class="process-icon"><i class="fas fa-lightbulb"></i></div>
                        <h4>Share Your Idea</h4>
                        <p>Tell us what you're looking for and share your inspiration.</p>
                    </div>
                </div>
                <div class="col-md-4 fade-up" style="transition-delay:0.15s;">
                    <div class="process-card">
                        <div class="process-step">2</div>
                        <div class="process-icon"><i class="fas fa-palette"></i></div>
                        <h4>We Craft It</h4>
                        <p>Our artisans handcraft your unique piece with precision and love.</p>
                    </div>
                </div>
                <div class="col-md-4 fade-up" style="transition-delay:0.3s;">
                    <div class="process-card">
                        <div class="process-step">3</div>
                        <div class="process-icon"><i class="fas fa-box-open"></i></div>
                        <h4>Fast Delivery</h4>
                        <p>Receive your beautifully packaged, one-of-a-kind gift at your doorstep.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 fade-up">
                <a href="custom-request.php" class="btn btn-primary-custom btn-lg px-5">Start Custom Request</a>
            </div>
        </div>
    </section>

    <!-- ─── Testimonials ────────────────────────────────────────────────── -->
    <section class="section-padding bg-white">
        <div class="container">
            <div class="section-title fade-up">
                <h2>What Our Customers Say</h2>
                <p>Real love from real people.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 fade-up">
                    <div class="testimonial-card">
                        <div class="stars">★★★★★</div>
                        <p>"The custom engraved necklace I ordered for my mom brought her to tears. The quality is absolutely stunning."</p>
                        <div class="testimonial-author">
                            <div class="author-avatar" style="background:#fce4ee;">S</div>
                            <div>
                                <strong>Sarah J.</strong>
                                <small>Custom Jewelry</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 fade-up" style="transition-delay:0.1s;">
                    <div class="testimonial-card">
                        <div class="stars">★★★★★</div>
                        <p>"The wall art adds such a warm, handmade touch to my living room. Will definitely buy again!"</p>
                        <div class="testimonial-author">
                            <div class="author-avatar" style="background:#fff3e0;">M</div>
                            <div>
                                <strong>Michael T.</strong>
                                <small>Wall Art</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 fade-up" style="transition-delay:0.2s;">
                    <div class="testimonial-card">
                        <div class="stars">★★★★★</div>
                        <p>"The packaging alone was an experience. Every detail shows how much care goes into each product."</p>
                        <div class="testimonial-author">
                            <div class="author-avatar" style="background:#e8f5e9;">E</div>
                            <div>
                                <strong>Emily R.</strong>
                                <small>Gift Sets</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ─── Newsletter ──────────────────────────────────────────────────── -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-card fade-up">
                <div class="row align-items-center g-4">
                    <div class="col-lg-6">
                        <h3 class="font-serif fw-bold mb-2">Join Our Community</h3>
                        <p class="mb-0" style="color:rgba(255,255,255,0.8);">Subscribe for exclusive offers, new arrivals, and handmade inspiration.</p>
                    </div>
                    <div class="col-lg-6">
                        <form class="newsletter-form" onsubmit="return false;">
                            <input type="email" placeholder="Your email address" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ─── Footer ──────────────────────────────────────────────────────── -->
    <footer>
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <a href="index.php" class="font-serif fs-3 fw-bold text-gradient d-inline-block mb-3">CraftyGifts</a>
                    <p class="text-secondary pe-lg-5">Bringing joy through handmade, thoughtfully designed products that celebrate life's special moments.</p>
                    <div class="social-links mt-4">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <h5 class="font-serif mb-4">Shop</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="products.php" class="text-secondary">All Products</a></li>
                        <li class="mb-2"><a href="#categories" class="text-secondary">Categories</a></li>
                        <li class="mb-2"><a href="#featured" class="text-secondary">Featured</a></li>
                        <li class="mb-2"><a href="#custom" class="text-secondary">Custom Orders</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6">
                    <h5 class="font-serif mb-4">Company</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-secondary">About Us</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary">Contact</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary">FAQs</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary">Shipping</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="font-serif mb-4">Newsletter</h5>
                    <p class="text-secondary mb-3">Get special offers and updates straight to your inbox.</p>
                    <div class="input-group">
                        <input type="email" class="form-control rounded-pill-start py-2 px-3" placeholder="Your Email">
                        <button class="btn btn-primary-custom rounded-pill-end px-4" style="border-radius:0 50px 50px 0;">Subscribe</button>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 CraftyGifts. All rights reserved. Made with <i class="fas fa-heart" style="color:var(--primary);"></i></p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
    });
    </script>
</body>
</html>
