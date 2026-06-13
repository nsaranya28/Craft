<?php
$pageTitle = 'Reviews';
include 'includes/header.php';
include 'includes/sidebar.php';

$success = ''; $error = '';

// Approve / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid = intval($_POST['review_id'] ?? 0);
    if ($rid > 0) {
        try {
            if (isset($_POST['approve'])) {
                $pdo->prepare("UPDATE reviews SET is_approved=1 WHERE id=?")->execute([$rid]);
                $success = "Review #{$rid} approved.";
            } elseif (isset($_POST['delete'])) {
                $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([$rid]);
                $success = "Review #{$rid} deleted.";
            }
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}

try {
    $reviews = $pdo->query("SELECT r.*, u.name as user_name, p.name as product_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id ORDER BY r.created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $reviews = []; $error = $e->getMessage();
}
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♥ Reviews ♥</h2>
            <p style="color: var(--text-light); margin: 0;">Manage customer feedback</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr><th>Product</th><th>Customer</th><th>Rating</th><th>Comment</th><th>Date</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                        <tr><td colspan="7" class="text-center py-4" style="color: var(--text-light);">No reviews yet ♡</td></tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $r): ?>
                            <tr>
                                <td class="fw-semibold small"><?php echo htmlspecialchars($r['product_name']); ?></td>
                                <td style="font-size: 0.85rem;"><?php echo htmlspecialchars($r['user_name']); ?></td>
                                <td>
                                    <span style="color: #f59e0b;">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <i class="fa-<?php echo $i < $r['rating'] ? 'solid' : 'regular'; ?> fa-star" style="font-size: 0.75rem;"></i>
                                        <?php endfor; ?>
                                    </span>
                                </td>
                                <td style="font-size: 0.82rem; max-width: 250px;" class="text-truncate"><?php echo htmlspecialchars($r['comment'] ?? '—'); ?></td>
                                <td style="font-size: 0.78rem; color: var(--text-light);"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                                <td>
                                    <span class="badge-status <?php echo $r['is_approved'] ? 'status-delivered' : 'status-ordered'; ?>">
                                        <?php echo $r['is_approved'] ? 'Approved' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="review_id" value="<?php echo $r['id']; ?>">
                                        <?php if (!$r['is_approved']): ?>
                                            <button type="submit" name="approve" class="btn btn-premium btn-sm py-1 px-2" title="Approve"><i class="fa-solid fa-check"></i></button>
                                        <?php endif; ?>
                                        <button type="submit" name="delete" class="btn btn-sm py-1 px-2" style="background: #fee2e2; color: #b91c1c; border: none; border-radius: 10px;" onclick="return confirm('Delete this review?')" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
