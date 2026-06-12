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
$error  = '';
$success = '';

// ── Verify OTP ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $entered = trim(implode('', $_POST['otp_digit'] ?? []));

    if (empty($entered) || strlen($entered) !== 6) {
        $error = 'Please enter the complete 6-digit OTP.';
    } else {
        $stmt = $pdo->prepare("SELECT otp, otp_expiry, email_verified FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'User not found. Please register again.';
        } elseif ($user['email_verified']) {
            $success = 'Your email is already verified. You can log in now.';
        } elseif (strtotime($user['otp_expiry']) < time()) {
            $error = 'OTP has expired. Please resend a new OTP.';
        } elseif ($entered !== $user['otp']) {
            $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
            $remaining = OTP_MAX_ATTEMPTS - $_SESSION['otp_attempts'];
            if ($remaining <= 0) {
                $pdo->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE id = ?")->execute([$userId]);
                unset($_SESSION['otp_user_id'], $_SESSION['otp_email'], $_SESSION['otp_attempts']);
                $error = 'Too many failed attempts. Please <a href="register.php" class="alert-link">register again</a>.';
            } else {
                $error = "Incorrect OTP. You have $remaining attempt(s) remaining.";
            }
        } else {
            $pdo->prepare("UPDATE users SET email_verified = 1, otp = NULL, otp_expiry = NULL WHERE id = ?")->execute([$userId]);
            unset($_SESSION['otp_user_id'], $_SESSION['otp_email'], $_SESSION['otp_attempts']);
            $_SESSION['verified'] = true;
            header("Location: login.php?verified=1");
            exit;
        }
    }
}

// ── Mask email for display ────────────────────────────────────────────────
$parts = explode('@', $email);
$masked = (strlen($parts[0]) > 2)
    ? substr($parts[0], 0, 2) . str_repeat('*', strlen($parts[0]) - 2)
    : $parts[0][0] . '*';
$maskedEmail = $masked . '@' . $parts[1];

// ── Get expiry timestamp for countdown ────────────────────────────────────
$stmt = $pdo->prepare("SELECT otp_expiry FROM users WHERE id = ?");
$stmt->execute([$userId]);
$row = $stmt->fetch();
$expiresAt = $row ? strtotime($row['otp_expiry']) : (time() + OTP_EXPIRY_SECONDS);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #fdf4f7 0%, #fce4ee 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        body::before, body::after {
            position: absolute;
            animation: float 7s ease-in-out infinite;
            opacity: 0.08;
        }
        body::before { content: '🔐'; top: 8%; left: 6%; font-size: 3.5rem; }
        body::after  { content: '♡'; bottom: 12%; right: 8%; font-size: 2.5rem; animation-delay: 1s; }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-18px); }
        }
        .glass-card {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(24px);
            border: 1.5px solid rgba(232,98,140,0.12);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(232,98,140,0.08);
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
            animation: fadeUp 0.6s ease;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .brand {
            font-size: 1.8rem;
            font-weight: 800;
            font-family: Georgia, serif;
            background: linear-gradient(135deg, #e8628c, #f4a0bd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 0.25rem;
        }
        .shield-icon {
            font-size: 3rem;
            display: inline-block;
            animation: pulse 2.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.06); }
        }
        .otp-boxes {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 1.5rem 0;
        }
        .otp-box {
            width: 54px;
            height: 64px;
            text-align: center;
            font-size: 26px;
            font-weight: 700;
            border: 2px solid #f0d0dc;
            border-radius: 16px;
            background: #fdf4f7;
            color: #2d2d2d;
            outline: none;
            caret-color: #e8628c;
            transition: all 0.2s;
        }
        .otp-box:focus {
            border-color: #e8628c;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(232,98,140,0.10);
            transform: translateY(-2px);
        }
        .otp-box.filled {
            border-color: #e8628c;
            background: #fff5f9;
            color: #e8628c;
        }
        .timer-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fce4ee;
            border: 1px solid #f0d0dc;
            border-radius: 50px;
            padding: 5px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #e8628c;
        }
        .timer-badge.expired {
            background: #fff3f3;
            border-color: #f5c6cb;
            color: #dc3545;
        }
        .progress {
            height: 4px;
            border-radius: 10px;
            background: #fce4ee;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #e8628c, #f4a0bd);
            transition: width 1s linear;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #e8628c, #f4a0bd);
            border: none;
            border-radius: 14px;
            padding: 0.8rem;
            font-weight: 600;
            color: #fff;
            transition: all 0.3s;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232,98,140,0.25);
            color: #fff;
        }
        .btn-primary-custom:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
        @media (max-width: 500px) {
            .glass-card { padding: 2rem 1.5rem; }
            .otp-box { width: 44px; height: 54px; font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="glass-card">
        <div class="text-center mb-2">
            <div class="shield-icon">🛡️</div>
            <div class="brand">CraftyGifts</div>
            <p class="text-secondary small mb-0">Enter the OTP sent to</p>
            <p class="fw-semibold" style="color:#e8628c;font-size:0.95rem;"><?php echo htmlspecialchars($maskedEmail); ?></p>
        </div>

        <div class="progress mb-3">
            <div class="progress-bar" id="progressBar" style="width:100%"></div>
        </div>

        <div class="text-center mb-2">
            <span class="timer-badge" id="timerBadge">
                <i class="fas fa-clock"></i>
                <span id="countdown">05:00</span>
            </span>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger rounded-4 py-2 small"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success rounded-4 py-2 small"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" id="otpForm">
            <div class="otp-boxes" id="otpBoxes">
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text" name="otp_digit[]" class="otp-box" id="box<?php echo $i; ?>" maxlength="1" inputmode="numeric" pattern="[0-9]" required autocomplete="off">
                <?php endfor; ?>
            </div>
            <button type="submit" name="verify" class="btn btn-primary-custom w-100" id="verifyBtn" disabled>
                <i class="fas fa-lock me-1"></i> Verify & Activate
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <p class="small text-secondary mb-1">Didn't receive the code?</p>
            <a href="resend_otp.php" class="fw-bold text-decoration-none" style="color:#e8628c;">
                <i class="fas fa-redo-alt me-1"></i> Resend OTP
            </a>
            <span class="mx-2 text-muted">·</span>
            <a href="register.php" class="text-secondary small text-decoration-none">Back to Register</a>
        </div>
    </div>

    <script>
    (function() {
        const boxes = document.querySelectorAll('.otp-box');
        const form  = document.getElementById('otpForm');
        const btn   = document.getElementById('verifyBtn');

        boxes.forEach((box, idx) => {
            box.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(-1);
                this.classList.toggle('filled', this.value !== '');
                if (this.value && idx < boxes.length - 1) boxes[idx + 1].focus();
                updateBtn();
            });
            box.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && idx > 0) {
                    boxes[idx - 1].value = '';
                    boxes[idx - 1].classList.remove('filled');
                    boxes[idx - 1].focus();
                    updateBtn();
                }
            });
            box.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                paste.split('').forEach((ch, i) => {
                    if (boxes[i]) { boxes[i].value = ch; boxes[i].classList.add('filled'); }
                });
                boxes[Math.min(paste.length, 5)].focus();
                updateBtn();
            });
        });

        function updateBtn() {
            btn.disabled = ![...boxes].every(b => b.value !== '');
        }
        updateBtn();

        form.addEventListener('submit', function() {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying…';
        });

        // ── Countdown ─────────────────────────────────────────────────────
        const expiresAt = <?php echo $expiresAt * 1000; ?>;
        const totalMs   = <?php echo OTP_EXPIRY_SECONDS * 1000; ?>;
        const cd = document.getElementById('countdown');
        const pb = document.getElementById('progressBar');
        const tb = document.getElementById('timerBadge');

        function tick() {
            const rem = Math.max(0, expiresAt - Date.now());
            const m = Math.floor(rem / 60000);
            const s = Math.floor((rem % 60000) / 1000);
            cd.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
            pb.style.width = (rem / totalMs * 100) + '%';
            if (rem <= 60000) { pb.style.background = 'linear-gradient(90deg,#dc3545,#ff6b6b)'; tb.classList.add('expired'); }
            if (rem <= 0) {
                cd.textContent = '00:00';
                pb.style.width = '0%';
                btn.disabled = true;
                clearInterval(timer);
            }
        }
        tick();
        const timer = setInterval(tick, 1000);
        boxes[0].focus();
    })();
    </script>
</body>
</html>
