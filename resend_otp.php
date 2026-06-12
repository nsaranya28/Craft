<?php
session_start();
require_once 'config.php';
require_once 'includes/mailer.php';

if (!isset($_SESSION['otp_user_id']) || !isset($_SESSION['otp_email'])) {
    header("Location: register.php");
    exit;
}

$userId = $_SESSION['otp_user_id'];
$email  = $_SESSION['otp_email'];

// Generate new OTP
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiry = date('Y-m-d H:i:s', time() + OTP_EXPIRY_SECONDS);

$stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE id = ?");
$stmt->execute([$otp, $expiry, $userId]);

// Reset attempt counter
$_SESSION['otp_attempts'] = 0;

// Get user name for email
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
$name = $user ? $user['name'] : 'User';

// Send OTP email
$mailError = '';
$sent = sendOtpEmail($email, $name, $otp, $mailError);

if ($sent) {
    $_SESSION['resend_success'] = 'A new OTP has been sent to your email.';
} else {
    $_SESSION['resend_error'] = 'Failed to resend OTP. ' . htmlspecialchars($mailError);
}

header("Location: verify_otp.php");
exit;
