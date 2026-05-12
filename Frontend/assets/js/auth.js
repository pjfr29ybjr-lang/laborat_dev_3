/**
 * Auth Module — login, register, logout, token management
 * weather-system/frontend/assets/js/auth.js
 */

const Auth = (() => {

  // ── State ────────────────────────────────────────────────
  let currentUser = null;

  const init = () => {
    currentUser = Storage.get('user');
  };

  const isLoggedIn = () => !!Storage.get('token');

  const getUser = () => currentUser || Storage.get('user');

  // ── Login ────────────────────────────────────────────────
  const login = async (email, password) => {
    const data = await API.post('auth/login', { email, password });
    _storeSession(data.data);
    return data.data;
  };

  // ── Register ─────────────────────────────────────────────
  const register = async (name, email, password, password_confirm) => {
    const data = await API.post('auth/register', { name, email, password, password_confirm });
    _storeSession(data.data);
    return data.data;
  };

  // ── Logout ────────────────────────────────────────────────
  const logout = () => {
    Storage.remove('token');
    Storage.remove('user');
    currentUser = null;
    Router.navigate('login');
  };

  // ── Fetch current user from backend ──────────────────────
  const refreshUser = async () => {
    try {
      const data = await API.get('auth/me');
      currentUser = data.data;
      Storage.set('user', currentUser);
      return currentUser;
    } catch (e) {
      logout();
    }
  };

  // ── Private ───────────────────────────────────────────────
  const _storeSession = ({ token, user }) => {
    Storage.set('token', token);
    Storage.set('user', user);
    currentUser = user;
  };

  // ── Guard: redirect to login if not authenticated ─────────
  const requireAuth = () => {
    if (!isLoggedIn()) {
      Router.navigate('login');
      return false;
    }
    return true;
  };

  return { init, isLoggedIn, getUser, login, register, logout, refreshUser, requireAuth };
})();