<?php
/**
 * AI Chatbot Floating Widget — include on any page:
 *   <?php include 'ai/chatbot-widget.php'; ?>
 */
$session_id = session_id() ?: bin2hex(random_bytes(16));
?>
<!-- AI Chatbot Widget -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
.chatbot-toggle {
    position: fixed; bottom: 24px; right: 24px;
    width: 60px; height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #E25F84, #F2A1B7);
    color: white; border: none;
    font-size: 1.6rem;
    box-shadow: 0 6px 25px rgba(232,98,140,0.35);
    cursor: pointer;
    z-index: 9999;
    transition: all 0.3s;
    display: flex; align-items: center; justify-content: center;
}
.chatbot-toggle:hover { transform: scale(1.1); box-shadow: 0 8px 30px rgba(232,98,140,0.5); }
.chatbot-toggle .badge-pulse {
    position: absolute; top: -4px; right: -4px;
    width: 20px; height: 20px;
    background: #E25F84; border-radius: 50%;
    border: 2px solid white;
    animation: pulse 2s infinite;
}
@keyframes pulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.2); opacity: 0.7; } 100% { transform: scale(1); opacity: 1; } }

.chatbot-panel {
    position: fixed; bottom: 96px; right: 24px;
    width: 380px; max-width: calc(100vw - 48px);
    height: 560px; max-height: calc(100vh - 140px);
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(232,98,140,0.15);
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(232,98,140,0.15);
    z-index: 9998;
    display: none;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}
.chatbot-panel.open { display: flex; }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.chatbot-header {
    background: linear-gradient(135deg, #E25F84, #F2A1B7);
    padding: 1rem 1.2rem;
    color: white;
    display: flex; align-items: center; gap: 0.8rem;
}
.chatbot-header .avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(255,255,255,0.25);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
}
.chatbot-header .title { flex-grow: 1; }
.chatbot-header .title h6 { margin: 0; font-weight: 700; font-size: 0.95rem; }
.chatbot-header .title small { opacity: 0.85; font-size: 0.7rem; }
.chatbot-header .close-btn { background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; opacity: 0.7; }

.chatbot-messages {
    flex-grow: 1;
    padding: 1rem;
    overflow-y: auto;
    display: flex; flex-direction: column;
    gap: 0.6rem;
    scroll-behavior: smooth;
}
.chatbot-messages::-webkit-scrollbar { width: 4px; }
.chatbot-messages::-webkit-scrollbar-thumb { background: var(--pink-200); border-radius: 4px; }

.msg {
    max-width: 85%;
    padding: 0.65rem 1rem;
    border-radius: 16px;
    font-size: 0.85rem;
    line-height: 1.5;
    animation: msgIn 0.3s ease;
}
@keyframes msgIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
.msg.bot {
    align-self: flex-start;
    background: #FFF3F5;
    border-bottom-left-radius: 4px;
    color: #442E3C;
}
.msg.user {
    align-self: flex-end;
    background: linear-gradient(135deg, #E25F84, #F2A1B7);
    border-bottom-right-radius: 4px;
    color: white;
}
.msg.typing {
    align-self: flex-start;
    background: #FFF3F5;
    display: flex; gap: 4px; padding: 0.8rem 1rem;
}
.msg.typing span {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--pink-300);
    animation: typing 1.4s infinite;
}
.msg.typing span:nth-child(2) { animation-delay: 0.2s; }
.msg.typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }

.chatbot-input {
    padding: 0.8rem 1rem;
    border-top: 1px solid rgba(232,98,140,0.1);
    display: flex; gap: 0.5rem;
    background: rgba(255,255,255,0.5);
}
.chatbot-input input {
    flex-grow: 1;
    border: 1.5px solid rgba(232,98,140,0.12);
    border-radius: 14px;
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
    outline: none;
    background: rgba(255,255,255,0.7);
    transition: border 0.3s;
}
.chatbot-input input:focus { border-color: #E25F84; }
.chatbot-input button {
    width: 42px; height: 42px; border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #E25F84, #F2A1B7);
    color: white;
    font-size: 1rem;
    cursor: pointer;
    transition: transform 0.2s;
    display: flex; align-items: center; justify-content: center;
}
.chatbot-input button:hover { transform: scale(1.05); }
.chatbot-input button:disabled { opacity: 0.5; cursor: not-allowed; }

@media (max-width: 480px) {
    .chatbot-panel { bottom: 80px; right: 12px; width: calc(100vw - 24px); height: 60vh; }
}
</style>

<div class="chatbot-toggle" id="chatbotToggle" onclick="toggleChatbot()">
    <i class="fa-solid fa-robot"></i>
    <span class="badge-pulse"></span>
</div>

<div class="chatbot-panel" id="chatbotPanel">
    <div class="chatbot-header">
        <div class="avatar">🤖</div>
        <div class="title">
            <h6>Crafty — AI Assistant ♡</h6>
            <small>✨ Always here to help</small>
        </div>
        <button class="close-btn" onclick="toggleChatbot()">✕</button>
    </div>
    <div class="chatbot-messages" id="chatbotMessages">
        <div class="msg bot">Hey there! 🎀 I'm Crafty, your AI Gift Assistant! How can I help you find the perfect gift today? 💕</div>
    </div>
    <div class="chatbot-input">
        <input type="text" id="chatbotInput" placeholder="Type your message..." onkeydown="if(event.key==='Enter') sendChat()">
        <button id="chatbotSend" onclick="sendChat()"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
</div>

<script>
let sessionId = '<?php echo $session_id; ?>';
let isChatLoading = false;

function toggleChatbot() {
    const panel = document.getElementById('chatbotPanel');
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) {
        document.getElementById('chatbotInput').focus();
    }
}

function addMessage(text, isUser) {
    const container = document.getElementById('chatbotMessages');
    const div = document.createElement('div');
    div.className = 'msg ' + (isUser ? 'user' : 'bot');
    div.innerHTML = text.replace(/\n/g, '<br>');
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function showTyping() {
    const container = document.getElementById('chatbotMessages');
    const div = document.createElement('div');
    div.className = 'msg typing';
    div.id = 'typingIndicator';
    div.innerHTML = '<span></span><span></span><span></span>';
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function hideTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
}

async function sendChat() {
    if (isChatLoading) return;
    const input = document.getElementById('chatbotInput');
    const msg = input.value.trim();
    if (!msg) return;

    addMessage(msg, true);
    input.value = '';
    isChatLoading = true;
    document.getElementById('chatbotSend').disabled = true;
    showTyping();

    try {
        const form = new FormData();
        form.append('message', msg);
        form.append('session_id', sessionId);
        const res = await fetch('ai/chatbot.php', { method: 'POST', body: form });
        const data = await res.json();
        hideTyping();
        if (data.success) {
            addMessage(data.reply, false);
        } else {
            addMessage('Oops! Something went wrong. Try again? 🥺', false);
        }
    } catch (e) {
        hideTyping();
        addMessage('Oops! I lost connection. Please try again. 🥺', false);
    }
    isChatLoading = false;
    document.getElementById('chatbotSend').disabled = false;
}
</script>
