/**
 * General Utilities
 * weather-system/frontend/assets/js/utils.js
 */

const Utils = (() => {

  // ── Toast ────────────────────────────────────────────────
  const toast = (message, type = 'info', duration = 3000) => {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.innerHTML = `<span class="toast-icon">${icons[type] || icons.info}</span><span>${message}</span>`;
    container.appendChild(el);
    setTimeout(() => el.remove(), duration + 400);
  };

  // ── Weather Icons (emoji mapping) ────────────────────────
  const weatherIcon = (code, isDay = true) => {
    const icons = {
      '01d': '☀️',  '01n': '🌙',
      '02d': '⛅',  '02n': '🌥️',
      '03d': '☁️',  '03n': '☁️',
      '04d': '☁️',  '04n': '☁️',
      '09d': '🌧️', '09n': '🌧️',
      '10d': '🌦️', '10n': '🌧️',
      '11d': '⛈️', '11n': '⛈️',
      '13d': '❄️',  '13n': '❄️',
      '50d': '🌫️', '50n': '🌫️',
    };
    return icons[code] || '🌤️';
  };

  // ── Format temperature ────────────────────────────────────
  const formatTemp = (value, unit = 'metric') => {
    const sym = unit === 'imperial' ? '°F' : '°C';
    return `${Math.round(value)}${sym}`;
  };

  // ── Format date ───────────────────────────────────────────
  const formatDate = (unixTs, locale = 'pt-AO') => {
    const d = new Date(unixTs * 1000);
    return d.toLocaleDateString(locale, { weekday: 'short', day: 'numeric', month: 'short' });
  };

  const formatTime = (unixTs, locale = 'pt-AO') => {
    return new Date(unixTs * 1000).toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit' });
  };

  const dayName = (unixTs, locale = 'pt-AO') => {
    return new Date(unixTs * 1000).toLocaleDateString(locale, { weekday: 'short' });
  };

  // ── Wind direction ────────────────────────────────────────
  const windDir = (deg) => {
    const dirs = ['N','NE','E','SE','S','SO','O','NO'];
    return dirs[Math.round(deg / 45) % 8];
  };

  // ── Debounce ──────────────────────────────────────────────
  const debounce = (fn, delay = 300) => {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
  };

  // ── Loading state ─────────────────────────────────────────
  const setLoading = (btn, loading) => {
    if (!btn) return;
    if (loading) {
      btn.dataset.originalText = btn.innerHTML;
      btn.innerHTML = '<span class="spinner spinner-sm"></span>';
      btn.disabled = true;
    } else {
      btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
      btn.disabled = false;
    }
  };

  // ── Skeleton placeholder ──────────────────────────────────
  const skeleton = (count = 1, type = 'card') => {
    return Array.from({ length: count },
      () => `<div class="skeleton skeleton-${type}"></div>`
    ).join('');
  };

  // ── Capitalize ────────────────────────────────────────────
  const capitalize = (str) => str ? str.charAt(0).toUpperCase() + str.slice(1) : '';

  // ── Get UV index label ────────────────────────────────────
  const uvLabel = (uv) => {
    if (uv <= 2) return { label: 'Baixo',     color: '#0ab577' };
    if (uv <= 5) return { label: 'Moderado',  color: '#f59e0b' };
    if (uv <= 7) return { label: 'Alto',      color: '#ff6d3b' };
    if (uv <= 10) return { label: 'Muito Alto', color: '#ef4444' };
    return { label: 'Extremo', color: '#7c3aed' };
  };

  // ── AQI label ────────────────────────────────────────────
  const aqiLabel = (aqi) => {
    const labels = ['Boa','Razoável','Moderada','Ruim','Muito Ruim'];
    return labels[aqi - 1] || 'N/D';
  };

  // ── Animate number ────────────────────────────────────────
  const animateNumber = (el, from, to, duration = 800) => {
    const start = performance.now();
    const step = (now) => {
      const p = Math.min((now - start) / duration, 1);
      el.textContent = Math.round(from + (to - from) * p);
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };

  // ── Validate email client-side ────────────────────────────
  const isValidEmail = (e) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e);

  // ── Group forecast by day ─────────────────────────────────
  const groupForecastByDay = (list) => {
    const days = {};
    list.forEach(item => {
      const date = new Date(item.dt * 1000).toDateString();
      if (!days[date]) days[date] = [];
      days[date].push(item);
    });
    return Object.entries(days).slice(0, 5).map(([date, items]) => ({
      date,
      dt: items[0].dt,
      temp_min: Math.min(...items.map(i => i.main.temp_min)),
      temp_max: Math.max(...items.map(i => i.main.temp_max)),
      icon: items[Math.floor(items.length / 2)].weather[0].icon,
      description: items[Math.floor(items.length / 2)].weather[0].description,
      humidity: Math.round(items.reduce((s, i) => s + i.main.humidity, 0) / items.length),
      wind: Math.round(items.reduce((s, i) => s + i.wind.speed, 0) / items.length),
    }));
  };

  return {
    toast, weatherIcon, formatTemp, formatDate, formatTime, dayName,
    windDir, debounce, setLoading, skeleton, capitalize, uvLabel, aqiLabel,
    animateNumber, isValidEmail, groupForecastByDay,
  };
})();