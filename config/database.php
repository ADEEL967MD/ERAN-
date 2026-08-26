<?php
declare(strict_types=1);

// Update these values for your hosting environment, or set matching environment variables.
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'global_mart_demo');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
define('APP_NAME', getenv('APP_NAME') ?: 'Global Mart Demo');
define('APP_URL', rtrim(getenv('APP_URL') ?: 'http://localhost/global-mart-demo', '/'));
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

function getDB(): PDO {
    static $db = null;
    if ($db instanceof PDO) {
        return $db;
    }
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $db = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $db;
    } catch (PDOException $exception) {
        http_response_code(500);
        $message = APP_DEBUG ? $exception->getMessage() : 'Unable to connect to the database. Check your configuration.';
        exit('<h1>Database connection error</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>');
    }
}
