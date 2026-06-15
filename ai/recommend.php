<?php
/**
 * AI Smart Gift Assistant — Recommendation Engine
 * Uses keyword matching + DB queries. Swap in real AI via api-bridge.php.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/api-bridge.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ─── Personalized Gift Generator ─────────────────────────────────────
    case 'generate_gifts':
        $relationship = $_POST['relationship'] ?? '';
        $occasion     = $_POST['occasion'] ?? '';
        $budget       = floatval($_POST['budget'] ?? 0);
        $age          = intval($_POST['age'] ?? 0);
        $gender       = $_POST['gender'] ?? '';

        // Build keyword query from relationship + occasion
        $keywords = strtolower("$relationship $occasion $gender");
        $gifts = recommendByKeywords($keywords, $budget);

        // Reason mapping
        $reasons = [
            'Friend'       => 'Friends love thoughtful, personalized gifts that show you care ♡',
            'Sister'       => 'Sisters appreciate heartfelt gifts that celebrate your special bond 💕',
            'Brother'      => 'Brothers enjoy cool, practical gifts that match their style 🎁',
            'Mother'       => 'Mothers cherish gifts made with love and thoughtfulness 🌸',
            'Father'       => 'Fathers value practical yet meaningful gestures of appreciation 🎯',
            'Wife'         => 'A romantic gift speaks volumes to your wife\'s heart 💖',
            'Husband'      => 'Surprise your husband with something that matches his passion 🎯',
            'Teacher'      => 'Teachers love gifts that acknowledge their hard work and dedication 📚',
            'Colleague'    => 'A professional yet thoughtful gift strengthens workplace bonds 🤝',
        ];
        $reason = $reasons[$relationship] ?? matchGiftReason($keywords);

        echo json_encode([
            'success' => true,
            'reason'  => $reason,
            'gifts'   => $gifts,
        ]);
        break;

    // ─── Trending Gifts ──────────────────────────────────────────────────
    case 'trending':
        $season = $_GET['season'] ?? 'default';
        $gifts = getTrendingGifts($season);
        echo json_encode(['success' => true, 'gifts' => $gifts]);
        break;

    // ─── Image Analysis (mock — real AI needs API key) ──────────────────
    case 'analyze_image':
        $session_id = $_POST['session_id'] ?? session_id();
        $file = $_FILES['image'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No image uploaded.']);
            break;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid('ai_img_') . '.' . $ext;
        $dest = __DIR__ . '/uploads/' . $name;
        move_uploaded_file($file['tmp_name'], $dest);
        $relPath = 'ai/uploads/' . $name;

        // Fake analysis — replace with real AI call
        $colors = ['pink', 'red', 'gold', 'blue', 'green', 'purple', 'white'];
        $themes = ['floral', 'geometric', 'minimalist', 'vintage', 'modern', 'cute'];
        $objects = ['heart', 'flower', 'star', 'circle', 'butterfly', 'ribbon', 'gift box'];

        $analysis = [
            'detected_colors'  => [ $colors[array_rand($colors)] ],
            'dominant_theme'   => $themes[array_rand($themes)],
            'detected_objects' => [ $objects[array_rand($objects)] ],
            'mood'             => ['warm', 'joyful', 'elegant', 'playful', 'romantic'][array_rand([0,1,2,3,4])],
            'confidence'       => round(75 + rand(0, 20) / 100, 2),
        ];

        $giftIdeas = recommendByKeywords(implode(' ', $analysis) . ' ' . ($_POST['occasion'] ?? ''), 0);
        $customIdeas = [
            'Add a personalized name or date to match the image theme',
            'Use the dominant colors from your image as a gift wrapping palette',
            'Incorporate the detected shape into a custom embroidery pattern',
            'Create a miniature version of the image as a keychain or ornament',
            'Turn the image pattern into a hand-painted ceramic design',
        ];

        // Save to DB
        $stmt = $pdo->prepare("INSERT INTO ai_image_uploads (session_id, image_path, analysis_result, recommendations) VALUES (?, ?, ?, ?)");
        $stmt->execute([$session_id, $relPath, json_encode($analysis), json_encode($giftIdeas)]);

        echo json_encode([
            'success'       => true,
            'image_url'     => '../' . $relPath,
            'analysis'      => $analysis,
            'gift_ideas'    => $giftIdeas,
            'customization' => $customIdeas,
        ]);
        break;

    // ─── Smart Search ────────────────────────────────────────────────────
    case 'smart_search':
        $query = trim($_POST['query'] ?? $_GET['query'] ?? '');
        if (!$query) {
            echo json_encode(['success' => false, 'message' => 'Please enter a search query.']);
            break;
        }

        $parsed = parseNaturalLanguage($query);
        $results = searchProducts($parsed);

        // Log analytics
        $stmt = $pdo->prepare("INSERT INTO ai_search_analytics (query, parsed_intent, results_count) VALUES (?, ?, ?)");
        $stmt->execute([$query, json_encode($parsed), count($results)]);

        echo json_encode([
            'success'  => true,
            'query'    => $query,
            'parsed'   => $parsed,
            'results'  => $results,
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}


// ─── Helper Functions ───────────────────────────────────────────────────────

function recommendByKeywords(string $keywords, float $budget): array {
    global $pdo;
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    $params = [];

    // Try real AI
    $aiIds = aiProductRecommend($keywords);
    if (!empty($aiIds)) {
        $placeholders = implode(',', array_fill(0, count($aiIds), '?'));
        $sql .= " AND p.id IN ($placeholders)";
        $params = $aiIds;
    } else {
        // Keyword matching
        $words = array_filter(explode(' ', strtolower($keywords)));
        $terms = [];
        foreach ($words as $w) {
            if (strlen($w) > 2) {
                $terms[] = $w;
            }
        }
        if (!empty($terms)) {
            $sql .= " AND (";
            $clauses = [];
            foreach ($terms as $t) {
                $clauses[] = "(LOWER(p.name) LIKE ? OR LOWER(p.description) LIKE ? OR LOWER(c.name) LIKE ?)";
                $like = "%$t%";
                $params[] = $like; $params[] = $like; $params[] = $like;
            }
            $sql .= implode(' OR ', $clauses) . ")";
        }
    }

    if ($budget > 0) {
        $sql .= " AND p.base_price <= ?";
        $params[] = $budget;
    }

    $sql .= " ORDER BY p.is_featured DESC, p.created_at DESC LIMIT 12";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function matchGiftReason(string $keywords): string {
    $reasons = [
        'friend'    => 'True friends deserve gifts that reflect your unique bond and shared memories 🎀',
        'love'      => 'Express your feelings with a gift that speaks directly from the heart 💕',
        'family'    => 'Family gifts are about celebrating the ties that bind us together forever 🌸',
        'work'      => 'A thoughtful gift shows colleagues and mentors how much you value them 🌟',
    ];
    foreach ($reasons as $key => $msg) {
        if (strpos($keywords, $key) !== false) return $msg;
    }
    return 'This recommendation is crafted just for you based on your unique preferences ♡';
}

function getTrendingGifts(string $season): array {
    global $pdo;
    // Best-sellers based on order_items
    $sql = "SELECT p.*, c.name as category_name, COALESCE(SUM(oi.quantity),0) as total_sold
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN categories c ON p.category_id = c.id
            GROUP BY p.id
            ORDER BY total_sold DESC, p.is_featured DESC
            LIMIT 8";
    return $pdo->query($sql)->fetchAll();
}

function parseNaturalLanguage(string $query): array {
    $query = strtolower($query);
    $parsed = [
        'original' => $query,
        'relationship' => '',
        'occasion'     => '',
        'budget'       => 0,
        'gender'       => '',
        'keywords'     => $query,
    ];

    $rels = ['friend'=>'friend', 'sister'=>'sister', 'brother'=>'brother', 'mother'=>'mother',
             'father'=>'father', 'wife'=>'wife', 'husband'=>'husband', 'teacher'=>'teacher',
             'girlfriend'=>'girlfriend', 'boyfriend'=>'boyfriend', 'parents'=>'parents'];
    foreach ($rels as $word => $rel) {
        if (strpos($query, $word) !== false) { $parsed['relationship'] = $rel; break; }
    }

    $occs = ['birthday'=>'Birthday', 'anniversary'=>'Anniversary', 'wedding'=>'Wedding',
             'valentine'=>'Valentine', 'graduation'=>'Graduation', 'diwali'=>'Diwali',
             'christmas'=>'Christmas', 'friendship'=>'Friendship Day'];
    foreach ($occs as $word => $occ) {
        if (strpos($query, $word) !== false) { $parsed['occasion'] = $occ; break; }
    }

    // Budget: match ₹ or Rs followed by number
    if (preg_match('/(?:₹|rs\.?\s*|under\s*|below\s*)(\d+)/i', $query, $m)) {
        $parsed['budget'] = (int)$m[1];
    }

    if (strpos($query, 'girl') !== false || strpos($query, 'female') !== false || strpos($query, 'her') !== false) {
        $parsed['gender'] = 'female';
    } elseif (strpos($query, 'boy') !== false || strpos($query, 'male') !== false || strpos($query, 'him') !== false) {
        $parsed['gender'] = 'male';
    }

    return $parsed;
}

function searchProducts(array $parsed): array {
    global $pdo;
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    $params = [];

    $words = array_filter(explode(' ', $parsed['keywords']));
    $terms = [];
    foreach ($words as $w) {
        if (strlen($w) > 2) $terms[] = $w;
    }
    if (!empty($terms)) {
        $sql .= " AND (";
        $clauses = [];
        foreach ($terms as $t) {
            $clauses[] = "(LOWER(p.name) LIKE ? OR LOWER(p.description) LIKE ? OR LOWER(c.name) LIKE ?)";
            $like = "%$t%";
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        $sql .= implode(' OR ', $clauses) . ")";
    }

    if ($parsed['budget'] > 0) {
        $sql .= " AND p.base_price <= ?";
        $params[] = $parsed['budget'];
    }

    $sql .= " ORDER BY p.is_featured DESC, p.created_at DESC LIMIT 12";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
