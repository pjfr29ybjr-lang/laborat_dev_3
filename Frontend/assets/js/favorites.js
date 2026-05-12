/**
 * Favorites Module
 * weather-system/frontend/assets/js/favorites.js
 */

const Favorites = (() => {
  let list = [];

  const load = async () => {
    try {
      const res = await API.get('/favorites');
      list = res.data || [];
      return list;
    } catch (e) {
      return [];
    }
  };

  const add = async (city, country, lat, lon) => {
    try {
      await API.post('/favorites', { city_name: city, country, lat, lon });
      await load();
      Utils.toast(Language.t('added_fav'), 'success');
      return true;
    } catch (err) {
      Utils.toast(err.message || 'Erro ao adicionar.', 'error');
      return false;
    }
  };

  const remove = async (id) => {
    try {
      await API.delete(`/favorites/${id}`);
      list = list.filter(f => f.id !== id);
      Utils.toast(Language.t('removed_fav'), 'info');
      return true;
    } catch (err) {
      Utils.toast(err.message || 'Erro ao remover.', 'error');
      return false;
    }
  };

  const isFavorite = (city, country) =>
    list.some(f => f.city_name.toLowerCase() === city.toLowerCase() && f.country === country);

  const getList = () => list;

  const render = (container) => {
    if (!container) return;
    if (list.length === 0) {
      container.innerHTML = `
        <div class="text-center" style="padding:4rem 2rem;color:var(--text-muted);">
          <div style="font-size:4rem;margin-bottom:1rem;">⭐</div>
          <p style="font-size:var(--text-lg);">Nenhuma cidade favorita ainda.</p>
          <p style="margin-top:.5rem;font-size:var(--text-sm);">Pesquise uma cidade e adicione aos favoritos.</p>
        </div>`;
      return;
    }

    container.innerHTML = `
      <div class="grid grid-auto">
        ${list.map(f => `
          <div class="card hover-lift animate-fade-in" data-fav-id="${f.id}">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <div>
                <h3 style="font-size:var(--text-lg);">${f.city_name}</h3>
                <p style="color:var(--text-muted);font-size:var(--text-sm);">${f.country}</p>
              </div>
              <div style="display:flex;gap:.5rem;">
                <button class="neu-icon-btn" onclick="Weather.searchCity('${f.city_name}');Router.navigate('dashboard');"
                  title="Ver clima">🌤️</button>
                <button class="neu-icon-btn" onclick="Favorites._handleRemove(${f.id})"
                  title="Remover">🗑️</button>
              </div>
            </div>
            <div style="margin-top:1rem;font-size:var(--text-xs);color:var(--text-muted);">
              Adicionado em ${new Date(f.created_at).toLocaleDateString('pt-AO')}
            </div>
          </div>
        `).join('')}
      </div>`;
  };

  const _handleRemove = async (id) => {
    if (!confirm('Remover esta cidade dos favoritos?')) return;
    const ok = await remove(id);
    if (ok) {
      const container = document.getElementById('favorites-container');
      render(container);
    }
  };

  return { load, add, remove, isFavorite, getList, render, _handleRemove };
})();