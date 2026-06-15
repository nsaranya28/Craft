<?php
/**
 * AI Smart Gift Assistant — Chatbot Backend
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/api-bridge.php';

header('Content-Type: application/json');

$message = trim($_POST['message'] ?? '');
$session_id = $_POST['session_id'] ?? session_id();

if (!$message) {
    echo json_encode(['success' => false, 'message' => 'Please type a message.']);
    exit;
}

$msgLower = strtolower($message);
$response = '';

// ─── Try real AI first ─────────────────────────────────────────────────────
$aiReply = aiChatResponse($message);
if ($aiReply) {
    $response = $aiReply;
} else {
    $response = matchResponse($msgLower);
}

// Save conversation
$stmt = $pdo->prepare("INSERT INTO ai_conversations (session_id, user_message, bot_response, intent) VALUES (?, ?, ?, ?)");
$stmt->execute([$session_id, $message, $response, detectIntent($msgLower)]);

echo json_encode([
    'success'  => true,
    'reply'    => $response,
    'session'  => $session_id,
]);


// ─── Intent Detection ───────────────────────────────────────────────────────

function detectIntent(string $msg): string {
    if (preg_match('/(hello|hi|hey|namaste|good\s*(morning|evening))/', $msg)) return 'greeting';
    if (preg_match('/(gift|recommend|suggest|idea|present)/', $msg)) return 'gift_recommendation';
    if (preg_match('/(birthday|anniversary|wedding|valentine|diwali|christmas)/', $msg)) return 'occasion_query';
    if (preg_match('/(price|cost|budget|₹|rupees|affordable)/', $msg)) return 'pricing';
    if (preg_match('/(delivery|shipping|ship|track)/', $msg)) return 'delivery';
    if (preg_match('/(return|refund|cancel|exchange)/', $msg)) return 'returns';
    if (preg_match('/(customize|personalize|engrave|name)/', $msg)) return 'customization';
    if (preg_match('/(payment|pay|card|upi|cod)/', $msg)) return 'payment';
    if (preg_match('/(order|place|buy|purchase|checkout)/', $msg)) return 'ordering';
    if (preg_match('/(thank|thanks|appreciate)/', $msg)) return 'thanks';
    return 'general';
}

function matchResponse(string $msg): string {
    // Greetings
    if (preg_match('/(hello|hi|hey|namaste|good\s*(morning|evening|afternoon|day))/', $msg)) {
        $greetings = [
            "Hey there! I'm Crafty, your AI Gift Assistant ♡ How can I make your day special?",
            "Hello! ✨ Need help finding the perfect gift? I'm here for you!",
            "Hi! So glad you're here! 💕 Tell me who you're shopping for and I'll find something magical!",
            "Hey hey! 🎀 Ready to find something beautiful? Let's get started!",
        ];
        return $greetings[array_rand($greetings)];
    }

    // Gift recommendations
    if (preg_match('/(gift|recommend|suggest|what.*get|idea|present|for\s+(my\s+)?(friend|sister|brother|mother|father|wife|husband|girlfriend|boyfriend))/', $msg)) {
        $rel = '';
        if (preg_match('/(friend|sister|brother|mother|father|wife|husband|girlfriend|boyfriend|teacher|colleague)/', $msg, $m)) $rel = $m[1];
        $occ = '';
        if (preg_match('/(birthday|anniversary|wedding|valentine|diwali|christmas|graduation|friendship)/', $msg, $m)) $occ = $m[1];

        $responses = [
            "I'd love to help you find the perfect gift! 🎁 Could you tell me:\n• Who is it for? (friend, sister, mother...)\n• What's the occasion? (birthday, anniversary...)\n• Your budget?",
            "Let's find something amazing! 💕 Just tell me:\n• Relationship with the recipient\n• The special occasion\n• How much you'd like to spend",
            "Ooh, gift shopping is so exciting! ✨ Help me narrow it down — who's the lucky person and what's the celebration?",
        ];

        if ($rel && $occ) {
            return "A $occ gift for your $rel? How lovely! 🥰 Head over to our <a href='ai-assistant.php' style='color:var(--primary);text-decoration:underline;'>AI Gift Generator</a> for personalized recommendations, or tell me their age and budget!";
        }
        if ($rel) {
            return "Shopping for your $rel? That's so sweet! 💕 Could you also let me know the occasion and budget so I can find the perfect match?";
        }

        return $responses[array_rand($responses)];
    }

    // Pricing
    if (preg_match('/(price|cost|budget|₹|rupees|how\s*much|affordable|cheap|expensive)/', $msg)) {
        return "Our prices range from ₹199 for small crafts to ₹2,999 for premium personalized gifts! 🎀 You can browse our <a href='manage_products.php' style='color:var(--primary);text-decoration:underline;'>full collection here</a> or tell me your budget and I'll find something perfect!";
    }

    // Delivery
    if (preg_match('/(delivery|shipping|ship|track|when\s*will|how\s*long|arrive)/', $msg)) {
        return "🚚 Here's what you need to know about delivery:\n• Standard delivery: 5-7 business days (FREE over ₹499)\n• Express delivery: 2-3 business days (₹99 extra)\n• We ship across India 🇮🇳\n• You'll get a tracking number once shipped!\nNeed more details? Visit our Delivery Policy page! 📦";
    }

    // Returns
    if (preg_match('/(return|refund|cancel|exchange)/', $msg)) {
        return "💝 Our Return Policy:\n• Easy returns within 7 days of delivery\n• Full refund for damaged/incorrect items\n• Custom/personalized items are non-returnable\n• Refunds processed within 5-7 business days\nNeed help with a return? Contact us and we'll sort it out! ✨";
    }

    // Customization
    if (preg_match('/(customize|personalize|engrave|name|monogram|made\s*to\s*order)/', $msg)) {
        return "Yes, we LOVE custom orders! 🎨 Here's what you can personalize:\n• Names, dates, initials engraved\n• Custom colors and sizes\n• Hand-painted designs\n• Special messages on cards\n👉 Visit our <a href='#' style='color:var(--primary);text-decoration:underline;'>Custom Orders</a> page to get started!";
    }

    // Payment
    if (preg_match('/(payment|pay|card|upi|cod|debit|credit|online)/', $msg)) {
        return "💳 We accept:\n• UPI (GPay, PhonePe, Paytm)\n• Credit/Debit Cards\n• Net Banking\n• Cash on Delivery (₹50 extra)\n• All payments are 100% secure! 🔒";
    }

    // Ordering
    if (preg_match('/(order|place|buy|purchase|checkout|cart)/', $msg)) {
        return "Ready to order? Awesome! 🎉 Just add items to your cart and checkout. You'll receive an OTP on your email to confirm the order. If you need help along the way, I'm right here! 💕";
    }

    // Occasion queries
    if (preg_match('/(birthday|anniversary|wedding|valentine|diwali|christmas|graduation|friendship\s*day|mother.*day|father.*day)/', $msg, $m)) {
        $occ = $m[1];
        return "Oh, $occ shopping! How exciting! 🎉 We have a beautiful collection perfect for this occasion. Would you like me to suggest some gifts? Just tell me who it's for and your budget! ✨";
    }

    // Thanks
    if (preg_match('/(thank|thanks|appreciate|you\'re\s*great|helpful)/', $msg)) {
        return "Aww, you're so welcome! 🥰 It's my absolute pleasure to help you find something special. If you ever need anything, I'm just a message away! 💕✨";
    }

    // About
    if (preg_match('/(who\s*(are|is)|what\s*(are|is)|about|tell.*more|yourself)/', $msg)) {
        return "I'm Crafty — your AI Gift Assistant! 🎀 I'm here to help you find the perfect handmade gift, answer questions, and make your shopping experience magical. I can suggest gifts, generate greeting messages, and even recommend products from images! Just tell me what you need! ✨";
    }

    // Fallback
    $fallbacks = [
        "That's interesting! 🤔 I'm still learning, but I can definitely help with gift recommendations, product searches, or questions about delivery and payments. What would you like to know? 💕",
        "I want to help! ✨ Could you try asking me about gift suggestions, product details, delivery info, or anything else about CraftyGifts?",
        "Hmm, I'm not sure I understood that one! 😅 But don't worry — I can still help you find amazing gifts! Try saying something like 'Suggest a gift for my sister under ₹500'! 🎁",
        "I'm here to help with all things gifting! 🎀 Just ask me for recommendations, check order status, or learn about our products. What's on your mind?",
    ];
    return $fallbacks[array_rand($fallbacks)];
}
