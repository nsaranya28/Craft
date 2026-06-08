// admin/assets/js/admin.js
// Core JavaScript for the admin panel – AJAX helpers, chart rendering, notification polling, UI toggles

// Simple fetch wrapper with CSRF token handling
function adminFetch(url, options = {}) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const headers = options.headers || {};
    if (csrfToken) {
        headers['X-CSRF-Token'] = csrfToken;
    }
    options.headers = headers;
    return fetch(url, options).then(res => res.json());
}

// Dark mode toggle – stores preference in localStorage
function initDarkModeToggle() {
    const toggle = document.getElementById('dark-mode-toggle');
    if (!toggle) return;
    const current = localStorage.getItem('admin-theme') || 'light';
    document.documentElement.dataset.theme = current;
    toggle.checked = current === 'dark';
    toggle.addEventListener('change', () => {
        const theme = toggle.checked ? 'dark' : 'light';
        document.documentElement.dataset.theme = theme;
        localStorage.setItem('admin-theme', theme);
    });
}

// Notification polling – fetch unread alerts every 30s
function startNotificationPolling() {
    const badge = document.getElementById('notification-badge');
    if (!badge) return;
    async function poll() {
        try {
            const data = await adminFetch('notifications_fetch.php');
            badge.textContent = data.unreadCount;
            badge.style.display = data.unreadCount > 0 ? 'inline-block' : 'none';
        } catch (e) {
            console.error('Notification poll error', e);
        }
    }
    poll();
    setInterval(poll, 30000);
}

document.addEventListener('DOMContentLoaded', () => {
    initDarkModeToggle();
    startNotificationPolling();
});
