<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Driver\Exception\Exception as MongoException;

define('MONGODB_URI', trim((string)(getenv('MONGODB_URI') ?: getenv('MONGO_URI'))));
define('MONGODB_DATABASE', trim((string)(getenv('MONGODB_DATABASE') ?: getenv('DB_NAME') ?: 'global_mart_demo')));
define('APP_NAME', getenv('APP_NAME') ?: 'Global Mart Demo');
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));

function detectAppUrl(): string {
    $configured = trim((string)getenv('APP_URL'));
    if ($configured !== '') return rtrim($configured, '/');
    $forwardedProto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwardedProto === 'https';
    $host = preg_replace('/[^A-Za-z0-9.:-]/', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost')) ?: 'localhost';
    return ($isHttps ? 'https' : 'http') . '://' . $host;
}
define('APP_URL', detectAppUrl());

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
if (APP_DEBUG) { error_reporting(E_ALL); ini_set('display_errors', '1'); }
else { error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED); ini_set('display_errors', '0'); }

function getDB(): Database {
    static $db = null;
    if ($db instanceof Database) return $db;
    if (MONGODB_URI === '') {
        http_response_code(500);
        exit('<h1>Database connection error</h1><p>MONGODB_URI is not configured in Heroku Config Vars.</p>');
    }
    try {
        $client = new Client(MONGODB_URI, [], ['serverSelectionTimeoutMS' => 8000, 'connectTimeoutMS' => 8000]);
        $db = $client->selectDatabase(MONGODB_DATABASE);
        $db->command(['ping' => 1])->toArray();
        return $db;
    } catch (Throwable $exception) {
        http_response_code(500);
        $message = APP_DEBUG ? $exception->getMessage() : 'Unable to connect to MongoDB. Check MONGODB_URI and MONGODB_DATABASE in Heroku Config Vars.';
        exit('<h1>Database connection error</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>');
    }
}
