javascript

/**
 * Dashboard Module
 * weather-system/frontend/assets/js/dashboard.js
 */

const Dashboard = (() => {

  const render = async () => {
    const main = document.getElementById('page-main');
    if (!main) return;

    const user = Auth.getUser();
    const defaultCity = user?.default_city || Storage.get('last_city') || 'Luanda';
    const lang = Language.getLanguage();
    const t = Language.t;

    main.innerHTML = `
      <div class="page-content page-enter">
        <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
          <div>
            <h1 style="margin-bottom:.25rem;">Olá, ${user?.name?.split(' ')[0] || 'Utilizador'} 👋</h1>
            <p style="color:var(--text-secondary);">Veja as condições climáticas em tempo real.</p>
          </div>
          <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
            <div style="position:relative;">
              <input type="text" id="city-search-input" class="form-input"
                style="padding-left:2.5rem;width:240px;"
                data-i18n-placeholder="search_city"
                placeholder="${t('search_city')}">
              <span style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);font-size:1.1rem;">🔍</span>
              <ul id="search-dropdown" class="search-dropdown hidden"></ul>
            </div>
            <div class="neu-tabs" style="min-width:140px;">
              <button class="neu-tab ${Storage.get('units') !== 'imperial' ? 'active' : ''}" onclick="Weather.setUnits('metric');this.closest('.neu-tabs').querySelectorAll('.neu-tab').forEach(b=>b.classList.remove('active'));this.classList.add('active');">°C</button>
              <button class="neu-tab ${Storage.get('units') === 'imperial' ? 'active' : ''}" onclick="Weather.setUnits('imperial');this.closest('.neu-tabs').querySelectorAll('.neu-tab').forEach(b=>b.classList.remove('active'));this.classList.add('active');">°F</button>
            </div>
            <button class="btn btn-secondary btn-sm" onclick="Dashboard.useMyLocation()">📍 Minha localização</button>
          </div>
        </div>

        <div id="weather-current">
          <div class="card" style="padding:3rem;text-align:center;">
            <div class="spinner" style="margin:0 auto 1rem;"></div>
            <p style="color:var(--text-muted);">${t('loading')}</p>
          </div>
        </div>

        <div id="weather-forecast"></div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;margin-top:1.5rem;" id="quick-favorites"></div>
      </div>
    `;

    Language.applyTranslations();
    _initSearch();
    Weather.searchCity(defaultCity);
    _renderQuickFavorites();
  };

  const useMyLocation = () => {
    if (!navigator.geolocation) {
      Utils.toast('Geolocalização não suportada.', 'warning');
      return;
    }
    navigator.geolocation.getCurrentPosition(
      pos => Weather.searchByCoords(pos.coords.latitude, pos.coords.longitude),
      ()  => Utils.toast('Não foi possível obter localização.', 'warning')
    );
  };

  // ── Private ───────────────────────────────────────────────

  const _initSearch = () => {
    const input    = document.getElementById('city-search-input');
    const dropdown = document.getElementById('search-dropdown');
    if (!input || !dropdown) return;

    const search = Utils.debounce(async (q) => {
      if (q.length < 2) { dropdown.classList.add('hidden'); return; }
      try {
        const res  = await API.get(`/weather/search?q=${encodeURIComponent(q)}`);
        const cities = res.data || [];
        if (!cities.length) { dropdown.classList.add('hidden'); return; }

        dropdown.innerHTML = cities.map(c =>
          `<li onclick="Weather.searchCity('${c.name}');document.getElementById('city-search-input').value='${c.name}';document.getElementById('search-dropdown').classList.add('hidden');">
             <strong>${c.name}</strong>, ${c.country} <small style="color:var(--text-muted);">${c.state || ''}</small>
           </li>`
        ).join('');
        dropdown.classList.remove('hidden');
      } catch (_) {}
    }, 350);

    input.addEventListener('input', e => search(e.target.value));
    input.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        Weather.searchCity(input.value);
        dropdown.classList.add('hidden');
      }
      if (e.key === 'Escape') dropdown.classList.add('hidden');
    });
    document.addEventListener('click', e => {
      if (!e.target.closest('#city-search-input') && !e.target.closest('#search-dropdown'))
        dropdown.classList.add('hidden');
    });
  };

  const _renderQuickFavorites = async () => {
    const container = document.getElementById('quick-favorites');
    if (!container) return;
    const favs = await Favorites.load();
    if (!favs.length) return;

    container.innerHTML = `
      <div style="grid-column:1/-1;display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem;">
        <span style="font-size:1.1rem;">⭐</span>
        <h3 style="font-size:var(--text-base);">Favoritos rápidos</h3>
      </div>
      ${favs.slice(0, 4).map(f => `
        <div class="neu-card hover-lift" style="padding:1rem;cursor:pointer;"
          onclick="Weather.searchCity('${f.city_name}');document.getElementById('city-search-input').value='${f.city_name}';">
          <div style="font-size:1.5rem;margin-bottom:.5rem;">🌤️</div>
          <div style="font-weight:700;font-size:var(--text-sm);">${f.city_name}</div>
          <div style="color:var(--text-muted);font-size:var(--text-xs);">${f.country}</div>
        </div>
      `).join('')}
    `;
  };

  return { render, useMyLocation };
})();
