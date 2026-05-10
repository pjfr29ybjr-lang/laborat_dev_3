export const ThemeManager = {
    init() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        this.updateIcon(savedTheme);
    },

    toggle() {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        this.updateIcon(next);
    },

    updateIcon(theme) {
        const btn = document.getElementById('theme-toggle');
        if (btn) btn.innerText = theme === 'light' ? '🌙' : '☀️';
    }
};