<?php
/**
 * AI Greeting Message Generator
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/api-bridge.php';

header('Content-Type: application/json');

$category = $_POST['category'] ?? '';
$tone     = $_POST['tone'] ?? '';
$name     = trim($_POST['name'] ?? '');
$custom   = trim($_POST['custom'] ?? '');

try {
    // Try real AI first
    $aiMsg = '';
    if ($category && $tone) {
        $aiMsg = aiGenerateGreeting($category, $tone, $name);
    }

    if ($aiMsg) {
        echo json_encode(['success' => true, 'message' => $aiMsg]);
        exit;
    }

    // Fallback to DB templates
    $stmt = $pdo->prepare("SELECT message FROM ai_greetings WHERE category = ? AND tone = ? ORDER BY RAND() LIMIT 1");
    $stmt->execute([$category, $tone]);
    $row = $stmt->fetch();

    if ($row) {
        $msg = $row['message'];
        if ($name) {
            // Insert name into greeting
            $msg = preg_replace('/(Happy|Wishing|Congratulations|Thank|Love|Dear)/', '$1 ' . htmlspecialchars($name) . ',', $msg, 1);
        }
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        // Generate dynamically if no template
        $msgs = [
            'Birthday' => [
                'Cute' => "Happy Birthday" . ($name ? " $name" : "") . "! 🎂 May your day be sprinkled with joy and wrapped in love! 🎀✨",
                'Emotional' => "Another beautiful year around the sun" . ($name ? ", $name" : "") . ". You deserve all the happiness in the world. Happy Birthday! 🥹💕",
                'Funny' => "Congrats on leveling up" . ($name ? " $name" : "") . "! 🎉 You're not old, you're vintage! 😂😘",
                'Professional' => "Wishing you a wonderful birthday" . ($name ? " $name" : "") . "! May the year ahead bring success and joy. 🌟",
                'Romantic' => "To the one who makes life magical" . ($name ? " $name" : "") . " — Happy Birthday, my love! You're my greatest gift. 💖✨",
            ],
            'Anniversary' => [
                'Cute' => "Happy Anniversary" . ($name ? " $name" : "") . "! 🥰 Your love story is my favorite! Wishing you forever of happiness! 💑🎀",
                'Emotional' => "Through every season, your love remains" . ($name ? " $name" : "") . ". That's the most beautiful thing. Happy Anniversary ❤️",
                'Funny' => "You two are proof that love (and tolerance) exists! 😂 Happy Anniversary" . ($name ? " $name" : "") . "! 🎉",
                'Professional' => "Wishing you a very happy anniversary. May your partnership continue to flourish! 🥂",
                'Romantic' => "You are my today and all of my tomorrows" . ($name ? " $name" : "") . ". Happy Anniversary, my love. 💕",
            ],
            'Wedding' => [
                'Cute' => "Congratulations on your wedding" . ($name ? " $name" : "") . "! 🎊 Two hearts, one journey, endless love! 💑🎀",
                'Emotional' => "Witnessing your love" . ($name ? " $name" : "") . " fills my heart with so much joy. Wishing you a beautiful life together! 🥹💕",
                'Funny' => "You found your person" . ($name ? " $name" : "") . "! Now you have a partner in crime for life! 😂 Congratulations! 🎉",
                'Professional' => "Wishing you a lifetime of love and happiness together. Congratulations on your union! 🌟",
                'Romantic' => "Love isn't just a feeling — it's a promise" . ($name ? " $name" : "") . ". Congratulations on finding forever! 💖",
            ],
            'Thank You' => [
                'Cute' => "Thank you so much" . ($name ? " $name" : "") . "! You just made my day a million times better! 💕 Sending hugs! 🤗",
                'Emotional' => "Your kindness touched my heart deeply" . ($name ? " $name" : "") . ". Thank you for being such a wonderful blessing in my life. 🥹",
                'Funny' => "You're too good to me" . ($name ? " $name" : "") . "! But please don't ever stop! 😂 Thank you thank you! 💕",
                'Professional' => "I sincerely appreciate your support and generosity" . ($name ? " $name" : "") . ". Thank you! 🌟",
                'Romantic' => "Grateful for every little thing you do" . ($name ? " $name" : "") . ". My heart is so full because of you. 💗",
            ],
            'Friendship' => [
                'Cute' => "You're the sprinkles on my cupcake" . ($name ? " $name" : "") . "! 💕 So grateful for you! 🌟",
                'Emotional' => "True friends are rare" . ($name ? " $name" : "") . ", and I'm so blessed to have you in my world. 🥹🌸",
                'Funny' => "We're the kind of friends who'd share a dessert and then fight over the last bite 😂 Love you" . ($name ? " $name" : "") . "! 💕",
                'Professional' => "Thank you for being a wonderful friend and colleague. Truly grateful! 🤝",
                'Romantic' => "You're my person" . ($name ? " $name" : "") . ", and I don't know what I'd do without you. Love you to the moon and back! 💖",
            ],
            'Love' => [
                'Cute' => "You're the sweetest part of my day" . ($name ? " $name" : "") . "! 💕 Every moment with you is magic! ✨",
                'Emotional' => "I never knew love until I met you" . ($name ? " $name" : "") . ". You changed everything. Forever yours. 🥹❤️",
                'Funny' => "I love you more than coffee" . ($name ? " $name" : "") . " — and that's saying A LOT! 😂☕💕",
                'Professional' => "Words cannot fully express my admiration and respect for you" . ($name ? " $name" : "") . ". Simply grateful. 💌",
                'Romantic' => "In your eyes I found my home" . ($name ? " $name" : "") . ". In your heart I found my peace. I love you beyond the stars. 💖✨",
            ],
        ];

        $msg = $msgs[$category][$tone] ?? "Wishing you joy and happiness" . ($name ? " $name" : "") . "! ♡";
        echo json_encode(['success' => true, 'message' => $msg]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Could not generate greeting.']);
}
