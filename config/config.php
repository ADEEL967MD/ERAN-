<?php
declare(strict_types=1);
// Heroku/InfinityFree: prefer environment Config Vars; placeholders are only for local setup.
function env_value(string $key, string $fallback = ''): string { $value = getenv($key); return ($value === false || $value === '') ? $fallback : $value; }
define('APP_URL', rtrim(env_value('APP_URL', 'https://YOUR-DOMAIN'), '/'));
define('ADMIN_API_URL', APP_URL . '/admin/api');
define('PUBLIC_PANEL_ORIGIN', APP_URL);
define('API_SECRET', env_value('API_SECRET', 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET'));
define('SETUP_KEY', env_value('SETUP_KEY', 'CHANGE_THIS_SETUP_KEY_BEFORE_UPLOAD'));
define('DB_HOST', env_value('DB_HOST', 'sqlXXX.infinityfree.com'));
define('DB_NAME', env_value('DB_NAME', 'if0_XXXXXXXX_eran'));
define('DB_USER', env_value('DB_USER', 'if0_XXXXXXXX'));
define('DB_PASS', env_value('DB_PASS', 'CHANGE_THIS_DATABASE_PASSWORD'));
define('UPLOAD_DIR', __DIR__ . '/../admin/storage/uploads');
define('SESSION_NAME', 'eran_unified_session');
if (session_status() !== PHP_SESSION_ACTIVE) { $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'); session_name(SESSION_NAME); session_set_cookie_params(['httponly'=>true,'secure'=>$secure,'samesite'=>'Lax','path'=>'/']); session_start(); }
