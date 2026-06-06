<?php
session_start();
include 'includes/db.php';

$category_id = isset($_GET['category']) ? $_GET['category'] : null;
$query = "SELECT * FROM products";
$params = [];

if ($category_id) {
    $query .= " WHERE category_id = ?";
    $params[] = $category_id;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

$catstmt = $pdo->query("SELECT * FROM categories");
$categories = $catstmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | CraftyGifts</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="glass">
        <nav>
            <a href="index.php" class="logo">CraftyGifts</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php" class="text-gradient">Shop</a></li>
                <li><a href="custom-request.php">Custom Order</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <div class="section-title">
                <h1>Browse Our Collection</h1>
                <p>Find the perfect gift or customize it to make it unique.</p>
            </div>

            <div style="display: flex; gap: 1rem; margin-bottom: 3rem; justify-content: center; flex-wrap: wrap;">
                <a href="products.php" class="btn <?php echo !$category_id ? 'btn-primary' : 'btn-outline'; ?>">All Items</a>
                <?php foreach($categories as $cat): ?>
                    <a href="products.php?category=<?php echo $cat['id']; ?>" class="btn <?php echo $category_id == $cat['id'] ? 'btn-primary' : 'btn-outline'; ?>">
                        <?php echo $cat['name']; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="grid">
                <?php foreach($products as $product): ?>
                <div class="card product-card animate-fade-in">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <div class="product-info">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="text-light" style="font-size: 0.9rem; margin-bottom: 1rem;"><?php echo substr($product['description'], 0, 80) . '...'; ?></p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="price">$<?php echo $product['base_price']; ?></span>
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Customize</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 CraftyGifts. All rights reserved.</p>
    </footer>
</body>
</html>
