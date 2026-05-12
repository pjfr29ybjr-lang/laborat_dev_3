/**
 * API Client — centralised HTTP requests with JWT
 * weather-system/frontend/assets/js/api.js
 */

const API = (() => {
  // api.js
const BASE_URL = 'http://localhost/weather-app/backend/index.php'; // Certifica-te que não tem / no fim

  const getToken = () => Storage.get('token');

  const request = async (method, endpoint, body = null, opts = {}) => {
    const token = getToken();
    const headers = { 'Content-Type': 'application/json' };
    if (token) headers['Authorization'] = `Bearer ${token}`;
    if (opts.headers) Object.assign(headers, opts.headers);

    const config = { method, headers };
    if (body && method !== 'GET') config.body = JSON.stringify(body);

    try {
      const res = await fetch(BASE_URL + endpoint, config);
      // Handle non-JSON responses (e.g. CSV download)
      if (opts.raw) return res;

      const data = await res.json();
      if (!res.ok) {
        throw { status: res.status, message: data.message || 'Erro desconhecido.', errors: data.errors };
      }
      return data;
    } catch (err) {
      if (err.status === 401) {
        Auth.logout();
        throw err;
      }
      throw err;
    }
  };

  return {
    get:    (endpoint, opts)       => request('GET',    endpoint, null, opts),
    post:   (endpoint, body, opts) => request('POST',   endpoint, body, opts),
    put:    (endpoint, body, opts) => request('PUT',    endpoint, body, opts),
    delete: (endpoint, opts)       => request('DELETE', endpoint, null, opts),
    download: async (endpoint)     => {
      const token = getToken();
      const url = `${BASE_URL}${endpoint}${endpoint.includes('?') ? '&' : '?'}token=${token}`;
      window.open(url, '_blank');
    },
  };
})();