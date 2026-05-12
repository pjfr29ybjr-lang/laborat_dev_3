/**
 * Weather Module — fetch & render weather data
 * weather-system/frontend/assets/js/weather.js
 */

const Weather = (() => {
  let currentWeather = null;
  let currentForecast = null;
  let units = Storage.get('units') || 'metric';

  // ── Public API ────────────────────────────────────────────

  const searchCity = async (city) => {
    if (!city.trim()) return;
    try {
      const lang = Language.getLanguage();
      const [wRes, fRes] = await Promise.all([
      API.get(`weather/current?city=${encodeURIComponent(city)}&units=${units}&lang=${lang}`),
      API.get(`weather/forecast?city=${encodeURIComponent(city)}&units=${units}&lang=${lang}`),
      ]);
      currentWeather  = wRes.data;
      currentForecast = fRes.data;
      renderCurrentWeather(currentWeather);
      renderForecast(currentForecast);
      Storage.set('last_city', city);
    } catch (err) {
      Utils.toast(err.message || Language.t('error_api'), 'error');
    }
  };

  const searchByCoords = async (lat, lon) => {
    try {
      const lang = Language.getLanguage();
      const [wRes, fRes] = await Promise.all([
       API.get(`weather/coords?lat=${lat}&lon=${lon}&units=${units}&lang=${lang}`),
       API.get(`weather/forecast-coords?lat=${lat}&lon=${lon}&units=${units}&lang=${lang}`),
]);
      currentWeather  = wRes.data;
      currentForecast = fRes.data;
      renderCurrentWeather(currentWeather);
      renderForecast(currentForecast);
    } catch (err) {
      Utils.toast(err.message || Language.t('error_api'), 'error');
    }
  };

  const setUnits = (u) => {
    units = u;
    Storage.set('units', u);
    // Re-fetch with new units if we have a city
    const lastCity = Storage.get('last_city');
    if (lastCity) searchCity(lastCity);
  };

  const getCurrentWeather = () => currentWeather;

  // ── Render functions ──────────────────────────────────────

  const renderCurrentWeather = (data) => {
    const container = document.getElementById('weather-current');
    if (!container || !data) return;

    const icon  = Utils.weatherIcon(data.weather?.[0]?.icon || '01d');
    const temp  = Utils.formatTemp(data.main?.temp, units);
    const feels = Utils.formatTemp(data.main?.feels_like, units);
    const desc  = Utils.capitalize(data.weather?.[0]?.description || '');

    container.innerHTML = `
      <div class="weather-hero animate-slide-up">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
          <div>
            <div style="font-size:var(--text-lg);opacity:.85;margin-bottom:.5rem;">
              📍 ${data.name}, ${data.sys?.country || ''}
            </div>
            <div class="weather-hero-temp">${temp}</div>
            <div style="font-size:var(--text-xl);opacity:.85;margin-top:.5rem;">${desc}</div>
            <div style="font-size:var(--text-base);opacity:.7;margin-top:.5rem;">
              ${Language.t('feels_like')}: ${feels}
            </div>
          </div>
          <div class="weather-hero-icon animate-float">${icon}</div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:1rem;margin-top:2rem;padding-top:1.5rem;border-top:1px solid rgba(255,255,255,0.2);">
          ${_detailChip('💧', Language.t('humidity'),    `${data.main?.humidity}%`)}
          ${_detailChip('💨', Language.t('wind'),        `${Math.round(data.wind?.speed)} m/s ${Utils.windDir(data.wind?.deg || 0)}`)}
          ${_detailChip('👁️', Language.t('visibility'),  `${((data.visibility || 0)/1000).toFixed(1)} km`)}
          ${_detailChip('🌡️', Language.t('pressure'),    `${data.main?.pressure} hPa`)}
          ${_detailChip('🌅', Language.t('sunrise'),     Utils.formatTime(data.sys?.sunrise))}
          ${_detailChip('🌇', Language.t('sunset'),      Utils.formatTime(data.sys?.sunset))}
        </div>
      </div>
    `;
  };

  const renderForecast = (data) => {
    const container = document.getElementById('weather-forecast');
    if (!container || !data?.list) return;

    const days = Utils.groupForecastByDay(data.list);
    const t = Language.t;

    container.innerHTML = `
      <div class="card animate-slide-up delay-200" style="margin-top:1.5rem;">
        <h3 style="margin-bottom:1.25rem;font-size:var(--text-lg);">📅 ${t('forecast')}</h3>
        <div class="forecast-strip">
          ${days.map((day, i) => `
            <div class="forecast-item ${i === 0 ? 'active' : ''}">
              <div style="font-size:var(--text-xs);font-weight:600;color:var(--text-muted);margin-bottom:.5rem;text-transform:capitalize;">
                ${i === 0 ? t('today') : Utils.dayName(day.dt)}
              </div>
              <div style="font-size:2rem;margin:.5rem 0;">${Utils.weatherIcon(day.icon)}</div>
              <div style="font-size:var(--text-sm);font-weight:700;">${Math.round(day.temp_max)}°</div>
              <div style="font-size:var(--text-xs);color:var(--text-muted);">${Math.round(day.temp_min)}°</div>
              <div style="font-size:var(--text-xs);color:var(--text-secondary);margin-top:.5rem;">
                💧${day.humidity}%
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    `;
  };

  // ── Private helpers ───────────────────────────────────────

  const _detailChip = (icon, label, value) => `
    <div style="background:rgba(255,255,255,0.12);border-radius:12px;padding:.75rem;backdrop-filter:blur(4px);">
      <div style="font-size:1.25rem;margin-bottom:.3rem;">${icon}</div>
      <div style="font-size:.7rem;opacity:.7;text-transform:uppercase;letter-spacing:.05em;">${label}</div>
      <div style="font-weight:700;font-size:.95rem;margin-top:.2rem;">${value}</div>
    </div>
  `;

  return { searchCity, searchByCoords, setUnits, getCurrentWeather, renderCurrentWeather, renderForecast };
})();