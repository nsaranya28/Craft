<?php
session_start();
require_once __DIR__ . '/admin/includes/auth.php';
requireAdminLogin();
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'AI Gift Assistant';
include 'admin/includes/header.php';
include 'admin/includes/sidebar.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$trending = $pdo->query("SELECT p.*, c.name as category_name, COALESCE(SUM(oi.quantity),0) as total_sold FROM products p LEFT JOIN order_items oi ON p.id = oi.product_id LEFT JOIN categories c ON p.category_id = c.id GROUP BY p.id ORDER BY total_sold DESC, p.is_featured DESC LIMIT 8")->fetchAll();
?>
<style>
.ai-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.ai-tab { padding: 0.6rem 1.2rem; border-radius: 14px; border: 1.5px solid var(--pink-200); background: transparent; color: var(--text); font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: all 0.3s; }
.ai-tab:hover { background: var(--pink-50); }
.ai-tab.active { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border-color: transparent; box-shadow: 0 4px 15px rgba(232,98,140,0.25); }
.ai-tab i { margin-right: 0.4rem; }
.ai-panel { display: none; animation: fadeIn 0.4s ease; }
.ai-panel.active { display: block; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.gift-card { background: rgba(255,255,255,0.6); border-radius: 16px; padding: 1rem; border: 1px solid var(--glass-border); transition: all 0.3s; }
.gift-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-hover); }
.gift-card img { width: 100%; height: 140px; object-fit: cover; border-radius: 12px; margin-bottom: 0.6rem; }
.gift-card h6 { font-size: 0.9rem; font-weight: 600; margin-bottom: 0.2rem; }
.gift-card .price { color: var(--primary); font-weight: 700; font-size: 0.95rem; }
.gift-card .badge { font-size: 0.65rem; }

.form-select-sm, .form-control-sm { border-radius: 12px !important; border-color: var(--pink-200) !important; }

.copy-btn { cursor: pointer; transition: all 0.2s; }
.copy-btn:hover { transform: scale(1.05); }

.confidence-bar { height: 6px; border-radius: 3px; background: var(--pink-100); overflow: hidden; }
.confidence-bar .fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--primary), var(--primary-light)); transition: width 0.8s; }
</style>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♡ AI Smart Gift Assistant ♡</h2>
            <p style="color: var(--text-light); margin: 0;">✨ Powered by intelligent recommendations</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="ai-tabs">
        <div class="ai-tab active" data-tab="generator"><i class="fa-solid fa-wand-magic-sparkles"></i>Gift Generator</div>
        <div class="ai-tab" data-tab="greetings"><i class="fa-solid fa-message"></i>Greetings</div>
        <div class="ai-tab" data-tab="image"><i class="fa-solid fa-image"></i>Image to Gift</div>
        <div class="ai-tab" data-tab="trending"><i class="fa-solid fa-fire"></i>Trending</div>
        <div class="ai-tab" data-tab="search"><i class="fa-solid fa-search"></i>Smart Search</div>
        <div class="ai-tab" data-tab="chat"><i class="fa-solid fa-robot"></i>AI Chat</div>
    </div>

    <!-- ─── Panel 1: Gift Generator ────────────────────────────────────── -->
    <div class="ai-panel active" id="panel-generator">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass-card">
                    <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♥ Tell me about them ♥</h5>
                    <form id="giftForm">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Who is it for?</label>
                            <select class="form-select form-select-sm" name="relationship" required>
                                <option value="">Select relationship</option>
                                <option value="Friend">Friend</option>
                                <option value="Best Friend">Best Friend</option>
                                <option value="Sister">Sister</option>
                                <option value="Brother">Brother</option>
                                <option value="Mother">Mother</option>
                                <option value="Father">Father</option>
                                <option value="Wife">Wife</option>
                                <option value="Husband">Husband</option>
                                <option value="Girlfriend">Girlfriend</option>
                                <option value="Boyfriend">Boyfriend</option>
                                <option value="Teacher">Teacher</option>
                                <option value="Colleague">Colleague</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Occasion?</label>
                            <select class="form-select form-select-sm" name="occasion" required>
                                <option value="">Select occasion</option>
                                <option value="Birthday">Birthday 🎂</option>
                                <option value="Anniversary">Anniversary 💑</option>
                                <option value="Wedding">Wedding 💒</option>
                                <option value="Valentine's Day">Valentine's Day 💕</option>
                                <option value="Friendship Day">Friendship Day 🤝</option>
                                <option value="Graduation">Graduation 🎓</option>
                                <option value="Diwali">Diwali 🪔</option>
                                <option value="Christmas">Christmas 🎄</option>
                                <option value="Mother's Day">Mother's Day 🌸</option>
                                <option value="Father's Day">Father's Day 🎯</option>
                                <option value="No Occasion">No Occasion (Just Because)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Budget (₹)</label>
                            <select class="form-select form-select-sm" name="budget" required>
                                <option value="0">Any Budget</option>
                                <option value="250">Under ₹250</option>
                                <option value="500">Under ₹500</option>
                                <option value="1000">Under ₹1,000</option>
                                <option value="1500">Under ₹1,500</option>
                                <option value="2000">Under ₹2,000</option>
                                <option value="3000">Under ₹3,000</option>
                                <option value="5000">Under ₹5,000</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Age (optional)</label>
                            <select class="form-select form-select-sm" name="age">
                                <option value="0">Any age</option>
                                <option value="5">0-12 (Kids)</option>
                                <option value="13">13-17 (Teen)</option>
                                <option value="18">18-25 (Young Adult)</option>
                                <option value="26">26-40 (Adult)</option>
                                <option value="41">41+ (Senior)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Gender (optional)</label>
                            <select class="form-select form-select-sm" name="gender">
                                <option value="">Prefer not to say</option>
                                <option value="female">Female ♀️</option>
                                <option value="male">Male ♂️</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-premium w-100 btn-sm"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Generate Recommendations</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="glass-card" id="giftResults">
                    <div class="text-center py-5" style="color: var(--text-light);">
                        <i class="fa-solid fa-wand-magic-sparkles fa-3x mb-3" style="color: var(--pink-200);"></i>
                        <p>Fill in the details and click "Generate" to get AI-powered gift recommendations! 🎀</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Panel 2: Greetings ─────────────────────────────────────────── -->
    <div class="ai-panel" id="panel-greetings">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass-card">
                    <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♥ Create a Greeting ♥</h5>
                    <form id="greetingForm">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Category</label>
                            <select class="form-select form-select-sm" name="category" required>
                                <option value="Birthday">Birthday 🎂</option>
                                <option value="Anniversary">Anniversary 💑</option>
                                <option value="Wedding">Wedding 💒</option>
                                <option value="Friendship">Friendship 💕</option>
                                <option value="Thank You">Thank You 🙏</option>
                                <option value="Love">Love ❤️</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Tone</label>
                            <select class="form-select form-select-sm" name="tone" required>
                                <option value="Cute">Cute 🥰</option>
                                <option value="Emotional">Emotional 🥹</option>
                                <option value="Funny">Funny 😂</option>
                                <option value="Professional">Professional 🌟</option>
                                <option value="Romantic">Romantic 💖</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Recipient Name (optional)</label>
                            <input type="text" class="form-control form-control-sm" name="name" placeholder="Enter their name...">
                        </div>
                        <button type="submit" class="btn btn-premium w-100 btn-sm"><i class="fa-solid fa-magic me-2"></i>Generate Message</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♥ Your Greeting ♥</h5>
                        <button class="btn btn-premium-outline btn-sm copy-btn" onclick="copyGreeting()"><i class="fa-regular fa-copy me-1"></i>Copy</button>
                    </div>
                    <div id="greetingOutput" class="p-4" style="background: var(--pink-50); border-radius: 16px; min-height: 200px; display: flex; align-items: center; justify-content: center; color: var(--text-light); text-align: center;">
                        <div>
                            <i class="fa-solid fa-feather fa-2x mb-2" style="color: var(--pink-200);"></i>
                            <p class="mb-0">Select a category and tone, then click generate! ✨</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Panel 3: Image to Gift ─────────────────────────────────────── -->
    <div class="ai-panel" id="panel-image">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="glass-card">
                    <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♥ Upload an Image ♥</h5>
                    <p class="small" style="color: var(--text-light);">Upload a photo and AI will analyze it to suggest matching craft gifts!</p>
                    <form id="imageForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Choose Image</label>
                            <input type="file" class="form-control form-control-sm" name="image" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Occasion (optional)</label>
                            <select class="form-select form-select-sm" name="occasion">
                                <option value="">Any occasion</option>
                                <option value="Birthday">Birthday</option>
                                <option value="Anniversary">Anniversary</option>
                                <option value="Wedding">Wedding</option>
                                <option value="Valentine's Day">Valentine's Day</option>
                                <option value="Just Because">Just Because</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-premium w-100 btn-sm"><i class="fa-solid fa-brain me-2"></i>Analyze & Recommend</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="glass-card" id="imageResults">
                    <div class="text-center py-5" style="color: var(--text-light);">
                        <i class="fa-solid fa-image fa-3x mb-3" style="color: var(--pink-200);"></i>
                        <p>Upload an image and AI will analyze it to find the perfect gift match! 📸✨</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Panel 4: Trending ──────────────────────────────────────────── -->
    <div class="ai-panel" id="panel-trending">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♥ Trending & Best-Selling Gifts ♥</h5>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="seasonFilter" style="border-radius: 12px; width: auto;">
                        <option value="all">All Time</option>
                        <option value="valentine">Valentine's Day 💕</option>
                        <option value="diwali">Diwali 🪔</option>
                        <option value="christmas">Christmas 🎄</option>
                        <option value="mothers-day">Mother's Day 🌸</option>
                        <option value="fathers-day">Father's Day 🎯</option>
                        <option value="new-year">New Year 🎉</option>
                    </select>
                </div>
            </div>
            <div class="row g-3" id="trendingGrid">
                <?php if (empty($trending)): ?>
                    <div class="col-12 text-center py-4" style="color: var(--text-light);">
                        <i class="fa-solid fa-fire fa-2x mb-2" style="color: var(--pink-200);"></i>
                        <p>No trending products yet — start selling! ♡</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($trending as $tp): ?>
                        <div class="col-md-3 col-6">
                            <div class="gift-card">
                                <img src="<?php echo htmlspecialchars($tp['image'] ? '../'.$tp['image'] : '../assets/img/products/default.jpg'); ?>" alt="">
                                <h6><?php echo htmlspecialchars($tp['name']); ?></h6>
                                <div class="price">₹<?php echo number_format($tp['base_price'], 2); ?></div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="badge" style="background: var(--pink-50); color: var(--primary);"><i class="fa-solid fa-fire me-1"></i><?php echo $tp['total_sold']; ?> sold</span>
                                    <span class="small" style="color: var(--text-light);"><?php echo htmlspecialchars($tp['category_name'] ?? ''); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ─── Panel 5: Smart Search ──────────────────────────────────────── -->
    <div class="ai-panel" id="panel-search">
        <div class="glass-card">
            <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♥ AI Smart Search ♥</h5>
            <p class="small mb-3" style="color: var(--text-light);">Search using natural language — just type what you're looking for!</p>
            <div class="input-group mb-4">
                <input type="text" class="form-control" id="smartSearchInput" placeholder='Try: "Gift for my best friend under ₹500" or "Anniversary gift for parents"' style="border-radius: 14px 0 0 14px; border-color: var(--pink-200);">
                <button class="btn btn-premium" id="smartSearchBtn" style="border-radius: 0 14px 14px 0;"><i class="fa-solid fa-search me-2"></i>Search</button>
            </div>
            <div id="searchResults">
                <div class="text-center py-4" style="color: var(--text-light);">
                    <i class="fa-regular fa-lightbulb fa-2x mb-2" style="color: var(--pink-200);"></i>
                    <p>Try searching like: <br><span style="color: var(--primary);">"Cute handmade gift for girlfriend"</span> or <span style="color: var(--primary);">"Gift for my brother under ₹1,000"</span> 💡</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Panel 6: AI Chat ───────────────────────────────────────────── -->
    <div class="ai-panel" id="panel-chat">
        <div class="glass-card" style="height: 500px; display: flex; flex-direction: column;">
            <div style="flex-grow: 1; overflow-y: auto; padding: 0.5rem;" id="aiChatMessages">
                <div class="msg bot" style="max-width: 85%; padding: 0.65rem 1rem; border-radius: 16px; background: #FFF3F5; color: #442E3C; font-size: 0.85rem; margin-bottom: 0.5rem; align-self: flex-start;">
                    Hey there! 🎀 I'm Crafty, your AI Gift Assistant! Ask me anything about gifts, orders, or our products! 💕
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid var(--glass-border);">
                <input type="text" id="aiChatInput" class="form-control form-control-sm" placeholder="Ask me anything..." style="border-radius: 14px;">
                <button class="btn btn-premium btn-sm" id="aiChatSend"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Recommendation Reason Modal -->
<div class="modal fade" id="reasonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-body text-center p-4">
                <i class="fa-solid fa-heart fa-3x mb-3" style="color: var(--primary);"></i>
                <h5 class="fw-bold">Why this gift?</h5>
                <p id="reasonText" style="color: var(--text-light);"></p>
                <button class="btn btn-premium btn-sm" data-bs-dismiss="modal">Got it! 💕</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ─── Tab switching ──────────────────────────────────────────────────────────
document.querySelectorAll('.ai-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.ai-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.ai-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('panel-' + this.dataset.tab).classList.add('active');
    });
});

// ─── Gift Generator ─────────────────────────────────────────────────────────
document.getElementById('giftForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Thinking...';

    const form = new FormData(this);
    form.append('action', 'generate_gifts');

    try {
        const res = await fetch('ai/recommend.php', { method: 'POST', body: form });
        const data = await res.json();
        const container = document.getElementById('giftResults');

        if (data.success && data.gifts.length > 0) {
            container.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♥ AI Recommendations ♥</h5>
                    <small style="color: var(--text-light);"><i class="fa-solid fa-lightbulb me-1"></i>${data.gifts.length} found</small>
                </div>
                <p class="small mb-3" style="color: var(--text-light);"><i class="fa-solid fa-quote-left me-1" style="color: var(--primary);"></i>${data.reason}</p>
                <div class="row g-3">
                    ${data.gifts.map(g => `
                        <div class="col-md-4 col-6">
                            <div class="gift-card">
                                <img src="${g.image ? '../'+g.image : '../assets/img/products/default.jpg'}" alt="">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6>${g.name}</h6>
                                        <div class="price">₹${parseFloat(g.base_price).toFixed(2)}</div>
                                    </div>
                                    <span class="badge" style="background: var(--pink-50); color: var(--primary); font-size: 0.6rem;">${g.category_name || ''}</span>
                                </div>
                                ${g.stock_quantity > 0 ? '<div style="font-size: 0.65rem; color: #15803d;"><i class="fa-solid fa-circle me-1" style="font-size: 0.4rem;"></i>In Stock</div>' : '<div style="font-size: 0.65rem; color: #b91c1c;"><i class="fa-solid fa-circle me-1" style="font-size: 0.4rem;"></i>Out of Stock</div>'}
                            </div>
                        </div>
                    `).join('')}
                </div>`;
        } else {
            container.innerHTML = `
                <div class="text-center py-5" style="color: var(--text-light);">
                    <i class="fa-solid fa-search fa-2x mb-2" style="color: var(--pink-200);"></i>
                    <p>Hmm, no exact matches found. Try adjusting the budget or occasion! 🎀</p>
                </div>`;
        }
    } catch(e) {
        document.getElementById('giftResults').innerHTML = `<div class="alert alert-danger">Something went wrong. Please try again.</div>`;
    }
    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-2"></i>Generate Recommendations';
});

// ─── Greeting Generator ─────────────────────────────────────────────────────
document.getElementById('greetingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Creating...';

    const form = new FormData(this);
    try {
        const res = await fetch('ai/greeting.php', { method: 'POST', body: form });
        const data = await res.json();
        const out = document.getElementById('greetingOutput');
        if (data.success) {
            out.innerHTML = `<p style="font-size: 1.05rem; line-height: 1.7; color: var(--text);">${data.message.replace(/\n/g, '<br>')}</p>`;
            out.dataset.greeting = data.message;
        } else {
            out.innerHTML = `<p style="color: var(--text-light);">Could not generate. Try again! 🥺</p>`;
        }
    } catch(e) {
        document.getElementById('greetingOutput').innerHTML = `<p style="color: var(--text-light);">Something went wrong.</p>`;
    }
    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-magic me-2"></i>Generate Message';
});

function copyGreeting() {
    const el = document.getElementById('greetingOutput');
    const text = el.dataset.greeting || el.innerText;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.querySelector('.copy-btn');
        btn.innerHTML = '<i class="fa-regular fa-check-circle me-1"></i>Copied!';
        setTimeout(() => btn.innerHTML = '<i class="fa-regular fa-copy me-1"></i>Copy', 2000);
    });
}

// ─── Image Upload ──────────────────────────────────────────────────────────
document.getElementById('imageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Analyzing...';

    const form = new FormData(this);
    form.append('action', 'analyze_image');
    form.append('session_id', '<?php echo session_id(); ?>');

    try {
        const res = await fetch('ai/recommend.php', { method: 'POST', body: form });
        const data = await res.json();
        const container = document.getElementById('imageResults');

        if (data.success) {
            container.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-5">
                        <img src="${data.image_url}" alt="" style="width:100%; border-radius: 16px; max-height: 220px; object-fit: cover;">
                        <div class="mt-2">
                            <div class="d-flex justify-content-between small">
                                <span style="color: var(--text-light);">AI Confidence</span>
                                <span class="fw-bold" style="color: var(--primary);">${(data.analysis.confidence * 100).toFixed(0)}%</span>
                            </div>
                            <div class="confidence-bar mt-1"><div class="fill" style="width: ${(data.analysis.confidence * 100).toFixed(0)}%"></div></div>
                        </div>
                        <div class="mt-2 d-flex flex-wrap gap-1">
                            ${data.analysis.detected_colors.map(c => `<span class="badge" style="background: var(--pink-50); color: var(--primary);">🎨 ${c}</span>`).join('')}
                            <span class="badge" style="background: #f0edfe; color: #6c4dff;">🎭 ${data.analysis.dominant_theme}</span>
                            <span class="badge" style="background: #ebfdf1; color: #15803d;">😊 ${data.analysis.mood}</span>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h6 class="fw-bold" style="font-family: 'Playfair Display', serif;">♥ Recommended Gifts ♥</h6>
                        <div class="row g-2 mb-3">
                            ${data.gift_ideas.slice(0, 4).map(g => `
                                <div class="col-6">
                                    <div class="gift-card p-2">
                                        <h6 style="font-size: 0.8rem;">${g.name}</h6>
                                        <div class="price" style="font-size: 0.85rem;">₹${parseFloat(g.base_price).toFixed(2)}</div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <h6 class="fw-bold" style="font-family: 'Playfair Display', serif;">♡ Customization Ideas ♡</h6>
                        <ul style="font-size: 0.82rem; color: var(--text);">
                            ${data.customization.map(c => `<li>${c}</li>`).join('')}
                        </ul>
                    </div>
                </div>`;
        } else {
            container.innerHTML = `<div class="text-center py-4" style="color: var(--text-light);"><p>${data.message || 'Could not analyze image.'}</p></div>`;
        }
    } catch(e) {
        document.getElementById('imageResults').innerHTML = `<div class="alert alert-danger">Upload failed. Try again.</div>`;
    }
    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-brain me-2"></i>Analyze & Recommend';
});

// ─── Smart Search ──────────────────────────────────────────────────────────
document.getElementById('smartSearchBtn').addEventListener('click', doSmartSearch);
document.getElementById('smartSearchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') doSmartSearch();
});

async function doSmartSearch() {
    const query = document.getElementById('smartSearchInput').value.trim();
    if (!query) return;

    const btn = document.getElementById('smartSearchBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Searching...';

    try {
        const form = new FormData();
        form.append('action', 'smart_search');
        form.append('query', query);
        const res = await fetch('ai/recommend.php', { method: 'POST', body: form });
        const data = await res.json();
        const container = document.getElementById('searchResults');

        if (data.success && data.results.length > 0) {
            container.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Found ${data.results.length} results for "${data.query}" ✨</h6>
                </div>
                <div class="row g-3">
                    ${data.results.map(g => `
                        <div class="col-md-4 col-6">
                            <div class="gift-card">
                                <img src="${g.image ? '../'+g.image : '../assets/img/products/default.jpg'}" alt="">
                                <h6>${g.name}</h6>
                                <div class="price">₹${parseFloat(g.base_price).toFixed(2)}</div>
                                <span class="badge" style="background: var(--pink-50); color: var(--primary); font-size: 0.6rem;">${g.category_name || ''}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>`;
        } else {
            container.innerHTML = `
                <div class="text-center py-4" style="color: var(--text-light);">
                    <i class="fa-solid fa-search fa-2x mb-2" style="color: var(--pink-200);"></i>
                    <p>No results for "${data.query}". Try different keywords! 💭</p>
                </div>`;
        }
    } catch(e) {
        document.getElementById('searchResults').innerHTML = `<div class="alert alert-danger">Search failed.</div>`;
    }
    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-search me-2"></i>Search';
}

// ─── AI Chat ───────────────────────────────────────────────────────────────
document.getElementById('aiChatSend').addEventListener('click', sendAIChat);
document.getElementById('aiChatInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') sendAIChat();
});

let aiChatLoading = false;
async function sendAIChat() {
    if (aiChatLoading) return;
    const input = document.getElementById('aiChatInput');
    const msg = input.value.trim();
    if (!msg) return;

    const container = document.getElementById('aiChatMessages');
    // Add user message
    const userDiv = document.createElement('div');
    userDiv.className = 'msg user';
    userDiv.style.cssText = 'max-width:85%;padding:0.65rem 1rem;border-radius:16px;background:linear-gradient(135deg,#E25F84,#F2A1B7);color:white;font-size:0.85rem;margin-bottom:0.5rem;align-self:flex-end;text-align:right;margin-left:auto;';
    userDiv.textContent = msg;
    container.appendChild(userDiv);

    input.value = '';
    aiChatLoading = true;

    // Show typing
    const typing = document.createElement('div');
    typing.className = 'msg bot';
    typing.style.cssText = 'max-width:85%;padding:0.65rem 1rem;border-radius:16px;background:#FFF3F5;color:#442E3C;font-size:0.85rem;margin-bottom:0.5rem;';
    typing.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Thinking...';
    container.appendChild(typing);
    container.scrollTop = container.scrollHeight;

    try {
        const form = new FormData();
        form.append('message', msg);
        form.append('session_id', '<?php echo session_id(); ?>');
        const res = await fetch('ai/chatbot.php', { method: 'POST', body: form });
        const data = await res.json();
        typing.remove();
        if (data.success) {
            const botDiv = document.createElement('div');
            botDiv.className = 'msg bot';
            botDiv.style.cssText = 'max-width:85%;padding:0.65rem 1rem;border-radius:16px;background:#FFF3F5;color:#442E3C;font-size:0.85rem;margin-bottom:0.5rem;';
            botDiv.innerHTML = data.reply.replace(/\n/g, '<br>');
            container.appendChild(botDiv);
        }
    } catch(e) {
        typing.remove();
        const errDiv = document.createElement('div');
        errDiv.className = 'msg bot';
        errDiv.style.cssText = 'max-width:85%;padding:0.65rem 1rem;border-radius:16px;background:#FFF3F5;color:#b91c1c;font-size:0.85rem;margin-bottom:0.5rem;';
        errDiv.textContent = 'Oops! Connection error. Try again. 🥺';
        container.appendChild(errDiv);
    }
    container.scrollTop = container.scrollHeight;
    aiChatLoading = false;
}
</script>

<?php include 'admin/includes/footer.php'; ?>
