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

.gift-card { background: rgba(255,255,255,0.6); border-radius: 16px; padding: 1rem; border: 1px solid var(--glass-border); transition: all 0.3s; height: 100%; }
.gift-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-hover); }
.gift-card .img-wrap { width: 100%; height: 150px; border-radius: 12px; overflow: hidden; margin-bottom: 0.6rem; background: var(--pink-50); }
.gift-card .img-wrap img { width: 100%; height: 100%; object-fit: cover; }
.gift-card h6 { font-size: 0.9rem; font-weight: 600; margin-bottom: 0.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gift-card .price { color: var(--primary); font-weight: 700; font-size: 0.95rem; }

.refresh-btn, .copy-btn { cursor: pointer; transition: all 0.2s; border-radius: 10px; padding: 0.35rem 0.75rem; font-size: 0.75rem; border: 1.5px solid var(--pink-200); background: transparent; color: var(--text-light); }
.refresh-btn:hover, .copy-btn:hover { background: var(--pink-50); color: var(--primary); transform: scale(1.02); }

.toolbar { display: flex; gap: 0.4rem; flex-wrap: wrap; }

.placeholder-state { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 200px; color: var(--text-light); text-align: center; padding: 2rem; }

.greeting-box { background: linear-gradient(135deg, var(--pink-50), #FFF5F7); border-radius: 16px; padding: 1.5rem; min-height: 200px; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 1.05rem; line-height: 1.7; color: var(--text); }

.form-select-sm, .form-control-sm { border-radius: 12px !important; border-color: var(--pink-200) !important; }

.analysis-tag { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; border-radius: 8px; font-size: 0.7rem; font-weight: 500; }
.analysis-tag.color { background: var(--pink-50); color: var(--primary); }
.analysis-tag.theme { background: #f0edfe; color: #6c4dff; }
.analysis-tag.mood { background: #ebfdf1; color: #15803d; }

.custom-idea-item { padding: 0.5rem 0.75rem; border-radius: 10px; background: rgba(255,255,255,0.4); margin-bottom: 0.3rem; font-size: 0.82rem; border-left: 3px solid var(--primary); }
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
                            <select class="form-select form-select-sm" name="budget">
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
                    <div class="placeholder-state">
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
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♥ Your Greeting ♥</h5>
                        <div class="toolbar">
                            <button class="refresh-btn" id="greetingRefreshBtn" onclick="refreshGreeting()" style="display:none;"><i class="fa-solid fa-rotate me-1"></i>Refresh</button>
                            <button class="copy-btn" id="greetingCopyBtn" onclick="copyGreeting()" style="display:none;"><i class="fa-regular fa-copy me-1"></i>Copy</button>
                        </div>
                    </div>
                    <div class="greeting-box" id="greetingOutput">
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
                                <option value="Birthday">Birthday 🎂</option>
                                <option value="Anniversary">Anniversary 💑</option>
                                <option value="Wedding">Wedding 💒</option>
                                <option value="Valentine's Day">Valentine's Day 💕</option>
                                <option value="Just Because">Just Because 🎀</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-premium w-100 btn-sm"><i class="fa-solid fa-brain me-2"></i>Analyze & Recommend</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="glass-card" id="imageResults">
                    <div class="placeholder-state">
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
                <div class="toolbar">
                    <button class="refresh-btn" onclick="refreshTrending()"><i class="fa-solid fa-rotate me-1"></i>Refresh</button>
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
                                <div class="img-wrap">
                                    <img src="<?php echo htmlspecialchars(getImgSrc($tp['image'] ?: '')); ?>" alt="<?php echo htmlspecialchars($tp['name']); ?>" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-200);font-size:2rem;\'><i class=\'fa-solid fa-gift\'></i></div>'">
                                </div>
                                <h6 title="<?php echo htmlspecialchars($tp['name']); ?>"><?php echo htmlspecialchars($tp['name']); ?></h6>
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
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♥ AI Smart Search ♥</h5>
                <div class="toolbar">
                    <button class="refresh-btn" id="searchRefreshBtn" onclick="refreshSmartSearch()" style="display:none;"><i class="fa-solid fa-rotate me-1"></i>Refresh</button>
                    <button class="copy-btn" id="searchCopyBtn" onclick="copySearchResults()" style="display:none;"><i class="fa-regular fa-copy me-1"></i>Copy</button>
                </div>
            </div>
            <p class="small mb-3" style="color: var(--text-light);">Search using natural language — just type what you're looking for!</p>
            <div class="input-group mb-4">
                <input type="text" class="form-control" id="smartSearchInput" placeholder='Try: "Gift for my best friend under ₹500" or "Anniversary gift for parents"' style="border-radius: 14px 0 0 14px; border-color: var(--pink-200);">
                <button class="btn btn-premium" id="smartSearchBtn" style="border-radius: 0 14px 14px 0;"><i class="fa-solid fa-search me-2"></i>Search</button>
            </div>
            <div id="searchResults">
                <div class="placeholder-state">
                    <i class="fa-regular fa-lightbulb fa-2x mb-2" style="color: var(--pink-200);"></i>
                    <p>Try searching like: <br><span style="color: var(--primary);">"Cute handmade gift for girlfriend"</span> or <span style="color: var(--primary);">"Gift for my brother under ₹1,000"</span> 💡</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Panel 6: AI Chat ───────────────────────────────────────────── -->
    <div class="ai-panel" id="panel-chat">
        <div class="glass-card" style="height: 520px; display: flex; flex-direction: column;">
            <div class="d-flex justify-content-between align-items-center px-2 pb-2 flex-wrap gap-2" style="border-bottom: 1px solid var(--glass-border);">
                <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♥ AI Chat ♥</h5>
                <div class="toolbar">
                    <button class="refresh-btn" onclick="clearChat()"><i class="fa-solid fa-eraser me-1"></i>Clear</button>
                </div>
            </div>
            <div style="flex-grow: 1; overflow-y: auto; padding: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem;" id="aiChatMessages">
                <div class="msg bot" style="max-width: 85%; padding: 0.65rem 1rem; border-radius: 16px; background: #FFF3F5; color: #442E3C; font-size: 0.85rem; align-self: flex-start; border-bottom-left-radius: 4px;">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ─── Image URL helper ────────────────────────────────────────────────────────
const BASE = ''; // ai-assistant.php is at craft/ root, so relative paths work directly
function imgUrl(path) {
    if (!path) return 'assets/img/products/default.jpg';
    if (path.startsWith('http')) return path;
    return path;
}

// ─── Tab switching ──────────────────────────────────────────────────────────
document.querySelectorAll('.ai-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.ai-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.ai-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('panel-' + this.dataset.tab).classList.add('active');
    });
});

// ─── Product card template ──────────────────────────────────────────────────
function productCard(g) {
    const img = imgUrl(g.image);
    const stock = g.stock_quantity > 0
        ? '<span style="font-size:0.65rem;color:#15803d;"><i class="fa-solid fa-circle me-1" style="font-size:0.4rem;"></i>In Stock</span>'
        : '<span style="font-size:0.65rem;color:#b91c1c;"><i class="fa-solid fa-circle me-1" style="font-size:0.4rem;"></i>Out of Stock</span>';
    return `
        <div class="col-md-4 col-6">
            <div class="gift-card">
                <div class="img-wrap">
                    <img src="${img}" alt="${g.name}" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-200);font-size:2rem;\'><i class=\'fa-solid fa-gift\'></i></div>'">
                </div>
                <h6 title="${g.name}">${g.name}</h6>
                <div class="price">₹${parseFloat(g.base_price).toFixed(2)}</div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="badge" style="background: var(--pink-50); color: var(--primary); font-size:0.6rem;">${g.category_name || ''}</span>
                    ${stock}
                </div>
            </div>
        </div>`;
}

// ─── Copy helper ────────────────────────────────────────────────────────────
async function copyText(text, btn) {
    try {
        await navigator.clipboard.writeText(text);
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-regular fa-check-circle me-1"></i>Copied!';
        setTimeout(() => btn.innerHTML = orig, 2000);
    } catch { /* fallback */ }
}

// ════════════════════════════════════════════════════════════════════════════
// Gift Generator
// ════════════════════════════════════════════════════════════════════════════
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
            const list = data.gifts.map(g => productCard(g)).join('');
            container.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♥ AI Recommendations ♥</h5>
                        <small style="color: var(--text-light);"><i class="fa-solid fa-lightbulb me-1"></i>${data.gifts.length} found</small>
                    </div>
                    <div class="toolbar">
                        <button class="refresh-btn" onclick="document.getElementById('giftForm').requestSubmit()"><i class="fa-solid fa-rotate me-1"></i>Refresh</button>
                        <button class="copy-btn" onclick="copyGiftResults()"><i class="fa-regular fa-copy me-1"></i>Copy</button>
                    </div>
                </div>
                <p class="small mb-3" style="color: var(--text-light);"><i class="fa-solid fa-quote-left me-1" style="color: var(--primary);"></i>${data.reason}</p>
                <div class="row g-3">${list}</div>`;
        } else {
            container.innerHTML = `
                <div class="placeholder-state">
                    <i class="fa-solid fa-search fa-2x mb-2" style="color: var(--pink-200);"></i>
                    <p>Hmm, no exact matches found. Try adjusting the budget or occasion! 🎀</p>
                </div>`;
        }
    } catch(e) {
        document.getElementById('giftResults').innerHTML = `<div class="alert alert-danger m-3">Something went wrong. Please try again.</div>`;
    }
    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-2"></i>Generate Recommendations';
});

async function copyGiftResults() {
    const container = document.getElementById('giftResults');
    const cards = container.querySelectorAll('.gift-card');
    let text = '';
    cards.forEach(c => {
        const name = c.querySelector('h6')?.textContent || '';
        const price = c.querySelector('.price')?.textContent || '';
        text += `${name} — ${price}\n`;
    });
    if (text) await copyText(text.trim(), container.querySelector('.copy-btn'));
}

// ════════════════════════════════════════════════════════════════════════════
// Greeting Generator
// ════════════════════════════════════════════════════════════════════════════
document.getElementById('greetingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    await generateGreeting(new FormData(this));
});

async function generateGreeting(form) {
    const btn = document.getElementById('greetingForm').querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Creating...';

    try {
        const res = await fetch('ai/greeting.php', { method: 'POST', body: form });
        const data = await res.json();
        const out = document.getElementById('greetingOutput');
        const refreshBtn = document.getElementById('greetingRefreshBtn');
        const copyBtn = document.getElementById('greetingCopyBtn');

        if (data.success) {
            out.innerHTML = data.message.replace(/\n/g, '<br>');
            out.dataset.greeting = data.message;
            refreshBtn.style.display = '';
            copyBtn.style.display = '';
        } else {
            out.innerHTML = '<p style="color: var(--text-light); margin:0;">Could not generate. Try again! 🥺</p>';
        }
    } catch(e) {
        document.getElementById('greetingOutput').innerHTML = '<p style="color: var(--text-light); margin:0;">Something went wrong.</p>';
    }
    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-magic me-2"></i>Generate Message';
}

async function refreshGreeting() {
    const form = new FormData(document.getElementById('greetingForm'));
    await generateGreeting(form);
}

async function copyGreeting() {
    const text = document.getElementById('greetingOutput').dataset.greeting || document.getElementById('greetingOutput').innerText;
    if (text) await copyText(text, document.getElementById('greetingCopyBtn'));
}

// ════════════════════════════════════════════════════════════════════════════
// Image Upload
// ════════════════════════════════════════════════════════════════════════════
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
            const colors = (data.analysis.detected_colors || []).map(c => `<span class="analysis-tag color">🎨 ${c}</span>`).join('');
            const gifts = (data.gift_ideas || []).slice(0, 4).map(g => `
                <div class="col-6">
                    <div class="gift-card p-2">
                        <div class="img-wrap" style="height:80px;">
                            <img src="${imgUrl(g.image)}" alt="${g.name}" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-200);font-size:1.5rem;\'><i class=\'fa-solid fa-gift\'></i></div>'">
                        </div>
                        <h6 style="font-size:0.8rem;margin-top:0.3rem;">${g.name}</h6>
                        <div class="price" style="font-size:0.85rem;">₹${parseFloat(g.base_price).toFixed(2)}</div>
                    </div>
                </div>
            `).join('');
            const customs = (data.customization || []).map(c => `<div class="custom-idea-item">✨ ${c}</div>`).join('');

            container.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♥ Analysis Results ♥</h5>
                    <button class="refresh-btn" onclick="document.getElementById('imageForm').requestSubmit()"><i class="fa-solid fa-rotate me-1"></i>Refresh</button>
                </div>
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="img-wrap" style="height:180px;">
                            <img src="${data.image_url}" alt="Uploaded" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-200);font-size:2rem;\'><i class=\'fa-solid fa-image\'></i></div>'">
                        </div>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between small">
                                <span style="color: var(--text-light);">AI Confidence</span>
                                <span class="fw-bold" style="color: var(--primary);">${(data.analysis.confidence * 100).toFixed(0)}%</span>
                            </div>
                            <div class="confidence-bar mt-1"><div class="fill" style="width:${(data.analysis.confidence * 100).toFixed(0)}%"></div></div>
                        </div>
                        <div class="mt-2 d-flex flex-wrap gap-1">
                            ${colors}
                            <span class="analysis-tag theme">🎭 ${data.analysis.dominant_theme || ''}</span>
                            <span class="analysis-tag mood">😊 ${data.analysis.mood || ''}</span>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h6 class="fw-bold mb-2" style="font-family: 'Playfair Display', serif;">♥ Recommended Gifts ♥</h6>
                        <div class="row g-2 mb-3">${gifts}</div>
                        <h6 class="fw-bold mb-2" style="font-family: 'Playfair Display', serif;">♡ Customization Ideas ♡</h6>
                        ${customs || '<p class="small" style="color:var(--text-light);">No customization ideas available.</p>'}
                    </div>
                </div>`;
        } else {
            container.innerHTML = `<div class="placeholder-state"><p>${data.message || 'Could not analyze image.'}</p></div>`;
        }
    } catch(e) {
        document.getElementById('imageResults').innerHTML = `<div class="alert alert-danger m-3">Upload failed. Try again.</div>`;
    }
    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-brain me-2"></i>Analyze & Recommend';
});

// ════════════════════════════════════════════════════════════════════════════
// Trending
// ════════════════════════════════════════════════════════════════════════════
async function refreshTrending() {
    const grid = document.getElementById('trendingGrid');
    grid.innerHTML = '<div class="col-12 text-center py-4" style="color:var(--text-light);"><i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i><p>Loading...</p></div>';

    try {
        const res = await fetch('ai/recommend.php?action=trending');
        const data = await res.json();
        if (data.success && data.gifts.length > 0) {
            grid.innerHTML = data.gifts.map(g => productCard(g)).join('');
        } else {
            grid.innerHTML = '<div class="col-12 text-center py-4" style="color:var(--text-light);"><i class="fa-solid fa-fire fa-2x mb-2" style="color:var(--pink-200);"></i><p>No trending products yet ♡</p></div>';
        }
    } catch(e) {
        grid.innerHTML = '<div class="col-12 text-center py-4" style="color:#b91c1c;"><p>Failed to load. Try again.</p></div>';
    }
}

// ════════════════════════════════════════════════════════════════════════════
// Smart Search
// ════════════════════════════════════════════════════════════════════════════
let lastSearchQuery = '';

document.getElementById('smartSearchBtn').addEventListener('click', doSmartSearch);
document.getElementById('smartSearchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') doSmartSearch();
});

async function doSmartSearch() {
    const query = document.getElementById('smartSearchInput').value.trim();
    if (!query) return;
    lastSearchQuery = query;

    const btn = document.getElementById('smartSearchBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Searching...';

    try {
        const form = new FormData();
        form.append('action', 'smart_search');
        form.append('query', query);
        const res = await fetch('ai/recommend.php', { method: 'POST', body: form });
        const data = await res.json();
        const container = document.getElementById('searchResults');
        const refreshBtn = document.getElementById('searchRefreshBtn');
        const copyBtn = document.getElementById('searchCopyBtn');

        if (data.success && data.results.length > 0) {
            const list = data.results.map(g => productCard(g)).join('');
            container.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0">Found ${data.results.length} results for "${data.query}" ✨</h6>
                </div>
                <div class="row g-3">${list}</div>`;
            refreshBtn.style.display = '';
            copyBtn.style.display = '';
        } else {
            container.innerHTML = `
                <div class="placeholder-state">
                    <i class="fa-solid fa-search fa-2x mb-2" style="color: var(--pink-200);"></i>
                    <p>No results for "${data.query}". Try different keywords! 💭</p>
                </div>`;
            refreshBtn.style.display = 'none';
            copyBtn.style.display = 'none';
        }
    } catch(e) {
        document.getElementById('searchResults').innerHTML = `<div class="alert alert-danger m-3">Search failed.</div>`;
    }
    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-search me-2"></i>Search';
}

async function refreshSmartSearch() {
    if (lastSearchQuery) {
        document.getElementById('smartSearchInput').value = lastSearchQuery;
        await doSmartSearch();
    }
}

async function copySearchResults() {
    const container = document.getElementById('searchResults');
    const cards = container.querySelectorAll('.gift-card');
    let text = '';
    cards.forEach(c => {
        const name = c.querySelector('h6')?.textContent || '';
        const price = c.querySelector('.price')?.textContent || '';
        text += `${name} — ${price}\n`;
    });
    if (text) await copyText(text.trim(), document.getElementById('searchCopyBtn'));
}

// ════════════════════════════════════════════════════════════════════════════
// AI Chat
// ════════════════════════════════════════════════════════════════════════════
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
    const userDiv = document.createElement('div');
    userDiv.className = 'msg user';
    userDiv.style.cssText = 'max-width:85%;padding:0.65rem 1rem;border-radius:16px;background:linear-gradient(135deg,#E25F84,#F2A1B7);color:white;font-size:0.85rem;align-self:flex-end;border-bottom-right-radius:4px;margin-left:auto;';
    userDiv.textContent = msg;
    container.appendChild(userDiv);

    input.value = '';
    aiChatLoading = true;

    const typing = document.createElement('div');
    typing.className = 'msg bot';
    typing.style.cssText = 'max-width:85%;padding:0.65rem 1rem;border-radius:16px;background:#FFF3F5;color:#442E3C;font-size:0.85rem;align-self:flex-start;border-bottom-left-radius:4px;';
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
            botDiv.style.cssText = 'max-width:85%;padding:0.65rem 1rem;border-radius:16px;background:#FFF3F5;color:#442E3C;font-size:0.85rem;align-self:flex-start;border-bottom-left-radius:4px;line-height:1.5;';
            botDiv.innerHTML = data.reply.replace(/\n/g, '<br>');
            container.appendChild(botDiv);
        }
    } catch(e) {
        typing.remove();
        const errDiv = document.createElement('div');
        errDiv.className = 'msg bot';
        errDiv.style.cssText = 'max-width:85%;padding:0.65rem 1rem;border-radius:16px;background:#FFF3F5;color:#b91c1c;font-size:0.85rem;align-self:flex-start;border-bottom-left-radius:4px;';
        errDiv.textContent = 'Oops! Connection error. Try again. 🥺';
        container.appendChild(errDiv);
    }
    container.scrollTop = container.scrollHeight;
    aiChatLoading = false;
}

function clearChat() {
    const container = document.getElementById('aiChatMessages');
    container.innerHTML = `
        <div class="msg bot" style="max-width:85%;padding:0.65rem 1rem;border-radius:16px;background:#FFF3F5;color:#442E3C;font-size:0.85rem;align-self:flex-start;border-bottom-left-radius:4px;">
            Hey there! 🎀 I'm Crafty, your AI Gift Assistant! Ask me anything about gifts, orders, or our products! 💕
        </div>`;
}
</script>

<?php
function getImgSrc($path) {
    if (!$path) return 'assets/img/products/default.jpg';
    if (preg_match('/^https?:\/\//i', $path)) return $path;
    return $path;
}
include 'admin/includes/footer.php'; ?>
