/**
 * Theme Module — dark / light mode
 * weather-system/frontend/assets/js/theme.js
 */

const Theme = (() => {
  const ATTR = 'data-theme';

  const get = () => Storage.get('theme') || 'light';

  const set = (theme) => {
    document.documentElement.setAttribute(ATTR, theme);
    Storage.set('theme', theme);
    // sync user preference via API if logged in
    if (Auth.isLoggedIn()) {
      API.put('/user/profile', { theme }).catch(() => {});
    }
    _updateToggleUI(theme);
  };

  const toggle = () => {
    set(get() === 'light' ? 'dark' : 'light');
  };

  const init = () => {
    const theme = get();
    document.documentElement.setAttribute(ATTR, theme);
    _updateToggleUI(theme);
  };

  const _updateToggleUI = (theme) => {
    const btn = document.getElementById('theme-toggle');
    if (!btn) return;
    btn.setAttribute('aria-label', theme === 'dark' ? 'Ativar modo claro' : 'Ativar modo escuro');
    btn.title = theme === 'dark' ? 'Modo claro' : 'Modo escuro';
  };

  return { get, set, toggle, init };
})();