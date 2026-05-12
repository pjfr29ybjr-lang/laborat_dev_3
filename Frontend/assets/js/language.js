/**
 * Language / i18n Module
 * weather-system/frontend/assets/js/language.js
 */

const Language = (() => {
  const translations = {
    pt: {
      // Nav
      dashboard:   'Painel',
      favorites:   'Favoritos',
      history:     'Histórico',
      settings:    'Configurações',
      profile:     'Perfil',
      logout:      'Sair',
      // Auth
      login:       'Entrar',
      register:    'Registrar',
      email:       'Email',
      password:    'Senha',
      name:        'Nome completo',
      confirm_pwd: 'Confirmar senha',
      forgot_pwd:  'Esqueceu a senha?',
      no_account:  'Não tem conta?',
      has_account: 'Já tem conta?',
      // Weather
      feels_like:  'Sensação térmica',
      humidity:    'Humidade',
      wind:        'Vento',
      visibility:  'Visibilidade',
      pressure:    'Pressão',
      sunrise:     'Nascer do sol',
      sunset:      'Pôr do sol',
      forecast:    'Previsão de 5 dias',
      today:       'Hoje',
      search_city: 'Pesquisar cidade...',
      add_favorite:'Adicionar aos favoritos',
      rem_favorite:'Remover dos favoritos',
      // Buttons
      save:        'Salvar',
      cancel:      'Cancelar',
      delete:      'Excluir',
      export_csv:  'Exportar CSV',
      export_pdf:  'Exportar PDF',
      clear_history:'Limpar histórico',
      // Messages
      loading:     'Carregando...',
      no_data:     'Nenhum dado encontrado.',
      error_api:   'Erro ao buscar dados do clima.',
      added_fav:   'Adicionado aos favoritos!',
      removed_fav: 'Removido dos favoritos.',
      history_cleared:'Histórico apagado.',
    },
    en: {
      dashboard:   'Dashboard',
      favorites:   'Favorites',
      history:     'History',
      settings:    'Settings',
      profile:     'Profile',
      logout:      'Logout',
      login:       'Login',
      register:    'Register',
      email:       'Email',
      password:    'Password',
      name:        'Full name',
      confirm_pwd: 'Confirm password',
      forgot_pwd:  'Forgot password?',
      no_account:  "Don't have an account?",
      has_account: 'Already have an account?',
      feels_like:  'Feels like',
      humidity:    'Humidity',
      wind:        'Wind',
      visibility:  'Visibility',
      pressure:    'Pressure',
      sunrise:     'Sunrise',
      sunset:      'Sunset',
      forecast:    '5-day forecast',
      today:       'Today',
      search_city: 'Search city...',
      add_favorite:'Add to favorites',
      rem_favorite:'Remove from favorites',
      save:        'Save',
      cancel:      'Cancel',
      delete:      'Delete',
      export_csv:  'Export CSV',
      export_pdf:  'Export PDF',
      clear_history:'Clear history',
      loading:     'Loading...',
      no_data:     'No data found.',
      error_api:   'Error fetching weather data.',
      added_fav:   'Added to favorites!',
      removed_fav: 'Removed from favorites.',
      history_cleared:'History cleared.',
    },
    es: {
      dashboard:   'Panel',
      favorites:   'Favoritos',
      history:     'Historial',
      settings:    'Configuración',
      profile:     'Perfil',
      logout:      'Salir',
      login:       'Iniciar sesión',
      register:    'Registrarse',
      email:       'Correo electrónico',
      password:    'Contraseña',
      name:        'Nombre completo',
      confirm_pwd: 'Confirmar contraseña',
      forgot_pwd:  '¿Olvidaste tu contraseña?',
      no_account:  '¿No tienes cuenta?',
      has_account: '¿Ya tienes cuenta?',
      feels_like:  'Sensación térmica',
      humidity:    'Humedad',
      wind:        'Viento',
      visibility:  'Visibilidad',
      pressure:    'Presión',
      sunrise:     'Amanecer',
      sunset:      'Atardecer',
      forecast:    'Pronóstico de 5 días',
      today:       'Hoy',
      search_city: 'Buscar ciudad...',
      add_favorite:'Agregar a favoritos',
      rem_favorite:'Quitar de favoritos',
      save:        'Guardar',
      cancel:      'Cancelar',
      delete:      'Eliminar',
      export_csv:  'Exportar CSV',
      export_pdf:  'Exportar PDF',
      clear_history:'Borrar historial',
      loading:     'Cargando...',
      no_data:     'No se encontraron datos.',
      error_api:   'Error al obtener datos del clima.',
      added_fav:   '¡Agregado a favoritos!',
      removed_fav: 'Quitado de favoritos.',
      history_cleared:'Historial borrado.',
    },
  };

  let current = Storage.get('language') || 'pt';

  const t = (key) => (translations[current] || translations.pt)[key] || key;

  const setLanguage = (lang) => {
    if (!translations[lang]) return;
    current = lang;
    Storage.set('language', lang);
    applyTranslations();
    document.documentElement.lang = lang;
  };

  const getLanguage = () => current;

  const applyTranslations = () => {
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      el.textContent = t(key);
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
      el.placeholder = t(el.getAttribute('data-i18n-placeholder'));
    });
  };

  return { t, setLanguage, getLanguage, applyTranslations };
})();