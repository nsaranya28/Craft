<?php
$host = 'localhost';
$username = 'root';
$password = 'pass'; // From your updated db.php

try {
    // Connect without database name first
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read schema.sql
    $sql = "DROP DATABASE IF EXISTS custom_craft_db;\n" . file_get_contents('schema.sql');
    
    // Execute SQL
    $pdo->exec($sql);
    
    // Automatically seed products, categories, users, and admins
    include 'seed.php';
    
    echo "<div style='font-family: sans-serif; padding: 2rem; background: #ecfdf5; color: #065f46; border: 1px solid #10b981; border-radius: 0.5rem;'>";
    echo "<h2>Success!</h2>";
    echo "<p>Database 'custom_craft_db' has been created, tables are initialized, and default products have been seeded successfully.</p>";
    echo "<p><a href='index.php' style='color: #059669; font-weight: bold;'>Go to Homepage</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family: sans-serif; padding: 2rem; background: #fef2f2; color: #991b1b; border: 1px solid #ef4444; border-radius: 0.5rem;'>";
    echo "<h2>Setup Failed</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please ensure your MySQL server is running and credentials are correct.</p>";
    echo "</div>";
}
?>
