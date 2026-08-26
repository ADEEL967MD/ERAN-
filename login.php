<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) redirect(appUrl('dashboard.php'));
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) $error='Your session expired. Please try again.';
    else { $result=loginUser(trim((string)post('username')), (string)post('password')); if($result['success']) redirect(appUrl('dashboard.php')); $error=$result['message']; }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login - <?= e(APP_NAME) ?></title><link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"></head><body class="auth-page"><main class="auth-card"><div class="auth-logo"><span class="brand-mark">GM</span></div><h1>Welcome back</h1><p class="muted">Sign in to your Global Mart demo account.</p><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><form method="post" class="form-stack"><?= csrfField() ?><label>Username or email<input name="username" autocomplete="username" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="btn primary full" type="submit">Sign in <i class="fas fa-arrow-right"></i></button></form><p class="auth-note">Demo user: <strong>demo</strong> / <strong>demo123</strong></p><p class="auth-switch">New here? <a href="register.php">Create an account</a></p></main></body></html>
