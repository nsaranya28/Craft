<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email)) {
        $_SESSION['settings_error'] = 'Name and email are required.';
    } else {
        // Check email not taken by another user
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $user_id]);
        if ($check->fetch()) {
            $_SESSION['settings_error'] = 'That email is already in use by another account.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
            $stmt->execute([$name, $email, $phone, $user_id]);
            $_SESSION['user_name'] = $name;
            $_SESSION['settings_success'] = 'Profile updated successfully!';
        }
    }
} elseif ($action === 'change_password') {
    $current  = $_POST['current_password'] ?? '';
    $new      = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!password_verify($current, $user['password'])) {
        $_SESSION['settings_error'] = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $_SESSION['settings_error'] = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $_SESSION['settings_error'] = 'Passwords do not match.';
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->execute([$hashed, $user_id]);
        $_SESSION['settings_success'] = 'Password changed successfully!';
    }
}

header("Location: dashboard.php?tab=settings");
exit;
