/**
 * LocalStorage Utility
 * weather-system/frontend/assets/js/storage.js
 */

const Storage = (() => {
  const PREFIX = 'ws_';

  const set = (key, value) => {
    try {
      localStorage.setItem(PREFIX + key, JSON.stringify(value));
    } catch (e) {
      console.warn('Storage.set failed:', e);
    }
  };

  const get = (key, fallback = null) => {
    try {
      const item = localStorage.getItem(PREFIX + key);
      return item !== null ? JSON.parse(item) : fallback;
    } catch (e) {
      return fallback;
    }
  };

  const remove = (key) => {
    try { localStorage.removeItem(PREFIX + key); }
    catch (e) {}
  };

  const clear = () => {
    try {
      Object.keys(localStorage)
        .filter(k => k.startsWith(PREFIX))
        .forEach(k => localStorage.removeItem(k));
    } catch (e) {}
  };

  return { set, get, remove, clear };
})();