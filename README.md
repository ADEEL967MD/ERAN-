# ERAN+ — Full-Stack E-Commerce Application

Brand: **ERAN+** | Stack: HTML5/CSS3/Vanilla JS (frontends) + Node.js/Express + MongoDB Atlas (backend)

## Structure
```
eran-plus/
├── public-panel/    # Customer-facing website (deploy separately)
├── admin-panel/     # Admin dashboard (deploy separately)
└── backend/         # Node.js + Express API (deploy separately)
```

## 1. MongoDB Atlas Setup
1. Create a free cluster at https://cloud.mongodb.com
2. Create a database user with a strong password (Database Access)
3. Whitelist your deployment platform's IP, or `0.0.0.0/0` for simplicity (Network Access)
4. Copy your connection string (Connect → Drivers)

**Important:** Your Atlas password was pasted in plain text in an earlier conversation. Rotate/change it in Atlas → Database Access before going to production.

## 2. Backend Deployment (Railway / Render / Heroku)
```
cd backend
npm install
cp .env.example .env
# fill in MONGODB_URI, JWT_SECRET, ADMIN_EMAIL, ADMIN_PASSWORD, CLIENT_URL, ADMIN_URL
npm run dev        # local test
node utils/seedAdmin.js   # creates the admin account (run once)
```
Push `backend/` to its own GitHub repo, connect to Railway/Render/Heroku, add the same environment variables in the platform's dashboard, deploy. Note the live URL (e.g. `https://eran-plus-api.up.railway.app`).

## 3. Public Panel Deployment (Vercel / Netlify)
1. Open `public-panel/assets/js/app.js`
2. Set `API_BASE_URL` to your backend's live URL
3. Push `public-panel/` to its own GitHub repo → import into Vercel/Netlify (static site, no build step needed)

## 4. Admin Panel Deployment (Vercel / Netlify)
1. Open `admin-panel/assets/js/admin.js`
2. Set `API_BASE_URL` to your backend's live URL
3. Push `admin-panel/` to its own GitHub repo → import into Vercel/Netlify
4. Restrict access (basic auth at hosting level, or a private/unlisted URL) since anyone with the link can reach the login screen

## 5. First Admin Login
Run `node utils/seedAdmin.js` once (uses `ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env`), then log into `admin-panel/login.html` with those credentials. Change the password immediately after first login.

## Environment Variables (backend/.env)
See `backend/.env.example` — never commit the real `.env` file to GitHub.

## Security Notes
- Passwords hashed with bcrypt, never stored in plain text
- JWT-based auth, protected routes via middleware
- Helmet, CORS, rate limiting, input validation on the backend
- MongoDB URI and JWT secret exist only in backend environment variables — never in frontend code
- Product images validated by type (JPEG/PNG/WEBP) and size (max 5MB)
