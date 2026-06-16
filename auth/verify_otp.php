<?php
session_start();
include '../includes/db.php';
include '../includes/mailer.php';

// Must come from the checkout OTP-send step
if (!isset($_SESSION['otp_code']) || !isset($_SESSION['otp_pending_order'])) {
    header("Location: ../checkout.php");
    exit;
}

$error   = '';
$success = '';

// ── Resend OTP ────────────────────────────────────────────────────────────────
if (isset($_GET['resend']) && $_GET['resend'] === '1') {
    $newOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['otp_code']       = $newOtp;
    $_SESSION['otp_expires_at'] = time() + 600; // 10 min

    $user_id = $_SESSION['user_id'];
    $stmt    = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    $mailError = '';
    if ($user && sendOtpEmail($user['email'], $user['name'], $newOtp, $mailError)) {
        $success = 'A new OTP has been sent to your registered email.';
    } else {
        $error = 'Failed to resend OTP. ' . htmlspecialchars($mailError);
    }
}

// ── Verify OTP ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOtp = trim(implode('', $_POST['otp_digit'] ?? []));

    if (empty($enteredOtp) || strlen($enteredOtp) < 6) {
        $error = 'Please enter the complete 6-digit OTP.';
    } elseif (time() > ($_SESSION['otp_expires_at'] ?? 0)) {
        $error = 'Your OTP has expired. Please <a href="?resend=1" class="alert-link">resend a new OTP</a>.';
    } elseif ($enteredOtp !== $_SESSION['otp_code']) {
        // Increment attempt counter
        $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
        if ($_SESSION['otp_attempts'] >= 5) {
            // Too many wrong attempts – cancel and redirect
            unset($_SESSION['otp_code'], $_SESSION['otp_pending_order'], $_SESSION['otp_expires_at'], $_SESSION['otp_attempts']);
            header("Location: ../checkout.php?otp_fail=1");
            exit;
        }
        $remaining = 5 - $_SESSION['otp_attempts'];
        $error = "Incorrect OTP. You have <strong>$remaining</strong> attempt(s) remaining.";
    } else {
        // ── OTP is correct → place the order ─────────────────────────────────
        $order = $_SESSION['otp_pending_order'];

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                "INSERT INTO orders (user_id, total_amount, status, payment_status, shipping_address)
                 VALUES (?, ?, 'ordered', 'paid', ?)"
            );
            $stmt->execute([$order['user_id'], $order['total'], $order['shipping_address']]);
            $orderId = $pdo->lastInsertId();

            // Save order items from cart
            if (!empty($_SESSION['cart'])) {
                $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                foreach ($_SESSION['cart'] as $cartItem) {
                    $pid = $cartItem['product_id'];
                    $qty = $cartItem['quantity'] ?? 1;
                    $priceStmt = $pdo->prepare("SELECT base_price FROM products WHERE id = ?");
                    $priceStmt->execute([$pid]);
                    $price = $priceStmt->fetchColumn() ?: 0;
                    $itemStmt->execute([$orderId, $pid, $qty, $price]);
                }
            }

            // Save UPI ID if payment is GPay or PhonePe
            if (!empty($order['upi_id'])) {
                @$pdo->exec("CREATE TABLE IF NOT EXISTS order_meta (order_id INT, meta_key VARCHAR(64), meta_value TEXT, INDEX idx_order (order_id))");
                $stmt = $pdo->prepare("INSERT INTO order_meta (order_id, meta_key, meta_value) VALUES (?, 'upi_id', ?)");
                $stmt->execute([$orderId, $order['upi_id']]);
            }

            $pdo->commit();

            // Store last order ID for feedback after redirect
            $_SESSION['last_order_id'] = $orderId;

            // Clear OTP & pending order data
            unset(
                $_SESSION['otp_code'],
                $_SESSION['otp_pending_order'],
                $_SESSION['otp_expires_at'],
                $_SESSION['otp_attempts']
            );
            $_SESSION['cart']         = [];
            $_SESSION['order_placed'] = true;
            $_SESSION['payment_method'] = $order['payment_method'];
            $_SESSION['upi_id']       = $order['upi_id'] ?? '';

            header("Location: ../user/rate-order.php?order_id=" . $orderId);
            exit;
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Failed to place order. Please try again.';
        }
    }
}

// Mask the email for display
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$row  = $stmt->fetch();
$maskedEmail = '';
if ($row) {
    [$local, $domain] = explode('@', $row['email']);
    $maskedEmail = substr($local, 0, 2) . str_repeat('*', max(2, strlen($local) - 2)) . '@' . $domain;
}

$expiresAt = $_SESSION['otp_expires_at'] ?? (time() + 600);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | CraftyGifts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* ── Page Background ── */
        .otp-bg {
            background: linear-gradient(135deg, var(--background) 0%, var(--pink-50) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        .otp-bg::before {
            content: '🔐';
            position: absolute;
            top: 8%;  left: 6%;
            font-size: 3rem;
            opacity: 0.08;
            animation: float 7s ease-in-out infinite;
        }
        .otp-bg::after {
            content: '♡';
            position: absolute;
            bottom: 12%; right: 8%;
            font-size: 2.5rem;
            color: var(--primary-light);
            opacity: 0.18;
            animation: float 5s ease-in-out infinite 1.2s;
        }

        /* ── Card ── */
        .otp-card {
            background: rgba(255,255,255,0.93);
            backdrop-filter: blur(20px);
            border: 1.5px solid rgba(232,98,140,0.12);
            border-radius: 30px;
            box-shadow: var(--shadow-soft);
            padding: 3rem 2.5rem;
        }

        /* ── OTP Input Boxes ── */
        .otp-inputs {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin: 2rem 0;
        }
        .otp-box {
            width: 58px;
            height: 68px;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            border: 2px solid var(--pink-200);
            border-radius: 16px;
            background: var(--cream);
            color: var(--text);
            transition: all 0.25s ease;
            outline: none;
            caret-color: var(--primary);
        }
        .otp-box:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(232,98,140,0.12);
            transform: translateY(-2px) scale(1.04);
        }
        .otp-box.filled {
            border-color: var(--primary);
            background: #fff5f9;
            color: var(--primary);
        }
        .otp-box.error-box {
            border-color: #dc3545;
            background: #fff5f5;
            animation: shake 0.4s ease;
        }

        /* ── Countdown Timer ── */
        .timer-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--pink-50);
            border: 1px solid var(--pink-200);
            border-radius: 50px;
            padding: 6px 18px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary);
        }
        .timer-badge.expired {
            background: #fff3f3;
            border-color: #f5c6cb;
            color: #dc3545;
        }

        /* ── Progress Bar ── */
        .otp-progress {
            height: 4px;
            border-radius: 10px;
            background: #fce4ee;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .otp-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #f4a0bd);
            border-radius: 10px;
            transition: width 1s linear;
        }

        /* ── Floating Bubbles ── */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(232,98,140,0.06);
            animation: float var(--d, 8s) ease-in-out infinite;
            animation-delay: var(--delay, 0s);
        }

        /* ── Shake animation for wrong OTP ── */
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-6px); }
            75%      { transform: translateX(6px); }
        }

        /* ── Submit Button ── */
        #verifyBtn {
            transition: all 0.3s ease;
        }
        #verifyBtn:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232,98,140,0.3);
        }
        #verifyBtn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        /* ── Shield icon animation ── */
        .shield-icon {
            font-size: 3.5rem;
            display: inline-block;
            animation: pulse-soft 2.5s ease-in-out infinite;
        }
        @keyframes pulse-soft {
            0%,100% { transform: scale(1); }
            50%      { transform: scale(1.06); }
        }
    </style>
</head>
<body class="otp-bg">

    <!-- Floating decorative bubbles -->
    <div class="bubble" style="width:180px;height:180px;top:-40px;right:15%;--d:9s;--delay:0s;"></div>
    <div class="bubble" style="width:100px;height:100px;bottom:60px;left:10%;--d:7s;--delay:2s;"></div>
    <div class="bubble" style="width:60px;height:60px;top:40%;left:3%;--d:6s;--delay:1s;"></div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">

                <!-- Brand -->
                <div class="text-center mb-4 fade-up visible">
                    <a href="../index.php" class="font-serif fs-2 fw-bold text-gradient text-decoration-none">CraftyGifts</a>
                </div>

                <!-- OTP Card -->
                <div class="otp-card fade-up visible" style="transition-delay:0.1s;">

                    <!-- Header -->
                    <div class="text-center mb-2">
                        <div class="shield-icon">🛡️</div>
                        <h2 class="font-serif fw-bold mt-2 mb-1">Order Verification</h2>
                        <p class="text-secondary mb-0">
                            We've sent a <strong>6-digit OTP</strong> to<br>
                            <span class="fw-semibold" style="color:var(--primary);"><?php echo htmlspecialchars($maskedEmail); ?></span>
                        </p>
                    </div>

                    <!-- Countdown progress bar -->
                    <div class="otp-progress mt-3">
                        <div class="otp-progress-bar" id="progressBar" style="width:100%;"></div>
                    </div>

                    <!-- Timer -->
                    <div class="text-center mb-1">
                        <span class="timer-badge" id="timerBadge">
                            <i class="fas fa-clock"></i>
                            <span id="countdown">10:00</span>
                        </span>
                    </div>

                    <!-- DEV MODE banner: shows OTP on screen when Gmail is not configured -->
                    <?php if (!empty($_SESSION['otp_dev_mode'])): ?>
                        <div class="alert mt-3 rounded-4" style="background:#fff8e1;border:2px dashed #f0b429;color:#7d5a00;" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-flask"></i>
                                <strong>Dev Mode — Gmail not configured</strong>
                            </div>
                            <p class="mb-1 small">Your OTP for this session is:</p>
                            <div style="font-size:2rem;font-weight:800;letter-spacing:10px;color:#c94f79;">
                                <?php echo htmlspecialchars($_SESSION['otp_code']); ?>
                            </div>
                            <p class="mb-0 mt-1 small">To send real emails, update <code>includes/mailer.php</code> with your Gmail credentials.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Alerts -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-4 mt-3" role="alert" id="alertBox">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success rounded-4 mt-3" role="alert" id="alertBox">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <!-- OTP Form -->
                    <form method="POST" id="otpForm">
                        <div class="otp-inputs" id="otpInputs">
                            <?php for ($i = 0; $i < 6; $i++): ?>
                                <input
                                    type="text"
                                    name="otp_digit[]"
                                    class="otp-box"
                                    id="otp<?php echo $i; ?>"
                                    maxlength="1"
                                    inputmode="numeric"
                                    autocomplete="<?php echo $i === 0 ? 'one-time-code' : 'off'; ?>"
                                    pattern="[0-9]"
                                    required
                                >
                            <?php endfor; ?>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 py-3 fs-5 d-flex align-items-center justify-content-center gap-2" id="verifyBtn">
                            <i class="fas fa-lock me-1"></i>
                            <span id="verifyBtnText">Verify & Place Order</span>
                        </button>
                    </form>

                    <!-- Resend & Back -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-secondary mb-1 small">Didn't receive the code?</p>
                        <a href="?resend=1" class="fw-bold text-decoration-none" style="color:var(--primary);" id="resendLink">
                            <i class="fas fa-redo-alt me-1"></i>Resend OTP
                        </a>
                        <span class="mx-2 text-muted">·</span>
                        <a href="../checkout.php" class="text-secondary text-decoration-none small">
                            <i class="fas fa-arrow-left me-1"></i>Back to Checkout
                        </a>
                    </div>

                </div><!-- /.otp-card -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        // ── OTP box auto-navigation ──────────────────────────────────────────
        const boxes  = document.querySelectorAll('.otp-box');
        const form   = document.getElementById('otpForm');
        const btn    = document.getElementById('verifyBtn');
        const btnTxt = document.getElementById('verifyBtnText');

        boxes.forEach((box, idx) => {
            box.addEventListener('input', e => {
                const val = e.target.value.replace(/\D/g, '');
                box.value = val.slice(-1);
                box.classList.toggle('filled', box.value !== '');

                if (box.value && idx < boxes.length - 1) {
                    boxes[idx + 1].focus();
                }
                checkComplete();
            });

            box.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !box.value && idx > 0) {
                    boxes[idx - 1].focus();
                    boxes[idx - 1].value = '';
                    boxes[idx - 1].classList.remove('filled');
                    checkComplete();
                }
                // Allow paste on first box
                if (e.key === 'v' && (e.ctrlKey || e.metaKey)) return;
            });

            // Handle paste on any box
            box.addEventListener('paste', e => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData)
                    .getData('text').replace(/\D/g, '').slice(0, 6);
                pasted.split('').forEach((ch, i) => {
                    if (boxes[i]) {
                        boxes[i].value = ch;
                        boxes[i].classList.add('filled');
                    }
                });
                const next = Math.min(pasted.length, 5);
                boxes[next].focus();
                checkComplete();
            });
        });

        function checkComplete() {
            const filled = [...boxes].every(b => b.value !== '');
            btn.disabled = !filled;
        }
        checkComplete(); // initial state

        // Shake on error
        <?php if ($error): ?>
        boxes.forEach(b => b.classList.add('error-box'));
        setTimeout(() => boxes.forEach(b => b.classList.remove('error-box')), 600);
        <?php endif; ?>

        // ── Countdown Timer ──────────────────────────────────────────────────
        const expiresAt   = <?php echo (int)$expiresAt; ?> * 1000; // ms
        const totalMs     = 10 * 60 * 1000; // 10 min
        const countdown   = document.getElementById('countdown');
        const progressBar = document.getElementById('progressBar');
        const timerBadge  = document.getElementById('timerBadge');
        const resendLink  = document.getElementById('resendLink');

        function updateTimer() {
            const remaining = Math.max(0, expiresAt - Date.now());
            const mins = Math.floor(remaining / 60000);
            const secs = Math.floor((remaining % 60000) / 1000);
            countdown.textContent =
                String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');

            const pct = (remaining / totalMs) * 100;
            progressBar.style.width = pct + '%';

            if (remaining <= 60000) {
                progressBar.style.background = 'linear-gradient(90deg,#dc3545,#ff6b6b)';
                timerBadge.classList.add('expired');
            }

            if (remaining === 0) {
                countdown.textContent = '00:00';
                progressBar.style.width = '0%';
                btn.disabled = true;
                btnTxt.textContent = 'OTP Expired – Resend';
                clearInterval(timerInterval);
            }
        }

        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);

        // ── Submit feedback ──────────────────────────────────────────────────
        form.addEventListener('submit', function () {
            btn.disabled = true;
            btnTxt.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying…';
        });

        // Focus first box on load
        boxes[0].focus();
    })();
    </script>
</body>
</html>
