// ===== ERAN+ Admin Panel - Core Logic =====
const API_BASE_URL = "https://your-backend-domain.example";

const AdminAuth = {
  getToken: () => localStorage.getItem('eranplus_admin_token'),
  setToken: (t) => localStorage.setItem('eranplus_admin_token', t),
  clear: () => { localStorage.removeItem('eranplus_admin_token'); localStorage.removeItem('eranplus_admin_user'); },
  getUser: () => JSON.parse(localStorage.getItem('eranplus_admin_user') || 'null'),
  setUser: (u) => localStorage.setItem('eranplus_admin_user', JSON.stringify(u)),
  isLoggedIn: () => !!AdminAuth.getToken()
};

async function adminApi(endpoint, method = 'GET', body = null, isForm = false) {
  const headers = {};
  const token = AdminAuth.getToken();
  if (token) headers['Authorization'] = `Bearer ${token}`;
  if (!isForm) headers['Content-Type'] = 'application/json';
  const opts = { method, headers };
  if (body) opts.body = isForm ? body : JSON.stringify(body);
  const res = await fetch(`${API_BASE_URL}/api${endpoint}`, opts);
  const data = await res.json();
  if (!res.ok) throw new Error(data.message || 'Request failed');
  return data;
}

function requireAdminAuth() {
  if (!AdminAuth.isLoggedIn()) window.location.href = 'login.html';
}

function toast(msg, type = 'success') {
  const el = document.createElement('div');
  el.style.cssText = `position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:${type === 'error' ? '#c0392b' : '#0f1631'};color:#fff;padding:12px 20px;border-radius:12px;z-index:999;font-size:14px;`;
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 2500);
}

function money(n) { return `Rs. ${Number(n).toLocaleString()}`; }

document.addEventListener('DOMContentLoaded', () => {
  const logoutBtn = document.querySelector('[data-admin-logout]');
  if (logoutBtn) logoutBtn.addEventListener('click', () => { AdminAuth.clear(); window.location.href = 'login.html'; });
});
