<?php
// ─── Database Configuration ───────────────────────────────────────────────
$host = 'localhost';
$dbname = 'custom_craft_db';
$username = 'root';
$password = 'pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ─── Constants ────────────────────────────────────────────────────────────
define('OTP_EXPIRY_SECONDS', 300);     // 5 minutes
define('OTP_MAX_ATTEMPTS', 3);         // max verification attempts
define('SITE_NAME', 'CraftyGifts');
