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
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
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
                    <li class="nav-item"><a class="nav-link active fw-medium" href="index.php">Home</a></li>
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
    <div class="navbar-scallop-divider"></div>

    <!-- Hero Section -->
    <section class="hero-section">
        <!-- Floating Hearts & Sparkles -->
        <!-- Cute Sparkles SVG -->
        <svg class="doodle" style="top: 8%; left: 4%; width: 24px; height: 24px; color: var(--primary-light); animation-delay: 0s; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;" viewBox="0 0 24 24">
            <path d="M12 3 L14 9 L20 12 L14 15 L12 21 L10 15 L4 12 L10 9 Z" fill="rgba(242, 161, 183, 0.15)"/>
        </svg>
        <svg class="doodle" style="top: 35%; left: 2%; width: 18px; height: 18px; color: var(--primary-light); animation-delay: 1s; animation-name: pulse-soft; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </svg>
        <svg class="doodle" style="top: 15%; left: 35%; width: 20px; height: 20px; color: var(--primary); animation-delay: 0.5s; animation-name: twinkle; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;" viewBox="0 0 24 24">
            <path d="M12 3 L14 9 L20 12 L14 15 L12 21 L10 15 L4 12 L10 9 Z" fill="rgba(226, 95, 132, 0.2)"/>
        </svg>
        <svg class="doodle" style="top: 18%; right: 35%; width: 28px; height: 28px; color: var(--primary-light); animation-delay: 2s; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.8;" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </svg>
        <svg class="doodle" style="top: 45%; left: 45%; width: 14px; height: 14px; color: var(--primary); animation-delay: 1.5s; animation-name: twinkle; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;" viewBox="0 0 24 24">
            <path d="M12 3 L14 9 L20 12 L14 15 L12 21 L10 15 L4 12 L10 9 Z" />
        </svg>
        <svg class="doodle" style="bottom: 25%; left: 15%; width: 16px; height: 16px; color: var(--primary-light); animation-delay: 3s; animation-name: pulse-soft; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </svg>
        <svg class="doodle" style="top: 25%; right: 8%; width: 22px; height: 22px; color: var(--primary-light); animation-delay: 0.8s; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </svg>
        
        <!-- Paper Plane Winding Trail -->
        <svg class="doodle-paperplane d-none d-lg-block" style="position: absolute; bottom: 8%; left: 22%; width: 220px; height: 100px; opacity: 0.6; z-index: 1; pointer-events: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 220 100" fill="none">
            <path d="M10 80 Q 70 95 110 55 T 180 30" stroke="var(--primary-light)" stroke-width="2" stroke-dasharray="4,4" stroke-linecap="round"/>
            <g transform="translate(180, 20) rotate(-15)">
                <path d="M0 10 L18 0 L6 13 L0 10 Z" fill="var(--primary)" stroke="var(--primary)" stroke-width="1"/>
                <path d="M18 0 L8 7 L6 13 L18 0 Z" fill="var(--primary-light)" stroke="var(--primary)" stroke-width="1"/>
            </g>
        </svg>

        <div class="container" style="position: relative; z-index: 3;">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 fade-up">
                    <div class="badge-custom">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="currentColor" style="color: var(--primary); vertical-align: middle;">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        Handmade with Love
                    </div>
                    <h1 class="display-4 fw-bold mb-4 font-serif text-dark" style="line-height: 1.25;">
                        Thoughtful Gifts,<br>
                        <span class="text-gradient">Handcrafted</span> with 
                        <span style="position: relative; display: inline-block;">
                            Love
                            <svg style="position: absolute; bottom: -6px; left: 0; width: 100%; height: 8px;" viewBox="0 0 100 10" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 5 Q 50 9 97 3" stroke="var(--primary)" stroke-width="3" stroke-linecap="round" fill="none"/>
                            </svg>
                        </span>
                        <span style="font-size: 1.8rem; color: var(--primary-light); font-family: 'Poppins';">♡</span>
                    </h1>
                    <p class="lead text-secondary mb-4" style="font-size: 1.05rem;">
                        <span style="color: var(--primary);">♥</span> Unique handcrafted gifts designed to create lasting memories. Explore our collection or request a custom design tailored just for you.
                    </p>
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <a href="products.php" class="btn btn-primary-custom btn-lg">
                            Shop Collection <i class="fas fa-arrow-right ms-2" style="font-size: 0.9rem;"></i>
                        </a>
                        <a href="#custom" class="btn btn-outline-custom btn-lg">
                            Custom Order 
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </a>
                    </div>
                    
                    <!-- Feature Highlights -->
                    <div class="feature-highlights">
                        <div class="feature-highlight-item">
                            <div class="feature-highlight-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 10c1.5-2 3.5-2.5 5-1.5s1.5 3.5 0 5L12 18l-5-4.5c-1.5-1.5-1.5-4 0-5s3.5-.5 5 1.5z" fill="rgba(226, 95, 132, 0.1)"/>
                                    <path d="M3 14c.5-1.5 2-2 3.5-1.5s2 2 3.5 1.5" />
                                    <path d="M14 14c.5 1 2 1.5 3 1s1.5-2 1-3.5" />
                                </svg>
                            </div>
                            <div><small class="fw-medium">Handmade<br>with Care</small></div>
                        </div>
                        <div class="feature-highlight-item">
                            <div class="feature-highlight-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="8" width="18" height="4" rx="1" fill="rgba(226, 95, 132, 0.1)"/>
                                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7" />
                                    <path d="M12 8v13" />
                                    <path d="M12 8H7a2 2 0 0 1 0-4c2 0 5 4 5 4z" />
                                    <path d="M12 8h5a2 2 0 0 0 0-4c-2 0-5 4-5 4z" />
                                </svg>
                            </div>
                            <div><small class="fw-medium">Unique &<br>Meaningful</small></div>
                        </div>
                        <div class="feature-highlight-item">
                            <div class="feature-highlight-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 8.4 19 13.5c-3 1.5-5.5.5-8 6.5z" fill="rgba(226, 95, 132, 0.1)"/>
                                    <path d="M9 21c2-3 4-5 7-6" />
                                </svg>
                            </div>
                            <div><small class="fw-medium">Sustainable<br>& Thoughtful</small></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 fade-up" style="transition-delay: 0.2s; position: relative;">
                    <div class="hero-img-container">
                        <!-- Ribbon Bow Overlay -->
                        <svg class="ribbon-bow-overlay" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="none" stroke="currentColor">
                            <path d="M32 32 C20 15, 8 20, 14 34 C18 42, 28 36, 32 32 Z" fill="#EA698B" stroke="#D14A74" stroke-width="2"/>
                            <path d="M32 32 C44 15, 56 20, 50 34 C46 42, 36 36, 32 32 Z" fill="#EA698B" stroke="#D14A74" stroke-width="2"/>
                            <path d="M30 33 C26 44, 16 52, 12 55 M12 55 C16 55, 20 50, 22 45" stroke="#D14A74" stroke-width="2.5" stroke-linecap="round"/>
                            <path d="M34 33 C38 44, 48 52, 52 55 M52 55 C48 55, 44 50, 42 45" stroke="#D14A74" stroke-width="2.5" stroke-linecap="round"/>
                            <circle cx="32" cy="32" r="5" fill="#D14A74"/>
                        </svg>
                        <div class="hero-img-wrap">
                            <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&q=80&w=800" alt="Handmade Gift" class="hero-img">
                        </div>
                    </div>
                    <!-- Sticker Note -->
                    <div class="sticker-note d-none d-lg-block">
                        <svg style="color: var(--primary); fill: var(--primary); margin-bottom: 2px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        <div style="font-family: 'Caveat', cursive; font-size: 1.25rem; line-height: 1.1; font-weight: 500;">
                            made<br>with<br>love <span style="font-family: 'Poppins'; font-size: 0.95rem;">♡</span>
                        </div>
                    </div>
                    <!-- Cloud Bubble -->
                    <div class="cloud-bubble-wrap d-none d-lg-block">
                        <svg class="cloud-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="100%" height="100%">
                            <path d="M 50,80 A 30,30 0 0,1 40,30 A 35,35 0 0,1 110,20 A 35,35 0 0,1 170,40 A 30,30 0 0,1 160,90 A 25,25 0 0,1 100,100 A 25,25 0 0,1 50,80 Z" fill="var(--white)" stroke="var(--pink-200)" stroke-width="2" stroke-dasharray="4,4"/>
                        </svg>
                        <div class="cloud-content">
                            <svg class="cloud-ribbon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="20" height="20" fill="none" stroke="currentColor">
                                <path d="M32 32 C20 18, 10 24, 16 36 C20 44, 30 36, 32 32 Z" fill="#FFE4EA" stroke="var(--primary)" stroke-width="2"/>
                                <path d="M32 32 C44 18, 54 24, 48 36 C44 44, 34 36, 32 32 Z" fill="#FFE4EA" stroke="var(--primary)" stroke-width="2"/>
                                <circle cx="32" cy="32" r="4" fill="var(--primary)"/>
                            </svg>
                            <p class="cloud-text">Because every gift<br>✧ tells a story ♡</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scallop bottom divider for Hero -->
    <div class="hero-bottom-divider"></div>
    <!-- Gingham Stripe -->
    <div class="gingham-stripe">
        <span class="stripe-heart" style="left: 10%;">♡</span>
        <span class="stripe-heart" style="left: 30%;">♡</span>
        <span class="stripe-heart" style="left: 50%;">♡</span>
        <span class="stripe-heart" style="left: 70%;">♡</span>
        <span class="stripe-heart" style="left: 90%;">♡</span>
    </div>
    <div class="hero-bottom-divider-reversed"></div>

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

    <!-- Statistics Section -->
    <section class="section-padding stats-section">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-md-4 fade-up">
                    <h3 class="display-4 font-serif text-primary mb-2">10K+</h3>
                    <p class="text-secondary fw-medium fs-5">Happy Customers</p>
                </div>
                <div class="col-md-4 fade-up" style="transition-delay: 0.2s;">
                    <h3 class="display-4 font-serif text-primary mb-2">500+</h3>
                    <p class="text-secondary fw-medium fs-5">Unique Designs</p>
                </div>
                <div class="col-md-4 fade-up" style="transition-delay: 0.4s;">
                    <h3 class="display-4 font-serif text-primary mb-2">100%</h3>
                    <p class="text-secondary fw-medium fs-5">Handmade with Love</p>
                </div>
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

    <!-- Instagram Gallery -->
    <section class="section-padding overflow-hidden">
        <div class="container-fluid px-4">
            <div class="section-title fade-up mb-4">
                <h2>Follow Us @CraftyGifts</h2>
                <p>Share your beautiful moments using #CraftyGiftsLove</p>
            </div>
            <div class="d-flex justify-content-center w-100 fade-up" style="gap: 15px; flex-wrap: wrap;">
                <img src="https://images.unsplash.com/photo-1584992236310-6edddc08acff?auto=format&fit=crop&q=80&w=400" alt="Insta 1" style="width: 18vw; min-width: 150px; aspect-ratio: 1; object-fit: cover; border-radius: 20px; box-shadow: var(--shadow-soft); transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <img src="https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&q=80&w=400" alt="Insta 2" style="width: 18vw; min-width: 150px; aspect-ratio: 1; object-fit: cover; border-radius: 20px; box-shadow: var(--shadow-soft); transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <img src="https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?auto=format&fit=crop&q=80&w=400" alt="Insta 3" style="width: 18vw; min-width: 150px; aspect-ratio: 1; object-fit: cover; border-radius: 20px; box-shadow: var(--shadow-soft); transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <img src="https://images.unsplash.com/photo-1549490349-8643362247b5?auto=format&fit=crop&q=80&w=400" alt="Insta 4" style="width: 18vw; min-width: 150px; aspect-ratio: 1; object-fit: cover; border-radius: 20px; box-shadow: var(--shadow-soft); transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <img src="https://images.unsplash.com/photo-1574633944818-a8325b48d516?auto=format&fit=crop&q=80&w=400" alt="Insta 5" style="width: 18vw; min-width: 150px; aspect-ratio: 1; object-fit: cover; border-radius: 20px; box-shadow: var(--shadow-soft); transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            </div>
            <div class="text-center mt-5 fade-up">
                <a href="#" class="btn btn-outline-custom"><i class="fab fa-instagram me-2"></i> View on Instagram</a>
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
