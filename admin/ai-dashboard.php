<?php
$pageTitle = 'AI Dashboard';
include 'includes/header.php';
include 'includes/sidebar.php';

try {
    // Chat analytics
    $totalChats = $pdo->query("SELECT COUNT(*) FROM ai_conversations")->fetchColumn();
    $uniqueSessions = $pdo->query("SELECT COUNT(DISTINCT session_id) FROM ai_conversations")->fetchColumn();
    $intentCounts = $pdo->query("SELECT intent, COUNT(*) as cnt FROM ai_conversations WHERE intent IS NOT NULL GROUP BY intent ORDER BY cnt DESC LIMIT 5")->fetchAll();
    $recentChats = $pdo->query("SELECT * FROM ai_conversations ORDER BY created_at DESC LIMIT 10")->fetchAll();

    // Search analytics
    $searchCount = $pdo->query("SELECT COUNT(*) FROM ai_search_analytics")->fetchColumn();
    $popularSearches = $pdo->query("SELECT query, COUNT(*) as cnt FROM ai_search_analytics GROUP BY query ORDER BY cnt DESC LIMIT 8")->fetchAll();

    // Image uploads
    $imageCount = $pdo->query("SELECT COUNT(*) FROM ai_image_uploads")->fetchColumn();

    // Greeting usage - from greeting page hits
    $greetingCount = $pdo->query("SELECT COUNT(*) FROM ai_greetings")->fetchColumn();

    // Products
    $totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

    // Revenue
    $totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status='paid'")->fetchColumn() ?: 0;

    // Top occasion keywords from custom_orders
    $occasionData = $pdo->query("SELECT title, COUNT(*) as cnt FROM custom_orders WHERE title IS NOT NULL GROUP BY title ORDER BY cnt DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    $totalChats = $uniqueSessions = $searchCount = $imageCount = $greetingCount = $totalProducts = $totalOrders = 0;
    $totalRevenue = 0;
    $intentCounts = $recentChats = $popularSearches = $occasionData = [];
}
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♡ AI Dashboard ♡</h2>
            <p style="color: var(--text-light); margin: 0;">AI Assistant analytics and insights</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, var(--pink-100), var(--pink-200)); color: var(--primary); width: 44px; height: 44px; font-size: 1.1rem;">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <h6 class="small fw-semibold" style="color: var(--text-light);">Chat Sessions</h6>
                <h4 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($uniqueSessions); ?></h4>
                <span class="small" style="color: var(--text-light);"><?php echo $totalChats; ?> messages</span>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, #e8e0ff, #d4c8ff); color: #6c4dff; width: 44px; height: 44px; font-size: 1.1rem;">
                    <i class="fa-solid fa-search"></i>
                </div>
                <h6 class="small fw-semibold" style="color: var(--text-light);">AI Searches</h6>
                <h4 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($searchCount); ?></h4>
                <span class="small" style="color: var(--text-light);">Smart queries</span>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #15803d; width: 44px; height: 44px; font-size: 1.1rem;">
                    <i class="fa-solid fa-image"></i>
                </div>
                <h6 class="small fw-semibold" style="color: var(--text-light);">Image Analyses</h6>
                <h4 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($imageCount); ?></h4>
                <span class="small" style="color: var(--text-light);">Uploads analyzed</span>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309; width: 44px; height: 44px; font-size: 1.1rem;">
                    <i class="fa-solid fa-message"></i>
                </div>
                <h6 class="small fw-semibold" style="color: var(--text-light);">Greetings</h6>
                <h4 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($greetingCount); ?></h4>
                <span class="small" style="color: var(--text-light);">Templates available</span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="glass-card">
                <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♥ Most Asked Topics ♥</h5>
                <?php if (empty($intentCounts)): ?>
                    <p class="text-center py-3" style="color: var(--text-light);">No chat data yet</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead><tr><th>Topic</th><th>Count</th></tr></thead>
                            <tbody>
                                <?php foreach ($intentCounts as $ic): ?>
                                    <tr>
                                        <td><span class="fw-semibold"><?php echo ucfirst($ic['intent']); ?></span></td>
                                        <td><?php echo $ic['cnt']; ?> messages</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card">
                <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♡ Popular Searches ♡</h5>
                <?php if (empty($popularSearches)): ?>
                    <p class="text-center py-3" style="color: var(--text-light);">No searches yet</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead><tr><th>Search Query</th><th>Searches</th></tr></thead>
                            <tbody>
                                <?php foreach ($popularSearches as $ps): ?>
                                    <tr>
                                        <td class="small">"<?php echo htmlspecialchars($ps['query']); ?>"</td>
                                        <td><span class="badge" style="background: var(--pink-50); color: var(--primary);"><?php echo $ps['cnt']; ?>x</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Chat Log -->
    <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♥ Recent Conversations ♥</h5>
        </div>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="custom-table">
                <thead><tr><th>User</th><th>Bot</th><th>Intent</th><th>Time</th></tr></thead>
                <tbody>
                    <?php if (empty($recentChats)): ?>
                        <tr><td colspan="4" class="text-center py-3" style="color: var(--text-light);">No conversations yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentChats as $rc): ?>
                            <tr>
                                <td style="max-width: 200px;" class="text-truncate small"><?php echo htmlspecialchars($rc['user_message']); ?></td>
                                <td style="max-width: 200px;" class="text-truncate small"><?php echo htmlspecialchars($rc['bot_response']); ?></td>
                                <td><span class="badge-status status-<?php echo $rc['intent'] ? 'delivered' : 'pending'; ?>"><?php echo $rc['intent'] ?: '—'; ?></span></td>
                                <td style="font-size: 0.72rem; color: var(--text-light);"><?php echo date('M d h:i A', strtotime($rc['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
