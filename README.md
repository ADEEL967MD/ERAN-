# ERAN+ Unified InfinityFree Application

This is one uploadable PHP 8.x + MySQL/PDO project. The public panel opens at the domain root, for example `https://your-domain.example`, and the admin panel opens at `https://your-domain.example/admin/`. The public panel calls the internal admin API at `/admin/api`; no second domain, MongoDB, Composer, or SSH is required.

## InfinityFree upload steps

1. Create one InfinityFree website and one MySQL database. Copy the exact database host, database name, username, and password from the InfinityFree control panel.
2. Upload all contents of this folder into the website's `htdocs` directory using File Manager or FTP.
3. Edit only `config/config.php`: set `APP_URL` to the exact HTTPS site URL, fill `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS`, and replace `API_SECRET` and `SETUP_KEY` with long private values. `ADMIN_API_URL` and `PUBLIC_PANEL_ORIGIN` are derived automatically from `APP_URL`.
4. Open phpMyAdmin and import `admin/database/database.sql` into the selected database.
5. Visit `https://your-domain.example/admin/database/seed.php?key=YOUR_SETUP_KEY` once. This creates the admin account, ERAN+ settings, sample package, and payment method. Immediately delete `admin/database/seed.php` after successful setup.
6. Login at `/admin/login.php` using `admin` / `admin123`, then change the password and update settings. Public users use the root `/login.php` and `/register.php` pages.

## What is connected

The root public pages use their own session UI and server-side API client. The API is served internally by `admin/api/index.php` and reads the same MySQL database. Authentication, settings, payment methods, packages, wallet data, team data, simulated deposits, uploads, withdrawals, password changes, and statements use the same API/database flow. Admin management pages update settings without editing PHP.

## Important upload notes

InfinityFree database hostnames are account-specific; do not use `localhost`. Make sure the `admin/storage/uploads` directory is writable. Keep `config/config.php` private and never publish its database password or API secret. If the site is hosted in a subdirectory rather than the domain root, adjust `APP_URL` and test the rewrite rules.

All financial-looking values and transaction workflows are simulated until the owner supplies a verified payment provider, compliance review, KYC/AML controls, secure webhooks, refunds, and monitoring. This application does not transfer real money or make guaranteed earnings claims.

## Heroku deployment

Deploy this project from the repository root so Heroku sees the root `composer.json` and `Procfile`. The application reads `APP_URL`, `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `API_SECRET`, and `SETUP_KEY` from Heroku Config Vars. Set `APP_URL` to the exact Heroku app URL, set the four database values to a reachable MySQL/MariaDB provider, and use a long random `API_SECRET`. Do not upload an `.env` file or hard-code credentials.

The root public site is `/`; the admin login is `/admin/login.php`; the API is `/admin/api`. After the database is imported, run the seed script once, change the admin password, and remove `admin/database/seed.php`. If Heroku reports a generic Internal Server Error, open Heroku Logs and check the first PHP/Apache error; the deployment must use the repository root and PHP must have cURL, PDO, PDO_MySQL, and fileinfo available.
