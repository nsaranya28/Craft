<?php
$pageTitle = 'Custom Orders';
include 'includes/header.php';
include 'includes/sidebar.php';

$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coid = intval($_POST['custom_id'] ?? 0);
    try {
        if (isset($_POST['approve'])) {
            $price = floatval($_POST['price_estimate'] ?? 0);
            $pdo->prepare("UPDATE custom_orders SET status='accepted', price_estimate=? WHERE id=?")->execute([$price, $coid]);
            $success = "Custom request #{$coid} accepted.";
        } elseif (isset($_POST['reject'])) {
            $pdo->prepare("UPDATE custom_orders SET status='rejected' WHERE id=?")->execute([$coid]);
            $success = "Custom request #{$coid} rejected.";
        } elseif (isset($_POST['complete'])) {
            $pdo->prepare("UPDATE custom_orders SET status='completed' WHERE id=?")->execute([$coid]);
            $success = "Custom request #{$coid} marked completed.";
        } elseif (isset($_POST['upload_preview'])) {
            $preview = trim($_POST['image_path']);
            $pdo->prepare("UPDATE custom_orders SET image_path=? WHERE id=?")->execute([$preview, $coid]);
            $success = "Design preview updated.";
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

try {
    $requests = $pdo->query("SELECT co.*, u.name as user_name, u.email as user_email FROM custom_orders co JOIN users u ON co.user_id = u.id ORDER BY co.created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $requests = []; $error = $e->getMessage();
}
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♡ Custom Orders ♡</h2>
            <p style="color: var(--text-light); margin: 0;">Manage customer design requests</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="glass-card text-center py-5" style="color: var(--text-light);">
            <i class="fa-solid fa-wand-magic-sparkles fa-3x mb-3" style="color: var(--pink-200);"></i>
            <p>No custom orders yet ♡</p>
        </div>
    <?php else: ?>
        <?php foreach ($requests as $req): ?>
            <div class="glass-card mb-3">
                <div class="row g-3 align-items-start">
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;"><?php echo htmlspecialchars($req['title'] ?: 'Custom Gift Request'); ?></h5>
                                <p class="small mb-0" style="color: var(--text-light);">
                                    by <strong><?php echo htmlspecialchars($req['user_name']); ?></strong> 
                                    &middot; <?php echo htmlspecialchars($req['user_email']); ?>
                                    &middot; <?php echo date('M d, Y', strtotime($req['created_at'])); ?>
                                </p>
                            </div>
                            <span class="badge-status status-<?php echo $req['status'] === 'pending' ? 'ordered' : ($req['status'] === 'accepted' ? 'delivered' : ($req['status'] === 'completed' ? 'paid' : 'cancelled')); ?>">
                                <?php echo $req['status']; ?>
                            </span>
                        </div>
                        <?php if ($req['description']): ?>
                            <p class="small mb-2"><?php echo nl2br(htmlspecialchars($req['description'])); ?></p>
                        <?php endif; ?>
                        <div class="d-flex flex-wrap gap-3 small" style="color: var(--text-light);">
                            <?php if ($req['color_selection']): ?><span>🎨 <?php echo htmlspecialchars($req['color_selection']); ?></span><?php endif; ?>
                            <?php if ($req['size_selection']): ?><span>📏 <?php echo htmlspecialchars($req['size_selection']); ?></span><?php endif; ?>
                            <?php if ($req['custom_message']): ?><span>💌 <?php echo htmlspecialchars($req['custom_message']); ?></span><?php endif; ?>
                            <?php if ($req['price_estimate']): ?><span class="fw-bold" style="color: var(--primary);">₹<?php echo number_format($req['price_estimate'], 2); ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?php if ($req['status'] === 'pending'): ?>
                            <form method="POST" class="d-flex flex-column gap-2">
                                <input type="hidden" name="custom_id" value="<?php echo $req['id']; ?>">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="price_estimate" class="form-control" placeholder="Set price" required>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="approve" class="btn btn-premium btn-sm flex-grow-1"><i class="fa-solid fa-check me-1"></i>Approve</button>
                                    <button type="submit" name="reject" class="btn btn-sm flex-grow-1" style="background: #fee2e2; color: #b91c1c; border: none; border-radius: 10px;" onclick="return confirm('Reject this request?')"><i class="fa-solid fa-xmark me-1"></i>Reject</button>
                                </div>
                            </form>
                        <?php elseif ($req['status'] === 'accepted'): ?>
                            <form method="POST" class="d-flex flex-column gap-2">
                                <input type="hidden" name="custom_id" value="<?php echo $req['id']; ?>">
                                <div class="d-flex gap-2">
                                    <input type="url" name="image_path" class="form-control form-control-sm" placeholder="Preview image URL" value="<?php echo htmlspecialchars($req['image_path'] ?? ''); ?>">
                                    <button type="submit" name="upload_preview" class="btn btn-premium-outline btn-sm" title="Upload Preview"><i class="fa-solid fa-image"></i></button>
                                </div>
                                <button type="submit" name="complete" class="btn btn-premium btn-sm"><i class="fa-solid fa-check-circle me-1"></i>Mark Complete</button>
                            </form>
                        <?php elseif ($req['status'] === 'completed' && $req['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($req['image_path']); ?>" alt="Design Preview" style="width: 100%; max-height: 120px; object-fit: cover; border-radius: 12px;">
                        <?php elseif ($req['status'] === 'rejected'): ?>
                            <span class="badge-status status-cancelled">Rejected</span>
                        <?php endif; ?>
                        <?php if ($req['special_instructions']): ?>
                            <div class="mt-2 small p-2" style="background: var(--pink-50); border-radius: 8px;">
                                <strong>📝 Notes:</strong> <?php echo nl2br(htmlspecialchars($req['special_instructions'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
