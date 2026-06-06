<?php
include 'includes/db.php';

// Sample categories
$pdo->exec("INSERT IGNORE INTO categories (id, name, description) VALUES 
(1, 'Personalized Mugs', 'Unique mugs with your photos and text'),
(2, 'Custom Jewelry', 'Handcrafted jewelry matching your style'),
(3, 'Wall Art', 'Decorative pieces for your home')");

// Sample products
$pdo->exec("INSERT IGNORE INTO products (id, category_id, name, description, base_price, image) VALUES 
(1, 1, 'Classic Photo Mug', 'High-quality ceramic mug with full color photo printing.', 14.99, 'https://images.unsplash.com/photo-1574633944818-a8325b48d516?auto=format&fit=crop&q=80&w=500'),
(2, 2, 'Silver Name Necklace', 'Handmade sterling silver necklace with custom name engraving.', 45.00, 'https://images.unsplash.com/photo-1515562141207-7a88bb7ce338?auto=format&fit=crop&q=80&w=500'),
(3, 3, 'Geometric Wooden Panel', 'Laser-cut wood panel for modern wall decoration.', 89.00, 'https://images.unsplash.com/photo-1549490349-8643362247b5?auto=format&fit=crop&q=80&w=500'),
(4, 1, 'Magic Heat-Changing Mug', 'Mug that reveals hidden image when hot liquid is added.', 19.99, 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?auto=format&fit=crop&q=80&w=500'),
(5, 2, 'Birthstone Ring', 'Custom gold ring with your choice of birthstone.', 120.00, 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&q=80&w=500'),
(6, 3, 'Abstract Canvas Paint', 'Original hand-painted abstract canvas art.', 150.00, 'https://images.unsplash.com/photo-1541963463532-d68292c34b19?auto=format&fit=crop&q=80&w=500')");

echo "Initial data seeded successfully.";
?>
