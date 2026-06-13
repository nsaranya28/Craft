<?php
$pageTitle = 'Messages';
include 'includes/header.php';
include 'includes/sidebar.php';

$success = ''; $error = '';

// Reply to a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_msg'])) {
    $mid = intval($_POST['msg_id']);
    $reply = trim($_POST['reply']);
    try {
        $pdo->prepare("UPDATE contact_messages SET reply=?, replied_at=NOW(), is_read=1 WHERE id=?")->execute([$reply, $mid]);
        $success = "Reply sent.";
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

// Mark as read
if (isset($_GET['mark_read'])) {
    $mid = intval($_GET['mark_read']);
    $pdo->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([$mid]);
    header('Location: manage_messages.php');
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $mid = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM contact_messages WHERE id=?")->execute([$mid]);
    header('Location: manage_messages.php');
    exit;
}

try {
    $messages = $pdo->query("SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $messages = []; $error = $e->getMessage();
}
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♡ Messages ♡</h2>
            <p style="color: var(--text-light); margin: 0;">Contact form submissions</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (empty($messages)): ?>
            <div class="col-12">
                <div class="glass-card text-center py-5" style="color: var(--text-light);">
                    <i class="fa-solid fa-envelope-open-text fa-3x mb-3" style="color: var(--pink-200);"></i>
                    <p>No messages yet ♡</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($messages as $m): ?>
                <div class="col-lg-6">
                    <div class="glass-card" style="<?php echo !$m['is_read'] ? 'border-left: 4px solid var(--primary);' : ''; ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($m['name']); ?></h6>
                                <a href="mailto:<?php echo htmlspecialchars($m['email']); ?>" style="color: var(--text-light); font-size: 0.8rem;"><?php echo htmlspecialchars($m['email']); ?></a>
                            </div>
                            <div class="d-flex gap-1">
                                <?php if (!$m['is_read']): ?>
                                    <a href="?mark_read=<?php echo $m['id']; ?>" class="btn btn-sm py-1 px-2" style="background: var(--pink-50); color: var(--primary); border: none; border-radius: 10px;" title="Mark Read"><i class="fa-solid fa-eye"></i></a>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $m['id']; ?>" class="btn btn-sm py-1 px-2" style="background: #fee2e2; color: #b91c1c; border: none; border-radius: 10px;" onclick="return confirm('Delete this message?')" title="Delete"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </div>
                        <?php if ($m['subject']): ?>
                            <div class="fw-semibold small mb-1" style="color: var(--text);"><?php echo htmlspecialchars($m['subject']); ?></div>
                        <?php endif; ?>
                        <p class="small mb-2" style="color: var(--text);"><?php echo nl2br(htmlspecialchars($m['message'])); ?></p>
                        <div style="color: var(--text-light); font-size: 0.7rem;"><?php echo date('M d, Y h:i A', strtotime($m['created_at'])); ?></div>

                        <?php if ($m['reply']): ?>
                            <div class="mt-3 p-3" style="background: var(--pink-50); border-radius: 12px;">
                                <div class="fw-semibold small mb-1" style="color: var(--primary);">♥ Your Reply:</div>
                                <p class="small mb-0"><?php echo nl2br(htmlspecialchars($m['reply'])); ?></p>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="msg_id" value="<?php echo $m['id']; ?>">
                                <div class="d-flex gap-2">
                                    <input type="text" name="reply" class="form-control form-control-sm" placeholder="Write a reply..." required style="border-radius: 12px;">
                                    <button type="submit" name="reply_msg" class="btn btn-premium btn-sm"><i class="fa-solid fa-reply me-1"></i>Reply</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
