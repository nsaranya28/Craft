<?php
// admin/index.php – entry point for admin panel
session_start();
require_once __DIR__ . '/includes/auth.php';

if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: auth/login.php');
    exit;
}
?>
