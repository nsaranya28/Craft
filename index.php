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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Glass Navbar -->
    <nav class="navbar navbar-expand-lg glass-nav">
        <div class="container-fluid">
            <a class="navbar-brand font-serif fs-4 fw-bold text-gradient" href="index.php">CraftyGifts</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link fw-medium" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="products.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#custom">Custom Order</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#about">About</a></li>
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

    <!-- Hero Section -->
    <section class="hero-section overflow-hidden">
        <!-- Cute Doodles -->
        <span class="doodle" style="top: 10%; left: 5%;">✨</span>
        <span class="doodle" style="top: 20%; right: 10%; font-size: 3rem;">💖</span>
        <span class="doodle" style="bottom: 10%; left: 40%; font-size: 2.5rem;">🌟</span>
        <span class="doodle" style="top: 60%; right: 5%;">🎀</span>
        
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 fade-up">
                    <div class="badge-custom">
                        <i class="fas fa-heart"></i> Handmade with Love
                    </div>
                    <h1 class="display-3 fw-bold mb-4 font-serif text-dark">
                        Thoughtful Gifts for <span class="text-gradient">Every Occasion</span>
                    </h1>
                    <p class="lead text-secondary mb-5">
                        Unique handcrafted gifts designed to create lasting memories. Explore our collection or request a custom design tailored just for you.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="products.php" class="btn btn-primary-custom btn-lg">
                            Shop Collection <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <a href="#custom" class="btn btn-outline-custom btn-lg">
                            Custom Order
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 fade-up" style="transition-delay: 0.2s;">
                    <div class="hero-img-wrap">
                        <img src="https://images.unsplash.com/photo-1549490349-8643362247b5?auto=format&fit=crop&q=80&w=800" alt="Handmade Gift" class="hero-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="section-padding bg-white" id="featured">
        <div class="container">
            <div class="section-title fade-up">
                <h2>Featured Creations</h2>
                <p>Discover our most loved handcrafted pieces, made with exceptional attention to detail.</p>
            </div>
            <div class="row g-4">
                <?php
                $stmt = $pdo->query("SELECT * FROM products LIMIT 3");
                $delay = 0;
                while($product = $stmt->fetch()):
                ?>
                <div class="col-md-4 fade-up" style="transition-delay: <?php echo $delay; ?>s;">
                    <div class="card product-card h-100">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h3 class="h5 font-serif mb-2 text-dark"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-secondary small mb-3 flex-grow-1"><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="price-tag">$<?php echo htmlspecialchars($product['base_price']); ?></span>
                                <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary-custom btn-sm">View <i class="fas fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                $delay += 0.2;
                endwhile; 
                ?>
            </div>
            <div class="text-center mt-5 fade-up">
                <a href="products.php" class="btn btn-outline-custom">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="section-padding" id="about">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 fade-up">
                    <img src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&q=80&w=800" alt="Crafting Process" class="img-fluid rounded-4 shadow-sm" style="border-radius: 30px;">
                </div>
                <div class="col-lg-6 fade-up" style="transition-delay: 0.2s;">
                    <h2 class="mb-4">Crafted with <span class="text-gradient">Passion</span></h2>
                    <p class="text-secondary mb-4">Every piece in our collection is carefully handcrafted by skilled artisans who pour their heart and soul into their work. We believe in quality over quantity, ensuring each gift is as unique as the person receiving it.</p>
                    
                    <div class="d-flex align-items-start mb-3">
                        <div class="text-primary fs-3 me-3"><i class="fas fa-leaf"></i></div>
                        <div>
                            <h5 class="font-serif">Eco-Friendly Materials</h5>
                            <p class="text-secondary small">Sustainable and responsibly sourced.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-3">
                        <div class="text-primary fs-3 me-3"><i class="fas fa-hands"></i></div>
                        <div>
                            <h5 class="font-serif">100% Handmade</h5>
                            <p class="text-secondary small">Authentic craftsmanship in every detail.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <div class="text-primary fs-3 me-3"><i class="fas fa-gift"></i></div>
                        <div>
                            <h5 class="font-serif">Beautiful Packaging</h5>
                            <p class="text-secondary small">Ready to be gifted immediately.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Custom Order Process -->
    <section class="section-padding bg-white" id="custom">
        <div class="container">
            <div class="section-title fade-up">
                <h2>How Custom Orders Work</h2>
                <p>Bring your unique vision to life in three simple steps.</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-4 fade-up">
                    <div class="process-card">
                        <div class="process-icon"><i class="fas fa-lightbulb"></i></div>
                        <h4 class="font-serif">1. Share Your Idea</h4>
                        <p class="text-secondary small">Tell us what you're looking for, upload sketches, and share your inspiration.</p>
                    </div>
                </div>
                <div class="col-md-4 fade-up" style="transition-delay: 0.2s;">
                    <div class="process-card">
                        <div class="process-icon"><i class="fas fa-palette"></i></div>
                        <h4 class="font-serif">2. We Craft It</h4>
                        <p class="text-secondary small">Our artisans will design and handcraft your custom piece with precision.</p>
                    </div>
                </div>
                <div class="col-md-4 fade-up" style="transition-delay: 0.4s;">
                    <div class="process-card">
                        <div class="process-icon"><i class="fas fa-box-open"></i></div>
                        <h4 class="font-serif">3. Fast Delivery</h4>
                        <p class="text-secondary small">Receive your beautifully packaged, one-of-a-kind gift right at your doorstep.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 fade-up">
                <a href="custom-request.php" class="btn btn-primary-custom btn-lg">Start Custom Request</a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="section-padding">
        <div class="container">
            <div class="section-title fade-up">
                <h2>Loved by Customers</h2>
                <p>Don't just take our word for it.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 fade-up">
                    <div class="testimonial-card">
                        <div class="quote-icon"><i class="fas fa-quote-right"></i></div>
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="fst-italic text-secondary mb-4">"The custom engraved necklace I ordered for my mom's birthday brought her to tears. The quality is absolutely stunning."</p>
                        <h6 class="font-serif fw-bold mb-0">- Sarah Jenkins</h6>
                    </div>
                </div>
                <div class="col-md-4 fade-up" style="transition-delay: 0.2s;">
                    <div class="testimonial-card">
                        <div class="quote-icon"><i class="fas fa-quote-right"></i></div>
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="fst-italic text-secondary mb-4">"I love the wall art I got from CraftyGifts. It adds such a warm, handmade touch to my living room. Will definitely buy again!"</p>
                        <h6 class="font-serif fw-bold mb-0">- Michael T.</h6>
                    </div>
                </div>
                <div class="col-md-4 fade-up" style="transition-delay: 0.4s;">
                    <div class="testimonial-card">
                        <div class="quote-icon"><i class="fas fa-quote-right"></i></div>
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="fst-italic text-secondary mb-4">"The packaging alone was an experience. The attention to detail in their products is unmatched by big box stores."</p>
                        <h6 class="font-serif fw-bold mb-0">- Emily R.</h6>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
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
                        <li class="mb-2"><a href="#" class="text-secondary">New Arrivals</a></li>
                        <li class="mb-2"><a href="#featured" class="text-secondary">Featured</a></li>
                        <li class="mb-2"><a href="#custom" class="text-secondary">Custom Orders</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6">
                    <h5 class="font-serif mb-4">Company</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#about" class="text-secondary">About Us</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary">Contact</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary">FAQs</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary">Shipping</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="font-serif mb-4">Join Our Newsletter</h5>
                    <p class="text-secondary mb-3">Subscribe to get special offers, free giveaways, and once-in-a-lifetime deals.</p>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control rounded-pill-start py-2 px-3 border-end-0" placeholder="Your Email Address">
                        <button class="btn btn-primary-custom rounded-pill-end px-4" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" type="button">Subscribe</button>
                    </div>
                </div>
            </div>
            <div class="text-center text-secondary border-top pt-4 mt-5">
                <p class="mb-0">&copy; 2026 CraftyGifts. All rights reserved. Handcrafted with <i class="fas fa-heart text-danger mx-1"></i></p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Fade up animation script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const fadeElements = document.querySelectorAll('.fade-up');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });
            
            fadeElements.forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>
