# Global Mart Demo

This project is a **demo/sandbox PHP application**. It does not process real payments, investments, withdrawals, or guaranteed returns.

## Requirements

PHP 8.1 or newer, the PDO MySQL extension, MySQL 8/MariaDB, and Apache or another PHP-capable web server.

## Installation

1. Copy the `global-mart-demo` folder into your web root.
2. Import `database/database.sql` into MySQL.
3. Update database values in `config/database.php`, or set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, and `APP_URL` environment variables.
4. From the project root, run `php database/seed.php` to create demo users.
5. Open `login.php`.

Demo credentials after seeding are `demo / demo123` and `admin / admin123` for the administrator panel at `admin/login.php`.

The database import is safe to run on a new database. The application uses prepared statements, CSRF tokens, password hashing, session regeneration after login, and output escaping. For production use, replace the demo payment flows with a properly reviewed payment system and set `APP_DEBUG=false`.
