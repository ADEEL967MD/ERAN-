<?php
declare(strict_types=1);
// Edit only this file before uploading to InfinityFree.
const APP_URL = 'https://YOUR-DOMAIN';
const ADMIN_API_URL = APP_URL . '/admin/api';
const PUBLIC_PANEL_ORIGIN = APP_URL;
const API_SECRET = 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET';
const SETUP_KEY = 'CHANGE_THIS_SETUP_KEY_BEFORE_UPLOAD';
const DB_HOST = 'sqlXXX.infinityfree.com';
const DB_NAME = 'if0_XXXXXXXX_eran';
const DB_USER = 'if0_XXXXXXXX';
const DB_PASS = 'CHANGE_THIS_DATABASE_PASSWORD';
const UPLOAD_DIR = __DIR__ . '/../admin/storage/uploads';
const SESSION_NAME = 'eran_unified_session';
if (session_status() !== PHP_SESSION_ACTIVE) { $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'); session_name(SESSION_NAME); session_set_cookie_params(['httponly'=>true,'secure'=>$secure,'samesite'=>'Lax','path'=>'/']); session_start(); }
