<?php
session_start();
include 'includes/db.php';

$id = isset($_GET['id']) ? $_GET['id'] : die('Product not found');
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if(!$product) die('Product not found');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> | Customize | CraftyGifts</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: start;
        }
        .main-img {
            width: 100%;
            border-radius: 1.5rem;
            box-shadow: var(--shadow);
        }
        .custom-form {
            background: white;
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: var(--shadow);
        }
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .form-section h3 {
            margin-bottom: 1rem;
            font-size: 1.1rem;
            color: var(--text);
        }
        .option-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .option-item {
            padding: 0.5rem 1.2rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .option-item.active {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
            color: var(--primary);
            font-weight: 600;
        }
        textarea {
            width: 100%;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            margin-top: 0.5rem;
            font-family: inherit;
        }
    </style>
</head>
<body>
    <header class="glass">
        <nav>
            <a href="index.php" class="logo">CraftyGifts</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Shop</a></li>
                <li><a href="cart.php">Cart (0)</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <div class="details-grid animate-fade-in">
                <div class="image-preview">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="main-img">
                    <div style="margin-top: 2rem;">
                        <h2><?php echo $product['name']; ?></h2>
                        <p style="font-size: 1.25rem; color: var(--primary); font-weight: 700; margin: 0.5rem 0;">$<?php echo $product['base_price']; ?></p>
                        <p class="text-light"><?php echo $product['description']; ?></p>
                    </div>
                </div>

                <div class="custom-form">
                    <h2 style="margin-bottom: 2rem;">Customize Your Item</h2>
                    
                    <form action="cart-action.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div class="form-section">
                            <h3>Choose Size</h3>
                            <div class="option-group">
                                <label class="option-item active"><input type="radio" name="size" value="Small" checked style="display:none"> Small</label>
                                <label class="option-item"><input type="radio" name="size" value="Medium" style="display:none"> Medium</label>
                                <label class="option-item"><input type="radio" name="size" value="Large" style="display:none"> Large</label>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Select Color</h3>
                            <div class="option-group">
                                <label class="option-item"><input type="radio" name="color" value="White" style="display:none"> White</label>
                                <label class="option-item"><input type="radio" name="color" value="Black" style="display:none"> Black</label>
                                <label class="option-item"><input type="radio" name="color" value="Natural" style="display:none"> Natural</label>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Custom Text / Personalization</h3>
                            <p style="font-size: 0.85rem; color: var(--text-light);">Enter the text you want engraved or printed.</p>
                            <textarea name="custom_text" rows="3" placeholder="e.g. 'Happy Birthday Sarah!', 'Established 2024'"></textarea>
                        </div>

                        <div class="form-section">
                            <h3>Upload Image (Optional)</h3>
                            <p style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 1rem;">Attach a photo for reference or printing.</p>
                            <input type="file" name="custom_image" class="btn btn-outline" style="width: 100%;">
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
                            <div style="flex: 1;">
                                <label>Quantity</label>
                                <input type="number" name="quantity" value="1" min="1" style="width: 100%; padding: 0.8rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                            </div>
                            <button type="submit" class="btn btn-primary" style="flex: 2; height: 50px; margin-top: 1.5rem;">Add to Cart</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer style="margin-top: 4rem;">
        <p>&copy; 2026 CraftyGifts. All rights reserved.</p>
    </footer>

    <script>
        // Simple JS for option selection visual
        const labels = document.querySelectorAll('.option-item');
        labels.forEach(label => {
            label.addEventListener('click', () => {
                const group = label.parentElement;
                group.querySelectorAll('.option-item').forEach(l => l.classList.remove('active'));
                label.classList.add('active');
            });
        });
    </script>
</body>
</html>
