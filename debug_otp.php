<?php
// Quick debug script - DELETE after fixing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Step 1: Vendor autoload</h2>";
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    die("<span style='color:red'>FAIL: vendor/autoload.php not found. Composer install may have failed.</span>");
}
require_once $autoload;
echo "<span style='color:green'>OK: vendor/autoload.php found</span><br>";

echo "<h2>Step 2: PHPMailer class</h2>";
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    die("<span style='color:red'>FAIL: PHPMailer class not found</span>");
}
echo "<span style='color:green'>OK: PHPMailer class loaded</span><br>";

echo "<h2>Step 3: mailer.php include</h2>";
include __DIR__ . '/includes/mailer.php';
echo "<span style='color:green'>OK: mailer.php loaded without errors</span><br>";

echo "<h2>Step 4: Session check</h2>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "otp_code in session: " . (isset($_SESSION['otp_code']) ? htmlspecialchars($_SESSION['otp_code']) : 'NOT SET') . "<br>";
echo "otp_pending_order in session: " . (isset($_SESSION['otp_pending_order']) ? 'SET' : 'NOT SET') . "<br>";

echo "<h2>Step 5: DB connection</h2>";
include __DIR__ . '/includes/db.php';
try {
    $pdo->query("SELECT 1");
    echo "<span style='color:green'>OK: Database connected</span><br>";
} catch (Exception $e) {
    echo "<span style='color:red'>FAIL: " . $e->getMessage() . "</span><br>";
}

echo "<h2>Step 6: Test OTP Email Send</h2>";
echo "<form method='POST'>";
echo "<input type='email' name='test_email' placeholder='Enter your email to test' style='padding:8px;width:300px;'> ";
echo "<button type='submit' name='send_test' style='padding:8px 16px;'>Send Test OTP Email</button>";
echo "</form>";

if (isset($_POST['send_test'])) {
    $testOtp   = '123456';
    $testEmail = $_POST['test_email'];
    echo "<br>Attempting to send OTP to: <strong>" . htmlspecialchars($testEmail) . "</strong><br>";
    $result = sendOtpEmail($testEmail, 'Test User', $testOtp);
    if ($result) {
        echo "<span style='color:green'>SUCCESS: OTP email sent! Check your inbox.</span><br>";
    } else {
        echo "<span style='color:red'>FAIL: Could not send email. Check mailer.php credentials.<br>";
        echo "Check C:\\xampp\\apache\\logs\\error.log for PHPMailer errors.</span><br>";
    }
}

echo "<hr><p style='color:gray;font-size:12px'>Delete this file (debug_otp.php) after fixing.</p>";
?>
