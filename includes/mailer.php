<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Send an OTP email to the given recipient.
 *
 * @param string $toEmail  Recipient email address.
 * @param string $toName   Recipient display name.
 * @param string $otp      6-digit OTP code.
 * @return bool            True on success, false on failure.
 */
function sendOtpEmail(string $toEmail, string $toName, string $otp): bool
{
    // ──────────────────────────────────────────────────────────────────────
    // TODO: Replace these values with your own Gmail credentials.
    //       Use an App Password (not your regular password).
    //       Gmail → Account → Security → 2-Step Verification → App Passwords
    // ──────────────────────────────────────────────────────────────────────
    $smtpUser = 'your_gmail@gmail.com';   // <-- your Gmail
    $smtpPass = 'your_app_password';      // <-- 16-char App Password

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($smtpUser, 'CraftyGifts');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your CraftyGifts Order OTP – ' . $otp;
        $mail->Body    = buildOtpEmailHtml($toName, $otp);
        $mail->AltBody = "Hello $toName,\n\nYour OTP to confirm your CraftyGifts order is: $otp\n\nThis code expires in 10 minutes. Do not share it with anyone.\n\n– CraftyGifts Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Build a beautiful HTML email body for the OTP.
 */
function buildOtpEmailHtml(string $name, string $otp): string
{
    $digits = str_split($otp);
    $digitBoxes = '';
    foreach ($digits as $d) {
        $digitBoxes .= "<span style=\"
            display:inline-block;
            width:52px; height:62px;
            line-height:62px;
            text-align:center;
            font-size:28px;
            font-weight:700;
            background:#fff;
            border:2px solid #e8628c;
            border-radius:14px;
            margin:0 5px;
            color:#c94f79;
            letter-spacing:0;
            box-shadow: 0 4px 14px rgba(232,98,140,0.12);
        \">$d</span>";
    }

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#fdf4f7;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#fdf4f7;padding:40px 20px;">
    <tr>
      <td align="center">
        <table width="560" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 8px 40px rgba(232,98,140,0.10);">
          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#e8628c 0%,#f4a0bd 100%);padding:36px 40px 28px;text-align:center;">
              <div style="font-size:36px;font-weight:800;color:#fff;letter-spacing:-1px;font-family:Georgia,serif;">CraftyGifts</div>
              <div style="font-size:13px;color:rgba(255,255,255,0.85);margin-top:4px;">Handcrafted with love 🎁</div>
            </td>
          </tr>
          <!-- Body -->
          <tr>
            <td style="padding:40px 40px 32px;">
              <p style="margin:0 0 8px;font-size:22px;font-weight:700;color:#2d2d2d;">Hello, $name! 👋</p>
              <p style="margin:0 0 28px;font-size:15px;color:#666;line-height:1.6;">
                You've requested to confirm your order on <strong>CraftyGifts</strong>. 
                Please use the One-Time Password (OTP) below to complete your purchase.
              </p>

              <!-- OTP Box -->
              <div style="background:#fdf4f7;border-radius:18px;padding:28px 20px;text-align:center;margin-bottom:28px;">
                <p style="margin:0 0 16px;font-size:13px;font-weight:600;color:#999;text-transform:uppercase;letter-spacing:1.5px;">Your OTP Code</p>
                <div>$digitBoxes</div>
                <p style="margin:16px 0 0;font-size:13px;color:#aaa;">Valid for <strong style="color:#e8628c;">10 minutes</strong> only</p>
              </div>

              <p style="margin:0;font-size:14px;color:#888;line-height:1.7;">
                🔒 For your security, never share this code with anyone.<br>
                If you did not request this, please ignore this email.
              </p>
            </td>
          </tr>
          <!-- Footer -->
          <tr>
            <td style="background:#fdf4f7;padding:24px 40px;text-align:center;border-top:1px solid #fce4ee;">
              <p style="margin:0;font-size:12px;color:#bbb;">© 2026 CraftyGifts · Handcrafted with <span style="color:#e8628c;">♥</span></p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}
