// ===== ERAN+ Public Panel - Core App Logic =====
const API_BASE_URL = "https://your-backend-domain.example";

const Auth = {
  getToken: () => localStorage.getItem('eranplus_token'),
  setToken: (t) => localStorage.setItem('eranplus_token', t),
  clear: () => { localStorage.removeItem('eranplus_token'); localStorage.removeItem('eranplus_user'); },
  getUser: () => JSON.parse(localStorage.getItem('eranplus_user') || 'null'),
  setUser: (u) => localStorage.setItem('eranplus_user', JSON.stringify(u)),
  isLoggedIn: () => !!Auth.getToken()
};

async function apiRequest(endpoint, method = 'GET', body = null, isForm = false) {
  const headers = {};
  const token = Auth.getToken();
  if (token) headers['Authorization'] = `Bearer ${token}`;
  if (!isForm) headers['Content-Type'] = 'application/json';

  const opts = { method, headers };
  if (body) opts.body = isForm ? body : JSON.stringify(body);

  const res = await fetch(`${API_BASE_URL}/api${endpoint}`, opts);
  const data = await res.json();
  if (!res.ok) throw new Error(data.message || 'Request failed');
  return data;
}

// Cart (client-side, per browser)
const Cart = {
  get: () => JSON.parse(localStorage.getItem('eranplus_cart') || '[]'),
  save: (items) => localStorage.setItem('eranplus_cart', JSON.stringify(items)),
  add: (product, qty = 1) => {
    const items = Cart.get();
    const existing = items.find(i => i.productId === product._id);
    if (existing) existing.quantity += qty;
    else items.push({ productId: product._id, name: product.name, price: product.price, image: product.image, quantity: qty });
    Cart.save(items);
  },
  update: (productId, qty) => {
    let items = Cart.get();
    if (qty <= 0) items = items.filter(i => i.productId !== productId);
    else items = items.map(i => i.productId === productId ? { ...i, quantity: qty } : i);
    Cart.save(items);
  },
  remove: (productId) => Cart.save(Cart.get().filter(i => i.productId !== productId)),
  clear: () => localStorage.removeItem('eranplus_cart'),
  total: () => Cart.get().reduce((sum, i) => sum + i.price * i.quantity, 0),
  count: () => Cart.get().reduce((sum, i) => sum + i.quantity, 0)
};

function requireAuth() {
  if (!Auth.isLoggedIn()) window.location.href = 'login.html';
}

function toast(msg, type = 'success') {
  const el = document.createElement('div');
  el.className = `ep-toast ep-toast-${type}`;
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(() => el.classList.add('show'), 10);
  setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 300); }, 2500);
}

function money(n) { return `Rs. ${Number(n).toLocaleString()}`; }

document.addEventListener('DOMContentLoaded', () => {
  const cartCountEl = document.querySelector('[data-cart-count]');
  if (cartCountEl) cartCountEl.textContent = Cart.count();

  const logoutBtn = document.querySelector('[data-logout]');
  if (logoutBtn) logoutBtn.addEventListener('click', () => { Auth.clear(); window.location.href = 'login.html'; });
});
