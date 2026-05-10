export function initNavbar() {
    const container = document.getElementById('navbar-container');
    if (!container) return;

    container.innerHTML = `
        <nav class="neumorphism" style="display: flex; justify-content: space-between; padding: 15px 30px; margin-bottom: 30px;">
            <div class="logo" style="font-weight: bold; font-size: 1.2rem;">Chley Weather</div>
            <div class="menu">
                <a href="home.html" style="margin-right: 15px; text-decoration: none; color: var(--text-color);">Home</a>
                <a href="dashboard.html" style="margin-right: 15px; text-decoration: none; color: var(--text-color);">Dashboard</a>
                <button id="theme-toggle" class="neumorphism-btn" style="padding: 5px 10px;">🌙</button>
            </div>
        </nav>
    `;
}